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
            $table->unsignedInteger('publish_status')->default(1)->comment('1: draft, 2: published');
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
            $table->dropColumn('publish_status');
        });
    }
};
