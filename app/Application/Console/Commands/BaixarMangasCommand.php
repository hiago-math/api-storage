<?php

namespace Application\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BaixarMangasCommand extends Command
{
    private array $titulos = [
        'Solo Leveling'
    ];
    protected $signature = 'teste:teste';

    protected $description = "[DDD] Create a new domain controller";

    public function handle()
    {
        foreach ($this->titulos as $titulo) {
            for ($i = 201; $i <= 10000; $i++) {
                $titulo = strtolower(str_replace(' ', '-', $titulo));
                $url = "https://www.lermangas.com.br/2024/09/{$titulo}-capitulo-{$i}.html";
                $return = $this->buscarCap($url, $titulo);

                if (!$return) break;
            }
        }
    }

    private function buscarCap(string $url, string $titulo, int $tentativa = 0)
    {
        if ($tentativa > 1) return false;
        $cap = Str::between($url, 'capitulo-', '.html');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $pageContent = curl_exec($ch);
        curl_close($ch);

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($status === 404) {
            echo 'Nao encontrado: ' . $tentativa. ' ' . $url . PHP_EOL;

            if (!Str::contains($url, '-const')) $url = Str::replace('.html', '-const.html', $url);
            $this->buscarCap($url, $titulo, ++$tentativa);
        }

        $pageContent = preg_replace('/\s+/', ' ', $pageContent);

        $links = Str::between($pageContent, 'ts_reader = [', ']');

        $links = explode(',', Str::replace('"', "'", $links));

        $dir = "mangas/{$titulo}/cap$cap";

        // Cria o diretório se não existir
        if (!Storage::disk('files')->exists($dir)) {
            Storage::disk('files')->makeDirectory($dir, 0755, true);
        }

        $count = 1;
        foreach ($links as $imgUrl) {
            $imgUrl = trim($imgUrl, "'\"");
            $imgUrl = trim($imgUrl, " '");

            if (!filter_var($imgUrl, FILTER_VALIDATE_URL)) continue;

            $imgPath = Storage::disk('files')->path($dir . '/');
            $this->downloadFile($imgUrl, $imgPath, "page{$count}.jpg");

            $count++;
        }
    }

    private function downloadFile(string $url, string $path, string $filename)
    {
        $finalPath = $path . "$filename";
        $ch = curl_init($url);
        $fp = fopen($finalPath, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }
}
