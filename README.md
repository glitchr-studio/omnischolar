# glitchr/omnischolar

One contract for a researcher's publications: their profile, their works, their books and the
editions of each, their citations - read from the services that know them, merged into one
list, exported as BibTeX, RIS or CSL-JSON.

```php
$works = $collector->collect([
    'openalex' => 'A5108007452',             // Keitaro Nakatani on OpenAlex
    'hal' => 'Keitaro Nakatani',             // his deposits in HAL, by the name he signs
    'crossref' => '0009-0005-1387-7295',     // the works deposited with his ORCID
]);
$works[0]->title;                            // one record per work, each field from the source trusted for it
$works[0]->pdfUrl;                           // the full text deposited in HAL
$export->bibtex($works);                     // @article{chocron2024acid, ...}

$books = (new Merger())->merge($openlibrary->works('Monica Neagoy'));
$books[0]->editions;                         // the editions of one book, newest first
```

This package holds the contract (`Source\SourceInterface`, `Source\SourceFactory`, `Registry`),
the models (`Work`, `Author`, `Contributor`, `Identifier`, `Venue`, `Metrics`, `Affiliation`), the
`Merger`, the `Collector`, the `Export` and the Symfony bundle. It requires nothing but
`symfony/http-client-contracts`. Each source is a package of its own:

| Package | Source | Key |
|---|---|---|
| [`omnischolar/openalex`](https://github.com/glitchr-studio/omnischolar-openalex) | OpenAlex: profiles and counts (h-index), works, citations, abstracts | none (`mailto`; a free key raises the daily budget) |
| [`omnischolar/hal`](https://github.com/glitchr-studio/omnischolar-hal) | HAL: French open archive, full texts, filter by domain (`shs.droit`) | none |
| [`omnischolar/crossref`](https://github.com/glitchr-studio/omnischolar-crossref) | Crossref: reference metadata by DOI, works by ORCID | none (`mailto`) |
| [`omnischolar/orcid`](https://github.com/glitchr-studio/omnischolar-orcid) | ORCID: identity, CV (positions, degrees, distinctions), declared works | none (public API) |
| [`omnischolar/openlibrary`](https://github.com/glitchr-studio/omnischolar-openlibrary) | Open Library: books, one record per edition, covers | none |
| [`omnischolar/arxiv`](https://github.com/glitchr-studio/omnischolar-arxiv) | arXiv: preprints | none |
| [`omnischolar/inspire`](https://github.com/glitchr-studio/omnischolar-inspire) | INSPIRE-HEP: physics literature, citation summary | none |

A source answers what it can - `capabilities()` says what - and throws `NotSupportedException`
for the rest. Unknown is `null` or an empty page; a service down or rate limited is an
`UnavailableException` (`RateLimitedException`), which a caller never caches as "nothing".

The `Merger` takes one record per work: same DOI, then same ISBN (books), arXiv id, HAL id,
then same title in the same year - a different DOI on both sides always keeps two records apart.
Each field comes from the source preferred for it (configurable), identifiers are united, and
the editions of a book are grouped under the newest one.

## Documentation

- [Installation and first calls](docs/installation.md)
- [Sources: what each can and cannot do](docs/sources.md)
- [Models and identifiers](docs/models.md)
- [Merging: the rules and the preferences](docs/merging.md)
- [Export: BibTeX, RIS, CSL-JSON](docs/export.md)
- [Symfony](docs/symfony.md)
- [The Docker harness](docs/harness.md)

## Symfony

```yaml
omnischolar:
    sources:
        openalex: { factory: openalex, options: { mailto: '%env(OMNISCHOLAR_MAILTO)%' } }
        hal: { factory: hal }
        droit: { factory: hal, options: { domains: [shs.droit] } }
        openlibrary: { factory: openlibrary, options: { mailto: '%env(OMNISCHOLAR_MAILTO)%' } }
    merger:
        preferences: { abstract: [hal, openalex] }
```

```php
public function __construct(SourceInterface $openalex, Collector $collector, Export $export) {}
```

## Docker: every source, for real

```sh
cd docker && cp .env.dist .env
docker compose run --rm omnischolar sources
docker compose run --rm omnischolar works openalex A5108007452 --from 2024 --to 2024
docker compose run --rm omnischolar merge openlibrary:"Monica Neagoy" crossref:"Monica Neagoy"
docker compose run --rm omnischolar test
```

License: LGPL-3.0-or-later.
