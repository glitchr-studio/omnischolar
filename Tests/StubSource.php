<?php

namespace Omnischolar\Tests;

use Omnischolar\Exception\NotSupportedException;
use Omnischolar\Exception\UnavailableException;
use Omnischolar\Model\Author;
use Omnischolar\Model\Identifier;
use Omnischolar\Model\Work;
use Omnischolar\Source\Capability;
use Omnischolar\Source\Page;
use Omnischolar\Source\Query;
use Omnischolar\Source\SourceInterface;

/** A source that serves the works it is given, two to a page, and logs what it is asked. */
final class StubSource implements SourceInterface
{
    /** @var list<string> */
    public static array $log = [];

    /** @param list<Work> $works */
    public function __construct(
        private readonly string $name,
        private readonly array $works = [],
        private readonly bool $down = false,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function capabilities(): array
    {
        return [Capability::WORKS];
    }

    public function author(Identifier|string $author): ?Author
    {
        throw NotSupportedException::operation($this->name, 'know authors');
    }

    public function works(Identifier|string $author, ?Query $query = null): Page
    {
        $offset = (int) $query?->cursor;
        self::$log[] = \sprintf('%s works %s from %d', $this->name, (string) $author, $offset);
        if ($this->down) {
            throw new UnavailableException($this->name, 'down');
        }
        $slice = \array_slice($this->works, $offset, 2);

        return new Page($slice, $offset + 2 < \count($this->works) ? (string) ($offset + 2) : null, \count($this->works));
    }

    public function work(Identifier|string $id): ?Work
    {
        return null;
    }

    public function search(Query $query): Page
    {
        return new Page();
    }
}
