# Models and identifiers

All models are read-only (`readonly` classes); `Work::with([...])` and `Query::with([...])` give
modified copies.

## Work

| Field | |
|---|---|
| `title`, `subtitle` | `fullTitle()` joins them |
| `type` | `WorkType`: `article`, `book`, `chapter`, `conference` (communications, posters), `preprint`, `thesis`, `report`, `dataset`, `software`, `review`, `editorial`, `other` |
| `authors` | `Contributor` list, editors included (`role`); `authorsOnly()`, `editors()`, `firstAuthor()` |
| `year`, `date` | the date is ISO 8601, as precise as known: `2024`, `2024-09`, `2024-09-25` |
| `venue` | `Venue`: name, type (`journal`, `conference`, `book`, `book_series`, `repository`), ISSNs, publisher, abbreviation |
| `publisher`, `volume`, `issue`, `pages`, `edition` | |
| `abstract`, `language`, `keywords`, `domains` | `domains`: HAL domains, arXiv / INSPIRE categories |
| `openAccess`, `url`, `pdfUrl`, `license` | `link()`: the PDF, else the landing page, else the DOI |
| `cover` | a cover image URL (Open Library) |
| `citations` | the count of the source it came from |
| `identifiers` | `Identifiers` |
| `source`, `sources` | the source of the record; after a merge, every source merged |
| `editions` | a book's editions, newest first (Merger) |
| `note` | arXiv's journal reference or comment |

## Author

`name`, `given`, `family`, `identifiers`, `alternativeNames`, `affiliations` (`Affiliation`: a
position, a degree, a distinction... with `kind`, `role`, `department`, `start`, `end`, `city`,
`country`, `ror`; `isCurrent()`; `affiliations(Affiliation::EMPLOYMENT)` filters), `metrics`
(`Metrics`: works, citations, h-index, i10-index, two-year mean citedness, counts by year - as one
source computes them), `urls`, `biography`, `keywords`, `country`.

## Identifier

```php
Identifier::parse('https://doi.org/10.1039/D4SC04973J');   // doi:10.1039/d4sc04973j
Identifier::parse('1-4129-9660-0');                         // isbn:9781412996600
Identifier::parse('arXiv:hep-th/9711200v3');                // arxiv:hep-th/9711200
Identifier::parse('bai:Juan.M.Maldacena.1');                // inspire_bai:Juan.M.Maldacena.1
Identifier::of('idhal', 'keitaro-nakatani');
Identifier::tryOf(Scheme::ORCID, '0000-0002-1825-0098');    // null: bad checksum
```

| Scheme | Normalised as |
|---|---|
| `doi` | lower case, without `https://doi.org/` or `doi:` |
| `isbn` | ISBN-13, checksum checked; an ISBN-10 is converted (`isbn10()` gives it back) |
| `issn` | `NNNN-NNNC`, checksum checked |
| `arxiv` | without its version: `2401.12345`, `hep-th/9711200` |
| `hal`, `idhal` | without version: `hal-04772417`; idHAL lower case |
| `orcid` | `0000-0000-0000-000X`, checksum checked |
| `openalex` | `W...`, `A...`, `S...`, `I...` |
| `inspire`, `inspire_bai` | the record number; the BAI as written |
| `openlibrary` | `OL...W`, `OL...M`, `OL...A` |
| `pmid`, `pmcid` | |

`parse()` reads `scheme:value`, the resolver URLs of each service, and the values whose shape
tells their scheme; a bare number (an INSPIRE record? a PubMed id?) needs its scheme.
`Identifiers` keeps each identifier once: `get()`, `value()`, `values()`, `has()`, `with()`,
`merge()`, `toArray()` / `fromArray()` for storage.
