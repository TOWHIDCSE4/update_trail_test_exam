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
        Schema::table('student_test_question_result', function (Blueprint $table) {
            $table->unsignedInteger('total_sub_question_count')->after('answer')->comment('tổng sub question trong question')->default(0);
            $table->unsignedInteger('correct_count')->after('total_sub_question_count')->comment('tổng sub question đúng')->default(0);
            $table->unsignedInteger('scores')->after('correct_count')->comment('điểm của sub question')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_test_question_result', function (Blueprint $table) {
            $table->dropColumn('total_sub_question_count');
            $table->dropColumn('correct_count');
            $table->dropColumn('scores');
        });
    }
};
