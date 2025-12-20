<?php

namespace Application\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GerarSwagger
{
    private string $filePath;
    private string $urlKcTokenServicos;
    private string $urlKcTokenPortal;
    private string $urlKcTokenRestrito;
    private string $appName;

    public function __construct()
    {
        $this->appName = strtoupper(env('APP_NAME'));
        $this->filePath = base_path('docs/swagger') . '/api-docs.json';

        $appName = str_replace('-', '_', $this->appName);

        $this->urlKcTokenPortal = env($appName . "_KEYCLOAK_BASE_URL") . "/realms/" . env('PORTAL_KEYCLOAK_REALM_NAME') . "/protocol/openid-connect/token";
        $this->urlKcTokenRestrito = env($appName . "_KEYCLOAK_BASE_URL") . "/realms/" . env('RESTRITO_KEYCLOAK_REALM_NAME') . "/protocol/openid-connect/token";
        $this->urlKcTokenServicos = env($appName . "_KEYCLOAK_BASE_URL") . "/realms/" . env($appName . '_KEYCLOAK_REALM') . "/protocol/openid-connect/token";
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
            try {
                $this->addRouteToSwagger($request, $response);
            } catch (\Exception $exception) {
                send_log($exception->getMessage() . ' Line:' . $exception->getLine(), exception: $exception);
            }

        return $response;
    }

    /**
     * @param Request $request
     * @param JsonResponse|BinaryFileResponse $response
     * @return void
     */
    private function addRouteToSwagger(Request $request, JsonResponse|BinaryFileResponse|Response $response): void
    {
        if($response instanceof BinaryFileResponse) return;

        $route = $request->route();
        $uri = '/' . $route->uri();
        $methods = $route->methods();

        $swaggerData = $this->loadExistingSwaggerData();

        if (!isset($swaggerData['paths'][$uri])) {
            $swaggerData['paths'][$uri] = [];
        }

        foreach ($methods as $method) {
            if ($method === 'HEAD') continue;
            $swaggerData['paths'][$uri][strtolower($method)] = [
                'tags' => [ucwords($request->segments()[2])],
                'summary' => "{$method} para a rota: '{$route->getName()}'",
                'description' => 'Descrição da rota',
                'parameters' => $this->formatParameters($route->parameters()),
                'requestBody' => $this->formatResquestBody($request),
                'responses' => $this->formatResponse($response, data_get($swaggerData, "paths.$uri." . strtolower($method) . ".responses", [])),
                "security" => [
                    [
                        "userAuthPortal" => [],
                        "userAuthRestrito" => [],
                        "apiAuth" => [],
                        "bearerAuth" => [],
                    ]
                ]
            ];
        }

        file_put_contents($this->filePath, json_encode($swaggerData, JSON_PRETTY_PRINT));
    }

    /**
     * @return array|mixed
     */
    private function loadExistingSwaggerData()
    {
        $decodedData = $this->padraoInicialSwagger();

        if (file_exists($this->filePath)) {
            $existingData = file_get_contents($this->filePath);

            $decodedData = json_decode($existingData, true);

            if (!isset($decodedData['openapi']) || !isset($decodedData['info']) || !isset($decodedData['paths'])) {

                file_put_contents($this->filePath, json_encode($decodedData, JSON_PRETTY_PRINT));
            }

            return $decodedData;
        }

        return $decodedData;
    }


    /**
     * @param array|null $parameters
     * @return array
     */
    private function formatParameters(?array $parameters): array
    {
        if (is_null($parameters)) $parameters = [];

        $formattedParameters[] = [
            "name" => "Realm",
            "in" => "header",
            "description" => "Realm usado no KeyCloack",
            "required" => true,
            "example" => "Servicos"
        ];

        foreach ($parameters as $name => $parameter) {
            $formattedParameters[] = [
                'name' => $name,
                'in' => 'path',
                'description' => ucfirst($name) . ' parameter',
                'required' => true,
                'example' => $parameter
            ];
        }

        return $formattedParameters;
    }

    /**
     * @param Request $request
     * @return array|null
     */
    private function formatResquestBody(Request $request): ?array
    {
        $content = json_decode($request->getContent(), true);

        if (empty($content)) return null;

        $contentType = !empty($request->headers->get('content-type'))
            ? $request->headers->get('content-type')
            : 'application/json';

        $requestBodyFormatado['required'] = true;
        $requestBodyFormatado['content'] = [
            $contentType => [
                'schema' => $this->formatSchemaRequestBody($content)
            ]
        ];

        return $requestBodyFormatado;
    }

    /**
     * @param JsonResponse $response
     * @param array $existente
     * @return array
     */
    private function formatResponse(JsonResponse $response, array $existente): array
    {
        if (array_key_exists($response->getStatusCode(), $existente)) {
            return $existente;
        }

        $responseContent = json_decode($response->getContent(), true);

        if (is_null($responseContent)) return [$response->getStatusCode() => []];

        $novo = [
            $response->getStatusCode() => [
                'description' => $responseContent['message'],
                'content' => [
                    $response->headers->get('content-type') => [
                        'schema' => $this->formatSchemaResponse($responseContent)
                    ]
                ]
            ]
        ];

        return array_merge_recursive_distinct_v2($existente, $novo);
    }

    /**
     * @param array $response
     * @return array
     */
    private function formatSchemaResponse(array $response): array
    {
        $final = [];
        foreach ($response as $chave => $valor) {
            $final['properties'][$chave] = [
                'type' => gettype($valor),
                is_array($valor) ? 'items' : 'example' => is_array($valor) ? $this->formatSchemaResponse($valor) : $valor
            ];
        }
        return $final;
    }

    /**
     * @param JsonResponse $response
     * @return array
     */
    private function formatSchemaRequestBody(array $request): array
    {
        $final = [];
        $final['type'] = 'object';

        foreach ($request as $chave => $valor) {
            $teste = [
                'type' => gettype($valor),
                'example' => $valor
            ];

            if (is_array($valor)) {
                $final['properties'][$chave] = [
                    'type' => gettype($valor),
                    'items' => $this->formatSchemaRequestBody($valor)
                ];
                continue;
            }

            if (is_file($valor)) {
                $teste = [
                    'type' => 'file'
                ];
            }

            $final['properties'][$chave] = $teste;

        }

        return $final;
    }

    /**
     * @return array
     */
    private function padraoInicialSwagger(): array
    {
        return [
            'openapi' => '3.0.0',
            'info' => [
                'title' => $this->appName . ' OliveiraTrust',
                'description' => 'Oliveira Trust API',
                'contact' => [
                    'name' => 'Developer',
                    'email' => 'fintools@oliveiratrust.com.br'
                ],
                'version' => '1.0'
            ],
            'paths' => [],
            'components' => [
                'securitySchemes' => [
                    'userAuthRestrito' => [
                        'type' => 'oauth2',
                        'description' => 'Logar com dados de usuario simulando o FRONT no Realm RESTRITO',
                        'in' => 'header',
                        'scheme' => 'https',
                        'name' => 'Authorization',
                        'flows' => [
                            "password" => [
                                'tokenUrl' => $this->urlKcTokenRestrito,
                                'scopes' => [],
                                'refreshUrl' => $this->urlKcTokenRestrito,
                            ],
                        ],
                    ],
                    'userAuthPortal' => [
                        'type' => 'oauth2',
                        'description' => 'Logar com dados de usuario simulando o FRONT no Realm Portal',
                        'in' => 'header',
                        'scheme' => 'https',
                        'name' => 'Authorization',
                        'flows' => [
                            "password" => [
                                'tokenUrl' => $this->urlKcTokenPortal,
                                'scopes' => [],
                                'refreshUrl' => $this->urlKcTokenPortal,
                            ],
                        ]
                    ],
                    'apiAuth' => [
                        'type' => 'oauth2',
                        'description' => 'Logar com dados da API simulando microsservicos no Realm Servicos',
                        'in' => 'header',
                        'scheme' => 'https',
                        'name' => 'Authorization',
                        'flows' => [
                            'clientCredentials' => [
                                'tokenUrl' => $this->urlKcTokenServicos,
                                'scopes' => []
                            ]
                        ]
                    ],
                    'bearerAuth' => [
                        'description' => 'Gere um token usando o POSTMAN ou INSOMNIA',
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT'
                    ]
                ],
            ]
        ];
    }
}
