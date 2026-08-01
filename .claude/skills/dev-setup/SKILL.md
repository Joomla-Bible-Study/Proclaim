---
name: dev-setup
description: Bootstrap a fresh clone of Proclaim for local development — install dependencies, build assets, configure, and symlink to a Joomla install. Use when setting up this repo for the first time or when media/js, media/css, media/images are missing.
---

# Proclaim Development Setup

1. Run `composer install --dev` to install dependencies (auto-clones joomla-cms for testing)
2. Run `npm install && npm run build` to generate `media/` assets (JS, CSS, images, vendor libs)
3. Run `composer setup` for interactive configuration (or manually edit `build.properties`)
4. Run `composer symlink` to link component to your Joomla installation

> **Note**: `media/js/`, `media/css/`, `media/images/`, `media/vendor/`, and `media/fancybox/` are
> generated — they are gitignored and must be built locally. Source files live in `build/media_source/`.
> Run `npm run build` after any changes to source JS/CSS.
