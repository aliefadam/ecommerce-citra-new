---
name: frontend-ui
description: Build or change Blade views, reusable UI components, Tailwind/CSS, Alpine or browser-side JavaScript, responsive behavior, and accessibility. Do not use for backend-only behavior.
---

# Frontend UI

## Workflow

1. Inspect the target view/component and its layout, then search for an existing equivalent pattern.
2. Reuse `resources/views/components/ui`, existing `ec-*` classes, Tailwind tokens, spacing, typography, controls, dialogs, and icons.
3. Keep Blade as the rendering layer and use existing Alpine or focused JavaScript patterns for interaction.
4. Change only the requested screen/component and preserve desktop and mobile behavior.
5. Run the most relevant feature/design-system or Playwright test; build assets when CSS/JS entry points change.

## Rules

- Preserve the established industrial storefront/admin design language; do not introduce generic standalone styling or redesign adjacent pages.
- Avoid new frontend dependencies unless the task cannot be met with the installed stack.
- Maintain semantic HTML, labels, focus behavior, keyboard access, `aria-*` state, and useful `data-testid` hooks.
- Do not trust client-provided totals, prices, stock, authorization, or company identifiers.
- Prefer reusable components for repeated UI, but do not extract a one-off fragment without demonstrated reuse.

## Verification

- [ ] Existing component and design tokens were reused where appropriate.
- [ ] Narrow/mobile and wide layouts remain usable.
- [ ] Keyboard, focus, labels, and visible states work.
- [ ] Relevant feature/browser test and `npm run build` pass when applicable.
