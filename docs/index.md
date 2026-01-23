# Proclaim Documentation

Proclaim (CWM Proclaim) is a Joomla 4+ component for managing and displaying Bible studies and sermons. It supports teachers, series, topics, locations, media files, podcasting, and social sharing with customizable templates.

## Documentation

| Document | Description |
|----------|-------------|
| [Requirements](requirements.md) | Project requirements, goals, constraints, and success metrics |
| [Plan](plan.md) | Implementation strategy, phased approach, and development practices |
| [Tasks](tasks.md) | Actionable improvement tasks with progress tracking |

## Quick Links

- **GitHub Repository**: [Joomla-Bible-Study/Proclaim](https://github.com/Joomla-Bible-Study/Proclaim)
- **PHP Requirement**: 8.3.0+
- **Joomla Compatibility**: 4.0+

## Getting Started

For development setup instructions, see [CLAUDE.md](../CLAUDE.md) in the repository root.

### Quick Setup

```bash
# Install dependencies (creates build.properties from template)
composer install --dev

# Run interactive setup wizard (configures paths, optionally installs Joomla)
composer setup

# Or do full setup in one command
./libraries/vendor/bin/phing dev.full-setup
```

### Build Commands

| Command | Description |
|---------|-------------|
| `composer setup` | Interactive setup wizard |
| `composer joomla-install` | Download and install Joomla (choose version) |
| `composer joomla-latest` | Show latest available Joomla version |
| `composer symlink` | Create symbolic links to Joomla |
| `composer test` | Run PHPUnit tests |
| `composer lint` | Check code style (dry-run) |
| `composer lint:fix` | Fix code style issues |
| `composer cs` | Run PHPCS |
| `composer check` | Run lint + tests together |
| `./libraries/vendor/bin/phing build` | Full build |

## Project Structure

- `admin/src/` - Administrator component code
- `site/src/` - Frontend component code
- `libraries/vendor/` - Composer dependencies
- `modules/` - Joomla modules
- `plugins/` - Joomla plugins
- `media/` - CSS, JS, and assets

## Support

Having issues? [Open an issue](https://github.com/Joomla-Bible-Study/Proclaim/issues) on GitHub.