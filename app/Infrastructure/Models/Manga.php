<?php

namespace Infrastructure\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Manga extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'mangas';

    protected $fillable = [
        'uid',
        'nome',
        'label',
        'link',
        'num_caps',
    ];
}
