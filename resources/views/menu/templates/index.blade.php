<x-admin-layout>
    <x-slot name="title">Menu Templates</x-slot>

    <style>
    .mt-shell { max-width: 900px; margin: 0 auto; padding: 20px 16px 80px; }
    .mt-hdr   { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
    .mt-title { font-size: 1.35rem; font-weight: 700; color: #1a1410; margin: 0; }
    .mt-back  { font-size: .85rem; color: #a0723a; text-decoration: none; }
    .mt-back:hover { text-decoration: underline; }

    .mt-empty { text-align: center; padding: 60px 20px; color: #9a8a7a; }
    .mt-empty i { font-size: 3rem; display: block; margin-bottom: 12px; color: #d0c4b4; }

    .mt-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }

    .mt-card {
        background: #fff;
        border: 1px solid #e8e2d8;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }
    .mt-card-name  { font-weight: 700; color: #1a1410; font-size: 1rem; }
    .mt-card-desc  { font-size: .83rem; color: #7a6e62; min-height: 18px; }
    .mt-card-count { font-size: .8rem; color: #a0723a; }
    .mt-card-foot  { display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap; }

    .mt-btn { padding: 6px 14px; border-radius: 7px; font-size: .83rem; font-weight: 600; border: none; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
    .mt-btn.--load   { background: #a0723a; color: #fff; }
    .mt-btn.--load:hover  { background: #8a6030; }
    .mt-btn.--del    { background: #fff3f3; color: #c0392b; border: 1px solid #fdd; }
    .mt-btn.--del:hover   { background: #ffe0e0; }

    /* Create form */
    .mt-create-card {
        background: #faf8f5;
        border: 2px dashed #d0c4b4;
        border-radius: 12px;
        padding: 20px;
    }
    .mt-form-label { font-size: .8rem; font-weight: 600; color: #7a6e62; text-transform: uppercase; letter-spacing: .05em; display: block; margin-bottom: 4px; }
    .mt-form-input { width: 100%; border: 1px solid #ddd; border-radius: 8px; padding: 8px 12px; font-size: .9rem; color: #1a1410; background: #fff; outline: none; }
    .mt-form-input:focus { border-color: #a0723a; box-shadow: 0 0 0 3px rgba(160,114,58,.12); }
    .mt-form-gap { margin-top: 10px; }
    .mt-save-btn { margin-top: 14px; background: #a0723a; color: #fff; border: none; border-radius: 8px; padding: 9px 22px; font-size: .9rem; font-weight: 600; cursor: pointer; }
    .mt-save-btn:hover { background: #8a6030; }
    </style>

    <div class="mt-shell">
        <div class="mt-hdr">
            <div>
                <a href="{{ route('menu.composer.index') }}" class="mt-back"><i class="bi bi-arrow-left"></i> Saved Menus</a>
                <h1 class="mt-title" style="margin-top:4px">Menu Templates</h1>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Create new template form --}}
        <div class="mt-create-card mb-4">
            <p style="font-size:.85rem;font-weight:700;color:#a0723a;margin-bottom:12px"><i class="bi bi-plus-circle me-1"></i> Save Current Composer State as Template</p>
            <p style="font-size:.82rem;color:#9a8a7a;margin-bottom:14px">
                Open a draft in the composer, build the menu structure, then come here and save it as a reusable template. Templates store only menu items — not venue, date, or guest count.
            </p>
            <label class="mt-form-label" for="tplName">Template Name</label>
            <input type="text" id="tplName" class="mt-form-input" placeholder="e.g. Standard Wedding Lunch" maxlength="255">
            <div class="mt-form-gap">
                <label class="mt-form-label" for="tplDesc">Description (optional)</label>
                <input type="text" id="tplDesc" class="mt-form-input" placeholder="e.g. 8-course South Indian lunch for 200 pax" maxlength="500">
            </div>
            <p style="font-size:.78rem;color:#b09070;margin-top:10px;margin-bottom:0">
                <i class="bi bi-info-circle me-1"></i>
                To save a specific menu structure: open that draft, then use the <strong>Save as Template</strong> button in the composer action bar.
            </p>
        </div>

        {{-- Templates grid --}}
        @if($templates->isEmpty())
            <div class="mt-empty">
                <i class="bi bi-collection"></i>
                <p style="font-size:1rem;font-weight:600;margin-bottom:6px">No templates yet</p>
                <p style="font-size:.85rem">Create a draft menu, then save it as a template from the composer.</p>
            </div>
        @else
            <div class="mt-grid">
                @foreach($templates as $tpl)
                <div class="mt-card">
                    <div class="mt-card-name">{{ $tpl->name }}</div>
                    @if($tpl->description)
                        <div class="mt-card-desc">{{ $tpl->description }}</div>
                    @endif
                    <div class="mt-card-count"><i class="bi bi-list-check me-1"></i>{{ $tpl->totalItems() }} items</div>
                    <div class="mt-card-foot">
                        <form method="POST" action="{{ route('menu.templates.load', $tpl) }}">
                            @csrf
                            <button type="submit" class="mt-btn --load">
                                <i class="bi bi-lightning-charge"></i> Load into New Draft
                            </button>
                        </form>
                        <form method="POST" action="{{ route('menu.templates.destroy', $tpl) }}"
                              onsubmit="return confirm('Delete template \'{{ addslashes($tpl->name) }}\'?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="mt-btn --del">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</x-admin-layout>
