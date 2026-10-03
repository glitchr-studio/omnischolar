<?php

namespace Omnischolar\Model;

/** Where a work appeared: a journal, a conference, a repository, a book series. */
final readonly class Venue
{
    public const JOURNAL = 'journal';
    public const CONFERENCE = 'conference';
    public const BOOK = 'book';
    public const BOOK_SERIES = 'book_series';
    public const REPOSITORY = 'repository';
    public const OTHER = 'other';

    /**
     * @param list<string> $issn print and electronic, normalised (2041-6520)
     */
    public function __construct(
        public string $name,
        public string $type = self::JOURNAL,
        public array $issn = [],
        public ?string $publisher = null,
        public ?string $abbreviation = null,
        public Identifiers $identifiers = new Identifiers(),
        public ?string $url = null,
    ) {
    }
}
