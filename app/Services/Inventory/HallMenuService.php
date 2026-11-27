<?php

namespace App\Services\Inventory;

use App\Models\EventRequest;
use App\Models\HallBooking;
use App\Models\HallBookingMenuItem;
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
                $items[] = ['name' => $m->name, 'change' => $m->change_type, 'note' => $m->notes, 'removed' => (bool) $m->is_removed]
                    + array_filter(['replaces' => $m->replaces, 'servings' => $m->servings]);
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
            'customizations' => $source === 'booking_menu' ? $this->customizations($booking->menuItems()->orderBy('sort_order')->get()->all(), (int) $booking->number_of_people) : [],
        ];
    }

    /** Fields of one saved menu line, as the editors and the kitchen use them. */
    public const LINE_FIELDS = ['name', 'meal_type', 'change_type', 'is_removed', 'notes', 'replaces', 'servings', 'charge_type', 'charge_amount'];

    private function lineOf($m): array
    {
        $l = is_array($m) ? $m : $m->only(self::LINE_FIELDS);
        $l['charge_amount'] = round((float) ($l['charge_amount'] ?? 0), 2);

        return array_merge(array_fill_keys(self::LINE_FIELDS, null), $l, ['is_removed' => (bool) ($l['is_removed'] ?? false)]);
    }

    /**
     * Kitchen wording of what differs from the package, e.g.
     * ["Upma → Semiya", "Extra: Ice Cream – 200 servings", "Removed: Banana"].
     */
    public function customizations(iterable $lines, ?int $guests = null): array
    {
        $out = [];
        foreach ($lines as $m) {
            $l = $this->lineOf($m);
            $serv = $l['servings'] ? ' – '.$l['servings'].' servings' : ($guests ? ' – '.$guests.' servings' : '');
            $note = $l['notes'] ? ' ('.$l['notes'].')' : '';
            $out[] = match (true) {
                $l['is_removed'] || $l['change_type'] === 'removed' => 'Removed: '.$l['name'].$note,
                $l['change_type'] === 'replaced' => ($l['replaces'] ?: '?').' → '.$l['name'].($l['servings'] ? $serv : '').$note,
                $l['change_type'] === 'added' => 'Extra: '.$l['name'].$serv.$note,
                $l['change_type'] === 'modified' || $l['servings'] || $l['notes'] => $l['name'].($l['servings'] ? $serv : '').$note,
                default => null,
            };
        }

        return array_values(array_filter($out));
    }

    /** Billable menu extras of a booking: [['label','description','amount']]. */
    public function chargedExtras(HallBooking $booking): array
    {
        $guests = (int) $booking->number_of_people;
        $out = [];
        foreach ($booking->menuItems as $m) {
            if ($m->is_removed || ! in_array($m->charge_type, ['per_person', 'fixed'], true) || (float) $m->charge_amount <= 0) {
                continue;
            }
            $qty = $m->servings ?: $guests;
            $amount = $m->charge_type === 'fixed' ? (float) $m->charge_amount : (float) $m->charge_amount * $qty;
            $out[] = [
                'label' => 'Menu extra — '.$m->name,
                'description' => $m->charge_type === 'fixed' ? 'Agreed amount' : number_format($qty).' × ₹'.number_format((float) $m->charge_amount, 2),
                'amount' => round($amount, 2),
            ];
        }

        return $out;
    }

    /** Are these submitted rows the same menu the booking already has? (avoids needless rewrites / audit noise) */
    public function sameAsSaved(HallBooking $booking, array $rows): bool
    {
        if (! $booking->menuItems()->exists()) {
            return false;
        }
        $norm = fn ($l) => [mb_strtolower(trim((string) $l['name'])), $l['meal_type'] ?: null, $l['change_type'] ?: 'plan',
            $l['notes'] ?: null, $l['replaces'] ?: null, $l['servings'] ? (int) $l['servings'] : null,
            in_array($l['charge_type'], ['per_person', 'fixed'], true) ? $l['charge_type'] : null, round((float) $l['charge_amount'], 2)];
        $a = array_map(fn ($m) => $norm($this->lineOf($m)), $booking->menuItems()->orderBy('sort_order')->get()->all());
        $b = array_map(fn ($r) => $norm($this->lineOf($r)), array_values(array_filter($rows, fn ($r) => trim((string) ($r['name'] ?? '')) !== '')));

        return $a === $b;
    }

    /** Starting lines for the menu editor: saved menu, else event-request picks, else plan description lines. */
    public function draftLines(HallBooking $booking): array
    {
        if ($booking->menuItems()->exists()) {
            return $booking->menuItems()->orderBy('sort_order')->get()->map(fn ($m) => $this->lineOf($m))->all();
        }
        if ($er = EventRequest::with('items')->where('hall_booking_id', $booking->id)->latest('id')->first()) {
            return $er->items->map(fn ($i) => $this->lineOf(['name' => $i->name_snapshot, 'meal_type' => $er->meal_type, 'change_type' => 'plan']))->all();
        }
        $booking->loadMissing('mealPlan', 'foodSplits.mealPlan');
        $plans = $booking->uses_mixed_food ? $booking->foodSplits->pluck('mealPlan')->filter() : collect([$booking->mealPlan])->filter();
        $lines = [];
        foreach ($plans as $plan) {
            foreach ($plan->dishList() as $name) {
                $lines[] = $this->lineOf(['name' => $name, 'change_type' => 'plan']);
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
                $change = in_array($row['change_type'] ?? 'plan', array_keys(HallBookingMenuItem::CHANGES), true) ? $row['change_type'] : 'plan';
                $charge = in_array($row['charge_type'] ?? null, ['per_person', 'fixed'], true) ? $row['charge_type'] : null;
                $replaces = $change === 'replaced' ? trim((string) ($row['replaces'] ?? '')) : '';
                $booking->menuItems()->create([
                    'name' => mb_substr($name, 0, 150),
                    'meal_type' => ($row['meal_type'] ?? null) ?: null,
                    'change_type' => $change,
                    'is_removed' => $change === 'removed',
                    'notes' => ($row['notes'] ?? '') !== '' && $row['notes'] !== null ? mb_substr((string) $row['notes'], 0, 300) : null,
                    'replaces' => $replaces !== '' ? mb_substr($replaces, 0, 150) : null,
                    'servings' => ! empty($row['servings']) ? (int) $row['servings'] : null,
                    'charge_type' => $change === 'removed' ? null : $charge,
                    'charge_amount' => $change === 'removed' || ! $charge ? 0 : round((float) ($row['charge_amount'] ?? 0), 2),
                    'sort_order' => $i++,
                    'updated_by' => $user->id,
                ]);
            }
            $after = $this->snapshot($booking->fresh())['items'];
            app(AuditLogService::class)->log('menu_updated', 'hall_booking', $booking->id, $booking->booking_reference,
                ['menu' => array_column($before, 'name')], ['menu' => array_map(fn ($m) => ($m['removed'] ? '-' : '').(isset($m['replaces']) ? $m['replaces'].'→' : '').$m['name'], $after)]);

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
