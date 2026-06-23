<?php

namespace App\Http\Controllers\MealRegister;

use App\Http\Controllers\Controller;
use App\Models\DailyMealEntryItem;
use App\Models\MealClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MealClientController extends Controller
{
    public function index(Request $request): View
    {
        $query = MealClient::with('creator')->orderBy('name');

        if ($request->filled('q')) {
            $q = '%' . $request->q . '%';
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', $q)
                  ->orWhere('contact_person', 'like', $q)
                  ->orWhere('mobile', 'like', $q);
            });
        }

        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active');
        }

        $clients = $query->paginate(30)->withQueryString();

        return view('meal-register.clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('meal-register.clients.create');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150', 'unique:meal_clients,name'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'mobile'         => ['nullable', 'digits_between:10,15'],
            'email'          => ['nullable', 'email', 'max:200'],
            'address'        => ['nullable', 'string', 'max:500'],
            'gst_number'     => ['nullable', 'string', 'max:20'],
            'remarks'        => ['nullable', 'string', 'max:1000'],
        ]);

        $data['created_by'] = auth()->id();
        $client = MealClient::create($data);

        if ($request->expectsJson()) {
            return response()->json(['id' => $client->id, 'name' => $client->name], 201);
        }

        return redirect()->route('meal-register.clients.index')
            ->with('success', "Client \"{$client->name}\" created.");
    }

    public function show(MealClient $client): View
    {
        $client->load('entries.items');
        $mealTypes    = DailyMealEntryItem::mealTypes();
        $totalEntries = $client->entries->count();
        $totalPlanned = 0;
        $totalActual  = 0;
        $typeSummary  = [];

        foreach ($client->entries as $entry) {
            foreach ($entry->items as $item) {
                $totalPlanned += $item->planned_count;
                $totalActual  += ($item->actual_count ?? 0);
                $t = $item->meal_type;
                if (!isset($typeSummary[$t])) {
                    $typeSummary[$t] = ['planned' => 0, 'actual' => 0];
                }
                $typeSummary[$t]['planned'] += $item->planned_count;
                $typeSummary[$t]['actual']  += ($item->actual_count ?? 0);
            }
        }

        $recentEntries = $client->entries()
            ->with('items')
            ->orderByDesc('meal_date')
            ->limit(10)
            ->get();

        return view('meal-register.clients.show', compact(
            'client', 'mealTypes', 'totalEntries', 'totalPlanned',
            'totalActual', 'typeSummary', 'recentEntries'
        ));
    }

    public function edit(MealClient $client): View
    {
        return view('meal-register.clients.edit', compact('client'));
    }

    public function update(Request $request, MealClient $client): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150', 'unique:meal_clients,name,' . $client->id],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'mobile'         => ['nullable', 'digits_between:10,15'],
            'email'          => ['nullable', 'email', 'max:200'],
            'address'        => ['nullable', 'string', 'max:500'],
            'gst_number'     => ['nullable', 'string', 'max:20'],
            'remarks'        => ['nullable', 'string', 'max:1000'],
        ]);

        $client->update($data);

        return redirect()->route('meal-register.clients.show', $client)
            ->with('success', 'Client updated.');
    }

    public function toggleActive(MealClient $client): RedirectResponse
    {
        $client->update(['active' => !$client->active]);
        $status = $client->active ? 'activated' : 'deactivated';
        return back()->with('success', "\"{$client->name}\" {$status}.");
    }
}
