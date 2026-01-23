# CWM Proclaim

A Joomla 4+ component for managing and displaying Bible studies and sermons, developed by a team of web servants to further the teaching of God's Word.

## Status

| Branch      | Version | Release Date | Joomla Version | PHP Minimum                                                                    |
|-------------|---------|--------------|----------------|--------------------------------------------------------------------------------|
| Development | 10.1.x  |              | 4+             | [![PHP](https://img.shields.io/badge/PHP-V8.3.0-green)](https://www.php.net/)  |
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

- [Wiki Home](https://github.com/Joomla-Bible-Study/Proclaim/wiki) - Main documentation
- [Development Setup](https://github.com/Joomla-Bible-Study/Proclaim/wiki/Setting-up-your-development-environment) - Configure your environment
- [Requirements](docs/requirements.md) - Project requirements and goals
- [Plan](docs/plan.md) - Implementation strategy
- [Tasks](docs/tasks.md) - Improvement tasks with progress tracking

## Quick Start

### Prerequisites

- PHP 8.3.0+
- Composer
- Joomla 4+ installation
- Git

### Installation for Development

```bash
# Clone your fork
git clone https://github.com/YOUR-USERNAME/Proclaim.git
cd Proclaim

# Install dependencies
composer install --dev

# Configure Joomla path (create build.properties)
echo "builder.joomla_path=/path/to/your/joomla" > build.properties

# Set up symbolic links
./libraries/vendor/bin/phing dev.Setup_Symbolic_Links
```

### Common Commands

| Command | Description |
|---------|-------------|
| `./libraries/vendor/bin/phpunit` | Run tests |
| `./libraries/vendor/bin/php-cs-fixer fix --dry-run` | Check code style |
| `./libraries/vendor/bin/php-cs-fixer fix` | Fix code style |
| `./libraries/vendor/bin/phing build` | Full build |
| `./libraries/vendor/bin/phing package` | Create package |

## Contributing

We appreciate contributions in various capacities.

### Development Workflow

1. [Fork this repository](http://help.github.com/fork-a-repo/)
2. Install dev dependencies: `composer install --dev`
3. [Set up your dev environment](https://github.com/Joomla-Bible-Study/Proclaim/wiki/Setting-up-your-development-environment)
4. [Create a topic branch](http://learn.github.com/p/branching.html)
5. Implement your feature or bug fix
6. Add/update unit tests for new functionality
7. Run `./libraries/vendor/bin/phing build` - fix issues if build fails
8. Commit and push your changes
9. [Submit a pull request](http://help.github.com/send-pull-requests/)

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