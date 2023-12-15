<?php

namespace Infrastructure\Apis\ExemploExternalApi\Services;

use Infrastructure\Apis\BaseServiceApi;
use Infrastructure\Apis\ExemploExternalApi\Interfaces\IExternalApi;

class ExternalApi extends BaseServiceApi implements IExternalApi
{
    public function __construct()
    {
        $this->setBaseUrl(config('custom.SERVICE_EXTERNAL_API'));
    }

    /**
     * {@inheritDoc}
     */
    public function exampleRequest(array $payload)
    {
        $response = $this->request('GET', $payload);

        return json_decode($response, true);
    }
}
