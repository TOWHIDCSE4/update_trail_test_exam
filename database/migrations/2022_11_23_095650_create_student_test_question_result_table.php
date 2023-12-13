<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_test_question_result', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('student_test_result_id')->index();
            $table->bigInteger('question_id');
            $table->text('answer')->nullable()->default(null)->comment('json text nội dung các câu trả lời');
            $table->tinyInteger('is_correct');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_test_question_result');
    }
};
