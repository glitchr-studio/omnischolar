<?php

/**
 * The sources, their options from the environment (.env): a source is
 * configured only when every key under "needs" is set (none needs one).
 *
 * @return array<string, array{factory: string, needs: list<string>, options: array<string, mixed>}>
 */
$env = static fn (string $key, mixed $default = null): mixed => (false !== ($v = getenv($key)) && '' !== $v) ? $v : $default;
$mailto = $env('OMNISCHOLAR_MAILTO');
$domains = array_values(array_filter(array_map('trim', explode(',', (string) $env('HAL_DOMAINS', '')))));

return [
    'openalex' => ['factory' => 'openalex', 'needs' => [], 'options' => ['mailto' => $mailto, 'api_key' => $env('OPENALEX_API_KEY')]],
    'hal' => ['factory' => 'hal', 'needs' => [], 'options' => ['domains' => $domains ?: null, 'mailto' => $mailto]],
    'droit' => ['factory' => 'hal', 'needs' => [], 'options' => ['domains' => ['shs.droit'], 'mailto' => $mailto]],
    'crossref' => ['factory' => 'crossref', 'needs' => [], 'options' => ['mailto' => $mailto]],
    'orcid' => ['factory' => 'orcid', 'needs' => [], 'options' => ['mailto' => $mailto]],
    'openlibrary' => ['factory' => 'openlibrary', 'needs' => [], 'options' => ['mailto' => $mailto]],
    'arxiv' => ['factory' => 'arxiv', 'needs' => [], 'options' => ['mailto' => $mailto]],
    'inspire' => ['factory' => 'inspire', 'needs' => [], 'options' => ['mailto' => $mailto]],
];
