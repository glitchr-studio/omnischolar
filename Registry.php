<?php

namespace Omnischolar;

use Omnischolar\Exception\InvalidConfigException;
use Omnischolar\Source\SourceFactoryInterface;
use Omnischolar\Source\SourceInterface;

/**
 * The sources by name, each built once from its factory and options:
 *
 *   new Registry([new OpenAlexSourceFactory($http), new HalSourceFactory($http)], [
 *       'openalex' => ['factory' => 'openalex', 'options' => ['mailto' => 'support@glitchr.io']],
 *       'droit' => ['factory' => 'hal', 'options' => ['domains' => ['shs.droit']]],
 *   ]);
 */
final class Registry
{
    /** @var array<string, SourceFactoryInterface> */
    private array $factories = [];

    /** @var array<string, SourceInterface> */
    private array $sources = [];

    /**
     * @param iterable<SourceFactoryInterface>                                       $factories
     * @param array<string, array{factory: string, options?: array<string, mixed>}> $config
     */
    public function __construct(iterable $factories, private readonly array $config)
    {
        foreach ($factories as $factory) {
            $this->factories[$factory->getName()] = $factory;
        }
    }

    public function get(string $name): SourceInterface
    {
        if (isset($this->sources[$name])) {
            return $this->sources[$name];
        }
        $source = $this->config[$name] ?? throw new InvalidConfigException(\sprintf('No "%s" source; configured: %s.', $name, implode(', ', array_keys($this->config)) ?: 'none'));
        $factory = $this->factories[$source['factory']] ?? throw new InvalidConfigException(\sprintf('No "%s" factory for the "%s" source; installed: %s.', $source['factory'], $name, implode(', ', array_keys($this->factories)) ?: 'none'));

        return $this->sources[$name] = $factory->create($source['options'] ?? []);
    }

    public function has(string $name): bool
    {
        return isset($this->config[$name]);
    }

    /** @return list<string> the configured sources, in the configured order */
    public function names(): array
    {
        return array_keys($this->config);
    }

    /** @return array<string, SourceInterface> in the configured order */
    public function all(): array
    {
        $all = [];
        foreach (array_keys($this->config) as $name) {
            $all[$name] = $this->get($name);
        }

        return $all;
    }

    /** @return list<string> the factories installed: what `factory:` may name */
    public function factories(): array
    {
        return array_keys($this->factories);
    }

    /** The options a source is configured with (its factory's defaults not included). */
    public function options(string $name): array
    {
        return $this->config[$name]['options'] ?? [];
    }
}
