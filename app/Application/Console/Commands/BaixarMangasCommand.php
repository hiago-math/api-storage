<?php

namespace Application\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Infrastructure\Models\Manga;

class BaixarMangasCommand extends Command
{
    protected $signature = 'teste:teste';

    protected $description = "[DDD] Create a new domain controller";

    public function handle(
        Manga $model
    )
    {
        $results = $model->newQuery()->get()->toArray();

        foreach ($results as $result) {
            for ($i = 0; $i <= 10000; $i++) {

                $url = Str::replace('.html', "-capitulo-{$i}.html", Arr::get($result, 'link'));

                $this->buscarCap($url, Arr::get($result, 'nome'));
            }
        }
    }

    private function buscarCap(string $url, string $titulo, int $tentativa = 0)
    {
        if ($tentativa > 4) return false;

        preg_match('/[-_](\d+)(?:\.html|-const\.html)/', $url, $matches);
        $cap = Arr::get($matches, 1);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $pageContent = curl_exec($ch);
        curl_close($ch);

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($tentativa === 2) {
            if (Str::contains($url, '-const')) $url = Str::replace('-const.html', '.html', $url);
            preg_match('/capitulo-(\d+)/', $url, $matches);
            if (isset($matches[1])) {
                $chapterNumber = (int)$matches[1]; // Converte para inteiro

                // Adiciona zero à esquerda se o número estiver entre 0 e 9
                $formattedChapterNumber = str_pad($chapterNumber, 2, '0', STR_PAD_LEFT);

                // Substitui o número do capítulo na URL com o número formatado
                $url = preg_replace('/capitulo-\d+/', 'capitulo-' . $formattedChapterNumber, $url);

                return $this->buscarCap($url, $titulo, ++$tentativa);
            }
        }

        if ($tentativa === 3) {
            $url = Str::replace("-capitulo-$cap", "_" . (int)$cap, $url);

            return $this->buscarCap($url, $titulo, ++$tentativa);
        }

        if ($status === 404) {
            echo 'Nao encontrado: ' . $tentativa . ' ' . $url . PHP_EOL;

            if (!Str::contains($url, '-const')) $url = Str::replace('.html', '-const.html', $url);

            return $this->buscarCap($url, $titulo, ++$tentativa);
        }

        echo $status . PHP_EOL;

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
