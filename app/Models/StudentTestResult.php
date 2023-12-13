<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentTestResult extends Model
{
    use HasFactory;

    protected $table = 'student_test_result';
    protected $guarded = [];
    public $timestamps = true;
}
