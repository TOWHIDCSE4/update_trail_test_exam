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
        Schema::create('library_question_matching_detail', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('library_question_id');
            $table->unsignedInteger('order')->nullable();
            $table->text('content_text_a')->nullable();
            $table->string('picture_url_a', 256)->nullable();
            $table->text('content_text_b')->nullable();
            $table->string('picture_url_b', 256)->nullable();
            $table->string('audio_url', 256)->nullable();
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
        Schema::dropIfExists('library_question_matching_detail');
    }
};
