# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Proclaim (CWM Proclaim) is a Joomla 4+ component for managing and displaying Bible studies/sermons. It supports teachers, series, topics, locations, media files, podcasting, and social sharing with customizable templates.

**PHP Requirement:** 8.3.0+
**Namespace:** `CWM\Component\Proclaim`

## Build Commands

```bash
# Install dependencies (vendor dir is libraries/vendor)
composer install --dev

# Run tests
./libraries/vendor/bin/phpunit

# Run a single test file
./libraries/vendor/bin/phpunit tests/unit/Admin/Helper/CwmparamsTest.php

# Run a specific test suite
./libraries/vendor/bin/phpunit --testsuite "Admin Helper Tests"

# PHP CS Fixer - dry run
./libraries/vendor/bin/php-cs-fixer fix --dry-run

# PHP CS Fixer - apply fixes
./libraries/vendor/bin/php-cs-fixer fix

# PHP CodeSniffer
./libraries/vendor/bin/phpcs --standard=./build/psr12/ruleset.xml .

# Phing build (runs lint + creates component zip)
./libraries/vendor/bin/phing build

# Phing lint only
./libraries/vendor/bin/phing lint

# Set up symlinks to local Joomla installation (configure build.properties first)
./libraries/vendor/bin/phing dev.Setup_Symbolic_Links
```

## Architecture

### Directory Structure

- `admin/src/` - Administrator component code (namespace: `CWM\Component\Proclaim\Administrator`)
- `site/src/` - Frontend component code (namespace: `CWM\Component\Proclaim\Site`)
- `libraries/vendor/` - Composer dependencies (non-standard location)
- `modules/` - Joomla modules (site and admin)
- `plugins/` - Joomla plugins (finder, task)
- `media/` - CSS, JS, and assets

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

Tests are in `tests/unit/` with structure mirroring the source:
- `tests/unit/Admin/Helper/` - Admin helper tests
- `tests/unit/Site/Helper/` - Site helper tests
- `tests/unit/Admin/Table/` - Table tests
- `tests/unit/Site/Model/` - Site model tests

Base test class: `CWM\Component\Proclaim\Tests\ProclaimTestCase`

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
- PHPCS ruleset: `build/psr12/ruleset.xml`
- Run `./libraries/vendor/bin/php-cs-fixer fix` before committing

### Naming Conventions

- Class naming: `Cwm` prefix (e.g., `CwmparamsModel`, `CwmteacherTable`)
- Template files in `tmpl/` and `layouts/` are excluded from linting

## Development Setup

1. Configure `build.properties` with your local Joomla path:
   ```
   builder.joomla_path=/path/to/your/joomla
   ```
2. Run `./libraries/vendor/bin/phing dev.Setup_Symbolic_Links` to symlink component to Joomla

## Documentation

All project documentation is maintained in the **Proclaim.wiki** repository, not in this main repository.

- **Wiki location**: `../Proclaim.wiki/` (sibling directory)
- **GitHub**: https://github.com/Joomla-Bible-Study/Proclaim/wiki

When updating documentation:
- Release notes go in wiki as `Whats-New-X.X.md`
- Update `Tasks.md` when features are completed
- Keep this repo's `README.md` minimal - link to wiki for details
