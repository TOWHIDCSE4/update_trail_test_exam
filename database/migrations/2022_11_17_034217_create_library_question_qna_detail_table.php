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
        Schema::create('library_question_qna_detail', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('library_question_id');
            $table->unsignedInteger('order');
            $table->text('sub_question')->nullable();
            $table->text('correct_answer')->nullable();
            $table->text('incorrect_answer')->nullable();
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
        Schema::dropIfExists('library_question_qna_detail');
    }
};
