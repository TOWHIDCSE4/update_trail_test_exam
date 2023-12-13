<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentTestQuestionResult extends Model
{
    use HasFactory;

    protected $table = 'student_test_question_result';
    protected $guarded = [];
    public $timestamps = true;

    public function libraryQuestion()
    {
        return $this->belongsTo(LibraryQuestion::class, 'question_id', 'id');
    }
}
