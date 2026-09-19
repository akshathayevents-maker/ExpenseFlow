# Architecture Decision Records

These ADRs record decisions that are **evident from the repository** (code, comments, commit messages). Where the original
reasoning is not recorded, the ADR says **Reason: UNKNOWN**. No historical rationale has been invented.
Status values: Accepted (in force in code), Superseded, Proposed (not implemented).

Index — see also [PROJECT_KNOWLEDGE.md](../../PROJECT_KNOWLEDGE.md) §37.

| ADR | Title | Status |
|---|---|---|
| [0001](0001-flat-apache-deployment.md) | Flat git-tree deployment served by Apache | Accepted |
| [0002](0002-committed-vite-build.md) | Vite build output is committed; no Node on production | Accepted |
| [0003](0003-postgres-prod-sqlite-tests.md) | PostgreSQL in production, SQLite in tests, driver-guarded raw SQL | Accepted (with known risk) |
| [0004](0004-public-access-via-signed-urls-and-tokens.md) | Public pages gated by signed URLs / random tokens | Accepted |
| [0005](0005-single-source-domain-services.md) | Single-source domain services for HR/payroll/money | Accepted |
| [0006](0006-in-app-notifications-only.md) | Notifications are in-app rows; no mail/queue delivery | Accepted (state, not necessarily intent) |

Template for new ADRs: Context → Decision → Evidence (files/commits) → Consequences → Reason if unknown → Status.
Add an ADR when a decision changes deployment, data model, security model, or a cross-cutting service.

## Evidence classes
Same scale as PROJECT_KNOWLEDGE.md: `VERIFIED-REPO`, `VERIFIED-TEST`, `PROD-VERIFIED`, `INFERRED`, `UNKNOWN`, `REQUIRES-PRODUCTION-VERIFICATION`. Every ADR ends with an **Evidence** line saying which class its key claims have.

## When to write / update an ADR (PROJECT_KNOWLEDGE §39 rule 6)
Add or update an ADR whenever a change alters: deployment topology, data model conventions (statuses, ledgers, DB engine), the security/access model (public surfaces, roles), a cross-cutting service (audit, notifications, settings), the queue/scheduler architecture, or the frontend build/asset strategy. Never rewrite history: mark the old ADR **Superseded by ADR-NNNN** and add the new one. If the reason is not recorded anywhere, write "Reason: UNKNOWN" — do not invent one.
