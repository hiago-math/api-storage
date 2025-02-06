<?php

namespace Infrastructure\Repositories;

use Illuminate\Support\Collection;
use Jenssegers\Mongodb\Eloquent\Model;

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
    public function getModel(): Model|\Illuminate\Database\Eloquent\Model
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
