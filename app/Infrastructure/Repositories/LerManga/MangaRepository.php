<?php

namespace Infrastructure\Repositories\LerManga;

use Domain\LerManga\Interfaces\Repositories\IMangaRepository;
use Illuminate\Support\Collection;
use Infrastructure\Models\LerManga;
use Infrastructure\Repositories\AbstractRepository;
use Shared\DTO\LerManga\CreateOrUpdateMangaDTO;

class MangaRepository extends AbstractRepository implements IMangaRepository
{
    public function __construct()
    {
        parent::__construct(LerManga::class);
    }

    public function saveManga(CreateOrUpdateMangaDTO $dto): Collection
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
