---
name: testing
description: Add, update, select, or troubleshoot PHPUnit unit/feature tests, Playwright browser tests, test fixtures, or verification strategy. Use when testing itself is a material part of the task.
---

# Testing

## Selection

- Pure service/domain logic: focused PHPUnit unit test.
- Route, middleware, validation, persistence, Blade response, or integration boundary: focused feature test.
- Responsive layout, focus/keyboard behavior, JavaScript interaction, or end-to-end user flow: one Playwright spec.
- Prefer an existing nearby test file and fixture style over creating a new harness.

## Workflow

1. Map the changed behavior to its nearest existing test and assertions.
2. Run the smallest reproducing test before editing when diagnosing a regression.
3. Assert observable behavior and important database/response side effects, not implementation details.
4. Run the changed test, then directly related tests; run the full suite only for broad or cross-cutting changes.

## Commands

- Method/class: `php artisan test --filter=<TestOrMethod>`
- File: `php artisan test tests/Feature/RelevantTest.php`
- Browser: `npm run test:e2e -- tests/Browser/relevant.spec.cjs`
- Full backend suite when justified: `composer test`

## Verification

- [ ] Test fails for the intended reason before the fix when practical.
- [ ] Fixtures are isolated and compatible with in-memory SQLite.
- [ ] Assertions cover the contract and relevant negative path without brittleness.
- [ ] Report exact commands and outcomes; do not claim tests that were not run.
