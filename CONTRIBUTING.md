# Contributing to Proclaim

Thank you for your interest in contributing to Proclaim! This document provides guidelines for contributing to the project.

## Project Overview

Proclaim (CWM Proclaim) is a Joomla 5+ component for managing and displaying Bible studies and sermons. It supports teachers, series, topics, locations, media files, podcasting, and social sharing with customizable templates.

- **Minimum PHP**: 8.3+
- **Minimum Joomla**: 5+ (must work on Joomla 6 without backward compatibility layer)
- **Namespace**: `CWM\Component\Proclaim`
- **License**: GPL-2.0-or-later

## Getting Started

### Development Setup

1. Fork and clone the repository
2. Install dependencies:
   ```bash
   composer install --dev
   npm install
   ```
3. Set up your development environment:
   ```bash
   composer setup
   composer symlink
   ```

### Branch Strategy

- **Main branch**: `development` (all PRs target this branch)
- **Feature branches**: `feature-descriptive-name`
- **Bug fixes**: `fix-descriptive-name`

## Code Standards

### PSR-12 Coding Standard

This project follows **PSR-12** coding standards, enforced by PHP CS Fixer.

**Before committing, always run:**
```bash
composer lint:fix
```

**Key requirements:**
- 4-space indentation (no tabs)
- Line length should not exceed 120 characters
- Space after type casts: `(int) $var`, `(string) $value`
- Opening brace on same line for control structures
- One blank line after namespace and use statements

### Joomla 5+ API Requirements

**Use modern Joomla APIs only:**
- ✅ `getDatabase()` / ❌ `getDbo()` or `$this->_db`
- ✅ `getIdentity()` / ❌ `getSession()->get('user')`
- ✅ Exceptions / ❌ `getError()`/`setError()`
- ✅ MVCFactory `createModel()`/`createTable()` / ❌ `new ClassName()`
- ✅ `$this->input` in controllers / ❌ `new Input()`
- ❌ No deprecated APIs: `jimport()`, `getErrorMsg()`, `CMSObject`, etc.

**Code must work WITHOUT Joomla's backward compatibility plugin enabled.**

### Naming Conventions

- Class names use `Cwm` prefix: `CwmteacherTable`, `CwmparamsModel`
- Follow Joomla's MVC naming conventions
- Use descriptive variable and method names

## Testing

### Running Tests

```bash
# Run PHP tests
composer test

# Run JavaScript tests
npm test

# Run all checks (linting + tests)
composer check:all
```

### Test Requirements

- **PHP**: PHPUnit tests in `tests/unit/`
- **JavaScript**: Jest tests in `tests/js/`
- All tests must pass before PR approval
- Add tests for new features and bug fixes

### Code Coverage

Aim for meaningful test coverage, especially for:
- New features
- Bug fixes
- Complex business logic
- Helper functions

## Pull Request Process

1. **Create a feature/fix branch** from `development`
2. **Make your changes** following code standards
3. **Run tests and linting**:
   ```bash
   composer lint:fix
   composer check:all
   ```
4. **Commit with clear messages**:
   - Use descriptive commit messages
   - Reference issue numbers when applicable
5. **Push and create PR** targeting `development`
6. **PR Description** should include:
   - Clear description of changes
   - Reference to related issues/discussions
   - Testing performed
   - Screenshots (for UI changes)

### PR Review

- All PRs require review before merging
- Address review feedback promptly
- Maintain a respectful, collaborative tone
- CI checks must pass

## Documentation

- **Code documentation**: Use PHPDoc blocks for classes and methods
- **Project documentation**: Lives in the [Proclaim.wiki](https://github.com/Joomla-Bible-Study/Proclaim/wiki) repository
- Update wiki documentation for new features
- Release notes go in wiki as `Whats-New-X.X.md`

## Issue Tracking

- **Bug reports**: Use GitHub Issues
- **Feature requests**: Use GitHub Discussions
- **Security issues**: See [SECURITY.md](SECURITY.md)

### Reporting Bugs

Include:
- Proclaim version
- Joomla version
- PHP version
- Steps to reproduce
- Expected vs actual behavior
- Error messages/logs

## Versioning

Check `build/versions.json` for version numbers:
- Use `next.minor` for `@since` tags (currently 10.1.0)
- Use `next.major` for breaking changes (currently 11.0.0)

## Building

```bash
# Install dependencies
composer install --dev
npm install

# Build frontend assets
composer build:assets

# Build component package
composer build

# Full build with all checks
composer build:full
```

## Common Tasks

```bash
# Check PHP syntax
composer lint:syntax

# Check code style
composer lint

# Fix code style
composer lint:fix

# Run specific test file
./libraries/vendor/bin/phpunit tests/unit/Admin/Helper/CwmparamsTest.php

# Sync language files
composer sync-languages

# Bump version
composer version -- -v 10.2.0
```

## Platform Targets

- Code must work natively on **Joomla 5 and 6**
- Avoid ALL deprecated Joomla APIs
- Test without backward compatibility layer
- PHP 8.3+ required (use modern PHP features)

## Getting Help

- **Documentation**: [Proclaim Wiki](https://github.com/Joomla-Bible-Study/Proclaim/wiki)
- **Discussions**: [GitHub Discussions](https://github.com/Joomla-Bible-Study/Proclaim/discussions)
- **Issues**: [GitHub Issues](https://github.com/Joomla-Bible-Study/Proclaim/issues)

## Code of Conduct

- Be respectful and professional
- Welcome newcomers and help them learn
- Focus on constructive feedback
- Respect different perspectives and experience levels

## License

By contributing to Proclaim, you agree that your contributions will be licensed under the GPL-2.0-or-later license.

---

Thank you for contributing to Proclaim! Your efforts help make this project better for the entire community.
