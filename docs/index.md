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

### Build Commands

```bash
# Install dependencies
composer install --dev

# Run tests
./libraries/vendor/bin/phpunit

# PHP CS Fixer
./libraries/vendor/bin/php-cs-fixer fix

# Phing build
./libraries/vendor/bin/phing build
```

## Project Structure

- `admin/src/` - Administrator component code
- `site/src/` - Frontend component code
- `libraries/vendor/` - Composer dependencies
- `modules/` - Joomla modules
- `plugins/` - Joomla plugins
- `media/` - CSS, JS, and assets

## Support

Having issues? [Open an issue](https://github.com/Joomla-Bible-Study/Proclaim/issues) on GitHub.