<?php

namespace Omnischolar\Tests;

use Omnischolar\Config;
use Omnischolar\Model\Work;
use Omnischolar\Model\WorkType;
use Omnischolar\Source\SourceFactory;
use Omnischolar\Source\SourceInterface;

/** Builds StubSources: options "name", "titles" (one work each, its source the name), "down". */
final class StubSourceFactory extends SourceFactory
{
    protected function populate(Config $c): void
    {
        $c->defaults(['omnischolar.factory_name' => 'stub', 'omnischolar.required_options' => ['name'], 'titles' => [], 'down' => false]);
    }

    protected function build(Config $c): SourceInterface
    {
        $works = array_map(static fn (string $title) => new Work($title, WorkType::ARTICLE, year: 2024, source: $c['name']), $c['titles']);

        return new StubSource($c['name'], $works, (bool) $c['down']);
    }
}
