<?php

namespace Domain\LerManga\Interfaces\Repositories;

use Illuminate\Support\Collection;
use Shared\DTO\LerManga\CreateOrUpdateMangaDTO;

interface IMangaRepository
{
    public function saveManga(CreateOrUpdateMangaDTO $dto): Collection;
}
