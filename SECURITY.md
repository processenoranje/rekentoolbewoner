# Security Policy

## Supported Versions

This repository contains **active production code**.

Only the **current main branch** is supported for security updates.
Older commits or forks may contain known vulnerabilities.

---

## Reporting a Vulnerability

If you discover a **security vulnerability**, we appreciate responsible disclosure.

### ✅ Please do

- Report security issues **privately**
- Provide enough detail to reproduce the issue
- Avoid exploiting the issue beyond what is necessary to demonstrate impact

### ❌ Please do NOT

- Do **not** open a public GitHub Issue for security vulnerabilities
- Do **not** include secrets (passwords, tokens, credentials) anywhere
- Do **not** attempt data extraction or destructive actions

---

## How to report

### Preferred route

Contact the maintainers **via the internally agreed security contact**  
(or the organization’s standard security incident channel).

> If you are unsure who to contact, reach out via a **private communication channel**
> used by the organization maintaining this repository.

---

## What to include in your report

Please include, where possible:

- A clear description of the vulnerability
- The affected component or path (e.g. `/admin/users.php`)
- Steps to reproduce (proof‑of‑concept, not exploit)
- Potential impact (e.g. data exposure, privilege escalation)
- Any relevant logs or screenshots (**without secrets**)

---

## Scope

This policy applies to:

- Authentication & authorization (RBAC)
- Admin interfaces (`/admin/*`)
- Data handling & export
- Form submission & storage
- Content management
- Configuration handling

Out of scope:

- Denial‑of‑service through traffic flooding
- Vulnerabilities caused by unsupported environments or modified deployments

---

## Security Practices (high level)

The application currently relies on:

- Role‑based access control (admin / editor / viewer)
- Server‑side permission checks
- Prepared statements for database access
- Sanitization & validation of dynamic content
- Defensive error handling (no stack traces to clients)

Details are documented in the **GitHub Wiki**.

---

## Coordinated Disclosure

We aim to:

1. Acknowledge receipt of a security report
2. Assess impact and severity
3. Address the issue in a reasonable timeframe
4. Communicate when a fix is available (if applicable)

We ask reporters to **not disclose vulnerabilities publicly** until a fix or mitigation is in place.

---

## Questions?

If you are unsure whether something qualifies as a security issue:

- **When in doubt: treat it as security‑sensitive**
- Report it privately rather than via a public issue

Thank you for helping keep this project secure.
