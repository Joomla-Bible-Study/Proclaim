# CWM Proclaim

A Joomla 4+ component for managing and displaying Bible studies and sermons, developed by a team of web servants to further the teaching of God's Word.

## Status

| Branch      | Version | Release Date | Joomla Version | PHP Minimum                                                                    |
|-------------|---------|--------------|----------------|--------------------------------------------------------------------------------|
| Development | 10.1.x  |              | 5.1.0+         | [![PHP](https://img.shields.io/badge/PHP-V8.3.0-green)](https://www.php.net/)  |
| Main        | 10.0.1  | Sep 10, 2025 | 4+             | [![PHP](https://img.shields.io/badge/PHP-V8.3.0-green)](https://www.php.net/)  |

> **Note:** The main branch reflects the current stable release. Bug fixes and minor updates go to main; new features go to development only.

## Features

- **Content Organization** - Manage studies by series, teachers, locations, and topics
- **Media Support** - Audio playback, YouTube embedding, and video integration
- **Podcasting** - Built-in podcast feed generation
- **Social Sharing** - Share content on social media platforms
- **Customizable Templates** - Flexible template system for varied presentation
- **Custom Pages** - Create your own HTML display pages

## Documentation

The main documentation and project tasks are maintained in the **Proclaim.wiki** repository. If cloned locally, it should be a sibling directory to this project.

- [Local Wiki Home](../Proclaim.wiki/Home.md) - Local access (if cloned)
- [Wiki Home](https://github.com/Joomla-Bible-Study/Proclaim/wiki) - Main documentation on GitHub
- [Development Setup](https://github.com/Joomla-Bible-Study/Proclaim/wiki/Setting-up-your-development-environment) - Configure your environment
- [Requirements](https://github.com/Joomla-Bible-Study/Proclaim/wiki/Requirements) - Project requirements and goals
- [Plan](https://github.com/Joomla-Bible-Study/Proclaim/wiki/Plan) - Implementation strategy
- [Tasks](https://github.com/Joomla-Bible-Study/Proclaim/wiki/Tasks) - Improvement tasks with progress tracking

## Quick Start

### Prerequisites

- PHP 8.3.0+
- Composer
- Node.js 20.0.0+ and npm 10.1.0+
- Joomla 4+ installation
- Git

### Installation for Development

```bash
# Clone your fork
git clone https://github.com/YOUR-USERNAME/Proclaim.git
cd Proclaim

# Install dependencies (creates build.properties from template if missing)
composer install --dev
npm install

# Run interactive setup wizard (configures paths and optionally installs Joomla)
composer setup

# Or manually configure build.properties and set up symbolic links
composer symlink
```

### Common Commands

| Command | Description |
|---------|-------------|
| `composer setup` | Interactive setup wizard |
| `composer joomla-install` | Download and install Joomla |
| `composer joomla-latest` | Show latest Joomla version available |
| `composer symlink` | Create symbolic links to Joomla |
| `composer clean` | Remove symbolic links (clean dev state) |
| `composer test` | Run PHPUnit tests |
| `composer check` | Run syntax + lint + tests |
| `composer check:all` | Run all checks + all tests (PHP + JS) |
| `composer lint:fix` | Fix code style issues |
| `composer build` | Build component package (zip) |
| `composer build:full` | Run all checks then build |

## Contributing

We appreciate contributions in various capacities.

### Development Workflow

1. [Fork this repository](http://help.github.com/fork-a-repo/)
2. Install dependencies: `composer install --dev && npm install`
3. Run setup wizard: `composer setup`
4. [Create a topic branch](http://learn.github.com/p/branching.html)
5. Implement your feature or bug fix
6. Add/update unit tests for new functionality
7. Run `composer check` to verify lint and tests pass
8. Commit and push your changes
9. [Submit a pull request](http://help.github.com/send-pull-requests/)

See [Development Setup](https://github.com/Joomla-Bible-Study/Proclaim/wiki/Setting-up-your-development-environment) for full details.

**Important:** Submit separate pull requests for each fix or feature. Avoid combining multiple changes in a single PR.

### Translation

Language files need periodic updates. Follow the same workflow above to submit translation changes or add new languages.

### Testing

For major releases, we have an approximate 2-week testing window. Contact us if you'd like to help test new versions.

## Reporting Issues

Use the [Issues](https://github.com/Joomla-Bible-Study/Proclaim/issues) section for bug reports or feature requests. When reporting bugs, include steps to reproduce.

## Contact

- **Email:** info@christianwebministries.org
- **Issues:** [GitHub Issues](https://github.com/Joomla-Bible-Study/Proclaim/issues)

## License

See [LICENSE.txt](LICENSE.txt) for details.
