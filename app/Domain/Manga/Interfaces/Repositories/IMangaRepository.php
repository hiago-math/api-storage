<?php

namespace Domain\Manga\Interfaces\Repositories;

use Illuminate\Support\Collection;
use Shared\DTO\Mangas\CreateMangaDTO;

interface IMangaRepository
{
    public function saveManga(CreateMangaDTO $dto): Collection;
}
