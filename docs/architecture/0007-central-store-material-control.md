# ADR 0007 — Central store material control (purchase → receipt → request → issue → usage)

**Status:** Accepted (2026-11-17)

## Context
Material losses (over-consumption, wastage, unrecorded withdrawals, overpriced purchases) could not be traced: OCR bill
import added stock directly, purchase plans never touched stock, and no link existed between stock movements and meals
or hall orders. Business decisions (confirmed by the owner): one central store; **approval of a material request
deducts stock immediately**; handover is a separate status; no compulsory recipes.

## Decision
1. `InventoryService::post()` is the single stock writer (row lock, append-only ledger, negative stock refused);
   `reverse()` writes a linked opposite row. Ledger `type` / item `unit` became strings (enum CHECK dropped).
2. Purchases: `purchases` / `purchase_items` (draft → submitted → approved → partially_received/received → completed).
   Stock moves only through `goods_receipts` (`GoodsReceiptService`). Uploaded bills become a purchase draft; the old
   "import to inventory" path now redirects to the purchase grid.
3. Item dictionary: `inventory_item_aliases` + `ItemMatchingService` (exact → supplier alias → alias → normalized → fuzzy,
   confidence 0–100, auto-select only ≥ `inventory.match_auto_select_confidence`).
4. Units: `config/inventory.php` dimensions (kg↔gram, litre↔ml, dozen→piece) + item rules in `inventory_unit_conversions`;
   anything else is refused.
5. Material requests: `material_requests` / `material_request_items`. Approval deducts (`material_issue`); handover
   records issued qty and collector without a second deduction (short handover → `reversal`); cancel after approval →
   `reversal`; returns → `material_return`; usage reconciliation `issued = returned + consumed + wasted + unexplained`
   with mandatory remarks and admin review above `inventory.wastage_review_percent`.
6. Links: meal register (`meal_entry_id` + `meal_type`), hall orders (`hall_booking_id` + `menu_snapshot`);
   hall final menu in `hall_booking_menu_items`; menu changes never edit requests — revisions (`revision_of_id`).
7. Roles: `users.role` unchanged; `users.is_storekeeper` flag (admins implicitly). Policies `PurchasePolicy`,
   `MaterialRequestPolicy`. Routes `inventory.*` (auth + verified).
8. Standards: recipes versioned (`version`, `parent_recipe_id`, `superseded_at`), `recipe_ingredients.quantity_per_guest`;
   requests store `recipe_version` and `suggested_base_quantity` next to what staff entered.

## Consequences
- "Available" stock already excludes approved-but-not-collected material; the stock page shows *physical* separately.
- Reports compute in PHP (works on SQLite and Postgres); fine for current volumes, revisit if ledger grows very large.
- Concurrency relies on Postgres `SELECT … FOR UPDATE`; SQLite tests prove the contract, not true parallelism.

**Evidence:** VERIFIED-REPO, VERIFIED-TEST (tests/Feature/Inventory/*). Production migration REQUIRES-PRODUCTION-VERIFICATION.
