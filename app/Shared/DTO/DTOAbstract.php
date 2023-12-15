<?php

namespace Shared\DTO;

abstract class DTOAbstract
{
    public function newInstance(): self
    {
        return app(get_class($this));
    }

    public function toArray(array $except = [], array $only = []): array
    {
        $return = collect(
            call_user_func('get_object_vars', $this)
        );

        if (!empty($only)) $return = $return->only($only);
        if (!empty($except)) $return = $return->except($except);

        return $return->toArray();
    }

    public function toJson(array $except = []): string
    {
        return json_encode($this->toArray($except));
    }

    public function isEmpty(): bool
    {
        return collect($this->toArray())->isEmpty();
    }
}
