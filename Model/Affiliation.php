<?php

namespace Omnischolar\Model;

/**
 * A line of a CV: a position held, a degree, a distinction, a membership.
 * Dates are ISO 8601, as precise as the source knows them ("2001", "1930-02").
 */
final readonly class Affiliation
{
    public const EMPLOYMENT = 'employment';
    public const EDUCATION = 'education';
    public const QUALIFICATION = 'qualification';
    public const INVITED_POSITION = 'invited-position';
    public const DISTINCTION = 'distinction';
    public const MEMBERSHIP = 'membership';
    public const SERVICE = 'service';
    /** An institution the source saw on the author's works, without a role nor dates it can vouch for. */
    public const OBSERVED = 'observed';

    /**
     * @param list<int> $years for OBSERVED: the years the institution appears on the works
     */
    public function __construct(
        public string $organization,
        public string $kind = self::EMPLOYMENT,
        public ?string $role = null,
        public ?string $department = null,
        public ?string $start = null,
        public ?string $end = null,
        public ?string $city = null,
        public ?string $country = null,
        public ?string $ror = null,
        public ?string $url = null,
        public array $years = [],
        public ?bool $current = null,
    ) {
    }

    /** Current when the source says so, or when it has a start and no end. */
    public function isCurrent(): bool
    {
        return $this->current ?? (null !== $this->start && null === $this->end);
    }
}
