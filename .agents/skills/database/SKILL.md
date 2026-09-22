---
name: database
description: Change or investigate Eloquent persistence, migrations, schema, relationships, constraints, indexes, data backfills, or query behavior. Use when table shape or stored data is material to the task.
---

# Database

## Workflow

1. Search by the exact table, column, relationship, or query symbol.
2. Read the current model plus only migrations that create or alter the relevant fields; consult callers when behavior is ambiguous.
3. Confirm MySQL production compatibility and SQLite test compatibility.
4. Preserve existing naming, foreign-key actions, company ownership, and transaction patterns.
5. Add or adjust the narrowest test that proves schema/query behavior.

## Rules

- Never infer a column or relationship from its name. Verify the model and migration.
- Avoid destructive or irreversible migrations. Do not edit unrelated schema or historical migrations already relied upon unless explicitly required.
- Consider indexes and constraints when changing filters, joins, uniqueness, or foreign keys; avoid N+1 queries using the repository's eager-loading patterns.
- Backfills must be deterministic, bounded, and safe on existing data. Keep schema changes and data assumptions explicit.
- Do not inspect the full migration history when targeted search identifies the relevant chain.

## Verification

- [ ] `up()` and `down()` behavior is safe and consistent.
- [ ] Foreign keys, nullability, uniqueness, indexes, casts, and fillable fields align.
- [ ] Company-scoped records cannot cross tenant boundaries.
- [ ] Focused database/feature tests pass under the configured SQLite test database.
