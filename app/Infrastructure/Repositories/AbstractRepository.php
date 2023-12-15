<?php

namespace Infrastructure\Repositories;

use Illuminate\Support\Collection;

abstract class AbstractRepository
{
    private $model;

    public function __construct(string  $model)
    {
        $this->model = instantiate_class($model);
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param array|null $values
     * @return Collection
     */
    protected function toCollect(array $values = null): Collection
    {
        $values = $values ?? [];
        return collect($values);
    }

    /**
     * @return array
     */
    protected function getAttributes(): array
    {
        return $this->model->fillable;
    }
}
