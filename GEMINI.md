# GEMINI.md

This file provides guidance to Gemini (Google's AI) when working with the Proclaim codebase.

## Project Overview

Proclaim (CWM Proclaim) is a Joomla 4+ component for managing and displaying Bible studies and sermons. It allows users to organize content by teachers, series, topics, and locations, with support for various media types, podcasting, and social sharing.

- **PHP Requirement:** 8.3.0+
- **Namespace:** `CWM\Component\Proclaim`
- **Minimum Joomla Version:** 4.0

## Core Commands

### Dependency Management
```bash
# Install dependencies (note: vendor directory is libraries/vendor)
composer install --dev
```

### Testing
```bash
# Run all unit tests
./libraries/vendor/bin/phpunit

# Run a specific test suite
./libraries/vendor/bin/phpunit --testsuite "Admin Helper Tests"

# Run a specific test file
./libraries/vendor/bin/phpunit tests/unit/Admin/Helper/CwmparamsTest.php
```

### Linting & Code Quality
```bash
# Check code style (dry run)
./libraries/vendor/bin/php-cs-fixer fix --dry-run
composer lint

# Apply code style fixes
./libraries/vendor/bin/php-cs-fixer fix
composer lint:fix

# Run PHP CodeSniffer
./libraries/vendor/bin/phpcs --standard=./build/psr12/ruleset.xml .
composer cs
```

### Build & Setup
```bash
# Run interactive setup wizard
composer setup

# Full build (runs lint and creates component zip)
composer build

# Set up symbolic links to a local Joomla installation
# (Requires builder.joomla_path to be set in build.properties)
composer symlink
```

## Architecture & Structure

### Directory Layout
- `admin/src/`: Backend component logic (Namespace: `CWM\Component\Proclaim\Administrator`)
- `site/src/`: Frontend component logic (Namespace: `CWM\Component\Proclaim\Site`)
- `libraries/src/`: Shared library logic (Namespace: `CWM\Proclaim\Libraries`)
- `libraries/vendor/`: Composer dependencies (non-standard location)
- `media/`: CSS, JavaScript, and other assets
- `modules/`: Joomla modules (both admin and site)
- `plugins/`: Joomla plugins (finder, task)
- `tests/unit/`: Unit tests mirroring the source structure

### Joomla 4 MVC Pattern
The project strictly follows Joomla 4's MVC architecture:
- **Controllers:** Handle user input and interactions.
- **Models:** Manage business logic and database operations.
- **Views:** Classes (typically `HtmlView.php`) that prepare data for display.
- **Templates (tmpl):** PHP files that render the HTML output.
- **Tables:** Classes representing database tables.
- **Services:** Dependency injection registration (`admin/services/provider.php`).

## Code Style Guidelines

- **Standard:** PSR-12 with Joomla-specific conventions.
- **Naming:** Classes often use a `Cwm` prefix (e.g., `CwmteacherTable`, `CwmparamsModel`).
- **Files:** Follow Joomla's file naming conventions for components.
- **Documentation:** Use PHPDoc for classes and methods.

## Important Notes

- **Vendor Directory:** Unlike many PHP projects, the `vendor` folder is located at `libraries/vendor`.
- **Namespace:** Always ensure you are using the correct namespace for the `admin` vs `site` code.
- **Testing:** New features or bug fixes should include corresponding unit tests in `tests/unit/`.
