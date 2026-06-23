<?php
namespace App\Http\Controllers\MealRegister;

use App\Http\Controllers\Controller;
use App\Models\MealClient;
use App\Models\MealEntry;
use App\Models\MealEntryItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DailyMealEntryController extends Controller
{
    private array $mealTypes;

    public function __construct()
    {
        $this->mealTypes = MealEntryItem::mealTypes();
    }

    public function index(Request $request): View
    {
        $isEmployee = auth()->user()->role === 'employee';
        $query = MealEntry::with(['client', 'items'])
            ->orderByDesc('entry_date')
            ->orderBy('meal_client_id');

        if ($request->filled('client_id')) {
            $query->where('meal_client_id', $request->client_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('entry_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('entry_date', '<=', $request->to);
        }

        // Employee: last 7 days only
        if ($isEmployee) {
            $query->whereDate('entry_date', '>=', now()->subDays(6)->toDateString());
        }

        $entries = $query->paginate(30)->withQueryString();
        $clients = MealClient::active()->orderBy('name')->get();

        return view('meal-register.entries.index', compact('entries', 'clients', 'isEmployee'));
    }

    public function create(): View
    {
        $clients   = MealClient::active()->orderBy('name')->get();
        $mealTypes = $this->mealTypes;
        $today     = now()->toDateString();

        return view('meal-register.entries.create', compact('clients', 'mealTypes', 'today'));
    }

    /**
     * AJAX: load existing entry for (client_id, entry_date).
     * Returns null if no entry; never creates on GET.
     */
    public function loadEntry(Request $request): JsonResponse
    {
        $request->validate([
            'client_id'  => ['required', 'exists:meal_clients,id'],
            'entry_date' => ['required', 'date'],
        ]);

        $entry = MealEntry::with(['items', 'creator', 'plannedUpdater', 'actualUpdater'])
            ->where('meal_client_id', $request->client_id)
            ->whereDate('entry_date', $request->entry_date)
            ->first();

        if (!$entry) {
            return response()->json(['entry' => null]);
        }

        $items = [];
        foreach ($this->mealTypes as $key => $meta) {
            $item = $entry->items->firstWhere('meal_type', $key);
            $items[$key] = [
                'planned' => $item?->planned_count,
                'actual'  => $item?->actual_count,
            ];
        }

        return response()->json([
            'entry' => [
                'id'               => $entry->id,
                'items'            => $items,
                'remarks'          => $entry->remarks,
                'created_by_name'  => $entry->creator?->name,
                'planned_by_name'  => $entry->plannedUpdater?->name,
                'actual_by_name'   => $entry->actualUpdater?->name,
                'updated_at'       => $entry->updated_at?->format('d M Y, g:i A'),
            ],
        ]);
    }

    /**
     * Upsert: creates entry if not found, updates items for all submitted meal types.
     * Tracks planned_updated_by / actual_updated_by when values change.
     */
    public function save(Request $request): RedirectResponse
    {
        $mealTypeKeys = implode(',', array_keys($this->mealTypes));
        $data = $request->validate([
            'meal_client_id'        => ['required', 'exists:meal_clients,id'],
            'entry_date'            => ['required', 'date'],
            'remarks'               => ['nullable', 'string', 'max:500'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.meal_type'     => ['required', 'string', 'in:' . $mealTypeKeys],
            'items.*.planned_count' => ['nullable', 'integer', 'min:0'],
            'items.*.actual_count'  => ['nullable', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($data) {
            $userId = auth()->id();

            $entry = MealEntry::firstOrNew([
                'meal_client_id' => $data['meal_client_id'],
                'entry_date'     => $data['entry_date'],
            ]);

            $isNew = !$entry->exists;
            if ($isNew) {
                $entry->created_by = $userId;
            }
            $entry->updated_by = $userId;
            $entry->remarks    = $data['remarks'] ?? $entry->remarks;
            $entry->save();

            foreach ($data['items'] as $idx => $item) {
                $mealType   = $item['meal_type'];
                $newPlanned = isset($item['planned_count']) && $item['planned_count'] !== '' ? (int)$item['planned_count'] : null;
                $newActual  = isset($item['actual_count'])  && $item['actual_count']  !== '' ? (int)$item['actual_count']  : null;

                $existing = $entry->items()->where('meal_type', $mealType)->first();

                if ($existing) {
                    $updates = [];
                    if ($newPlanned !== null && $newPlanned !== $existing->planned_count) {
                        $updates['planned_count']      = $newPlanned;
                        $updates['planned_updated_by'] = $userId;
                        $entry->planned_updated_by     = $userId;
                    }
                    if ($newActual !== null && $newActual !== $existing->actual_count) {
                        $updates['actual_count']      = $newActual;
                        $updates['actual_updated_by'] = $userId;
                        $entry->actual_updated_by     = $userId;
                    }
                    if (!empty($updates)) {
                        $existing->update($updates);
                    }
                } else {
                    if ($newPlanned !== null) $entry->planned_updated_by = $userId;
                    if ($newActual !== null)  $entry->actual_updated_by  = $userId;
                    $entry->items()->create([
                        'meal_type'          => $mealType,
                        'planned_count'      => $newPlanned,
                        'actual_count'       => $newActual,
                        'sort_order'         => $this->mealTypes[$mealType]['sort'] ?? $idx,
                        'planned_updated_by' => $newPlanned !== null ? $userId : null,
                        'actual_updated_by'  => $newActual  !== null ? $userId : null,
                    ]);
                }
            }

            $entry->save(); // persist audit columns updated above
        });

        return redirect()->route('meal-register.entries.create', [
            'client_id'  => $data['meal_client_id'],
            'entry_date' => $data['entry_date'],
        ])->with('success', 'Entry saved successfully.');
    }

    public function show(MealEntry $entry): View
    {
        $entry->load(['client', 'items', 'creator', 'updater', 'plannedUpdater', 'actualUpdater']);
        $mealTypes = $this->mealTypes;
        return view('meal-register.entries.show', compact('entry', 'mealTypes'));
    }

    public function destroy(MealEntry $entry): RedirectResponse
    {
        $entry->delete();
        return redirect()->route('meal-register.entries.index')
            ->with('success', 'Entry deleted.');
    }

    public function previousDay(Request $request): JsonResponse
    {
        $clientId  = $request->query('client_id');
        $entryDate = $request->query('entry_date');

        if (!$clientId || !$entryDate) {
            return response()->json(['entry' => null]);
        }

        $yesterday = Carbon::parse($entryDate)->subDay()->toDateString();

        $entry = MealEntry::with('items')
            ->where('meal_client_id', $clientId)
            ->whereDate('entry_date', $yesterday)
            ->first();

        if (!$entry) {
            return response()->json(['entry' => null]);
        }

        return response()->json([
            'entry' => [
                'date'  => $yesterday,
                'items' => $entry->items->map(fn($i) => [
                    'meal_type'     => $i->meal_type,
                    'planned_count' => $i->planned_count,
                ])->values(),
            ],
        ]);
    }
}
