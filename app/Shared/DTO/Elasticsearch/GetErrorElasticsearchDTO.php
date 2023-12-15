<?php

namespace Shared\DTO\Elasticsearch;

use Shared\DTO\DTOAbstract;

class GetErrorElasticsearchDTO extends DTOAbstract
{
    /**
     * @var string|null
     */
    public ?string $index;

    /**
     * @var string|null
     */
    public ?string $message;

    /**
     * @var string|null
     */
    public ?string $message_exception;

    /**
     * @var int|null
     */
    public ?int $code;

    /**
     * @param string|null $index
     * @param string|null $message
     * @param string|null $message_exception
     * @param int|null $code
     * @return GetErrorElasticsearchDTO
     */
    public function register(?string $index = null, ?string $message = null, ?string $message_exception = null, ?int $code = null)
    {
        $this->index = $index;
        $this->message = $message;
        $this->message_exception = $message_exception;
        $this->code = $code;

        return $this;
    }
}
