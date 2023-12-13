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
            $table->string('staff_oid', 128)->after('user_oid')->comment('object id của staff làm thử bài')->nullable();
            $table->unsignedInteger('test_type')->after('staff_oid')->comment('1: NORMAL,2: STAFF_PRE_TEST')->default(1);
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
            $table->dropColumn('staff_oid');
            $table->dropColumn('test_type');
        });
    }
};
