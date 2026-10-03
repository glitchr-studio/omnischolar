<?php

namespace Omnischolar\Source;

/** Builds a source from its options (a contact address, a key, a domain...). */
interface SourceFactoryInterface
{
    /** The name sources are configured with: "openalex", "hal"... */
    public function getName(): string;

    /** @param array<string, mixed> $options */
    public function create(array $options = []): SourceInterface;
}
