<?php

namespace Infrastructure\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class LerManga extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'ler_mangas';

    protected $fillable = [
        'uid',
        'nome',
        'label',
        'link',
        'total_chapters',
        'infos',
        'chapters',
        'sync',
    ];
}
