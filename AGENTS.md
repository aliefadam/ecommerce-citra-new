# Repository Agent Guide

## Stack and structure

- Laravel 13 on PHP 8.3; Eloquent models live in `app/Models`, HTTP controllers/middleware/resources in `app/Http`, domain operations in `app/Services`, and routes in `routes/`.
- The UI is server-rendered Blade with Tailwind CSS 4, Alpine.js, and Vite. Reusable Blade UI components are under `resources/views/components/ui`.
- Production uses MySQL. PHPUnit feature/unit tests use in-memory SQLite; Playwright covers browser flows.
- This is a multi-company ecommerce application. Company isolation, authorization, prices, stock, payments, and customer data are trust boundaries.

## Working method

- **TOKEN-EFFICIENT MODE:** SEARCH -> IDENTIFY -> READ MINIMUM CONTEXT -> MODIFY -> VERIFY -> STOP.
- Search first with a specific symbol, route, table, error, or test name. Start with the 3-5 most likely files and reassess before expanding.
- Read only relevant sections of large files. Do not recursively explore or inspect `vendor/`, `node_modules/`, `storage/`, generated output, binaries, caches, or logs unless evidence requires it.
- Reuse context already gathered; do not repeatedly read unchanged files. Bound command output and stop exploring once there is enough evidence for a safe change.
- Load only the `.agents/skills/*/SKILL.md` whose description matches the task. Cross-domain tasks may use more than one; never load all skills speculatively.
- Never guess schemas, columns, relationships, signatures, routes, or existing behavior. Search and read their authoritative source when they affect correctness.

## Change rules

- **Make the smallest correct change.** Follow nearby patterns and preserve public APIs unless the request requires a change.
- Do not perform unrelated refactors, renames, dependency swaps, broad formatting, or speculative abstractions. Reuse an existing service, helper, component, and design pattern when suitable.
- Keep controllers focused and put reusable domain logic in the existing service layer. Use Eloquent relationships/scopes and API resources consistently with neighboring code.
- Preserve LF, four-space indentation, Laravel naming conventions, and the style enforced by Pint. Never expose secrets or commit `.env` values.
- Do not weaken validation, authorization, company scoping, transaction boundaries, or server-side calculation to make a test pass.

## Verification and handoff

- Verify narrowly: syntax/static check where useful, then the closest unit/feature/browser test; broaden only when the change has wider impact.
- Backend tests: `php artisan test --filter=<TestOrMethod>` or a specific test path. Formatting: `vendor/bin/pint --dirty`. Frontend build: `npm run build`. Browser tests: `npm run test:e2e -- <spec>` when the affected flow requires them.
- Do not mutate production-like data or run destructive migrations/commands without explicit authorization.
- Final responses should state only what changed, important files, verification performed, and any remaining issue. Do not repeat the request or paste edited source.
