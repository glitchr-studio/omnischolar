<?php

namespace Omnischolar\Source;

use Omnischolar\Model\WorkType;

/**
 * What to ask works() and search() for. Every criterion is optional; a
 * source applies those it understands (its README says which) and ignores
 * the others, except $domains, which a source without
 * Capability::DOMAINS refuses rather than answer unfiltered.
 *
 *   new Query(text: 'responsabilité civile', domains: ['shs.droit'], from: 2020, limit: 50)
 *   $query->with(['cursor' => $page->next])
 */
final readonly class Query
{
    public const NEWEST = 'newest';
    public const OLDEST = 'oldest';
    public const RELEVANCE = 'relevance';
    public const CITED = 'cited';

    /**
     * @param list<WorkType> $types   only these kinds of works
     * @param list<string>   $domains subject codes: a HAL domain (shs.droit), an arXiv or INSPIRE category (hep-th)
     * @param int            $limit   works per page (each source caps it)
     * @param string|null    $cursor  the Page::$next of the page before
     */
    public function __construct(
        public ?string $text = null,
        public ?string $title = null,
        public ?string $author = null,
        public ?int $from = null,
        public ?int $to = null,
        public array $types = [],
        public array $domains = [],
        public ?bool $openAccess = null,
        public int $limit = 25,
        public ?string $cursor = null,
        public string $sort = self::NEWEST,
    ) {
    }

    public function with(array $changes): self
    {
        return new self(...array_merge(get_object_vars($this), $changes));
    }

    /** The year range, as one inclusive pair (either end may be open). */
    public function hasYears(): bool
    {
        return null !== $this->from || null !== $this->to;
    }

    /** Whether a year falls within the range (an unknown year does when there is no range). */
    public function inYears(?int $year): bool
    {
        if (!$this->hasYears()) {
            return true;
        }

        return null !== $year && (null === $this->from || $year >= $this->from) && (null === $this->to || $year <= $this->to);
    }
}
