<?php

namespace Omnischolar\Model;

/** An author's counts, as one source computes them: they differ from one source to the next. */
final readonly class Metrics
{
    /**
     * @param array<int, array{works?: int|null, citations?: int|null}> $byYear by year, oldest first
     */
    public function __construct(
        public ?int $works = null,
        public ?int $citations = null,
        public ?int $hIndex = null,
        public ?int $i10Index = null,
        public ?float $twoYearMeanCitedness = null,
        public array $byYear = [],
        public ?string $source = null,
    ) {
    }
}
