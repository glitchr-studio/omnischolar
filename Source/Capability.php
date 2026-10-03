<?php

namespace Omnischolar\Source;

/** What a source can be asked, and what its answers hold. */
enum Capability: string
{
    /** author(): a profile. */
    case AUTHOR = 'author';
    /** works(): an author's works, page by page. */
    case WORKS = 'works';
    /** work(): one work by an identifier. */
    case WORK = 'work';
    /** search(): works by text, title, author, year, type. */
    case SEARCH = 'search';
    /** An author's counts: works, citations, h-index. */
    case METRICS = 'metrics';
    /** Each work's citation count. */
    case CITATIONS = 'citations';
    case ABSTRACTS = 'abstracts';
    /** Open-access status and full-text links. */
    case OPEN_ACCESS = 'open_access';
    /** Book covers. */
    case COVERS = 'covers';
    /** A CV: positions, degrees, distinctions. */
    case AFFILIATIONS = 'affiliations';
    /** Filtering by subject domain (Query::$domains). */
    case DOMAINS = 'domains';
}
