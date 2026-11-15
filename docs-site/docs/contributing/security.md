---
title: Security policy
sidebar_position: 4
description: How to report a vulnerability and what is in scope.
---

# Security policy

StructCRM stores commercially sensitive data — contracts, client records, pricing and
payroll-adjacent time entries — for multiple companies in one database. Tenant isolation bugs
are treated as security issues, not ordinary bugs.

## Reporting a vulnerability

**Do not open a public issue.**

Use GitHub's private reporting: repository → **Security** → **Report a vulnerability**
(GitHub Security Advisories). If that is unavailable, contact a maintainer directly.

Please include:

1. Affected version or commit.
2. A description of the issue and its impact.
3. Reproduction steps or a proof of concept.
4. Any suggested fix or mitigation.
5. Whether you intend to disclose publicly, and when.

## What to expect

| Stage | Commitment |
| :--- | :--- |
| Acknowledgement | Within 5 working days |
| Initial assessment | Within 10 working days |
| Fix or mitigation plan | Communicated with the assessment |
| Credit | Offered in the advisory unless you prefer otherwise |

We will keep you informed of progress and coordinate disclosure timing with you.

## In scope

- **Tenant isolation** — reading or writing another company's records through the UI, the
  REST API, the client portal or the AI assistant.
- **Authentication and authorisation** — session handling, the `client` guard separation,
  Sanctum token handling, permission bypasses, privilege escalation to `owner`.
- **Signed URL abuse** — invitation, set-password and unsubscribe links.
- **Injection** — SQL injection, mass-assignment, unsafe file handling.
- **File upload** — uploading executable content, path traversal, serving private files.
- **AI tool abuse** — getting the assistant to bypass policies, act across tenants, or expose
  data the user cannot see; prompt injection that escapes the documented tool set.
- **Rate-limit bypass** on login or the assistant endpoint.
- **Sensitive data exposure** — secrets in logs, stack traces in production, verbose errors.

## Out of scope

- Findings that require an already-compromised host, database or `APP_KEY`.
- Missing security headers with no demonstrated impact.
- Automated scanner output without a reproduction.
- Denial of service through brute-force volume.
- Social engineering of maintainers.
- Issues in dependencies with no exploitable path in this application (report upstream, but
  tell us so we can pin a version).
- The demo seeders shipping known passwords — they are for local evaluation only and must not
  be run in production.

## Safe harbour

We will not pursue legal action against researchers who:

- Test against their own instance or a deployment they are authorised to test.
- Avoid accessing, modifying or exfiltrating data that is not theirs.
- Stop and report as soon as a vulnerability is confirmed.
- Give us reasonable time to fix before public disclosure.

## Hardening your deployment

See the [deployment security checklist](/docs/operations/deployment#security-checklist). The
minimum: `APP_DEBUG=false`, HTTPS, `APP_URL` on `https://`, no demo seeders, database not
publicly reachable, and a working backup you have actually restored once.
