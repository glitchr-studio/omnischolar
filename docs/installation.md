# Installation and first calls

```sh
composer require glitchr/omnischolar omnischolar/openalex omnischolar/hal omnischolar/openlibrary
```

PHP 8.2 or later. The core needs only `symfony/http-client-contracts`; each source package
brings `symfony/http-client`. With `ext-intl`, the Merger compares titles across accents more
thoroughly (a fallback table covers the Latin accents without it).

## One source

```php
use Omnischolar\OpenAlex\OpenAlexSourceFactory;
use Omnischolar\Source\Query;
use Symfony\Component\HttpClient\HttpClient;

$openalex = (new OpenAlexSourceFactory(HttpClient::create()))->create(['mailto' => 'support@glitchr.io']);

$author = $openalex->author('A5108007452');
$author->metrics->hIndex;                    // 51
$author->orcid();                            // 0009-0005-1387-7295

$page = $openalex->works('A5108007452', new Query(from: 2024, to: 2024));
foreach ($page as $work) {
    echo $work->year, ' ', $work->title, ' - ', $work->venue?->name, "\n";
}
$next = $openalex->works('A5108007452', new Query(cursor: $page->next));   // the next page
```

An author or a work is named by an `Identifier` or a string: one of the source's own
identifiers (`'A5108007452'`, `'OL7092953A'`, `'keitaro-nakatani'`, `'Juan.M.Maldacena.1'`), a
resolver URL (`https://orcid.org/...`, `https://doi.org/...`), or - for the sources that search
by name (HAL, Crossref, Open Library, arXiv, INSPIRE) - a name.

## Several sources

```php
use Omnischolar\Collector;
use Omnischolar\Registry;

$registry = new Registry(
    [new OpenAlexSourceFactory($http), new HalSourceFactory($http), new CrossrefSourceFactory($http)],
    [
        'openalex' => ['factory' => 'openalex', 'options' => ['mailto' => 'support@glitchr.io']],
        'hal' => ['factory' => 'hal'],
        'crossref' => ['factory' => 'crossref', 'options' => ['mailto' => 'support@glitchr.io']],
    ],
);
$collector = new Collector($registry);
$works = $collector->collect(['openalex' => 'A5108007452', 'hal' => 'Keitaro Nakatani']);
$collector->incomplete;   // the sources skipped (down, rate limited): do not cache this answer
```

`Collector::collect()` follows every page of each source (up to `$max`), then merges. To read one
source's pages into one list, `Collector::all($source, $author, $query, $max)`.

## Errors

| Exception | When |
|---|---|
| `NotSupportedException` | the source does not do that, or cannot read that identifier |
| `RateLimitedException` | 429: slow down (`$retryAfter` seconds, when the service says) |
| `UnavailableException` | 5xx, a timeout, a network error: never "nothing" |
| `ProviderException` | any other error answer (a malformed query: 400) |
| `InvalidConfigException` | a source not configured, a factory not installed, an option missing |

All implement `Omnischolar\Exception\OmnischolarException`. Not found is `null` or an empty page.
