# ADR-0003 — PostgreSQL in production, SQLite in tests, driver-guarded raw SQL

**Status:** Accepted, with a known risk

## Context
Production DB is PostgreSQL (`.env.example`, raw `ALTER TABLE … CHECK` migrations). Tests run on SQLite `:memory:` (`phpunit.xml`).

## Decision
Enum-like changes use raw Postgres SQL wrapped in `if (DB::getDriverName() === 'pgsql')` (commit `d89b64a`: "replace enum()->change() with raw PostgreSQL ALTER TABLE"), migrations carry idempotency guards (commit `1297216`). Reports use Postgres functions (`TO_CHAR`, `EXTRACT`).

## Consequences
- Constraints (e.g. `expense_requests_status_check`) and defaults (e.g. GST `2.50` vs `3.00`) differ between test and prod DBs; tests cannot catch a status the CHECK rejects.
- Some report queries would fail on SQLite; they are untested.
- New statuses require a new raw migration that redefines the CHECK list.

**Reason for not running tests on Postgres: UNKNOWN.**

**Evidence:** migrations, `phpunit.xml` `VERIFIED-REPO`; prod uses PostgreSQL `PROD-VERIFIED` (maintainer notes); the default-value divergence is `VERIFIED-REPO` by reading migrations (no test asserts it); test suite currently 825 tests / 1 failing on SQLite (`VERIFIED-TEST`, run 2026-09-19).
