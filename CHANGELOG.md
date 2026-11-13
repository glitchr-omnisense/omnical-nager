# Changelog

## [Unreleased]

## [1.0.0] - 2026-11-13

The first stable version. It requires `omnisense/contracts` `^1.0`, `omnisense/omnical` `^1.0` with no stability flag, so `composer require omnical/nager` installs it in a project with Composer's default minimum-stability; the 1.x series follows semantic versioning. A tag `vX.Y.Z` is released by `.github/workflows/release.yml` once the package's CI passes on it.

- 2026-11-10: Nager.Date as a declared provider (provider.json, no class): API v4's
  /Holidays/{country}/{year}, one call per year, English names, ISO 3166-2 subdivisions, types;
  its usage policy (policy.json: no rate limits, commercial use by sponsorship, no holiday portal
  of one's own). Tests on answers recorded that day, a commercial site refused without its
  sponsorship, discovery by `DeclarativeFactory::installed()`, the bare check, a live check of two
  calls.
