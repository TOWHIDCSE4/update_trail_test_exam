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
        Schema::create('library_test', function (Blueprint $table) {
            $table->id();
            $table->string('topic', 500)->index();
            $table->unsignedInteger('level');
            $table->unsignedInteger('test_time')->comment('minutes');
            $table->string('creator_id', 128);
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
        Schema::dropIfExists('library_test');
    }
};
