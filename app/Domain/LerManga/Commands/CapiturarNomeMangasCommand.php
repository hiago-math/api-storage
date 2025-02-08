<?php

namespace Domain\LerManga\Commands;

use Domain\LerManga\Interfaces\Repositories\IMangaRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Infrastructure\Models\LerManga;
use Shared\DTO\LerManga\CreateOrUpdateMangaDTO;

class CapiturarNomeMangasCommand extends Command
{
    private array $resultados = [];
    protected $signature = 'test:test';

    protected $description = "[DDD] Create a new domain controller";

    public function __construct(
        private IMangaRepository $repository,
    )
    {
        parent::__construct();

    }

    public
    function handle(
        CreateOrUpdateMangaDTO $dto,
    )
    {
        $url = "https://www.lermangas.com.br/search/label/Series";

        $this->buscarMangas($url);

        $this->test();

        $this->salvarMangas($dto);

    }

    private function buscarMangas(string $url)
    {
        if (empty($url)) return;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $pageContent = curl_exec($ch);
        curl_close($ch);

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($status === 404) return;

        $pageContent = htmlspecialchars_decode(preg_replace('/\s+/', ' ', $pageContent), ENT_QUOTES);

        preg_match_all("/<a class='series' href='(.*?)' title='(.*?)'>/", $pageContent, $matches);

        if (empty($matches)) dd('acabou');

        foreach ($matches[0] as $key => $match) {
            if (LerManga::query()->where('label', sanitizar_string($matches[2][$key]))->exists()) continue;

            $this->resultados[] = [
                'href' => $matches[1][$key],
                'title' => $matches[2][$key]
            ];
        }

        $newPattern = "/<a class='blog-pager-older-link' href='(.*?)'/";
        preg_match_all($newPattern, $pageContent, $matches);

        $this->buscarMangas(current($matches[1]));
    }

    private function salvarMangas(CreateOrUpdateMangaDTO $dto)
    {

        foreach ($this->resultados as $resultado) {
            try {
                $dto->register(
                    uuid_create(),
                    Arr::get($resultado, 'title'),
                    sanitizar_string(Arr::get($resultado, 'title')),
                    Arr::get($resultado, 'href'),
                    Arr::get($resultado, 'infos', []) ?? [],
                    $this->buscarTotalChapters(Arr::get($resultado, 'title'))
                );

                $this->repository->saveManga($dto);
            } catch (\Throwable $exception) {
                dd($resultado, $exception);
            }

        }
    }

    private function test()
    {
        $newResult = [];
        foreach ($this->resultados as $resultado) {
            $url = Arr::get($resultado, 'href');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $pageContent = curl_exec($ch);
            curl_close($ch);

            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($status === 404) return;

            $pageContent = preg_replace('/\s+/', ' ', $pageContent);

            $pattern = '/<div class="fmed">\s*<b>(.*?)<\/b>\s*<span>(.*?)<\/span>\s*<\/div>/';

            preg_match_all($pattern, $pageContent, $matches, PREG_SET_ORDER);
            $result = [];

            foreach ($matches as $match) {

                if (Str::contains($match[1], ['<', '>'])) $match[1] = Str::afterLast($match[1], '>');

                $result = array_merge($result, [$match[1] => $match[2]]);
            }

            $pattern = '/<div class=\'cover\' style=\'background-image: url\("([^"]*)"\);\'/';

            preg_match($pattern, $pageContent, $matches);

            $result['img_cover'] = $matches[1];

            $pattern = '/<div class="desc">\s*<p>(.*?)<\/p>\s*<\/div>/s';

            preg_match($pattern, $pageContent, $matches);

            $result['desc'] = $matches[1];

            $resultado['infos'] = $result;

            $newResult[] = $resultado;
        }

        $this->resultados = $newResult;
    }

    private function buscarTotalChapters(string $name)
    {
        $url = "https://www.lermangas.com.br/feeds/posts/default/-/$name?alt=json-in-script&start-index=1&max-results=400";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $pageContent = curl_exec($ch);
        curl_close($ch);

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $payload = Str::between($pageContent, "gdata.io.handleScriptLoaded(", ');');

        $json = json_decode($payload, true);

        return Arr::get($json, 'feed.openSearch$totalResults.$t');
    }
}
