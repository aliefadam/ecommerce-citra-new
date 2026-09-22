---
name: commerce-security
description: Handle security-sensitive ecommerce flows involving authentication, roles, company isolation, checkout, coupons, stock reservations, payments or webhooks, uploads, tax documents, and external shipping or WhatsApp integrations.
---

# Commerce Security

## Workflow

1. Identify the trust boundary and attacker-controlled inputs before editing.
2. Trace middleware/authorization, validation, company ownership, service logic, persistence, and outbound side effects.
3. Inspect the nearest regression/security test and the relevant integration configuration without exposing secrets.
4. Preserve server-authoritative calculations and atomic state transitions; make one minimal, evidence-based fix.
5. Test the success case plus the most relevant unauthorized, replay, tampering, or cross-company case.

## Invariants

- Never accept client prices, totals, discount eligibility, stock, payment state, role, or company ownership as authoritative.
- Webhooks must retain signature/credential validation, idempotency, explicit status transitions, and safe retry behavior.
- Scope reads and writes to the active company where the domain requires it; verify object ownership after route binding.
- Protect uploads by validating type/size/content and by using existing storage access patterns.
- Keep secrets in environment/config boundaries and redact credentials, tokens, personal data, and raw payment payloads from output/logging.
- Do not weaken a guard to satisfy a failing test and do not execute real external side effects during verification.

## Verification

- [ ] Authorization, validation, ownership, and state-transition checks are server-side.
- [ ] Concurrency/idempotency and transaction boundaries were considered.
- [ ] Negative-path regression test passes alongside the focused happy path.
- [ ] No secret or sensitive customer/payment data is exposed.
