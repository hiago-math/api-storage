<?php

namespace Domain\LerManga\Jobs;

use Domain\LerManga\Interfaces\Repositories\IMangaRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Infrastructure\Jobs\Job;
use Infrastructure\Models\LerManga;
use Shared\DTO\LerManga\CreateOrUpdateMangaDTO;

class CapiturarCapitulosMangasJob extends Job
{
    private IMangaRepository $repository;
    private CreateOrUpdateMangaDTO $dto;


    public function __construct(
        private string $uid
    )
    {
        $this->dto = app(CreateOrUpdateMangaDTO::class);
        $this->repository = app(IMangaRepository::class);
    }

    public
    function handle(
        LerManga $model
    )
    {
        send_log($this->uid);
        $result = $model->newQuery()
            ->where('uid', $this->uid)
            ->first()
            ?->toArray();

        try {
            $this->dto->nome = Arr::get($result, 'nome');
            $this->dto->label = sanitizar_string(Arr::get($result, 'nome'));
            $this->dto->link = Arr::get($result, 'link');

            $this->paginateFake(50, Arr::get($result, 'total_chapters'));
        } catch (\Throwable $e) {
            dd($e);
            send_log($e->getMessage(), ['uid' => $this->uid, 'result' => $result], 'error', $e);
        }
    }

    private function getChaptersForManga(int $start, int $max)
    {
        $name = $this->dto->nome;
        $url = "https://www.lermangas.com.br/feeds/posts/default/-/$name?alt=json-in-script&start-index=$start&max-results=$max";

        send_log($url);

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
