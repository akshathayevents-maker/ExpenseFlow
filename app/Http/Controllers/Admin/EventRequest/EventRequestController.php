<?php

namespace App\Http\Controllers\Admin\EventRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest\AdminUpdateEventRequestRequest;
use App\Http\Requests\EventRequest\CreateEventRequestShellRequest;
use App\Http\Requests\EventRequest\DecisionCommentRequest;
use App\Models\EventRequest;
use App\Models\EventRequestMenuCategory;
use App\Services\EventRequest\EventRequestService;
use App\Services\EventRequest\EventRequestTokenService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventRequestController extends Controller
{
    public function __construct(
        private readonly EventRequestService $service,
        private readonly EventRequestTokenService $tokens,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', EventRequest::class);

        $requests = EventRequest::query()
            ->status($request->input('status'))
            ->when($request->filled('client'), fn ($q) => $q->where('client_name', 'ilike', '%'.$request->input('client').'%'))
            ->when($request->filled('meal_type'), fn ($q) => $q->where('meal_type', $request->input('meal_type')))
            ->when($request->filled('menu_type'), fn ($q) => $q->where('menu_type', $request->input('menu_type')))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('event_date', $request->input('date')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = EventRequest::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('event-request.admin.index', compact('requests', 'counts'));
    }

    public function create(): View
    {
        $this->authorize('create', EventRequest::class);

        return view('event-request.admin.create');
    }

    public function store(CreateEventRequestShellRequest $request): RedirectResponse
    {
        $eventRequest = $this->service->createDraft($request->validated(), $request->user());

        return redirect()
            ->route('admin.event-requests.show', $eventRequest)
            ->with('status', 'Event request created. Share the link below with the client.');
    }

    public function show(EventRequest $eventRequest): View
    {
        $this->authorize('view', $eventRequest);

        $eventRequest->load(['items', 'revisions', 'hallBooking']);
        $token = $eventRequest->activeToken();
        $categories = EventRequestMenuCategory::active()->ordered()->with(['activeItems'])->get();
        $selectedItemIds = $eventRequest->items->pluck('menu_item_id')->filter()->all();

        // What the client last actually submitted, before any admin edits —
        // used to highlight what the admin has changed vs. the original ask.
        $clientBaselineItemIds = $this->clientBaselineItemIds($eventRequest, $selectedItemIds);

        return view('event-request.admin.show', compact(
            'eventRequest', 'token', 'categories', 'selectedItemIds', 'clientBaselineItemIds'
        ));
    }

    /**
     * @param  int[]  $fallback
     * @return int[]
     */
    private function clientBaselineItemIds(EventRequest $eventRequest, array $fallback): array
    {
        $revision = $eventRequest->revisions
            ->whereIn('action', ['client_submitted', 'client_resubmitted'])
            ->last();

        if (! $revision) {
            return $fallback;
        }

        return collect($revision->snapshot['items'] ?? [])
            ->pluck('menu_item_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function update(AdminUpdateEventRequestRequest $request, EventRequest $eventRequest): RedirectResponse
    {
        $data = $request->safe()->except('menu_item_ids');
        $this->service->adminUpdate($eventRequest, $data, $request->input('menu_item_ids'), $request->user());

        return back()->with('status', 'Event request updated.');
    }

    public function needChanges(DecisionCommentRequest $request, EventRequest $eventRequest): RedirectResponse
    {
        $this->service->requestChanges($eventRequest, $request->string('comment'), $request->user());

        return back()->with('status', 'Marked as Need Changes — the client can edit via their existing link.');
    }

    public function reject(DecisionCommentRequest $request, EventRequest $eventRequest): RedirectResponse
    {
        $this->service->reject($eventRequest, $request->string('comment'), $request->user());

        return back()->with('status', 'Event request rejected.');
    }

    public function approve(Request $request, EventRequest $eventRequest): RedirectResponse
    {
        $this->authorize('decide', $eventRequest);

        $this->service->approve($eventRequest, $request->user());

        return back()->with('status', 'Approved — the event now appears on the booking calendar.');
    }

    public function regenerateLink(Request $request, EventRequest $eventRequest): RedirectResponse
    {
        $this->authorize('update', $eventRequest);

        $this->tokens->regenerate($eventRequest);

        return back()->with('status', 'Link regenerated. The previous link no longer works.');
    }

    public function deactivateLink(Request $request, EventRequest $eventRequest): RedirectResponse
    {
        $this->authorize('update', $eventRequest);

        $this->tokens->deactivate($eventRequest);

        return back()->with('status', 'Link deactivated.');
    }
}
