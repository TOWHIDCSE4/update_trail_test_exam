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
        Schema::table('student_test_result', function (Blueprint $table) {
            $table->unsignedInteger('total_subs_question_count')->after('end_at')->comment('tổng tất cả sub question trong bài test')->default(0);
            $table->unsignedInteger('correct_count')->after('total_subs_question_count')->comment('tổng tất ca sub question đúng trong bài test')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_test_result', function (Blueprint $table) {
            $table->dropColumn('total_subs_question_count');
            $table->dropColumn('correct_count');
        });
    }
};
