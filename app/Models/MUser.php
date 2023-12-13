<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class MUser extends Model
{
    protected $connection = 'mongo-backend';
    protected $collection = 'users';
    protected $primaryKey = '_id';

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
