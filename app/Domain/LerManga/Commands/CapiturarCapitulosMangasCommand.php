<?php

namespace Domain\LerManga\Commands;

use Domain\LerManga\Interfaces\Repositories\IMangaRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Infrastructure\Models\LerManga;
use Shared\DTO\LerManga\CreateOrUpdateMangaDTO;

class CapiturarCapitulosMangasCommand extends Command
{
    protected $signature = 'test:test1';

    protected $description = "[DDD] Create a new domain controller";

    public function __construct(
        private IMangaRepository       $repository,
        private CreateOrUpdateMangaDTO $dto
    )
    {
        parent::__construct();

    }

    public
    function handle(
        LerManga $model
    )
    {
        $results = $model->newQuery()->get()->toArray();

        foreach ($results as $result) {
            $this->dto->nome = Arr::get($result, 'nome');
            $this->dto->label = sanitizar_string(Arr::get($result, 'nome'));
            $this->dto->link = Arr::get($result, 'link');
            $this->paginateFake(50, Arr::get($result, 'total_chapters'));

            $this->dto = $this->dto->newInstance();
        }
    }

    private function getChaptersForManga(int $start, int $max)
    {
        $name = $this->dto->nome;
        $url = "https://www.lermangas.com.br/feeds/posts/default/-/$name?alt=json-in-script&start-index=$start&max-results=$max";

        echo $url . PHP_EOL;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $pageContent = curl_exec($ch);
        curl_close($ch);

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $payload = Str::between($pageContent, "gdata.io.handleScriptLoaded(", ');');

        $json = json_decode($payload, true);

        $entrys = Arr::pluck(Arr::get($json, 'feed.entry'), 'link.4');

        $chapters = [];

        foreach ($entrys as $entry) {
            if (Arr::get($entry, 'href') === $this->dto->link) continue;

            $chapters = array_merge($chapters, [
                Arr::get($entry, 'title', '') => Arr::get($entry, 'href')
            ]);
        }

        return $chapters;
    }

    private function paginateFake(int $max, int $total, int $start = 1)
    {
        while ($max <= $total) {
            $this->updateChapters($this->getChaptersForManga($start, $max));
            $start = $max;
            $max = $max + 50;
        }

        if ($max > $total) {
            $this->updateChapters($this->getChaptersForManga($start, $total));
        }
    }

    private function updateChapters(array $chapters)
    {

        $this->dto->chapters = $chapters;
        $this->repository->saveManga($this->dto);
    }
}
