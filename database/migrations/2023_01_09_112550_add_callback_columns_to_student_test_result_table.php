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
            $table->string('code')->after('id')->nullable()->default(null)->index();
            // $table->string('extra_data')->after('listening_score')->nullable()->default(null);
            $table->string('url_callback')->after('listening_score')->nullable()->default(null);
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
            $table->dropColumn('code');
            // $table->dropColumn('extra_data');
            $table->dropColumn('url_callback');
        });
    }
};
