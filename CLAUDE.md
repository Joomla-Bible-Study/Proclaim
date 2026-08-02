# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Proclaim (CWM Proclaim) is a Joomla 4+ component for managing and displaying Bible studies/sermons. It supports teachers, series, topics, locations, media files, podcasting, and social sharing with customizable templates.

**PHP Requirement:** 8.3.0+
**Namespace:** `CWM\Component\Proclaim`

> **Claude Code:** load the `joomla` skill (Skill tool) at the start of any session touching this project's PHP, forms, manifests, or extension structure — it carries the J5/J6 API rules, canonical upstream references, and the gotchas this codebase is built against.

## Build Commands

```bash
# Run all PHP tests (unit + integration)
composer test

# Run only unit tests
composer test:unit

# Run only integration tests
composer test:integration

# Run a single PHP test file
./libraries/vendor/bin/phpunit tests/unit/Admin/Helper/CwmparamsTest.php

# Run a specific test suite
./libraries/vendor/bin/phpunit --testsuite "Admin Helper Tests"

# Check PHP syntax errors
composer lint:syntax

# Check code style via php-cs-fixer (dry-run)
composer lint

# Fix code style via php-cs-fixer
composer lint:fix

# Run lint + PHP tests
composer check

# Run all checks + all tests (PHP + JS)
composer check:all

# Full build with all checks
composer build:full

# Build frontend assets (JS/CSS)
composer build:assets

# Build component package (zip)
composer build

# Setup development environment
composer setup

# Create symlinks to Joomla installation
composer symlink

# Install Joomla (interactive)
composer joomla-install

# Show latest available Joomla version
composer joomla-latest

# Clean dev state (remove symlinks)
composer clean

# Sync and translate language files
composer sync-languages

# Force re-translate ALL language keys (use after major English changes)
composer sync-languages-force

# Bump version (for releases)
composer version -- -v 10.2.0
composer version -- -v 10.2.0-beta1
composer version -- -v 10.2.0-dev -c "New Codename"
```

## Architecture

### Directory Structure

- `libraries/vendor/` - Composer dependencies (non-standard location)
- `build/media_source/` - Source JS, CSS, images, and vendor libraries (committed to git)
- `media/` - Generated JS/CSS/assets (gitignored; produced by `npm run build`)

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

Tests are in `tests/unit/` with structure mirroring the source:
- `tests/unit/Admin/Helper/` - Admin helper tests
- `tests/unit/Site/Helper/` - Site helper tests
- `tests/unit/Admin/Table/` - Table tests
- `tests/unit/Site/Model/` - Site model tests

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
