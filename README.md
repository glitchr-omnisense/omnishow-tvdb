# omnishow/tvdb

[omnisense/omnishow](https://github.com/glitchr-omnisense/omnishow)'s TheTVDB, under any PHP host,
through its API v4: a token got once from the project's key (and a subscriber's PIN for a
user-supported key) and kept for the provider's life; search; a series' or a film's extended record
with its people, by its TheTVDB number or by an IMDb or TMDB number (`/search/remoteid`); an
episode; a season's episodes in aired order; titles and overviews in the language asked when
TheTVDB has them (its three-letter codes: "fr" is asked as "fra").

```php
$tvdb = (new TvdbFactory($httpClient))->create(['api_key' => getenv('TVDB_KEY'), 'language' => 'fr']);
$episodes = $tvdb->episodes(WorkRef::tvdb(70533), 1);
```

Options: `api_key` (required), `pin`, `language` (null: TheTVDB's own), `api`.

Written on Symfony's HTTP client contracts, like the family: no v4 client known that takes the
application's client.

## TheTVDB's terms, as read on 2026-10-24

From [api-information](https://thetvdb.com/api-information): the API is licensed, for commercial
projects and individual developers (discounts for open source and non-profit projects; prices from
TheTVDB's sales). End users who see its metadata must see a direct link to TheTVDB.com; the sample
wording: "Metadata provided by TheTVDB. Please consider adding missing information or subscribing."

## Verified, and not

- Verified (the core's harness, PHP 8.4): the token got once, search in French, a series by its
  IMDb number with its people and identifiers, a season in order, unknown, a refused key;
  `BareTest`; PHPStan max. The answers are **shaped after TheTVDB's v4 reference and written by
  hand**: no key was used. The `sourceName` of remote identifiers ("IMDB", "TheMovieDB.com",
  "Wikidata") is the reference's, not seen live.
- Not verified live: no key.

## Usage policy

The package declares TheTVDB's usage policy as data, in [policy.json](policy.json)
(omnisense/contracts' schema, read at its sources on 2026-11-10): rate limits none published
("excessive calls" are refused, as TheTVDB determines them): unknown; free with the credit and a
link under US$50,000 of yearly revenue, paid licences above (or the subscriber pin), a copy of the
database or a caching proxy recommended, no redistribution, images not covered by the API licence.
Through omnishow's registry every call is held to it: a site's own limit is counted; a commercial
use cannot be declared allowed, a 429 and its `Retry-After` hold every next call until the wait is
over (`UsageLimitReachedException`), and a site may tighten the policy in its configuration
(`'policy' => [...]`), never loosen it. What the terms do not say is declared unknown; the sources
and the details are in the file's `provenance`.

## Responsibility

An independent, unofficial client, affiliated with, endorsed by or sponsored by none of the services
it reaches. The MIT licence covers this code only and grants no right to the service or its data:
whoever runs it complies with the terms above and obtains what they require. The authors publish
it free of charge, make no commercial use of it and accept no liability for its use.

## Licence

MIT.
