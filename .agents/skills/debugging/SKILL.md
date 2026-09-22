---
name: debugging
description: Diagnose and fix a reported error, failing test, regression, unexpected output, or runtime issue. Pair with one domain skill only when the evidence reaches that domain.
---

# Debugging

## Workflow

1. Reproduce the issue or establish the exact observable failure.
2. Search the error text, failing assertion, route, or symbol before opening files.
3. Trace the shortest execution path and read only participating code and the closest test.
4. Form one evidence-backed root-cause hypothesis and test it with a focused diagnostic.
5. Apply the smallest fix, rerun the reproduction, then run the nearest regression test.

## Rules

- Do not combine several speculative fixes or start with a broad refactor.
- Separate root cause from symptoms, stale generated state, environment differences, and test-fixture problems.
- Inspect logs only when the reported behavior requires them; limit by time, request, exception, or identifier and redact sensitive values.
- When the evidence points to database, UI, API, or commerce security, load only that matching skill.
- Stop once the root cause is fixed and relevant regression risk is covered.

## Verification

- [ ] Original failure was reproduced or precisely evidenced.
- [ ] The fix explains the observed failure without unrelated changes.
- [ ] Focused regression test now passes.
- [ ] Adjacent direct callers or paths were checked where risk warrants it.
