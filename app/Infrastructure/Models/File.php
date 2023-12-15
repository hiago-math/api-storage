<?php

namespace Infrastructure\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class File extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'files';

    protected $fillable = [
        'file_uid',
        'file_name',
        'extension',
        'mime_type',
        'url',
        'size',
        'hash_file',
        'content_file'
    ];
}
