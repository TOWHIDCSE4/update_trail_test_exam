<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryQuestion extends Model
{
    protected $table = 'library_question';
    protected $guarded = [];
    public $timestamps = true;

    public function studentTestQuestionResult()
    {
        return $this->hasMany(StudentTestQuestionResult::class, 'question_id', 'id');
    }
}
