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
        Schema::table('library_test', function (Blueprint $table) {
            $table->string('test_type', 256)->after('topic')->default('COMMON');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('library_test', function (Blueprint $table) {
            $table->dropColumn('test_type');
        });
    }
};
