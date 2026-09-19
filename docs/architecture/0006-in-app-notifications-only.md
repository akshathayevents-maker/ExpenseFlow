# ADR-0006 — Notifications are in-app rows; no mail/queue delivery

**Status:** Accepted as the *current state*; intent UNKNOWN

## Evidence
`NotificationService` inserts `app_notifications` rows synchronously. No `Mail`, `Notification`, `ShouldQueue`, or `dispatch()` usage exists in `app/` or `routes/`. `.env.example` mentions Brevo SMTP and Redis queues for production, and production has supervisor queue workers, but nothing in code uses them.

## Consequences
- Users only see notifications when they open the app.
- Password-reset email (Breeze) depends on the mail driver; with `MAIL_MAILER=log` no mail leaves the server (production mail driver REQUIRES-PRODUCTION-VERIFICATION).
- Introducing queued jobs/mail requires a deliberate decision on queue driver, retries/timeouts and worker command (see PROJECT_KNOWLEDGE §15, §35).

**Reason workers were provisioned without jobs: UNKNOWN** (possibly carried over from the retired release-based template, which defined `expenseflow-worker` and `expenseflow-ocr-worker` programs).

**Evidence:** absence of `dispatch(`/`ShouldQueue`/`Mail::` in `app/` and `routes/` `VERIFIED-REPO` (grep 2026-09-19); prod mail driver and worker purpose `REQUIRES-PRODUCTION-VERIFICATION`/`UNKNOWN`.
