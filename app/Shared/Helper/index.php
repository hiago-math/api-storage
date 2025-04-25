<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Infrastructure\Elasticsearch\Rest;
use Jenssegers\Mongodb\Connection;
use MongoDB\Driver\Exception\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\{Cache, Log};

if (!function_exists('get_files_routes')) {

    /**
     * @param string $folderPath
     */
    function get_files_routes(string $folderPath): void
    {
        if (!is_dir($folderPath)) {
            die("A pasta '$folderPath' não existe.");
        }

        $files = scandir($folderPath);

        foreach ($files as $file) {
            if (is_file($folderPath . '/' . $file) && $file !== '.' && $file !== '..') {
                require_once $folderPath . '/' . $file;
            }
        }
    }
}

if (!function_exists('instantiate_class')) {

    /**
     * @param string $class
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    function instantiate_class(string $class): mixed
    {
        return app()->make($class);
    }
}

if (!function_exists('get_ddd_domains')) {

    /**
     * @return array
     */
    function get_ddd_domains(): array
    {
        $domains = [];

        $diretorios = base_path("app" . DIRECTORY_SEPARATOR . "Domain");
        if (File::exists($diretorios)) {
            collect(File::directories($diretorios))
                ->map(
                    function ($diretorios) use (&$domains) {
                        $domains[] = Str::afterLast($diretorios, DIRECTORY_SEPARATOR);
                    }
                );
        }

        return $domains;
    }
}

if (!function_exists('get_ddd_infrastructure_apis')) {

    /**
     * @return array
     */
    function get_ddd_infrastructure_apis(): array
    {
        $domains = [];

        $diretorios = base_path("app" . DIRECTORY_SEPARATOR . "Infrastructure/Apis");
        if (File::exists($diretorios)) {
            collect(File::directories($diretorios))
                ->map(
                    function ($diretorios) use (&$domains) {
                        $domains[] = Str::afterLast($diretorios, DIRECTORY_SEPARATOR);
                    }
                );
        }

        return $domains;
    }
}

if (!function_exists('remove_values_null')) {

    /**
     * @param array $array
     * @return array
     */
    function remove_values_null(array $itens): array
    {
        return array_filter($itens, function ($item) {
            return !is_null($item);
        });
    }
}

if (!function_exists('send_log')) {

    /**
     * @param string $messgae
     * @param array $options
     * @param string $type
     * @param Exception $exception
     * @return void
     */
    function send_log(string $message, array $options = [], string $type = "info", \Exception $exception = null)
    {
//        $doctype = \Shared\Enums\DocTypesElasticsearchEnum::DOC;
        if (!is_null($exception)) {
            $options['message_exception'] = $exception->getMessage();
            $options['code'] = $exception->getCode();
            $options['file'] = $exception->getFile() . ": " . $exception->getLine();
            $options['trace'] = $exception->getTraceAsString();
//            $doctype = \Shared\Enums\DocTypesElasticsearchEnum::ERROR;
        }

        Log::$type($message, $options);
        $options['message'] = $message;

//        create_log_elastic($type, $doctype, $options);
    }
}

if (!function_exists('create_log_elastic')) {

    /**
     * @param string $index
     * @param string $doctype
     * @param array $options
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    function create_log_elastic(string $index, string $doctype, array $options)
    {
        $logService = instantiate_class(\Domain\Logs\Interfaces\Services\ILogService::class);
        $createElasticsearchDto = instantiate_class(\Shared\DTO\Elasticsearch\CreateElasticsearchDTO::class);

        $createElasticsearchDto->register($index, $doctype, $options);
        $logService->createLog($createElasticsearchDto);
    }
}

if (!function_exists('remove_mask_zip_code')) {

    /**
     * @param string $zip_code
     * @return string
     */
    function remove_mask_zip_code(string $zip_code): string
    {
        return str_replace('-', '', $zip_code);
    }
}

if (!function_exists('add_extension')) {

    /**
     * @param string $filename
     * @param string $extension
     * @return string
     */
    function add_extension(string $filename, string $extension): string
    {
        $extensionFile = Str::afterLast($filename, '.');
        if ($extensionFile === $extension) return $filename;

        return "$filename.$extension";
    }
}

if (!function_exists('prepare_errors_validators')) {

    /**
     * @param array $errors
     * @return array
     */
    function prepare_errors_validators(array $errors): array
    {
        $error = [];
        foreach ($errors as $field => $value) {
            $error[] = $value;
        }

        return $error;
    }
}

if (!function_exists('remove_null_array')) {

    /**
     * @param array $array
     * @return array
     */
    function remove_null_array(array $array): array
    {
        return array_filter($array, function ($value) {
            return $value !== null;
        });
    }
}

if (!function_exists('get_hash_file')) {

    /**
     * @param string $binFile
     * @return string
     */
    function get_hash_file(string $content): string
    {
        return hash('md5', $content);
    }
}

if (!function_exists('db_mongo_check')) {

    /**
     * @return string
     */
    function db_mongo_check(): string
    {
        try {
            $mongodb = new Connection(config('database.connections.mongodb'));;
            $con = $mongodb->getMongoClient()->listDatabaseNames();
            if (!empty($con)) return "Ok";
            return "Error";
        } catch (AuthenticationException $authenticationException) {
            return "Error";
        }
    }
}

if (!function_exists('db_redis_check')) {

    /**
     * @return string
     */
    function db_redis_check(): string
    {
        try {
            $client = new \Predis\Client([
                'scheme' => 'tcp',
                'host' => 'redis',
                'port' => 6379,
            ]);
            $client->ping();
            return "Ok";
        } catch (\Exception $exception) {
            return "Error";
        }
    }
}

if (!function_exists('time_start_app')) {

    /**
     * @return string
     */
    function time_start_app(): string
    {
        $timeStarted = Storage::get('uptime.txt');
        return Carbon::parse($timeStarted)->timezone(config('app.timezone'))->diffForHumans();
    }
}

if (!function_exists('memory_usage')) {

    /**
     * @return string
     */
    function memory_usage(): string
    {
        return round(memory_get_usage() / (1024 * 1024), 2) . ' MB';
    }
}

if (!function_exists('compress_binary_file')) {

    /**
     * @param string|null $binary
     * @return string|null
     */
    function compress_binary_file(?string $binary = null): ?string
    {
        if (!$binary) return $binary;

        $binary =  new \MongoDB\BSON\Binary($binary, \MongoDB\BSON\Binary::TYPE_GENERIC);

        return utf8_encode(gzcompress($binary));
    }
}

if (!function_exists('uncompressed_binary_file')) {

    /**
     * @param string|null $binary
     * @return string|null
     */
    function uncompressed_binary_file(?string $binary = null): ?string
    {
        if (!$binary) return $binary;

        return gzuncompress(utf8_decode($binary));
    }
}


if (!function_exists('sanitizar_string')) {
    function sanitizar_string(?string $palavra = null): ?string
    {
        if (is_null($palavra)) return $palavra;

        $palavra = str_replace(' ', '_', $palavra);

        $acentos = [
            'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì',
            'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü',
            'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë',
            'ì', 'í', 'î', 'ï', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú',
            'û', 'ü', 'ý', 'ÿ'
        ];

        $semAcentos = [
            'A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I',
            'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U',
            'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u',
            'u', 'u', 'y', 'y'
        ];

        $palavra = str_replace($acentos, $semAcentos, $palavra);

        $caracteresEspeciais = [
            '@', '&', 'ç'
        ];

        $caracteresSemEspeciais = [
            'a', '_e_', 'c'
        ];

        $palavra = str_replace($caracteresEspeciais, $caracteresSemEspeciais, $palavra);

        $pontuacaoParaRemover = [
            '.', ',', '!', '/', '(', ')', 'º', ':'
        ];

        $palavra = str_replace($pontuacaoParaRemover, '', $palavra);

        return strtolower($palavra);
    }
}

if (!function_exists('get_modules')) {
    function get_modules(int $cacheMinutos = 60, array $tags = []): array
    {
        $separator = DIRECTORY_SEPARATOR;
        $domains = get_by_cache('list_modules', []);
        if (is_local() || empty($domains)) {
            $domains = [];
            $dir = base_path('app' . $separator . 'Modules');
            if (Illuminate\Support\Facades\File::exists($dir)) {
                collect(Illuminate\Support\Facades\File::directories($dir))
                    ->map(
                        function ($dir) use ($separator, &$domains) {
                            $domains[] = Illuminate\Support\Str::afterLast($dir, $separator);
                        });
                save_in_cache('list_modules', $domains, $cacheMinutos, $tags);
            }
        }
        return $domains;
    }
}


if (!function_exists('is_laravel')) {
    function is_laravel()
    {
        return app() instanceof \Illuminate\Foundation\Application;
    }
}

if (!function_exists('is_production')) {
    function is_production()
    {
        return in_array(config('app.env'), ['production', 'prod']);
    }
}

if (!function_exists('is_local')) {
    function is_local()
    {
        return in_array(config('app.env'), ['local', 'localhost']);
    }
}

if (!function_exists('is_develop')) {
    function is_develop()
    {
        return in_array(config('app.env'), ['develop', 'dev']);
    }
}

if (!function_exists('is_hmg')) {
    function is_hmg()
    {
        return in_array(config('app.env'), ['homol', 'hmg']);
    }
}

if (!function_exists('response_api')) {
    /**
     * Retorno padrao para as resposta para API
     * @param $data
     * @param bool $status
     * @param string $message
     * @param int $status_code
     * @return JsonResponse
     */
    function response_api($data, bool $status = true, string $message = '', int $status_code = 200): JsonResponse
    {
        return response()->json([
            'success' => $status,
            'message' => $message,
            'data' => is_array($data) ? $data : [$data]
        ], $status_code);
    }
}

if (!function_exists('response_ok')) {
    /**
     * Retorno padrao de SUCESSO para as resposta para API
     * @param $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    function response_ok($data, string $message = '', int $statusCode = 200): JsonResponse
    {
        return response_api($data, true, $message, $statusCode);
    }
}

if (!function_exists('response_no')) {
    /**
     * Retorno padrao de ERROR para as resposta para API
     * @param $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    function response_no($data, string $message = '', int $statusCode = 400): JsonResponse
    {
        $data = is_array($data) ? $data : [$data];
        return response_api($data, false, $message, $statusCode);
    }
}

if (!function_exists('pluck_matriz')) {
    /**
     * extrair parte de um ARRAY/MATRIZ com base num indice
     * @param array $array
     * @param $idx
     * @param array $setup
     * @return array
     */
    function pluck_matriz(array $array, $idx, $setup = [])
    {
        $dados = [];
        foreach ($array as $row) {

            if (!is_array($idx)) {
                $valor = \Illuminate\Support\Arr::get($row, $idx, null);
                if (!empty($valor))
                    $dados[] = $valor;
            } else {
                $tmp = [];
                foreach ($idx as $key) {
                    $valor = \Illuminate\Support\Arr::get($row, $key, null);
                    if (!empty($valor))
                        $tmp[$key] = $valor;
                }
                if ($tmp) {
                    $dados[] = $tmp;
                }
            }
        }

        return $dados;
    }
}

if (!function_exists('group_array_by_id')) {

    /**
     * extrai um determinado indice unico e coloca o mesmo como indice do array
     * @param $array
     * @param $nameIdx
     * @return array
     *
     * setKeyidxByMatriz( $array, 'codigo' )
     * DE
     * [0] => [
     * 'codigo' => '102030',
     * 'name' => fulano,
     * 'email' => fulano@
     * ]
     * PARA
     * [102030] => [
     * 'codigo' => 102030,
     * 'name' => fulano,
     * 'email' => fulano@
     * ]
     *
     */
    function group_array_by_id($array, $nameIdx)
    {
        $tmp = [];
        if (is_array($array) && !empty($array)) {
            foreach ($array as $row) {
                $tmp[Illuminate\Support\Arr::{'get'}($row, $nameIdx)] = $row;
            }
        }
        return $tmp;
    }
}

if (!function_exists('remove_duplicados')) {
    function remove_duplicados($array, $nameIdx)
    {
        return array_values(group_array_by_id($array, $nameIdx));
    }
}

if (!function_exists('send_log_error')) {
    function send_log_error($msg, $channel = 'slack')
    {
        if (!empty($msg)) {
            $msg = is_array($msg) ? $msg : [$msg];
            app('log')->channel($channel)->error(config('app.name'), $msg);
        }
        return true;
    }
}

if (!function_exists('search_like_in_array')) {

    /**
     * @param $array
     * @param $search
     * @return false|int|string
     */
    function search_like_in_array($array, $search)
    {
        foreach ($array as $key => $value) {
            $current_key = $key;
            if ($search === $value or (is_array($value) && search_like_in_array($value, $search) !== false)) {
                return $current_key;
            }
        }
        return false;
    }
}

if (!function_exists('backtrace')) {
    function backtrace(): \Illuminate\Support\Collection
    {
        $trace = debug_backtrace();

        return collect([
            'file' => $trace[1]['file'] ?? null,
            'line' => $trace[1]['line'] ?? null,
            'class' => $trace[2]['class'] ?? null,
            'function' => $trace[2]['function'] ?? null,
        ]);
    }
}

if (!function_exists('send_log')) {
    /**
     * @param string|null $message
     * @param array $setup
     * @param string $action
     * @param Throwable|null $exception
     * @param bool $logSlack
     */
    function send_log(string $message = null, array $setup = [], string $action = 'info', ?\Throwable $exception = null, bool $logSlack = false)
    {
        // envia o backtrace E exception em ambiente local
        if (is_local()) {
            $setup = array_merge($setup, [
                "Classe" => backtrace()->get('class'),
                "Function" => backtrace()->get('function'),
                "Linha" => backtrace()->get('line'),
                "Arquivo" => backtrace()->get('file')
            ]);

            if (!is_null($exception)) {
                $setup = array_merge($setup, get_exception($exception));
            }
        }

        if (!is_local() && $logSlack) {
            log_slack(
                $message,
                backtrace()->get('class'),
                backtrace()->get('function'),
                backtrace()->get('line'),
                backtrace()->get('file'),
                $exception,
                $setup
            );
        }

        Log::{$action}($message, $setup);
    }
}


if (!function_exists('get_exception')) {
    /**
     * Separa todas as informações do exception em um array
     *
     * @param \Throwable|null $exception
     * @return array
     */
    function get_exception(?\Throwable $exception = null): array
    {
        if (is_null($exception)) {
            return [];
        }

        return [
            'eMessage' => $exception->getMessage(),
            'eFile' => $exception->getFile(),
            'eLine' => $exception->getLine(),
            'eCode' => $exception->getCode(),
            'eTraceAsString' => $exception->getTraceAsString(),
            'eTrace' => $exception->getTrace(),
        ];
    }
}

if (!function_exists('log_slack')) {

    /**
     * @param string $message
     * @param string|null $class
     * @param string|null $function
     * @param string|null $line
     * @param string|null $file
     * @param Throwable|null $exception
     * @param array|null $setup
     */
    function log_slack(
        string     $message,
        ?string    $class = null,
        ?string    $function = null,
        ?string    $line = null,
        ?string    $file = null,
        ?Throwable $exception = null,
        ?array     $setup = null
    )
    {
        $nomeProjeto = config('custom.PROJETO');
        $type = null;

        if ($exception) {
            $line = $exception->getLine();
            $file = $exception->getFile();
            $type = get_class($exception);
        }

        app('log')->channel('slack')->error(
            strtoupper($nomeProjeto), [
                'Tipo' => $type,
                'URL' => config('custom.URL'),
                'Ambiente' => config('custom.AMBIENTE'),
                'Classe' => $class,
                'Função' => $function,
                'Erro' => $message,
                'Linha' => $line,
                'Arquivo' => $file,
                'Extras' => $setup
            ]
        );
    }
}

if (!function_exists('group_by_key')) {

    /**
     * @param $array
     * @param $keyName
     * @return array
     */
    function group_by_key($array, $keyName)
    {
        $tmp = [];
        if (is_array($array) && !empty($array)) {
            foreach ($array as $row) {
                $tmp[Illuminate\Support\Arr::{'get'}($row, $keyName)][] = $row;
            }
        }
        return $tmp;
    }
}

if (!function_exists('object_to_array')) {
    function object_to_array($d)
    {
        if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $d = get_object_vars($d);
        }

        if (is_array($d)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return array_map(__FUNCTION__, $d);
        } else {
            // Return array
            return $d;
        }
    }
}

if (!function_exists('is_cache_redis')) {
    function is_cache_redis(): bool
    {
        return in_array(env('CACHE_DRIVER'), ['redis', 'redis_local']);
    }
}

if (!function_exists('get_by_cache')) {
    function get_by_cache($key, $default = null, array $tags = [])
    {
        if (!is_cache_redis()) {
            return Cache::get($key, $default);
        }

        $tags = array_merge([config('cache.prefix')], $tags);
        $tags = array_unique($tags);

        return Cache::tags($tags)->get($key, $default);

    }
}

if (!function_exists('save_in_cache')) {
    function save_in_cache(string $key, $value, int $minutes = 0, array $tags = [])
    {
        if (!is_cache_redis()) {
            Cache::put($key, $value, now()->addMinutes($minutes));
            return;
        }

        $tags = array_merge([config('cache.prefix')], $tags);
        $tags = array_unique($tags);

        Cache::tags($tags)->put($key, $value, now()->addMinutes($minutes));

    }
}

if (!function_exists('limpar_cache')) {
    function limpar_cache($key, array $tags = [])
    {
        if (!is_cache_redis()) {
            return Cache::forget($key);
        }

        $tags = array_merge([config('cache.prefix')], $tags);
        $tags = array_unique($tags);

        return Cache::tags($tags)->forget($key);

    }
}

if (!function_exists('is_json')) {
    function is_json($string, $return_data = false)
    {
        try {
            $data = json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : TRUE) : FALSE;
        } catch (\TypeError $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('just_number')) {
    function just_number(&$string)
    {
        $string = preg_replace("/[^0-9]/", "", $string);
        return $string;
    }
}

if (!function_exists('format_number')) {
    /**
     * @param $valor
     * @param int $decimais
     * @param string $decPoint
     * @param string $thousands
     * @return string
     */
    function format_number($valor, int $decimais = 8, $decPoint = ',', $thousands = '.')
    {
        if (is_null($valor)) {
            $valor = 0;
        }
        return number_format(floatval($valor) ?? 0, $decimais, $decPoint, $thousands);
    }
}

if (!function_exists('format_number_decimal_calc')) {
    /**
     * @param $valor
     * @param string $decPoint
     * @param string $thousands
     * @return string
     */
    function format_number_decimal_calc($valor, $decPoint = ',', $thousands = '.')
    {
        if (is_null($valor)) {
            $valor = 0;
        }

        $valorCalc = $valor;
        if (!empty($valorCalc)) {
            $valExplode = explode('.', $valorCalc);
            $decimais = count($valExplode) === 2 ? strlen($valExplode[1]) : 2;
            $valor = number_format(floatval($valor) ?? 0, $decimais, $decPoint, $thousands);
        }

        return $valor;
    }
}

if (!function_exists('normaliza_monetario')) {
    function normaliza_monetario($valor)
    {
        if ($valor and strpos($valor, ',') !== false) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }
        return $valor;
    }
}

if (!function_exists('fotmata_cpf_cnpj')) {
    function fotmata_cpf_cnpj(?string $inscricao = null, ?string $tipo = null)
    {
        if (is_null($inscricao)) return null;

        $cpf = str_pad($inscricao, 11, '0', STR_PAD_LEFT);
        $cnpj = str_pad($inscricao, 14, '0', STR_PAD_LEFT);

        $formatoCpf = sprintf(
            '%s.%s.%s-%s',
            substr($cpf, 0, 3),
            substr($cpf, 3, 3),
            substr($cpf, 6, 3),
            substr($cpf, 9)
        );
        $formatoCnpj = sprintf(
            '%s.%s.%s/%s-%s',
            substr($cnpj, 0, 2),
            substr($cnpj, 2, 3),
            substr($cnpj, 5, 3),
            substr($cnpj, 8, 4),
            substr($cnpj, 12, 2)
        );

        return strlen($inscricao) > 11 || $tipo == 'cnpj' ? $formatoCnpj : $formatoCpf;
    }
}

if (!function_exists('exec_job_agora')) {
    function exec_job_agora($job): void
    {
        app(\Illuminate\Bus\Dispatcher::class)->dispatchNow($job);
    }
}

if (!function_exists('get_ddd_domains')) {
    function get_ddd_domains(int $cacheMinutos = 60, array $tags = []): array
    {
        $separator = DIRECTORY_SEPARATOR;
        $domains = get_by_cache('list_ddd_domains', []);
        if (empty($domains)) {
            $dir = base_path('app' . $separator . 'Domain');
            if (Illuminate\Support\Facades\File::exists($dir)) {
                collect(Illuminate\Support\Facades\File::directories($dir))
                    ->map(
                        function ($dir) use ($separator, &$domains) {
                            $domains[] = Illuminate\Support\Str::afterLast($dir, $separator);
                        });
                save_in_cache('list_ddd_domains', $domains, $cacheMinutos, $tags);
            }
        }
        return $domains;
    }
}

if (!function_exists('is_uuid4')) {
    /**
     * Retorna se é um uuid versão 4
     *
     * @param string $uuid
     * @return bool
     */
    function is_uuid4(string $uuid): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) !== 1;
    }
}

if (!function_exists('get_arquivos_rotas')) {
    function get_arquivos_rotas(string $dir, string $sufixCache = null): array
    {
        $sufix = \Illuminate\Support\Str::afterLast($dir, '/');
        $keyCache = 'list_routes_' . ($sufixCache ? $sufixCache . '_' : '') . $sufix;
        $rotas = get_by_cache($keyCache, []);

        if (is_local() || is_console() || empty($rotas)) {
            $rotas = [];
            foreach (\Illuminate\Support\Facades\File::files($dir) as $f) $rotas[] = $sufix . '/' . strtolower($f->getFilename());
            if (!is_local()) save_in_cache($keyCache, $rotas, 360);
        }

        return $rotas;
    }
}

if (!function_exists('dto_querie_prepare')) {
    function dto_querie_prepare(
        &$querie,
        Fintools\SDKCore\Contracts\DTOAbstract $dto
    ): void
    {
        if ($dto->isNotEmpty() && $dto->isFiltravel()) {

            #verifica se eh instancia Model
            $tableNome = method_exists($querie, 'getModel') ? $querie->getModel()->getTable() : null;

            #verifica se é instancia QuerieBuilding
            $tableNome = $tableNome ? $tableNome : (method_exists($querie, 'getTable') ? $querie->getTable() : null);

            if ($tableNome) {
                $where = app(\Fintools\SDKCore\Others\PrepareFiltroByDTO::class)
                    ->build($dto, config("filters.{$tableNome}.de_para", []));
                if (Arr::get($where, 'raw')) {
                    $querie = $querie->whereRaw($where['raw'], $where['prepare']);
                }
            } else {
                send_log(__METHOD__ . ':' . __LINE__ . ' ===> Tabela nao indentificada');
            }
        }
    }
}

if (!function_exists('is_datadog_enabled')) {
    function is_datadog_enabled()
    {
        return !is_local() && config('logging.log_datadog_enabled', false) == true;
    }
}

if (!function_exists('is_console')) {
    function is_console(): bool
    {
        return app()->runningInConsole();
    }
}

if (!function_exists('is_ddd')) {
    function is_ddd(): bool
    {
        return config('app.is_ddd', true) == true;
    }
}

