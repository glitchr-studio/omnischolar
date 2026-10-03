<?php

namespace Omnischolar;

use Omnischolar\Exception\InvalidConfigException;

/**
 * A source's configuration while its factory builds it: the options
 * given, the factory's defaults under them, and the keys that must be filled.
 *
 * @extends \ArrayObject<string, mixed>
 */
final class Config extends \ArrayObject
{
    /** Sets each key not set yet (a null counts as not set). */
    public function defaults(array $defaults): self
    {
        foreach ($defaults as $key => $value) {
            if (!$this->offsetExists($key) || null === $this[$key]) {
                $this[$key] = $value;
            }
        }

        return $this;
    }

    /** @param string[] $keys */
    public function validateNotEmpty(array $keys): void
    {
        $missing = array_values(array_filter($keys, fn (string $key) => !isset($this[$key]) || '' === $this[$key] || [] === $this[$key]));
        if ($missing) {
            throw new InvalidConfigException(\sprintf('The "%s" source needs: %s.', $this['omnischolar.factory_name'] ?? '?', implode(', ', $missing)));
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->offsetExists($key) && null !== $this[$key] ? $this[$key] : $default;
    }
}
