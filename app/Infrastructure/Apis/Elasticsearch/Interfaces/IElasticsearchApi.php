<?php

namespace Infrastructure\Apis\Elasticsearch\Interfaces;

use Illuminate\Support\Collection;
use Shared\DTO\Elasticsearch\CreateElasticsearchDTO;
use Shared\DTO\Elasticsearch\SearchElasticsearchDTO;

interface IElasticsearchApi
{
    /**
     * @param CreateElasticsearchDTO $createElasticsearchDto
     * @return Collection
     */
    public function create(CreateElasticsearchDTO $createElasticsearchDto): Collection;

    /**
     * @param SearchElasticsearchDTO $searchElasticsearchDto
     * @return Collection
     */
    public function search(SearchElasticsearchDTO $searchElasticsearchDto): Collection;
}
