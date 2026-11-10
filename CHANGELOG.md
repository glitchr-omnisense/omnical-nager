# Changelog

## 1.x (unreleased)

- 2026-11-10: Nager.Date as a declared provider (provider.json, no class): API v4's
  /Holidays/{country}/{year}, one call per year, English names, ISO 3166-2 subdivisions, types;
  its usage policy (policy.json: no rate limits, commercial use by sponsorship, no holiday portal
  of one's own). Tests on answers recorded that day, a commercial site refused without its
  sponsorship, discovery by `DeclarativeFactory::installed()`, the bare check, a live check of two
  calls.
