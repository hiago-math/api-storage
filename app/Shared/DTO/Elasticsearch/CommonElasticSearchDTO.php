<?php

namespace Shared\DTO\Elasticsearch;

use Shared\DTO\DTOAbstract;

abstract class CommonElasticSearchDTO extends DTOAbstract
{
    /**
     * @var string|null
     */
    public ?string $index;

    /**
     * @var string|null
     */
    public ?string $type;

    /**
     * @var array|null
     */
    public ?array $body;

    /**
     * @param string|null $index
     * @param string|null $doctype
     * @param array|null $paylaod
     */
    public function register(?string $index, ?string $type = 'doc', ?array $body = []): self
    {
        $this->index = $index;
        $this->type = $type;
        $this->body = $body;

        return $this;
    }


}
