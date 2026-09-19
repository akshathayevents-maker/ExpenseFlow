# Legacy: release-based / nginx deployment templates (NOT in use)

These files describe an earlier design: `/var/www/expenseflow/current -> releases/<ts>`, nginx + a dedicated
php-fpm pool, `supervisor` programs pointing at `current/`, and a release-pruning `cleanup.sh`.

Production (`expense.akshathay.com`) does not run that design: it is served by **Apache** from the **flat** git tree
`/var/www/akshathayexpense`, and `deployment/deploy.sh` (flat, hardened) is the only supported deploy path.

They were moved here (not deleted) so history and ideas are preserved. Do not apply them to the production
server. Their presence next to a flat `deploy.sh` was the architectural mismatch behind the deployment problems
(`deploy.sh` was rewritten flat in commit f2cfea1 while these stayed release-based).
If a release-based layout is ever wanted again, it needs a new, tested deploy/rollback pair plus an Apache
DocumentRoot change — do not mix it with the flat script.
