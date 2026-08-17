@php
    $old = fn ($key, $default = null) => old($key, $eventRequest->{$key} ?? $default);
    $categoryIcons = ['welcome drinks' => '🥂', 'soup' => '🍲', 'starter' => '🍢', 'main course' => '🍛', 'rice' => '🍚', 'indian bread' => '🫓', 'gravy' => '🍛', 'dessert' => '🍮', 'ice cream' => '🍨', 'beverage' => '🥤'];

    $menuData = $categories->map(function ($category) use ($categoryIcons) {
        return [
            'id'          => $category->id,
            'name'        => $category->name,
            'icon'        => $categoryIcons[strtolower($category->name)] ?? '🍽',
            'description' => $category->description,
            'items'       => $category->activeItems->map(fn ($item) => [
                'id'                  => $item->id,
                'name'                => $item->name,
                'description'         => $item->description,
                'is_veg'              => (bool) $item->is_veg,
                'price'               => (float) $item->price_per_person,
                'is_popular'          => (bool) $item->is_popular,
                'is_chef_recommended' => (bool) $item->is_chef_recommended,
            ])->values(),
        ];
    })->values();
@endphp
<x-event-request.public-layout title="Plan your event">

    <div class="erp-hero">
        <div>
            <div class="erp-eyebrow">Event Request &middot; {{ $eventRequest->referenceNumber() }}</div>
            <h1 class="erp-hero-title">
                @if($eventRequest->status === 'need_changes') Update your event @else Plan Your Event @endif
            </h1>
            <p class="erp-hero-sub">Build your menu in minutes. Live pricing updates instantly.</p>
        </div>
        <div class="erp-hero-side">
            <span class="erp-step-pill" id="stepIndicator">Step 1 of 3</span>
            <div class="erp-hero-icon"><i class="bi bi-cup-hot"></i></div>
        </div>
    </div>

    @if($eventRequest->status === 'need_changes' && $eventRequest->admin_comment)
        <div class="erp-card p-3 mb-4" style="background:var(--erp-warn-bg);border-color:transparent">
            <div class="fw-bold" style="color:var(--erp-warn);font-size:.85rem"><i class="bi bi-pencil-square me-1"></i>Changes requested</div>
            <div style="font-size:.85rem;color:var(--erp-ink-2)">{{ $eventRequest->admin_comment }}</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="erp-card p-3 mb-4" style="background:var(--erp-crit-bg);border-color:transparent">
            @foreach($errors->all() as $error)
                <div style="font-size:.85rem;color:var(--erp-crit)">{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="erp-progress" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="3">
        <div class="dot done" id="progDot1"></div>
        <div class="dot" id="progDot2"></div>
        <div class="dot" id="progDot3"></div>
    </div>

    {{-- Menu data for Step 2's category browser. Rendered once as JSON —
         item cards are built lazily in JS only when a category is opened,
         so hundreds of dishes never hit the DOM up front. --}}
    @php $jsonFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP; @endphp
    <script type="application/json" id="erpMenuData">{!! json_encode($menuData, $jsonFlags) !!}</script>
    <script type="application/json" id="erpSelectedIds">{!! json_encode(array_values($selectedItemIds), $jsonFlags) !!}</script>

    <form method="POST" action="{{ route('event-request.public.submit', $token) }}" id="erpForm" novalidate>
        @csrf
        <div id="selectedInputsContainer"></div>

        {{-- ══════ STEP 1 — EVENT DETAILS ══════ --}}
        <div class="erp-step active" id="step1">
            <div class="erp-card p-4">
                <div class="erp-section-title">Your event details</div>
                <p class="erp-section-sub">Just the essentials — we'll confirm the rest with you.</p>

                <div class="erp-field-group">
                    <div class="erp-float">
                        <i class="bi bi-person erp-icon"></i>
                        <input id="client_name" name="client_name" required placeholder=" " value="{{ $old('client_name') }}">
                        <label for="client_name">Client Name *</label>
                    </div>
                    <div class="erp-float">
                        <i class="bi bi-telephone erp-icon"></i>
                        <input id="client_mobile" name="client_mobile" required pattern="[0-9]{10}" maxlength="10" inputmode="numeric" placeholder=" " value="{{ $old('client_mobile') }}">
                        <label for="client_mobile">Mobile Number *</label>
                    </div>
                    <div class="erp-float">
                        <i class="bi bi-envelope erp-icon"></i>
                        <input type="email" id="client_email" name="client_email" placeholder=" " value="{{ $old('client_email') }}">
                        <label for="client_email">Email (optional)</label>
                    </div>
                    <div class="erp-float">
                        <i class="bi bi-tag erp-icon"></i>
                        <input id="event_name" name="event_name" placeholder=" " value="{{ $old('event_name') }}">
                        <label for="event_name">Event Name</label>
                    </div>
                    <div class="erp-float">
                        <i class="bi bi-calendar-event erp-icon"></i>
                        <input type="date" id="event_date" name="event_date" required placeholder=" " min="{{ today()->toDateString() }}" value="{{ $old('event_date') ? \Illuminate\Support\Carbon::parse($old('event_date'))->toDateString() : '' }}">
                        <label for="event_date">Event Date *</label>
                    </div>
                    <div class="erp-float">
                        <i class="bi bi-people erp-icon"></i>
                        <input type="number" id="guest_count" name="guest_count" min="1" max="20000" required placeholder=" " value="{{ $old('guest_count') }}">
                        <label for="guest_count">Expected Guest Count *</label>
                    </div>
                </div>

                <div class="mt-4">
                    <span class="erp-section-title" style="font-size:.86rem">Meal Type *</span>
                    <div class="erp-pill-row mt-2" id="mealTypePicker" role="radiogroup" aria-label="Meal type">
                        @foreach(\App\Models\EventRequest::mealTypes() as $value => $label)
                            <button type="button" class="erp-pill {{ $old('meal_type') === $value ? 'selected' : '' }}" data-value="{{ $value }}" role="radio" aria-checked="{{ $old('meal_type') === $value ? 'true' : 'false' }}">{{ $label }}</button>
                        @endforeach
                    </div>
                    <input type="hidden" name="meal_type" id="meal_type" value="{{ $old('meal_type') }}">
                </div>

                <div class="mt-4">
                    <span class="erp-section-title" style="font-size:.86rem">Menu Type *</span>
                    <div class="erp-menutype-row mt-2" id="menuTypePicker" role="radiogroup" aria-label="Menu type">
                        @php $menuTypeMeta = ['veg' => '🥬', 'non_veg' => '🍗', 'both' => '🍽']; @endphp
                        @foreach(\App\Models\EventRequest::menuTypes() as $value => $label)
                            <button type="button" class="erp-menutype-card {{ $old('menu_type') === $value ? 'selected' : '' }}" data-value="{{ $value }}" role="radio" aria-checked="{{ $old('menu_type') === $value ? 'true' : 'false' }}">
                                <span class="emoji">{{ $menuTypeMeta[$value] }}</span>
                                <span class="label">{{ $label }}</span>
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="menu_type" id="menu_type" value="{{ $old('menu_type') }}">
                </div>

                <div class="mt-4 erp-float">
                    <textarea id="special_instructions" name="special_instructions" placeholder=" ">{{ $old('special_instructions') }}</textarea>
                    <label for="special_instructions">Special Instructions</label>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="button" class="erp-btn erp-btn-primary" id="toStep2Btn">Continue to menu <i class="bi bi-arrow-right"></i></button>
                </div>
            </div>
        </div>

        {{-- ══════ STEP 2 — CATEGORY BROWSER ══════ --}}
        <div class="erp-step" id="step2">
            <div class="erp-builder-layout">
                <main>
                    <div class="erp-card p-4 mb-3">
                        <div class="erp-section-title">Build your menu</div>
                        <p class="erp-section-sub mb-0">Open a category, add dishes, and come back — repeat until your menu feels right.</p>
                    </div>

                    @if($categories->isEmpty())
                        <div class="erp-card erp-empty">
                            <div class="glyph"><i class="bi bi-emoji-frown"></i></div>
                            <div class="title">No menu items available</div>
                            <div class="body">Please contact the administrator to set up the menu for this event.</div>
                        </div>
                    @else
                        <div class="erp-filter-tabs" id="globalFilterTabs" role="radiogroup" aria-label="Diet filter">
                            <button type="button" class="erp-filter-tab selected" data-filter="all">All</button>
                            <button type="button" class="erp-filter-tab" data-filter="veg">Veg</button>
                            <button type="button" class="erp-filter-tab" data-filter="non_veg">Non-Veg</button>
                            <button type="button" class="erp-filter-tab" data-filter="popular">Popular</button>
                        </div>

                        <div class="erp-cat-list" id="categoryList"></div>
                    @endif

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="erp-btn erp-btn-ghost" id="backToStep1Btn"><i class="bi bi-arrow-left"></i> Back</button>
                        <button type="button" class="erp-btn erp-btn-gold" id="toStep3Btn" disabled>Review order <i class="bi bi-arrow-right"></i></button>
                    </div>
                </main>

                {{-- Desktop sticky summary --}}
                <aside class="erp-card erp-summary-rail p-4">
                    <div class="erp-section-title" style="font-size:.9rem;margin-bottom:14px">Live summary</div>
                    <div class="erp-summary-line"><span>Guests</span><strong id="sumGuests">0</strong></div>
                    <div class="erp-summary-line"><span>Selected items</span><strong id="sumCount">0</strong></div>
                    <div class="erp-summary-line"><span>Cost per person</span><strong id="sumPerPerson">₹0</strong></div>
                    <div class="erp-summary-line"><span>Subtotal</span><strong id="sumSubtotal">₹0</strong></div>
                    <div class="erp-summary-line"><span>CGST @ 2.5%</span><strong id="sumCgst">₹0</strong></div>
                    <div class="erp-summary-line"><span>SGST @ 2.5%</span><strong id="sumSgst">₹0</strong></div>
                    <div class="erp-summary-total">
                        <span style="font-size:.85rem;color:var(--erp-ink-2);font-weight:650">Estimated total</span>
                        <strong id="sumTotal">₹0</strong>
                    </div>
                    <button type="button" class="erp-btn erp-btn-gold erp-btn-block mt-3" id="desktopReviewBtn" disabled>Review &amp; submit <i class="bi bi-arrow-right"></i></button>
                </aside>
            </div>
        </div>

        {{-- ══════ STEP 3 — REVIEW & SUBMIT ══════ --}}
        <div class="erp-step" id="step3">
            <div class="erp-card p-4">
                <div class="erp-section-title">Review your request</div>
                <p class="erp-section-sub">Double-check everything before you submit.</p>

                <div class="erp-review-recap" id="reviewRecap"></div>

                <div id="reviewGroups"></div>

                <div class="erp-review-line">
                    <span class="fw-bold small text-muted">Per person cost</span>
                    <strong id="reviewPerPerson">₹0</strong>
                </div>
                <div class="erp-review-line">
                    <span class="fw-bold small text-muted">Subtotal</span>
                    <strong id="reviewSubtotal">₹0</strong>
                </div>
                <div class="erp-review-line">
                    <span class="fw-bold small text-muted">CGST @ 2.5%</span>
                    <strong id="reviewCgst">₹0</strong>
                </div>
                <div class="erp-review-line">
                    <span class="fw-bold small text-muted">SGST @ 2.5%</span>
                    <strong id="reviewSgst">₹0</strong>
                </div>
                <div class="erp-status-total-bar">
                    <span class="fw-bold small text-muted">Estimated total</span>
                    <strong id="reviewTotal" style="font-size:1.2rem">₹0</strong>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="erp-btn erp-btn-ghost" id="backToStep2Btn"><i class="bi bi-arrow-left"></i> Back to menu</button>
                    <button type="submit" class="erp-btn erp-btn-gold" id="submitBtn"><i class="bi bi-check2"></i> Submit request</button>
                </div>
            </div>
        </div>
    </form>

    {{-- Full-screen mobile category selector --}}
    <div class="erp-cat-overlay" id="catOverlay">
        <div class="erp-cat-overlay-header">
            <div class="erp-cat-overlay-title-row">
                <button type="button" class="erp-cat-overlay-back" id="catOverlayBack" aria-label="Back to categories"><i class="bi bi-arrow-left"></i></button>
                <div class="erp-cat-overlay-title" id="catOverlayTitle">Category</div>
            </div>
        </div>
        <div class="erp-cat-overlay-body" id="catOverlayBody"></div>
        <div class="erp-cat-overlay-footer">
            <button type="button" class="erp-btn erp-btn-primary erp-btn-block" id="catOverlayDone">Done</button>
        </div>
    </div>

    {{-- Mobile sticky compact bar (Step 2 only) --}}
    <div class="erp-stickybar" id="mobileStickyBar">
        <div class="erp-stickybar-info">
            <span class="erp-stickybar-count"><span id="barCount">0</span> Items Selected</span>
            <span class="erp-stickybar-perperson"><span id="barPerPerson">₹0</span><span class="unit">/person</span></span>
        </div>
        <div class="erp-stickybar-actions">
            <span class="erp-stickybar-total-label">Total <span id="barTotal">₹0</span></span>
            <button type="button" class="erp-btn erp-btn-gold" id="viewSummaryBtn">View Summary</button>
        </div>
    </div>

    {{-- Bottom sheet — full breakdown, opened from "View Summary" --}}
    <div class="erp-sheet-backdrop" id="mobileSheetBackdrop"></div>
    <div class="erp-sheet" id="mobileSheet">
        <div class="erp-sheet-handle"></div>
        <div class="erp-sheet-row">
            <div>
                <span class="erp-sheet-total-label d-block">Estimated total</span>
                <span class="erp-sheet-total-value" id="sheetTotal">₹0</span>
            </div>
            <button type="button" class="erp-btn erp-btn-ghost erp-btn-icon" id="sheetClose" aria-label="Close summary"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="erp-sheet-detail">
            <div class="erp-summary-line"><span>Guests</span><strong id="sheetGuests">0</strong></div>
            <div class="erp-summary-line"><span>Selected items</span><strong id="sheetCount">0</strong></div>
            <div class="erp-summary-line"><span>Cost per person</span><strong id="sheetPerPerson">₹0</strong></div>
            <div class="erp-summary-line"><span>Subtotal</span><strong id="sheetSubtotal">₹0</strong></div>
            <div class="erp-summary-line"><span>CGST @ 2.5%</span><strong id="sheetCgst">₹0</strong></div>
            <div class="erp-summary-line"><span>SGST @ 2.5%</span><strong id="sheetSgst">₹0</strong></div>
            <div class="erp-sheet-selected-list" id="sheetSelectedList"></div>
            <button type="button" class="erp-btn erp-btn-gold erp-btn-block mt-3" id="sheetActionBtn">Continue</button>
        </div>
    </div>

</x-event-request.public-layout>
