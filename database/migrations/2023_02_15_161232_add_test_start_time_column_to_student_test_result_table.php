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
            $table->dateTime('test_start_time')->nullable()->default(null)->comment('thời gian bắt đầu làm bài');
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
            $table->dropColumn('test_start_time');
        });
    }
};
