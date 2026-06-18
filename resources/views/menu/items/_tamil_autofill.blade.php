{{--
  Tamil auto-fill partial.
  Requires: $translateUrl (string), $taPrefilled (bool)
  Targets IDs: #item_en, #item_ta, #miTaHint, #miTaHintText, #miRefreshBtn
--}}
<style>
.mi-ta-row {
    display: flex; gap: 8px; align-items: flex-start;
}
.mi-ta-row .mi-input { flex: 1; min-width: 0; }

.mi-refresh-btn {
    flex-shrink: 0;
    padding: 11px 13px;
    border: 1.5px solid var(--mi-border);
    border-radius: var(--mi-r-sm);
    background: transparent;
    color: var(--mi-muted);
    cursor: pointer;
    font-size: .82rem;
    font-weight: 600;
    white-space: nowrap;
    transition: all .12s;
    display: flex; align-items: center; gap: 5px;
    -webkit-tap-highlight-color: transparent;
    line-height: 1.1;
    touch-action: manipulation;
}
.mi-refresh-btn:hover { border-color: var(--mi-gold); color: var(--mi-gold); }
.mi-refresh-btn.mi-spinning i { animation: mi-spin .65s linear infinite; }
@keyframes mi-spin { to { transform: rotate(360deg); } }

.mi-ta-hint {
    font-size: .78rem; margin-top: 5px;
    display: none; align-items: center; gap: 5px; color: var(--mi-muted);
}
.mi-ta-hint.show { display: flex; }
.mi-ta-hint-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: #16a34a; flex-shrink: 0;
}
.mi-ta-hint.--modified .mi-ta-hint-dot { background: var(--mi-gold); }

@media (max-width: 420px) {
    .mi-refresh-btn .mi-refresh-label { display: none; }
    .mi-refresh-btn { padding: 11px; }
}
</style>

<script>
(function () {
'use strict';

var TRANSLATE_URL = @json($translateUrl);
var PREFILLED     = @json($taPrefilled);

var userModified  = PREFILLED;  // true = stop auto-sync
var lastSuggested = '';         // last value we wrote into Tamil field
var debounceTimer = null;

document.addEventListener('DOMContentLoaded', function () {
    var enInput    = document.getElementById('item_en');
    var taInput    = document.getElementById('item_ta');
    var hintEl     = document.getElementById('miTaHint');
    var hintText   = document.getElementById('miTaHintText');
    var refreshBtn = document.getElementById('miRefreshBtn');

    if (!enInput || !taInput) return;

    // ── Mark user-modified on any manual Tamil keystroke ──────────────────
    taInput.addEventListener('input', function () {
        if (taInput.value !== lastSuggested) {
            userModified = true;
            if (hintEl && hintText) {
                hintEl.classList.add('--modified');
                hintText.textContent = 'Manually edited.';
            }
        }
    });

    // ── Auto-fill on English input (600 ms debounce) ──────────────────────
    enInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        if (userModified) return;

        var val = enInput.value.trim();
        if (val.length < 2) {
            taInput.value = '';
            lastSuggested = '';
            hideHint();
            return;
        }
        debounceTimer = setTimeout(function () { fetchTranslation(val, false); }, 600);
    });

    // ── Refresh button — always re-generates, ignores userModified ────────
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function () {
            var val = enInput.value.trim();
            if (!val) return;
            userModified  = false;
            lastSuggested = '';
            fetchTranslation(val, true);
        });
    }

    // ── On load: if Tamil empty + English present → suggest ───────────────
    if (!PREFILLED && enInput.value.trim() && !taInput.value.trim()) {
        fetchTranslation(enInput.value.trim(), false);
    }

    // ─────────────────────────────────────────────────────────────────────

    function fetchTranslation(english, isExplicit) {
        if (refreshBtn) refreshBtn.classList.add('mi-spinning');

        var url = TRANSLATE_URL + '?q=' + encodeURIComponent(english);

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (data) {
                var suggestion = (data.tamil || '').trim();

                if (suggestion && (!userModified || isExplicit)) {
                    taInput.value = suggestion;
                    lastSuggested = suggestion;
                    userModified  = false;
                    showHint();
                } else if (isExplicit && !suggestion) {
                    showNoSuggestion();
                }
            })
            .catch(function () { /* translation optional — swallow errors */ })
            .finally(function () {
                if (refreshBtn) refreshBtn.classList.remove('mi-spinning');
            });
    }

    function showHint() {
        if (!hintEl || !hintText) return;
        hintEl.classList.remove('--modified');
        hintEl.classList.add('show');
        hintText.textContent = 'Auto-filled suggestion. You can edit this.';
    }

    function showNoSuggestion() {
        if (!hintEl || !hintText) return;
        hintEl.classList.add('show');
        hintText.classList.remove('--modified');
        hintText.textContent = 'No suggestion found — type Tamil directly.';
    }

    function hideHint() {
        if (hintEl) hintEl.classList.remove('show', '--modified');
    }
});

})();
</script>
