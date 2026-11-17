<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\Inventory\ItemMatchingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** JSON helper for pasted rows: free text -> ranked item suggestions. */
class ItemLookupController extends Controller
{
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
