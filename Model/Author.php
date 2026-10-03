<?php

namespace Omnischolar\Model;

/** A researcher's profile, as one source knows it. */
final readonly class Author
{
    /**
     * @param list<string>      $alternativeNames how the name is also spelt
     * @param list<Affiliation> $affiliations     positions, degrees, distinctions; or institutions observed on the works
     * @param list<string>      $urls             their pages (personal site, institution, profiles)
     * @param list<string>      $keywords         research topics
     */
    public function __construct(
        public string $name,
        public ?string $given = null,
        public ?string $family = null,
        public Identifiers $identifiers = new Identifiers(),
        public array $alternativeNames = [],
        public array $affiliations = [],
        public ?Metrics $metrics = null,
        public array $urls = [],
        public ?string $biography = null,
        public array $keywords = [],
        public ?string $country = null,
        public ?string $source = null,
    ) {
    }

    /** @return list<Affiliation> those of one kind (Affiliation::EMPLOYMENT...) */
    public function affiliations(string $kind): array
    {
        return array_values(array_filter($this->affiliations, static fn (Affiliation $a) => $a->kind === $kind));
    }

    public function orcid(): ?string
    {
        return $this->identifiers->value(Scheme::ORCID);
    }
}
