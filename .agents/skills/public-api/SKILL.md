---
name: public-api
description: Modify or review versioned public catalog API routes, controllers, filters, pagination, caching, JSON resources, or response contracts under `routes/api.php` and `app/Http/*/Api`.
---

# Public API

## Workflow

1. Start from the exact route in `routes/api.php` and identify middleware, controller, resource, and contract tests/docs.
2. Trace requested inputs through validation/filtering to the Eloquent query and JSON resource.
3. Preserve the `/api/v1/companies/{companySlug}` company boundary, status visibility, pagination, and cache semantics.
4. Make additive, backward-compatible response changes unless a breaking change is explicitly requested.
5. Verify status, headers, JSON shape, filtering, pagination, and tenant isolation with a focused feature test.

## Rules

- Serialize through existing API resources; avoid exposing model internals or sensitive columns.
- Bound page size and normalize search/sort/filter inputs using existing controller helpers.
- Prevent N+1 queries by matching resource access with explicit eager loading.
- When response-affecting data or filters change, inspect cache keys/invalidation before editing.
- Keep public API changes separate from web route behavior unless both are in scope.

## Verification

- [ ] Authentication/throttling and company scope remain enforced.
- [ ] Existing clients keep a compatible response shape.
- [ ] Query count and cache behavior were considered.
- [ ] Focused API feature tests pass.
