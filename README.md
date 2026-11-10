# omnical/nager

[Nager.Date](https://nagerholidays.com) for [omnisense/omnical](https://github.com/glitchr-omnisense/omnical),
under any PHP host, with no key: the public holidays of 200+ countries, by subdivision (ISO
3166-2) and type, through API v4.

```
composer require omnical/nager
```

**A package of data, no PHP class**: `provider.json` declares the provider, `policy.json` its
usage policy; omnical's declarative mechanism loads both at run time. One GET endpoint per year,
plain JSON, no key: a definition says it all.

**Commercial use needs a sponsorship** of the project ([GitHub Sponsors](https://github.com/sponsors/nager)).
A commercial site says so, and is refused until it says it holds the agreement:

```php
$registry = new Registry(DeclarativeFactory::installed($http), [
    'holidays' => ['factory' => 'nager', 'usage' => ['commercial' => true, 'contract' => true]],
]);
$registry->holidays('holidays')->holidays(HolidayQuery::year('DE', 2027, 'DE-SN'));
```

## Options

| Option | |
|---|---|
| `user_agent` | sent on every request (not required; name the site and a contact) |
| `base_uri` | `https://nagerholidays.com/api/v4`, or a self-hosted instance (the Docker image needs a licence key, which sponsors receive) |
| `timeout` | seconds |
| `policy`, `usage` | the site's tightening, what the site is |

## What is called, how it is read

`GET /api/v4/Holidays/{country}/{year}`, one call per calendar year of the query; a 404 (an
unknown country) is no holiday.

| Holiday | From |
|---|---|
| first (= last) | `date` |
| name | `name`: v4 names holidays **in English only** (language `en`, whatever is asked) |
| country | `countryCode` |
| nationwide, regions | `nationalHoliday`, `subdivisionCodes` (ISO 3166-2) |
| types | `holidayTypes`: Public, Bank, School, Authorities, Optional, Observance |

v3 (`date.nager.at/api/v3`, with local names) ends on 2027-01-31: this package calls v4.

## Usage policy (read on 2026-11-10)

From the [terms](https://nagerholidays.com/legal/termsofservice) (last updated 2023-09-15) and
the [API page](https://nagerholidays.com/api):
- commercial use: "we require active sponsorship" (`contract`);
- "it is not allowed to use the holiday information to publish or operate your own holiday
  portal" (redistribution forbidden);
- no rate limits ("unlimited queries": the policy's limits are none, `[]`);
- nothing on attribution, caching or storage (unknown); the answers carry
  `Cache-Control: public, max-age=604800`.

## Verified, and not

- Verified on 2026-11-10: the v4 OpenAPI document (`/openapi/community-v4.json`), the terms, the
  API page; three answers recorded at 04:21 UTC (France and Germany 2027, and XX's 404) are the
  tests' fixtures, kept whole; the live check (two calls) passed at 04:26 UTC.
- Not verified: the Pro API (beta, key in `x-api-key`), the Docker image.

## Licence

MIT (the package; Nager.Date's code is MIT too). The data's licence is not stated.
