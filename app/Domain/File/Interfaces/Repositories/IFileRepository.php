<?php

namespace Domain\File\Interfaces\Repositories;

use Illuminate\Support\Collection;
use Shared\DTO\Files\CreateFileDTO;

interface IFileRepository
{
    /**
     * @param CreateFileDTO $createFileDTO
     * @return Collection
     */
    public function createFile(CreateFileDTO $createFileDTO): Collection;

    /**
     * @param string $field
     * @param string $value
     * @return Collection
     */
    public function findFileBy(string $field, string $value): Collection;
}
