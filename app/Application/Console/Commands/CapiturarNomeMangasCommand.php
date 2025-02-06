<?php

namespace Application\Console\Commands;

use Domain\Manga\Interfaces\Repositories\IMangaRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Shared\DTO\Mangas\CreateMangaDTO;

class CapiturarNomeMangasCommand extends Command
{
    private array            $resultados = [];
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
        CreateMangaDTO $dto,
    )
    {
        $url = "https://www.lermangas.com.br/search/label/Series";
        $this->buscarMangas($url);

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

        if ($status === 404) return ;

        $pageContent = preg_replace('/\s+/', ' ', $pageContent);

        preg_match_all("/<a class='series' href='(.*?)' title='(.*?)'>/", $pageContent, $matches);

        if (empty($matches)) dd('acabou');

        foreach ($matches[0] as $key => $match) {
            $this->resultados[] = [
                'href' => $matches[1][$key],
                'title' => $matches[2][$key]
            ];
        }

        $newPattern = "/<a class='blog-pager-older-link' href='(.*?)'/";
        preg_match_all($newPattern, $pageContent, $matches);

        $this->buscarMangas(current($matches[1]));
    }

    private function salvarMangas(CreateMangaDTO $dto)
    {
        foreach ($this->resultados as $resultado) {
            $dto->register(
                uuid_create(),
                Arr::get($resultado, 'title'),
                sanitizar_string(Arr::get($resultado, 'title')),
                Arr::get($resultado, 'href')
            );

            $this->repository->saveManga($dto);
        }
    }
}
