# ADR-0008 — Multilingual item catalogue and one shared item search

**Status:** Accepted (2026-11-17) · extends ADR-0007

## Context
Kitchen and purchasing staff type item names in English, Tamil script and Tamil in English letters
(Tanglish), with many spellings (dal/dhal/daal, paruppu/parupu/parpu). Item selectors used a native
`<datalist>` (no ranking, no Tamil), the admin list used its own `LIKE`, and invoice matching had a
normaliser that stripped Tamil vowel signs.

## Decision
- **Data:** `inventory_items.name_ta`, `inventory_items.search_key` (phonetic key of the English name);
  `inventory_item_aliases.{language, alias_type, phonetic_key}`. A generic (no supplier) alias belongs to
  exactly one item — partial unique index `inv_alias_generic_unique ON (normalized_alias) WHERE vendor_id IS NULL`.
  Supplier invoice wording stays vendor-scoped and may repeat across items.
- **Text rules:** `App\Support\ItemText` is the only normaliser (keeps `\p{M}`, drops pack sizes, phonetic
  rules dh→d, th→t, aa→a, oo→u, doubled letters collapsed…). Keys are derived on model save.
- **Search:** `App\Services\Inventory\ItemSearchService` is the only item search. Ranking: exact English 100 ·
  exact Tamil 96 · exact alias 92 · whole word 80 · prefix 75 · contains 60 · fuzzy 45–50 (only when nothing
  else matched). `exact_id` is returned only for a single exact-tier hit; ambiguous words (e.g. "dal") return a
  list and are never auto-selected. Term index is cached and versioned; item/alias model events flush it.
  Used by: `inventory.items.search` (JSON, any signed-in user, no costs), the purchase/material-request grids
  and bill review via `resources/views/inventory/_item-picker.blade.php`, the admin item list, duplicate
  warnings, and `ItemMatchingService` (invoice lines).
- **Catalogue rules:** `ItemCatalogueService` (alias conflicts, duplicate groups). Never merges items.
- **Seed:** `InventoryCatalogueSeeder` (data in `database/seeders/data/kitchen_catalogue.php`), idempotent;
  reuses existing items (fills only a missing Tamil name), new items start at zero stock. Registered in DatabaseSeeder (safe because it never changes existing rows); run alone with
  `php artisan db:seed --class=InventoryCatalogueSeeder`. 279 items in 16 categories.
- **Navigation:** admin "Inventory" + "Central Store" groups merged into one **Store & Inventory** group;
  non-admin roles keep their policy-gated group under the same name. All route names unchanged.

## Consequences
- Rolling back the migration drops Tamil names and alias metadata (aliases themselves stay); re-running the
  seeder restores seeded Tamil names. Admin-entered Tamil names would be lost — back up first.
- Search runs in PHP over the cached term index (no pg_trgm dependency). Measured on PostgreSQL 14:
  267 items / 1,268 terms → p50 0.3 ms, p95 2.3 ms; 3,267 items / 10,268 terms → p50 2.7 ms, p95 19 ms,
  index build 245 ms (once per change).

**Evidence:** VERIFIED-REPO, VERIFIED-TEST (SQLite + PostgreSQL 14 scratch cluster). Tamil spellings in the seed
data are INFERRED from common Tamil Nadu usage — REQUIRES review by kitchen staff.
