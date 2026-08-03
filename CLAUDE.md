# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Proclaim (CWM Proclaim) is a Joomla 4+ component for managing and displaying Bible studies/sermons. It supports teachers, series, topics, locations, media files, podcasting, and social sharing with customizable templates.

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
