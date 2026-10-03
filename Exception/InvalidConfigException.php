<?php

namespace Omnischolar\Exception;

/** A source misconfigured: a key missing, an unknown factory. */
final class InvalidConfigException extends \LogicException implements OmnischolarException
{
}
