# Kitchen & Hall Inventory Control — Phase 1 Audit and Plan

Date: 2026-11-17 · Branch `development` · Baseline `php artisan test`: **825 passed / 0 failed** (SQLite, 2,080 assertions).
Evidence labels follow CLAUDE.md: VERIFIED-REPO (read in code), VERIFIED-TEST (ran), INFERRED, REQUIRES-PRODUCTION-VERIFICATION.

---

## 1. Existing modules relevant to material control (VERIFIED-REPO)

| Module | Entry points | Tables | State |
|---|---|---|---|
| Inventory categories / items | `Admin\Inventory\InventoryCategoryController`, `InventoryItemController` (`admin.inventory.*`, admin only) | `inventory_categories`, `inventory_items` | CRUD + manual stock transaction form |
| Stock ledger | `InventoryService::addStock/deductStock/adjustStock` | `inventory_transactions` | Row-locked, balance_before/after, alerts |
| Stock alerts | `StockAlertController`, `InventoryService::checkAndCreateAlerts` | `inventory_stock_alerts` | Low/out-of-stock + manager notification |
| Purchase plans | `PurchasePlanController`, `PurchasePlanningService` | `purchase_plans`, `purchase_plan_items` | Suggestion list only; statuses draft→approved→ordered→completed; **never touches stock** |
| Bill upload / OCR | `InventoryBillController`, `InvoiceOCRService` → `OCR\PaddleOCRService` (python) + `OCR\InvoiceParserService` | `inventory_bill_uploads`, `inventory_bill_items` | Upload → OCR → review grid → **import adds stock directly** |
| Expense requests | `ExpenseRequestService`, settlement services | `expense_requests`, `vendors` | Money requests; unrelated to stock |
| Meal Register | `MealRegister\DailyMealEntryController` (`meal-register.entries.*`) | `meal_clients`, `meal_entries`, `meal_entry_items` | Planned vs actual **head-counts** per client/date/meal type |
| Hall bookings | `Hall\HallBookingController` (703 lines), `MealPlanController` | `hall_bookings`, `hall_booking_meals`, `hall_booking_food_splits`, `meal_plans` | Booking, pricing, invoice, payments |
| Event requests | `Admin\EventRequest\*`, `Services\EventRequest\*`, public portal | `event_requests`, `event_request_items` (+ revisions) | Client picks menu items → approval creates a `food_only` hall booking |
| Kitchen recipes | `Kitchen\RecipeController`, `Employee\KitchenController` (calculator) | `recipes`, `recipe_ingredients`, `recipe_sops` | Recipe library, `yield_per_batch`, ingredients optionally linked to inventory |
| Daily closing | `DailyClosingController` | `daily_closings` (+ vestigial `stock_*` columns) | Cash closing; no stock integration |
| Audit / notifications | `AuditLogService::log`, `NotificationService::send/sendToAdmins/sendToManagers` | `audit_logs`, `app_notifications` | Synchronous, in-app only |
| Analytics | `AnalyticsController@inventory` | reads `inventory_transactions` | Purchase/usage/wastage cost by date |

## 2. Database structure relevant here (VERIFIED-REPO, migrations)

- `inventory_items`: `unit` **enum** (`kg, gram, litre, ml, packet, piece, box, bundle, cylinder, dozen`) — DB CHECK on both SQLite and Postgres; `current_stock/minimum_stock/maximum_stock decimal(12,3)`; `average_cost decimal(12,2)`.
- `inventory_transactions`: `type` **enum** (`purchase, usage, adjustment, wastage, transfer`); `quantity` always positive; `balance_before/after`; `unit_cost`; loose polymorphic `reference_type/reference_id`; `created_by`.
- `purchase_plan_items.priority` enum; `purchase_plans.status` string.
- `inventory_bill_items`: free-text `item_name`, `unit`, `quantity decimal(12,3)`, optional `inventory_item_id`, `imported` flag.
- `meal_entries` unique `(meal_client_id, entry_date)`; `meal_entry_items` unique `(meal_entry_id, meal_type)`, meal types `breakfast|lunch|dinner` (model), integer `planned_count/actual_count`.
- `hall_bookings` has `meal_plan_id`, `number_of_people`, `has_breakfast/lunch/dinner`, `uses_mixed_food`; children `hall_booking_meals(meal_type, guest_count, special_requirements)`, `hall_booking_food_splits(meal_plan_id, meal_plan_name, guest_count, price_per_guest)`.
- `meal_plans`: `name, category, description, price_per_person` — **no menu-item rows**.
- `event_request_items`: snapshot of chosen menu items (`name_snapshot`, `category_name_snapshot`) — the only structured "final menu" in the system.
- `users.role` enum `admin|manager|employee`. **No storekeeper role.**
- `vendors`: name/phone/address — used by expenses; reusable as purchase suppliers.

## 3. Current stock calculation (VERIFIED-REPO)

- `current_stock` is a cached column; truth = append-only `inventory_transactions` with `balance_before/after`, written only inside `DB::transaction` + `lockForUpdate()`.
- `deductStock` refuses if `before < quantity` (negative stock prevented).
- `adjustStock` sets an absolute quantity and stores `abs(diff)` as `quantity`: the direction is only recoverable from `balance_after - balance_before`. `InventoryTransaction::isAddition()` treats every `adjustment` as an addition → **wrong sign for downward adjustments** in any consumer using it.
- Average cost: weighted on `addStock` with a unit cost.
- No concept of reserved/committed stock, incoming quantity or units other than the item's single `unit`.

## 4. Purchase and invoice workflow (VERIFIED-REPO)

1. Purchase plans are advisory; approval/ordering/completion never move stock.
2. Bill upload: file → sha256 duplicate check → synchronous python OCR → bill + lines `review_pending`.
3. Review page: item matching is `name LIKE %text%` or first-word LIKE (no aliases, no confidence, no supplier context).
4. **Import** (admin): creates missing inventory items on the fly, then `addStock(type=purchase)` for every line — **no approval step, no goods-receipt verification, no unit conversion** (bill unit ignored; quantity added in the item's unit even if the bill says grams vs kg).
5. Corrections overwrite lines (delete + recreate); only `extracted_json` keeps the original. No per-field correction history.

## 5. Meal workflow (VERIFIED-REPO)

Meal Register records per client/date planned and actual **head-counts** for breakfast/lunch/dinner. It has no preparation name, no menu and no link to stock. Entry upsert is open to every authenticated user (create/save routes have no role middleware; delete is `role.hall`). Employees see the last 7 days.

## 6. Hall order workflow and menu customisation (VERIFIED-REPO)

- A booking selects one `meal_plan` (or mixed splits of several plans with guest counts). Plans are a **name + price + description only**.
- **There is no per-booking menu and no menu customisation on hall bookings in this codebase.** Structured menu selection exists only in the Event Request portal (`event_request_items`), which on approval creates a `food_only` booking linked via `event_requests.hall_booking_id`.
- Consequence for Module Five: "final customised menu" must be introduced (see §8, Phase 5). Business statement that plans are customised per order is **not reflected in the repo** — REQUIRES-PRODUCTION-VERIFICATION that production runs the same code.

## 7. Approval, audit, notification, roles (VERIFIED-REPO)

- Approval pattern: status columns + `approved_by/approved_at`; policies exist for expenses/events/HR only — **no inventory policies**; inventory routes rely on `role.admin` middleware.
- `AuditLogService::log(action, module, refId, label, old, new)`; `NotificationService::send(user, type, title, message, url)` / `sendToAdmins` / `sendToManagers`.
- Roles: admin, manager (hall + approvals), employee. Kitchen staff = employees. A storekeeper must be modelled without breaking the role enum used across ~all modules.

## 8. Missing integrations (gap list)

1. No purchase document between "plan" and "stock": no bulk entry, approval, goods receipt, partial receipt, supplier link, payment info.
2. OCR import bypasses approval and receipt; no alias dictionary; no confidence.
3. No material request / issue / handover / return flow; stock is deducted ad hoc via `usage` form by admins only.
4. No link from stock movements to meals or hall bookings.
5. No consumption / wastage / variance reconciliation.
6. No unit conversion; single unit per item enforced by enum.
7. No reserved / incoming stock view.
8. Recipes are unversioned and disconnected from actual consumption.
9. No loss-control reports.

## 9. Bugs and technical debt found

| # | Finding | Impact |
|---|---|---|
| B1 | Bill import adds stock with no approval/receipt and ignores bill units | Stock inflated / wrong, no accountability |
| B2 | `InventoryTransaction::isAddition()` counts downward adjustments as additions | Wrong signs in movement displays |
| B3 | `InventoryItemController@index` search: `where(name ilike)->orWhere(sku ilike)` not grouped → filters (category/status) are bypassed when searching; `ilike` is Postgres-only (untestable on SQLite) | Wrong list results |
| B4 | Bill auto-match uses `LIKE %first word%` → "Big Onion" can map to "Big Basket Bag" etc. | Silent wrong mapping |
| B5 | Bill import silently creates new items in `InventoryCategory::first()` with unit default `piece` | Dictionary pollution |
| B6 | `monthly_spend` uses `created_at` (entry date), not purchase date | Report skew |
| B7 | `resolveAlertsIfRestocked` uses `auth()->id()` inside a service | Null in CLI contexts |
| B8 | Meal Register `save` has no role restriction while delete does (by design? INFERRED) | Note only |
| B9 | No inventory tests at all | Regressions undetected |

## 10. Reuse decisions

| Need | Reuse | Extend / new |
|---|---|---|
| Stock movement | `InventoryService` (single writer) | Add `receive()`, `issue()`, `returnToStock()`, `reverse()`, `adjustTo()` built on one private `post()`; widen `type` to string with new types; add `reversal_of_id`, `entered_quantity`, `entered_unit` |
| Suppliers | `vendors` | none |
| Invoice upload / OCR | `InventoryBillController` upload + `InvoiceOCRService` | Replace **import** with "Create purchase"; add `ItemMatchingService` + `inventory_item_aliases` |
| Purchase plans | kept as is (advisory) | `purchases.purchase_plan_id` optional link |
| Meal register | `meal_entries` / `meal_entry_items` | Material requests reference them; detail page shows materials |
| Hall | `hall_bookings`, `meal_plans`, `event_request_items` | `hall_booking_menu_items` (final menu) seeded from event request items or entered manually |
| Recipes | `recipes`, `recipe_ingredients` | Phase 7: add versioning + per-guest standard quantities |
| Audit / notifications | `AuditLogService`, `NotificationService` | — |
| Roles | `users.role` unchanged | `users.is_storekeeper` boolean (admins/managers implicitly allowed) + Gate abilities in `AppServiceProvider` |

## 11. Proposed database changes (all additive, idempotent guards, reversible)

1. `inventory_transactions.type` enum → `string(30)`; `inventory_items.unit` enum → `string(20)` (Postgres: drop CHECK; SQLite: column rebuild via `->change()`). Existing values untouched. Add `reversal_of_id`, `entered_quantity`, `entered_unit`, `reference_label`; index `(created_at)`.
2. `inventory_unit_conversions` (item-specific, e.g. 1 bag rice = 25 kg); global dimension conversions (kg↔gram, litre↔ml, dozen→piece) in `config/inventory.php`.
3. `inventory_item_aliases` (alias, normalized, vendor_id nullable, unit_hint, source, confirmations).
4. `purchases`, `purchase_items`, `goods_receipts`, `goods_receipt_items`.
5. `inventory_bill_items` + `match_confidence`, `match_method`, `original_item_name`; `inventory_bill_uploads.purchase_id`.
6. `material_requests`, `material_request_items`.
7. `users.is_storekeeper`.
8. Phase 5: `hall_booking_menu_items`. Phase 7: `recipes.version`, `recipes.parent_recipe_id`, `recipe_ingredients.quantity_per_guest`.

New transaction types: `purchase_receipt`, `material_issue`, `material_return`, `adjustment`, `correction`, `wastage`, `reversal` (legacy `purchase`, `usage`, `transfer` remain readable).

## 12. Stock rules adopted

- Physical stock = `current_stock`. Approval of a material request **deducts immediately** (`material_issue`) — so "approved, not handed over" stock is already out of `current_stock` and shown separately as *Awaiting handover*.
- Handover records `issued` quantity, collector and time; **no second deduction**. Issued < approved → automatic `reversal` for the difference.
- Cancel before handover → `reversal` of the full approved quantity.
- Returns after handover → `material_return` (+stock).
- Reconciliation per line: `issued = returned + consumed + wasted + unexplained`; remarks mandatory for wastage and unexplained; wastage above a threshold (default 10 % of issued) needs admin review. Kitchen wastage does not move stock (it already left the store); store-side spoilage uses the `wastage` transaction.
- Incoming = approved purchase quantity not yet received.
- Never update or delete a ledger row; corrections are new rows referencing the original.

## 13. Service architecture

```
InventoryService (single stock writer, row locks, alerts)
  ├─ UnitConversionService      (to base unit; refuses unknown conversions)
  ├─ PurchaseService             (draft/submit/approve/reject/cancel, duplicate guard)
  ├─ GoodsReceiptService         (partial receipts → InventoryService::receive)
  ├─ ItemMatchingService         (exact → normalized → supplier alias → confirmed mapping → fuzzy; confidence)
  ├─ InvoicePurchaseService      (bill → reviewable purchase draft; save aliases)
  ├─ MaterialRequestService      (submit/approve-partial/reject/handover/cancel/return/reconcile/revise)
  ├─ ConsumptionReportService    (variance, wastage, cost per guest, anomalies)
  └─ RecipeStandardService       (Phase 7: history → suggestions, versioning)
```

## 14. Phased plan

| Phase | Deliverables | Key tests |
|---|---|---|
| 2 | Migrations 1–5,7; unit conversion; `InventoryService` extension; purchases + bulk grid + approval + partial receipt; alias dictionary + matching; bill → purchase; remove direct import | bulk purchase, duplicate guard, receipt partial, alias match, correction saves alias, invoice never adds stock |
| 3 | Material requests (employee grid, admin line approval, deduction on approval, storekeeper handover, cancel/reversal, returns), role dashboards | deduction on approval, no double deduction, reversal, negative-stock + concurrency, authorization |
| 4 | Meal Register "Request materials" + materials panel + reconciliation screen | linkage, reconciliation math, remarks |
| 5 | Hall booking final menu, material requests per booking, revision workflow, cost per event | menu snapshot, revision keeps original |
| 6 | Loss-control dashboard + reports + filters | variance & anomaly queries |
| 7 | Recipe versioning, per-guest standards, suggested quantities | versioning, no retroactive change |

## 15. Risks and migration considerations

- Enum → string migration on Postgres must drop the CHECK constraints by name (`inventory_transactions_type_check`, `inventory_items_unit_check`); guarded with `IF EXISTS`. **Take a DB backup before deploying.**
- Removing direct bill import changes admin behaviour: already-imported bills stay `imported`; bills `review_pending` now go through "Create purchase".
- Deduct-on-approval means stock shown drops before goods physically leave; dashboards label this explicitly.
- SQLite tests cannot prove Postgres row-lock behaviour; concurrency is tested at the service contract level (re-reading locked rows) — true parallelism REQUIRES-PRODUCTION-VERIFICATION.
- Production has no Node: CSS/JS stay inline in Blade (existing pattern) so no rebuild needed unless `resources/css` changes.
- Hall menu customisation does not exist in code; Phase 5 introduces it (business confirmation advisable).

---

## 16. Implementation status (2026-11-17)

All seven phases are implemented on `development` (uncommitted at time of writing) and covered by `tests/Feature/Inventory/*`
(36 tests). Full suite: **865 passed / 0 failed** (SQLite). Migrations generate clean SQL on PostgreSQL via
`php artisan migrate --pretend`; they have **not been run** on any real database.

| Phase | Status | Tests |
|---|---|---|
| 2 Bulk purchase, receipt, dictionary, bill → purchase | Done | `PurchaseWorkflowTest`, `ItemMatchingTest`, `StockLedgerTest` |
| 3 Material requests, deduction on approval, handover, reversal, returns | Done | `MaterialRequestWorkflowTest` |
| 4 Meal Register link + usage | Done | `MealRegisterIntegrationTest` |
| 5 Hall final menu, snapshots, revisions, cost/margin | Done | `HallOrderIntegrationTest` |
| 6 Loss-control dashboard + 14 reports + CSV | Done | `LossControlReportTest` |
| 7 Versioned per-person standards + suggestions | Done | `RecipeStandardTest` |

Known limits / follow-ups:
- Concurrency proven at contract level on SQLite; real `FOR UPDATE` behaviour REQUIRES-PRODUCTION-VERIFICATION on Postgres.
- `AuditLogService` records `auth()->id()`; service calls from CLI/jobs must run with a signed-in user (none exist today).
- Postgres unique `(normalized_alias, vendor_id, inventory_item_id)` treats NULL vendor as distinct; duplicates are prevented in code (`firstOrNew`).
- Report "food revenue" uses `HallBooking::meal_cost` (plan price × guests or split amounts), excluding tax/services.
- Grid UI was verified by rendering tests only; manual check at ≤390 px in a browser still recommended.
