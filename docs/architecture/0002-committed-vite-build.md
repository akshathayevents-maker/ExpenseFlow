# ADR-0002 — Vite build output is committed; no Node on production

**Status:** Accepted

## Context
An earlier deploy script built assets on the server (commit `7d0f813` added a NODE_OPTIONS memory limit and manifest validation for that build). The stated goal of the later change was "NO npm/Node.js on production" (commit `85caabd` message). The underlying incident/reason beyond that: **UNKNOWN**.

## Decision
Commit `public/build/` (commits `14d9259` "Track prebuilt frontend assets", `85caabd` "deploy.sh to use prebuilt frontend assets — REMOVED ALL FRONTEND BUILD STEPS").
`.gitignore` explicitly un-ignores `/public/build/`. `deploy.sh` verifies that the target commit contains a complete manifest and that
every referenced asset is tracked (`manifest_missing_commit`), and health-check compares the served `manifest.json` hash with the repo.

## Consequences
- Any change under `resources/css|js|scss` must be followed by `npm run build` locally and committing `public/build/`, or it will not ship.
- Merge conflicts in hashed build files are possible; rebuild instead of merging.
- The main admin UI does not depend on the bundle (Bootstrap comes from a CDN) — see PROJECT_KNOWLEDGE §12.

**Evidence:** `.gitignore`, commits, `deploy.sh` manifest checks `VERIFIED-REPO`; no Node on prod `INFERRED` from commit message and deploy script (server not inspected).
