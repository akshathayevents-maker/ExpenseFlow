<?php

namespace App\Services\Inventory;

use App\Models\EventRequest;
use App\Models\HallBooking;
use App\Models\MaterialRequest;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

/**
 * The FINAL menu of a hall order, used to snapshot what a material request was
 * prepared for. Sources (first available): booking menu lines (customised),
 * linked event-request selections, selected plan(s).
 */
class HallMenuService
{
    public function snapshot(HallBooking $booking): array
    {
        $booking->loadMissing(['mealPlan', 'foodSplits']);
        $plans = $booking->uses_mixed_food && $booking->foodSplits->isNotEmpty()
            ? $booking->foodSplits->map(fn ($s) => ['plan' => $s->meal_plan_name, 'guests' => $s->guest_count])->values()->all()
            : ($booking->mealPlan ? [['plan' => $booking->mealPlan->name, 'guests' => $booking->number_of_people]] : []);

        $items = [];
        $source = 'plan';
        if ($booking->menuItems()->exists()) {
            $source = 'booking_menu';
            foreach ($booking->menuItems()->orderBy('sort_order')->get() as $m) {
                $items[] = ['name' => $m->name, 'change' => $m->change_type, 'note' => $m->notes, 'removed' => (bool) $m->is_removed];
            }
        } elseif ($er = EventRequest::with('items')->where('hall_booking_id', $booking->id)->latest('id')->first()) {
            $source = 'event_request';
            foreach ($er->items as $i) {
                $items[] = ['name' => $i->name_snapshot, 'change' => 'selected', 'note' => $i->category_name_snapshot, 'removed' => false];
            }
        }

        return [
            'captured_at' => now()->toIso8601String(),
            'source' => $source,
            'plans' => $plans,
            'guests' => $booking->number_of_people,
            'items' => $items,
        ];
    }

    /** Starting lines for the menu editor: saved menu, else event-request picks, else plan description lines. */
    public function draftLines(HallBooking $booking): array
    {
        if ($booking->menuItems()->exists()) {
            return $booking->menuItems->map(fn ($m) => $m->only(['name', 'meal_type', 'change_type', 'is_removed', 'notes']))->all();
        }
        if ($er = EventRequest::with('items')->where('hall_booking_id', $booking->id)->latest('id')->first()) {
            return $er->items->map(fn ($i) => ['name' => $i->name_snapshot, 'meal_type' => $er->meal_type, 'change_type' => 'plan', 'is_removed' => false, 'notes' => null])->all();
        }
        $booking->loadMissing('mealPlan', 'foodSplits.mealPlan');
        $plans = $booking->uses_mixed_food ? $booking->foodSplits->pluck('mealPlan')->filter() : collect([$booking->mealPlan])->filter();
        $lines = [];
        foreach ($plans as $plan) {
            foreach ($plan->dishList() as $name) {
                $lines[] = ['name' => $name, 'meal_type' => null, 'change_type' => 'plan', 'is_removed' => false, 'notes' => null];
            }
        }

        return $lines;
    }

    /**
     * Replace the booking's final menu. Never touches existing material requests;
     * returns approved/issued requests whose menu snapshot is now out of date
     * (their requesters are told to raise a revision).
     */
    public function saveMenu(HallBooking $booking, array $rows, User $user): array
    {
        return DB::transaction(function () use ($booking, $rows, $user) {
            $before = $this->snapshot($booking)['items'];
            $booking->menuItems()->delete();
            $i = 0;
            foreach ($rows as $row) {
                $name = trim((string) ($row['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $change = in_array($row['change_type'] ?? 'plan', ['plan', 'added', 'modified', 'removed'], true) ? $row['change_type'] : 'plan';
                $booking->menuItems()->create([
                    'name' => mb_substr($name, 0, 150),
                    'meal_type' => $row['meal_type'] ?? null,
                    'change_type' => $change,
                    'is_removed' => $change === 'removed',
                    'notes' => isset($row['notes']) ? mb_substr((string) $row['notes'], 0, 300) : null,
                    'sort_order' => $i++,
                    'updated_by' => $user->id,
                ]);
            }
            $after = $this->snapshot($booking->fresh())['items'];
            app(AuditLogService::class)->log('menu_updated', 'hall_booking', $booking->id, $booking->booking_reference,
                ['menu' => array_column($before, 'name')], ['menu' => array_map(fn ($m) => ($m['removed'] ? '-' : '').$m['name'], $after)]);

            $stale = MaterialRequest::where('hall_booking_id', $booking->id)
                ->whereIn('status', ['submitted', 'approved', 'issued'])->get()
                ->filter(fn ($r) => ($r->menu_snapshot['items'] ?? []) != $after);
            foreach ($stale as $r) {
                if ($r->requester) {
                    app(NotificationService::class)->send($r->requester, 'hall_menu_changed', "Menu changed: {$r->reference()}",
                        'The hall menu changed after this request. Create a revision if quantities must change.',
                        route('inventory.material-requests.show', $r));
                }
            }

            return $stale->values()->all();
        });
    }
}
