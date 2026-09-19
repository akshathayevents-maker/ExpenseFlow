# ADR-0004 — Public pages gated by signed URLs / random tokens

**Status:** Accepted

## Decision
1. **Payment page** (`/pay/{id}`, `/pay/{id}/qr`): Laravel `signed` middleware, 30-day `temporarySignedRoute` (`ExpenseRequest::paymentPageUrl()/qrUrl()`). Staff actions (`mark-paid`, `reject`, `proof`) deliberately have **no route auth middleware**; role/Gate is checked inside `PaymentRequestController` so the buttons still work in WhatsApp's in-app browser where session cookies may be missing (route comments in `routes/web.php`; commits `1d34d72`, `f50cae9`, `6f265ef`).
2. **Event Request Portal** (`/event-request/{token}`): 24-char random token in `event_request_public_tokens` (active/expiry/revoked); invalid → HTTP 410.
3. **QR image** is served through a controller with realpath containment; a separate unsigned `/storage/qr-codes/...` URL exists **only** for og:image link previews (accepted trade-off documented in `ExpenseRequest::qrOgImageUrl()`).

## Consequences
CSRF still applies to pay POSTs. Public POSTs are not rate-limited. Links are bearer secrets (30-day window). `payment_mode=wallet` on the public page is not backed by a wallet debit. Never loosen the in-controller checks.

**Evidence:** routes, controller, model `VERIFIED-REPO`; no automated test covers the pay page or event-request portal (none found) so behaviour is not `VERIFIED-TEST`; the WhatsApp in-app-browser rationale is `INFERRED` from route comments/commit subjects.
