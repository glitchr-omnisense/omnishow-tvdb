# Changelog

## [Unreleased]

## [1.0.0] - 2026-11-13

The first stable release. Omnishow's TheTVDB, under any PHP host: series and their episodes in aired
order, films, a work by its TheTVDB, IMDb or TMDB number, people and translations in the language asked,
through TheTVDB's API v4 (a token got once from the project's key and a subscriber's PIN).

### Release

- Requires `omnisense/contracts: ^1.0`, `omnisense/omnishow: ^1.0`: stable versions, so `composer
  require omnishow/tvdb` installs in a project at Composer's default minimum-stability.
  `extra.branch-alias` names 1.x (`1.x-dev`); the CI (`.github/workflows/ci.yml`, glitchr-studio/ci's
  php-package.yml) is callable, and `release.yml` runs it on a tag before glitchr-studio/ci's
  php-release.yml.
