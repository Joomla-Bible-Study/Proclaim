# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Proclaim (CWM Proclaim) is a Joomla 4+ component for managing and displaying Bible studies/sermons. It supports teachers, series, topics, locations, media files, podcasting, and social sharing with customizable templates.

**PHP Requirement:** 8.3.0+
**Namespace:** `CWM\Component\Proclaim`

## Build Commands

```bash
# Install PHP dependencies (vendor dir is libraries/vendor)
composer install --dev

# Install JS dependencies
npm install

# Run all PHP tests (unit + integration)
composer test

# Run only unit tests
composer test:unit

# Run only integration tests
composer test:integration

# Run JS tests
npm test

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

- `admin/src/` - Administrator component code (namespace: `CWM\Component\Proclaim\Administrator`)
- `site/src/` - Frontend component code (namespace: `CWM\Component\Proclaim\Site`)
- `libraries/vendor/` - Composer dependencies (non-standard location)
- `modules/` - Joomla modules (site and admin)
- `plugins/` - Joomla plugins (finder, task)
- `build/media_source/` - Source JS, CSS, images, and vendor libraries (committed to git)
- `media/` - Generated JS/CSS/assets (gitignored; produced by `npm run build`)

### Joomla MVC Pattern

Each section (admin/site) follows Joomla 4's MVC structure:
- `Controller/` - Request handlers
- `Model/` - Business logic and data access
- `View/` - View classes (HtmlView.php)
- `Helper/` - Utility classes
- `Table/` - Database table classes (admin only)
- `Field/` - Custom form fields (admin only)
- `tmpl/` - PHP template files

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

### PSR-12 Requirements

- **Type casts**: Always include a space after the cast operator: `(int) $var`, `(string) $value`
- **Indentation**: 4 spaces, no tabs
- **Line length**: Should not exceed 120 characters
- **Braces**: Opening brace on same line for control structures, new line for classes/methods
- **Namespaces**: One blank line after namespace declaration
- **Use statements**: Grouped by type, one blank line after use block

### Tools

- PHP CS Fixer config: `.php-cs-fixer.dist.php`
- Run `composer lint:fix` before committing

### Naming Conventions

- Class naming: `Cwm` prefix (e.g., `CwmparamsModel`, `CwmteacherTable`)
- Template files in `tmpl/` and `layouts/` are excluded from linting

## Development Setup

1. Run `composer install --dev` to install dependencies
2. Run `npm install && npm run build` to generate `media/` assets (JS, CSS, images, vendor libs)
3. Run `composer setup` for interactive configuration (or manually edit `build.properties`)
4. Run `composer symlink` to link component to your Joomla installation

> **Note**: `media/js/`, `media/css/`, `media/images/`, `media/vendor/`, and `media/fancybox/` are
> generated — they are gitignored and must be built locally. Source files live in `build/media_source/`.
> Run `npm run build` after any changes to source JS/CSS.

## Documentation

All project documentation is maintained in the **Proclaim.wiki** repository, not in this main repository.

- **Wiki location**: `../Proclaim.wiki/` (sibling directory)
- **GitHub**: https://github.com/Joomla-Bible-Study/Proclaim/wiki

When updating documentation:
- Release notes go in wiki as `Whats-New-X.X.md`
- Update `Tasks.md` when features are completed
- Keep this repo's `README.md` minimal - link to wiki for details
