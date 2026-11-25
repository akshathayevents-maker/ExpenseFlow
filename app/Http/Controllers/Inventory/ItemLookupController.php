<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\Inventory\ItemMatchingService;
use App\Services\Inventory\ItemSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** JSON helpers for item selectors: typed search, and pasted rows -> ranked suggestions. */
class ItemLookupController extends Controller
{
    /** Shared item search (English / Tamil / Tanglish). Never returns costs or stock values. */
    public function search(Request $request, ItemSearchService $search): JsonResponse
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:25'],
        ]);

        return response()->json($search->search($data['q'], (int) ($data['limit'] ?? 10)));
    }

    public function match(Request $request, ItemMatchingService $matcher): JsonResponse
    {
        $data = $request->validate([
            'texts' => ['required', 'array', 'max:100'],
            'texts.*' => ['nullable', 'string', 'max:200'],
            'vendor_id' => ['nullable', 'integer'],
        ]);
        $out = [];
        foreach ($data['texts'] as $i => $text) {
            $suggestions = $matcher->suggest((string) $text, $data['vendor_id'] ?? null);
            $out[$i] = [
                'suggestions' => $suggestions,
                'auto' => $matcher->isAutoAcceptable($suggestions[0] ?? null) ? $suggestions[0]['item_id'] : null,
            ];
        }

        return response()->json(['results' => $out]);
    }
}
