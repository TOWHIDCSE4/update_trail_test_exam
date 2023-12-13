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
        Schema::create('student_test_result', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('library_test_id')->index();
            $table->string('user_oid', 128)->index()->comment('object id của user làm bài');
            $table->dateTime('end_at')->nullable()->default(null)->comment('thời gian kết thúc làm bài');
            $table->unsignedInteger('score')->default(0)->comment('tổng điểm question làm đúng');
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
        Schema::dropIfExists('student_test_result');
    }
};
