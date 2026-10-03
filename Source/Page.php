<?php

namespace Omnischolar\Source;

use Omnischolar\Model\Work;

/**
 * One page of works. $next is the cursor of the following page (pass it
 * as Query::$cursor), null on the last one.
 *
 * @implements \IteratorAggregate<int, Work>
 */
final readonly class Page implements \IteratorAggregate, \Countable
{
    /**
     * @param list<Work> $works
     * @param int|null   $total how many works the whole query matches, when the source says
     */
    public function __construct(
        public array $works = [],
        public ?string $next = null,
        public ?int $total = null,
    ) {
    }

    public function isLast(): bool
    {
        return null === $this->next;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->works);
    }

    public function count(): int
    {
        return \count($this->works);
    }
}
