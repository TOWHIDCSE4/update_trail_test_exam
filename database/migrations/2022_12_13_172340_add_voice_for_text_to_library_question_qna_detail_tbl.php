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
        Schema::table('library_question_qna_detail', function (Blueprint $table) {
            $table->unsignedInteger('voice_for_sub_question')->after('sub_question')->default(0)->comment('0: no voice, 1: has voice');
            $table->unsignedInteger('voice_for_answer')->after('incorrect_answer')->default(0)->comment('0: no voice, 1: has voice');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('library_question_qna_detail', function (Blueprint $table) {
            $table->dropColumn('voice_for_sub_question');
            $table->dropColumn('voice_for_answer');
        });
    }
};
