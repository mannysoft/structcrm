---
title: Contributing
sidebar_position: 1
description: How to set up, branch, test and submit a change to StructCRM.
---

# Contributing

Thanks for helping. StructCRM is MIT-licensed and contributions of code, tests and
documentation are welcome.

## Before you start

- **Bugs and small fixes** — open a pull request directly.
- **New features** — open an issue describing the problem first, so we can agree on the
  shape before you spend time on it.
- **Security issues** — do **not** open a public issue. Follow the
  [security policy](/docs/contributing/security).

## Set up the project

```bash
git clone https://github.com/mannysoft/structcrm.git
cd structcrm
composer run setup
php artisan db:seed          # roles, permissions and demo users
npm run dev                  # or: composer run dev
```

Full details in [Installation](/docs/getting-started/installation).

## Branch and commit

```bash
git checkout -b fix/progress-billing-retention
```

| Prefix | Use |
| :--- | :--- |
| `fix/` | Bug fix |
| `feat/` | New capability |
| `docs/` | Documentation only |
| `refactor/` | Behaviour-preserving restructure |
| `test/` | Tests only |

Commit messages: short imperative subject, then a body explaining *why* when the change is
not obvious.

```text
Fix retention rounding on progress billings

Retention was calculated from the unrounded gross amount, producing a
one-cent drift on every billing. Round once, after applying the rate.
```

## Quality gates

Before opening a pull request:

```bash
vendor/bin/pint --dirty                 # formatting
php artisan test --compact              # full suite
```

Both must pass. If your change touches the UI, also run the relevant Dusk test.

## What a good pull request contains

- **One concern.** A rename plus a feature is two pull requests.
- **Tests** for the behaviour you added or fixed, including the failure path (missing
  permission, validation error, cross-tenant access).
- **No unrelated reformatting.** It hides the real diff.
- **Migrations** that are safe on an existing database (no destructive column changes
  without a backfill plan).
- **Documentation** updates when behaviour, configuration or the API changes.
- **No secrets**, no `.env` edits, no credentials in fixtures.

## Checklist

- [ ] Branch is up to date with `main`
- [ ] `vendor/bin/pint --dirty` run
- [ ] `php artisan test --compact` passes
- [ ] New/changed behaviour covered by tests
- [ ] Cross-tenant path tested where relevant
- [ ] Docs updated (`docs-site/`, and `docs/` if it is an internal design document)
- [ ] `.ai/rules` updated if you introduced a durable convention

## Code conventions

Read [Code style](/docs/developer-guide/code-style) and
`.ai/rules/index.md`. The rules that most often bite new contributors:

- Override `uniqueIds()` when a model uses `HasUuids` with an integer primary key.
- Money hook names must differ from their column name, and negative columns get no hook.
- Kebab-case API resources use snake_case route parameters.
- Create routes must be registered in the getting-started prerequisites map.

## Documentation contributions

See [Writing documentation](/docs/contributing/documentation). Documentation-only pull
requests need no PHP test run, but the docs build must pass.

## Reporting a bug

Include:

1. StructCRM commit/version and PHP version.
2. Exact steps to reproduce.
3. What you expected, what happened.
4. Relevant log lines (`storage/logs/laravel.log`) with secrets removed.
5. The role of the user you were signed in as — many "bugs" are permission behaviour.

## Review expectations

- Maintainers review for correctness, tenancy safety and test coverage.
- Expect questions about edge cases; they are usually the reason a change is delayed.
- Small, focused pull requests get merged fastest.

## License

By contributing you agree that your contribution is licensed under the project's MIT
license.
