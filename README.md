# CWM Proclaim

A Joomla 5+ component for managing and displaying Bible studies and sermons, developed by a team of web servants to further the teaching of God's Word.

## Build Status

| CI                                                                                                                                                                  | CodeQL                                                                                                                                                                                 | PHP                                                                            | Node                                                                                  | npm                                                                               |
|---------------------------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------|---------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------|
| [![CI](https://github.com/Joomla-Bible-Study/Proclaim/actions/workflows/ci.yml/badge.svg?branch=development)](https://github.com/Joomla-Bible-Study/Proclaim/actions/workflows/ci.yml) | [![CodeQL](https://github.com/Joomla-Bible-Study/Proclaim/actions/workflows/codeql.yml/badge.svg?branch=development)](https://github.com/Joomla-Bible-Study/Proclaim/actions/workflows/codeql.yml) | [![PHP](https://img.shields.io/badge/PHP-V8.3.0-green)](https://www.php.net/) | [![Node](https://img.shields.io/badge/Node-V20.0-green)](https://nodejs.org/en/) | [![npm](https://img.shields.io/badge/npm-V10.1.0-green)](https://nodejs.org/en/) |

| Latest Release                                                                                                                                                    | License                                                                                                                          | Joomla                                                                                    |
|-------------------------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------|
| [![Latest Release](https://img.shields.io/github/v/release/Joomla-Bible-Study/Proclaim)](https://github.com/Joomla-Bible-Study/Proclaim/releases/latest) | [![License](https://img.shields.io/badge/License-GPL--2.0--or--later-blue)](LICENSE.txt) | [![Joomla](https://img.shields.io/badge/Joomla-5.1.0+-blue)](https://www.joomla.org/) |

| Branch      | Description                                           |
|-------------|-------------------------------------------------------|
| Development | Next minor release (10.2.x) -- new features and fixes |
| Main        | Current stable release -- bug fixes only              |

## Looking for an Installable Package?

This repository is the source code for development. For a ready-to-install package:
- **Latest stable release:** [GitHub Releases](https://github.com/Joomla-Bible-Study/Proclaim/releases/latest)
- **Detailed changes:** [What's New in 10.1](https://github.com/Joomla-Bible-Study/Proclaim/wiki/What's-New-v10.1)
- **Full changelog:** [Commit History](https://github.com/Joomla-Bible-Study/Proclaim/commits/development)

## Features

- **Content Organization** -- Manage studies by series, teachers, locations, and topics
- **Media Support** -- Audio playback, YouTube, Vimeo, Wistia, Resi.io, Dailymotion, Rumble, SoundCloud, and Facebook integration
- **Podcasting** -- Built-in podcast feed generation with per-series RSS
- **Multi-Campus** -- Location-based content isolation with per-campus access levels
- **Analytics** -- Play/download tracking, Chart.js dashboard, CSV export, printable reports
- **Customizable Templates** -- Visual layout editor with drag-and-drop element arrangement
- **Scripture Integration** -- Multi-provider Bible API with verse popups and translation switching
- **Social Sharing** -- Share content on social media platforms

## Documentation

Full documentation is maintained in the [Proclaim Wiki](https://github.com/Joomla-Bible-Study/Proclaim/wiki).

- [Wiki Home](https://github.com/Joomla-Bible-Study/Proclaim/wiki) -- Main documentation
- [What's New in 10.1](https://github.com/Joomla-Bible-Study/Proclaim/wiki/What's-New-v10.1) -- Release notes
- [Development Setup](https://github.com/Joomla-Bible-Study/Proclaim/wiki/Setting-up-your-development-environment) -- Configure your environment
- [Standards and Conventions](https://github.com/Joomla-Bible-Study/Proclaim/wiki/Standards-and-Conventions) -- Coding standards
- [Database Schema](https://github.com/Joomla-Bible-Study/Proclaim/wiki/Database-Schema) -- Entity relationships
- [Tasks](https://github.com/Joomla-Bible-Study/Proclaim/wiki/Tasks) -- Improvement tasks with progress tracking

## Quick Start

### Prerequisites

- PHP 8.3.0+
- Composer
- Node.js 20.0.0+ and npm 10.1.0+
- Joomla 5.1.0+ installation
- Git

### Installation for Development

```bash
# Clone your fork
git clone https://github.com/YOUR-USERNAME/Proclaim.git
cd Proclaim

# Install dependencies (auto-clones joomla-cms for testing — one-time ~500MB)
composer install --dev
npm install

# Build frontend assets (JS/CSS/images)
npm run build

# Run interactive setup wizard (configures paths and optionally installs Joomla)
composer setup

# Or manually configure build.properties and set up symbolic links
composer symlink
```

> **Disk space:** `composer install --dev` automatically clones the [joomla-cms](https://github.com/joomla/joomla-cms)
> repository as a sibling directory and installs its Composer dependencies. This provides real Joomla CMS classes
> for unit testing instead of stubs. Approximate sizes:
> - joomla-cms shallow clone: ~50MB
> - joomla-cms Composer dependencies: ~96MB
> - Proclaim Composer dependencies: ~42MB
> - Proclaim npm dependencies: ~136MB
> - **Total dev environment: ~1GB** (excluding git history)

### Common Commands

| Command | Description |
|---------|-------------|
| `composer setup` | Interactive setup wizard |
| `composer joomla-install` | Download and install Joomla |
| `composer symlink` | Create symbolic links to Joomla |
| `composer clean` | Remove symbolic links (clean dev state) |
| `npm run build` | Build all frontend assets (JS + CSS + images) |
| `composer test` | Run PHPUnit tests |
| `npm test` | Run Jest tests |
| `composer check:all` | Run all checks + all tests (PHP + JS) |
| `composer lint:fix` | Fix PHP code style issues |
| `npm run lint:js` | Lint JavaScript with ESLint |
| `composer build` | Build component package (zip) |

## Contributing

We appreciate contributions in various capacities.

### Development Workflow

1. [Fork this repository](http://help.github.com/fork-a-repo/)
2. Install dependencies: `composer install --dev && npm install && npm run build`
3. Run setup wizard: `composer setup`
4. [Create a topic branch](http://learn.github.com/p/branching.html)
5. Implement your feature or bug fix
6. Add/update unit tests for new functionality
7. Run `composer check:all` to verify all checks pass
8. Commit and push your changes
9. [Submit a pull request](http://help.github.com/send-pull-requests/)

See [Development Setup](https://github.com/Joomla-Bible-Study/Proclaim/wiki/Setting-up-your-development-environment) for full details.

**Important:** Submit separate pull requests for each fix or feature. Avoid combining multiple changes in a single PR.

### Which Branch Should My Pull Request Target?

| Type of change | Target branch |
|----------------|---------------|
| **Bug fix** -- The software crashes, produces the wrong result, or behaves contrary to its specification | **[main](https://github.com/Joomla-Bible-Study/Proclaim/tree/main)** |
| **Feature / Enhancement** -- New behavior, refactoring, performance improvements, UI tweaks | **[development](https://github.com/Joomla-Bible-Study/Proclaim/tree/development)** |

### Translation

Language files need periodic updates. Follow the same workflow above to submit translation changes or add new languages.

## Reporting Issues

Use the [Issues](https://github.com/Joomla-Bible-Study/Proclaim/issues) section for bug reports or feature requests. When reporting bugs, include steps to reproduce.

## Contact

- **Email:** info@christianwebministries.org
- **Issues:** [GitHub Issues](https://github.com/Joomla-Bible-Study/Proclaim/issues)
- **Discussions:** [GitHub Discussions](https://github.com/Joomla-Bible-Study/Proclaim/discussions)

## License

CWM Proclaim is distributed under the GNU General Public License version 2 or later.
See [LICENSE.txt](LICENSE.txt) for details.

This software includes third-party components with their own license terms.
See [THIRD_PARTY_LICENSES.md](THIRD_PARTY_LICENSES.md) for details, including
important information about Fancybox licensing for commercial use.

## Copyright

(C) 2014 CWM Team. Distributed under the GNU General Public License version 2 or later.
