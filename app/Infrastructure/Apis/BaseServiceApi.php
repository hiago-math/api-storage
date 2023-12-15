<?php

namespace Infrastructure\Apis;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Str;

class BaseServiceApi
{
    /**
     * @var Client
     */
    protected Client $client;

    /**
     * @var array
     */
    protected array $headers = [];

    /**
     * @var string
     */
    protected string $baseUrl;

    /**
     * @return string
     */
    protected function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     */
    protected function setBaseUrl($baseUrl)
    {
        $lastChar = Str::substr($baseUrl, -1);
        if ($lastChar !== DIRECTORY_SEPARATOR) $baseUrl .= DIRECTORY_SEPARATOR;

        $this->baseUrl = $baseUrl;
    }

    /**
     * @return string
     */
    protected function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $headers
     */
    protected function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $payload
     * @param null $file
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function request(string $method, string $uri, array $payload = [], $file = null)
    {
        $this->updateClient();

        $data = [];
        $request = new Request($method, $uri);

        if (!empty($payload)) $data['json'] = $payload;

        if (!empty($file)) {
            $multiData['multipart'][] = ['Content-type' => 'application/json', 'name' => 'json', 'contents' => json_encode($payload)];
            $fileContent = ['Content-type' => 'multipart/form-data', 'name' => 'file', 'contents' => is_string($file) ? $file : file_get_contents($file)];

            $fileContent['Mime-Type'] = $file->getMimeType();
            $fileContent['filename'] = $file->getClientOriginalName();

            $multiData['multipart'][] = $fileContent;

            $multipart = new MultipartStream($multiData['multipart']);
            $request = $request->withBody($multipart);

            send_log("Log de request -> {$uri}: ", [$this->baseUrl, $method, $uri, $multiData]);
        }

        if (empty($data)) {
            $data['form-params'] = [];
        }

        send_log("Log de request -> {$uri}: ", [$this->baseUrl, $method, $uri, $data]);

        try {
            $send = $this->client->send($request, $data);
        } catch (ClientException $exception) {
            $response = $exception->getResponse();

            send_log('ClientException', ['code' => $response->getStatusCode(), 'response' => $response->getBody()], 'error', $exception);

            throw $exception;
        }

        $contentsResponse = $send->getBody()->getContents();

        send_log("Log de response -> {$uri}: ", [$uri, $contentsResponse]);

        return $contentsResponse;
    }

    protected function updateClient()
    {
        $token = '';

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => $token
            ], $this->headers
        ]);
    }

}
