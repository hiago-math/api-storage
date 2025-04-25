<?php

namespace Domain\LerManga\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\HandlerStack;
use Illuminate\Console\Command;
use GuzzleHttp\Promise;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'url:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando que roda as filas configuradas no projeto por dominio';

    public function handle(Promise\Utils $utils)
    {
        $bucket = $this->argument('bucket');
        $key = $this->argument('key');
        $chunkSize = (int)$this->argument('chunkSize');

        // Crie um cliente S3
        $s3Client = new S3Client([
            'version' => 'latest',
            'region'  => 'us-east-1', // Altere para sua região
        ]);

        // Obtenha o tamanho do arquivo
        $result = $s3Client->headObject([
            'Bucket' => $bucket,
            'Key'    => $key,
        ]);
        $fileSize = $result['ContentLength'];

        $chunks = (int)ceil($fileSize / $chunkSize);

        for ($i = 0; $i < $chunks; $i++) {
            $rangeStart = $i * $chunkSize;
            $rangeEnd = ($i + 1) * $chunkSize - 1;
            if ($rangeEnd >= $fileSize) {
                $rangeEnd = $fileSize - 1;
            }

            $result = $s3Client->getObject([
                'Bucket' => $bucket,
                'Key'    => $key,
                'Range'  => "bytes=$rangeStart-$rangeEnd",
            ]);

            $body = $result['Body']->getContents();
            Storage::disk('local')->put("chunks/chunk_$i", $body);
        }

        $this->info('File downloaded in chunks successfully.');
    }
}
