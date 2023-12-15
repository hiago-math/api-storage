<?php

namespace Infrastructure\Apis\Elasticsearch\Contracts;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

abstract class ElasticsearchServiceAbstract
{
    protected Client $client;

    protected array $structure;

    public function __construct()
    {
        $this->client = ClientBuilder::create()->setHosts([config('custom.ELASTICSEARCH_URL')])->setRetries(0)->build();

    }


    /**
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->structure);
    }

    /**
     * @param array $response
     * @return Collection
     */
    public function validateResponse(array $response): Collection
    {
        $firstHits = Arr::get($response, 'hits', []);
        $secondHits = Arr::get($firstHits,'hits', []);
        return collect($secondHits);
    }
}
