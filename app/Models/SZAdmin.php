<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class SZAdmin extends Model
{
    protected $connection = 'mongo-safe-zone-backend';
    protected $collection = 'admins';
    protected $primaryKey = '_id';

    protected $hidden = [
        'password'
    ];
}
