<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Infrastructure\Elasticsearch\Rest;
use Jenssegers\Mongodb\Connection;
use MongoDB\Driver\Exception\AuthenticationException;

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
        $doctype = \Shared\Enums\DocTypesElasticsearchEnum::DOC;
        if (!is_null($exception)) {
            $options['message_exception'] = $exception->getMessage();
            $options['code'] = $exception->getCode();
            $options['file'] = $exception->getFile() . ": " . $exception->getLine();
            $options['trace'] = $exception->getTraceAsString();
            $doctype = \Shared\Enums\DocTypesElasticsearchEnum::ERROR;
        }

        Log::$type($message, $options);
        $options['message'] = $message;

        create_log_elastic($type, $doctype, $options);
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

    function db_mongo_check()
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

    function db_redis_check()
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

    function time_start_app()
    {
        $timeStarted = Storage::get('uptime.txt');
        return Carbon::parse($timeStarted)->timezone(config('app.timezone'))->diffForHumans();
    }
}

if (!function_exists('memory_usage')) {

    function memory_usage()
    {
        return round(memory_get_usage() / (1024 * 1024), 2) . ' MB';
    }
}

