<?php

namespace Omnischolar\Tests;

use Omnischolar\Collector;
use Omnischolar\Exception\InvalidConfigException;
use Omnischolar\Registry;
use PHPUnit\Framework\TestCase;

final class RegistryTest extends TestCase
{
    protected function setUp(): void
    {
        StubSource::$log = [];
    }

    private function registry(): Registry
    {
        return new Registry([new StubSourceFactory()], [
            'hal' => ['factory' => 'stub', 'options' => ['name' => 'hal', 'titles' => ['Un', 'Deux', 'Trois']]],
            'openalex' => ['factory' => 'stub', 'options' => ['name' => 'openalex', 'titles' => ['Deux', 'Quatre']]],
            'arxiv' => ['factory' => 'stub', 'options' => ['name' => 'arxiv', 'down' => true]],
        ]);
    }

    public function testSourcesByNameBuiltOnce(): void
    {
        $registry = $this->registry();

        self::assertSame(['hal', 'openalex', 'arxiv'], $registry->names());
        self::assertSame(['stub'], $registry->factories());
        self::assertSame($registry->get('hal'), $registry->get('hal'));
        self::assertTrue($registry->has('openalex'));
        self::assertSame(['name' => 'arxiv', 'down' => true], $registry->options('arxiv'));
        self::assertCount(3, $registry->all());

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('No "crossref" source; configured: hal, openalex, arxiv.');
        $registry->get('crossref');
    }

    public function testARequiredOption(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('The "stub" source needs: name.');
        (new Registry([new StubSourceFactory()], ['x' => ['factory' => 'stub']]))->get('x');
    }

    public function testTheCollectorReadsEveryPageMergesAndSaysWhatItSkipped(): void
    {
        $collector = new Collector($this->registry());
        $works = $collector->collect(['hal' => 'someone', 'openalex' => 'A1', 'arxiv' => 'Someone']);

        self::assertSame(['hal works someone from 0', 'hal works someone from 2', 'openalex works A1 from 0', 'arxiv works Someone from 0'], StubSource::$log);
        self::assertCount(4, $works, '"Deux" in both, once');
        self::assertSame(['arxiv' => '[arxiv] down'], $collector->incomplete);
        $deux = array_values(array_filter($works, static fn ($w) => 'Deux' === $w->title))[0];
        self::assertSame(['openalex', 'hal'], $deux->sources);
    }

    public function testAllStopsAtTheMost(): void
    {
        self::assertCount(2, Collector::all($this->registry()->get('hal'), 'x', null, 2));
    }
}
