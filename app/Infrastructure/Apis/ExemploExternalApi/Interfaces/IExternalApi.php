<?php

namespace Infrastructure\Apis\ExemploExternalApi\Interfaces;

interface IExternalApi
{
    /**
     * @param array $payload
     * @return mixed
     */
    public function exampleRequest(array $payload);
}
