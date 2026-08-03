<?php

namespace App\Http\Controllers\EventRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest\PublicSubmitEventRequestRequest;
use App\Models\EventRequest;
use App\Models\EventRequestMenuCategory;
use App\Services\EventRequest\EventRequestService;
use App\Services\EventRequest\EventRequestTokenService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class PublicEventRequestController extends Controller
{
    public function __construct(
        private readonly EventRequestTokenService $tokens,
        private readonly EventRequestService $service,
    ) {
    }

    public function show(string $token): View|Response
    {
        $eventRequest = $this->tokens->resolve($token);

        if (! $eventRequest) {
            return response()->view('event-request.public.invalid-link', [], 410);
        }

        $eventRequest->load('items');

        if (! $eventRequest->isEditableByClient()) {
            return view('event-request.public.status', compact('eventRequest', 'token'));
        }

        $categories = EventRequestMenuCategory::active()
            ->ordered()
            ->with(['activeItems' => fn ($q) => $q->orderBy('display_order')])
            ->get()
            ->filter(fn (EventRequestMenuCategory $category) => $category->activeItems->isNotEmpty())
            ->values();

        $selectedItemIds = $eventRequest->items->pluck('menu_item_id')->filter()->all();

        return view('event-request.public.wizard', compact('eventRequest', 'token', 'categories', 'selectedItemIds'));
    }

    public function submit(PublicSubmitEventRequestRequest $request, string $token): RedirectResponse|View
    {
        $eventRequest = $this->tokens->resolve($token);

        if (! $eventRequest || ! $eventRequest->isEditableByClient()) {
            return response()->view('event-request.public.invalid-link', [], 410);
        }

        $data = $request->safe()->except('menu_item_ids');

        $eventRequest = $this->service->submitFromClient($eventRequest, $data, $request->input('menu_item_ids', []));

        return redirect()->route('event-request.public.show', $token)
            ->with('justSubmitted', true)
            ->with('reference', $eventRequest->referenceNumber());
    }
}
