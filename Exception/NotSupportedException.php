<?php

namespace Omnischolar\Exception;

/** The source does not read that kind of identifier, or does not do that at all. */
final class NotSupportedException extends \LogicException implements OmnischolarException
{
    public static function identifier(string $source, string $identifier, string $what = 'look up'): self
    {
        return new self(\sprintf('The "%s" source cannot %s "%s".', $source, $what, $identifier));
    }

    public static function operation(string $source, string $operation): self
    {
        return new self(\sprintf('The "%s" source does not %s.', $source, $operation));
    }
}
