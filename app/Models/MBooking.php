<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class MBooking extends Model
{
    protected $connection = 'mongo-backend';
    protected $collection = 'bookings';
    protected $primaryKey = '_id';
}
