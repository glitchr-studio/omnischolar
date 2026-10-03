# The Docker harness

`docker/` runs this package with every `omnischolar/*` source installed - from GitHub (branch
1.x), or from the checkouts beside this one when `OMNISCHOLAR_PLUGINS=../..` is set in
`docker/.env` - and a console that asks the real services.

```sh
cd docker && cp .env.dist .env       # OMNISCHOLAR_MAILTO, an OpenAlex key if you have one
docker compose run --rm omnischolar sources
```

| Command | |
|---|---|
| `sources` | the sources installed and configured, and what each can do |
| `author <source> <author>` | a profile (JSON) |
| `works <source> <author>` | an author's works, every page up to `--max` |
| `work <source> <id>` | one work (JSON) |
| `search <source> [text]` | `--title`, `--author` |
| `merge <source:author>...` | several sources merged; `--no-editions` |
| `test` | every package's tests |

`works`, `search` and `merge` take `--from`, `--to`, `--type` (repeatable), `--domain`
(repeatable), `--sort` (`newest`, `oldest`, `relevance`, `cited`), `--limit` (per page),
`--max`, and `--format` (`table`, `json`, `bibtex`, `ris`, `csl-json`).

The configured sources are `openalex`, `hal`, `droit` (HAL filtered on `shs.droit`), `crossref`,
`orcid`, `openlibrary`, `arxiv`, `inspire`.

```sh
docker compose run --rm omnischolar works openalex A5108007452 --from 2024 --to 2024
docker compose run --rm omnischolar works openlibrary "Monica Neagoy"
docker compose run --rm omnischolar merge openlibrary:"Monica Neagoy" crossref:"Monica Neagoy"
docker compose run --rm omnischolar merge openalex:A5108007452 hal:"Keitaro Nakatani" --from 2024 --format bibtex
docker compose run --rm omnischolar search droit "responsabilité civile" --from 2024
docker compose run --rm omnischolar author inspire bai:Juan.M.Maldacena.1
docker compose run --rm omnischolar work crossref 10.1039/d4sc04973j
```

The image is `php:8.4-cli-alpine` with `intl` and Composer; the harness's packages live in the
`harness` volume of the `omnischolar-harness` project.
