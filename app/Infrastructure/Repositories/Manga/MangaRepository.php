<?php

namespace Infrastructure\Repositories\Manga;

use Domain\Manga\Interfaces\Repositories\IMangaRepository;
use Illuminate\Support\Collection;
use Infrastructure\Models\Manga;
use Infrastructure\Repositories\AbstractRepository;
use Shared\DTO\Mangas\CreateMangaDTO;

class MangaRepository extends AbstractRepository implements IMangaRepository
{
    public function __construct()
    {
        parent::__construct(Manga::class);
    }

    public function saveManga(CreateMangaDTO $dto): Collection
    {
        return $this->toCollect(
            $this->getModel()
            ->updateOrCreate(
                ['label' => $dto->label],
                $dto->toArray()
            )
            ?->toArray()
        );
    }
}
