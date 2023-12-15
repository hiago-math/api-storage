<?php

namespace Infrastructure\Repositories\File;

use Domain\File\Interfaces\Repositories\IFileRepository;
use Illuminate\Support\Collection;
use Infrastructure\Models\File;
use Infrastructure\Repositories\AbstractRepository;
use Shared\DTO\Files\CreateFileDTO;

class FileRepository extends AbstractRepository implements IFileRepository
{

    public function __construct()
    {
        parent::__construct(File::class);
    }

    /**
     * {@inheritDoc}
     */
    public function createFile(CreateFileDTO $createFileDTO): Collection
    {
        $created = $this->getModel()
            ->create($createFileDTO->toArray());

        return $this->toCollect($created->toArray());
    }

    /**
     * {@inheritDoc}
     */
    public function findFileBy(string $field, string $value): Collection
    {
        $result = $this->getModel()
            ->where($field, $value)
            ->first()
            ?->toArray();

        return $this->toCollect($result);
    }
}
