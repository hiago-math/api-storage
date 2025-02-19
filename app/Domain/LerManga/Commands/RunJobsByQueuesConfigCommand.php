<?php

namespace Domain\LerManga\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

class RunJobsByQueuesConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:work-fast {domain?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando que roda as filas configuradas no projeto por dominio';

    public function handle()
    {
        $argumento = $this->argument('domain');

        throw_if(is_null($argumento), new \Exception('É obrigatório informar um dominio!'));

        $argumento = Str::snake($argumento);

        $command = 'php artisan queue:work --queue=';
        $jobsName = config('jobs_name');

        throw_if(!array_key_exists($argumento, $jobsName), ValidationException::withMessages([
            'Filas do dominio passado não configurado. Configure no arquivo config/jobs_name.'
        ]));

        foreach (Arr::get($jobsName, $argumento) as $item) {
            $command .= "$item,";
        }

        $command = Str::beforeLast($command, ',');

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(null);
        $process->run(function ($type, $line) {
            $this->output->write($line);
        });

    }
}
