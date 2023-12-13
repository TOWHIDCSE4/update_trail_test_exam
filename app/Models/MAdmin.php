<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class MAdmin extends Model
{
    protected $connection = 'mongo-backend';
    protected $collection = 'admins';
    protected $primaryKey = '_id';

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
