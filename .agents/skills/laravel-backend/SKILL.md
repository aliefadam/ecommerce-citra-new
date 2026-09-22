---
name: laravel-backend
description: Implement or modify Laravel controllers, services, models, middleware, console commands, mail, or server-rendered application behavior. Do not use for database-only, UI-only, or public API-only work.
---

# Laravel Backend

## Workflow

1. Locate the route or entry point and the closest related test.
2. Trace only the invoked controller, middleware, model relationships, and service calls.
3. Follow the nearest implementation pattern; extend an existing service before adding a new abstraction.
4. Validate at the HTTP boundary and keep authoritative pricing, stock, permissions, and company context server-side.
5. Make the smallest change and run the narrowest relevant PHPUnit test.

## Repository rules

- Controllers may orchestrate; reusable business rules belong in `app/Services`.
- Respect route model binding, named routes, middleware order, Eloquent scopes, casts, fillable fields, and eager-loading patterns already in use.
- Preserve `DB::transaction` boundaries for multi-write operations. Do not hide failures or add broad exception catches.
- Treat queued mail, scheduled commands, filesystem uploads, and external integrations as side-effect boundaries; inspect their configuration and existing failure handling before changing them.

## Verification

- [ ] Route, validation, authorization, and company scope still agree.
- [ ] Changed PHP parses and the focused PHPUnit test passes.
- [ ] Direct callers and response/redirect contracts were checked.
- [ ] No unrelated behavior or public contract changed.
