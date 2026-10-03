<?php

namespace Omnischolar\Exception;

/**
 * The service could not be reached, or is down: never "not found". A
 * caller that caches an answer must not cache this one.
 */
class UnavailableException extends ProviderException
{
}
