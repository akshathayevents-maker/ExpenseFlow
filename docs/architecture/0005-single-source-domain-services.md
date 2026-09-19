# ADR-0005 — Single-source domain services for HR, payroll and money

**Status:** Accepted

## Decision (evident from docblocks)
- `PayableDaysCalculator`: only place that decides working days, weekly-offs, holidays and payable days (class docblock: "SINGLE source of truth … the Phase-2.5 architecture review identified the duplication risk").
- `LeaveBalanceService` (ledger-derived balance), `LeaveService`, `MonthlyPayableService` (money math only, never persists), `OvertimeCalculationService`, `AdvanceEligibilityService` (reuses `MonthlyPayableService`).
- Ledger tables are append-only (`employee_leave_ledger`, `advance_transactions`, `wallet_transactions`, `inventory_transactions`); cached balances (`employee_advances.outstanding_amount`, wallet `balance`) are written only inside the same DB transaction as the ledger row, with `lockForUpdate()` for wallets/stock.
- Attendance rules live in `EmployeeAttendanceService` and are enforced server-side, not just hidden in Blade.
- Attendance-first gate is route middleware (`EnsureAttendanceMarked`) so it cannot be bypassed via `redirect()->intended()`.

## Consequences
New features must call these services rather than re-deriving rules. Their docblocks contain locked business rules and documented ambiguities (e.g. approved-but-undisbursed advances) — read them before changing behaviour.
**Original business sign-off records: UNKNOWN** (docblocks say formulas were "explicitly specified by the business").

**Evidence:** docblocks/code `VERIFIED-REPO`; HR behaviour broadly `VERIFIED-TEST` (large attendance/leave/overtime/payroll suites, run 2026-09-19); business sign-off `UNKNOWN`.
