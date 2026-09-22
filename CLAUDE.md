# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Proclaim (CWM Proclaim) is a Joomla 5+ component (minimum 5.4.0, Joomla 6 supported) for managing and displaying Bible studies/sermons. It supports teachers, series, topics, locations, media files, podcasting, and social sharing with customizable templates.

**PHP Requirement:** 8.3.0+
**Namespace:** `CWM\Component\Proclaim`

> **Claude Code:** load the `joomla` skill (Skill tool) at the start of any session touching this project's PHP, forms, manifests, or extension structure — it carries the J5/J6 API rules, canonical upstream references, and the gotchas this codebase is built against.

## Architecture

### Key Admin Components

- **Extension/ProclaimComponent.php** - Main component class
- **Lib/** - Core utilities (backup, restore, assets, stats, conversion)
- **Helper/** - Shared utilities (params, thumbnails, images, database, tags)

### Core Entities

Messages (sermons), Teachers, Series, Topics, Locations, MediaFiles, Servers, Podcasts, Templates, TemplateCodes, Comments

### Service Provider

`admin/services/provider.php` - Registers component with Joomla's DI container using MVCFactory, CategoryFactory, ComponentDispatcherFactory, and RouterFactory.

## Testing

### PHP Tests (PHPUnit)

Tests are in `tests/unit/`, structure mirrors the source.

Base test class: `CWM\Component\Proclaim\Tests\ProclaimTestCase`

### JavaScript Tests (Jest)

JS tests are in `tests/js/` and use Jest with jsdom for DOM testing.

```bash
# Run JS tests
npm test

# Run JS tests in watch mode (for development)
npm run test:watch

# Run JS tests with coverage
npm run test:coverage
```

Test files should be named `*.test.js` or `*.spec.js`. Coverage reports are generated in `build/reports/coverage-js/`.

**PhpStorm Integration**: Jest is auto-detected. Use gutter icons next to tests, or right-click test files to run.

## Code Style

This project follows **PSR-12** coding standards. All code must pass PHP CS Fixer before committing.

### Tools

- PHP CS Fixer config: `.php-cs-fixer.dist.php`
- Run `composer lint:fix` before committing

### Before opening a PR: run the IDE inspector on changed files

`php -l` and a green test suite do **not** catch an unresolved class or method.
The file parses; the symbol is only resolved when the line runs. If that line is
on a path the suites do not exercise — an installer, a restore, a scheduled
task — it ships and fails in production.

Run PhpStorm's inspection on each changed PHP file (`get_file_problems`, or
Code → Inspect Code) and read the result before pushing.

⚠️ **Include warnings.** "Undefined class" and "Method not found" are severity
**WARNING**, not ERROR. Checking errors only returns an empty list and looks
like a clean file.

Two examples, both of which passed `php -l` and the full suite:

| Symptom | Where |
|---|---|
| `Undefined class 'Factory'` | `HealthRegistry` used `Factory` and `DatabaseInterface` with neither imported |
| `Method 'getDatabase' not found` | `CwmbackupController` has no such method; it extends `BaseController` without the database-aware trait |

⚠️ Do **not** act on its "Qualifier is unnecessary" warnings for `\count`,
`\sprintf` and similar. Those leading slashes are PHP CS Fixer's
`native_function_invocation` rule — following the IDE there fights
`composer lint` on every commit.

### Naming Conventions

- Class naming: `Cwm` prefix (e.g., `CwmparamsModel`, `CwmteacherTable`)
- Template files in `tmpl/` and `layouts/` are excluded from linting

## Versioning

Bump version (for releases) — flags aren't discoverable from `composer.json` alone (it just points to `cwm-bump`):
```bash
composer version -- -v 10.2.0
composer version -- -v 10.2.0-beta1
composer version -- -v 10.2.0-dev -c "New Codename"
```

## Development Setup

See the `dev-setup` skill for bootstrapping a fresh clone (dependency install, asset build, configuration, symlinking).

## Documentation

All project documentation is maintained in the **Proclaim.wiki** repository, not in this main repository.

- **Wiki location**: `../Proclaim.wiki/` (sibling directory)
- **GitHub**: https://github.com/Joomla-Bible-Study/Proclaim/wiki

When updating documentation:
- Release notes go in wiki as `Whats-New-X.X.md`
- Update `Tasks.md` when features are completed
- Keep this repo's `README.md` minimal - link to wiki for details
