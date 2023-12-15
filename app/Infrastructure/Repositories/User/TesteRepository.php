<?php

namespace Infrastructure\Repositories\User;

use Domain\User\Interfaces\Repositories\ITesteRepository;
use Infrastructure\Models\User;
use Infrastructure\Repositories\AbstractRepository;

class TesteRepository extends AbstractRepository implements ITesteRepository
{
    public function __construct()
    {
        parent::__construct(User::class);
    }
}
