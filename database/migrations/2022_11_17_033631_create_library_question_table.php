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
        Schema::create('library_question', function (Blueprint $table) {
            $table->id();
            $table->string('title', 1024);
            $table->unsignedInteger('main_type')->index()->comment('1: sort; 2: q_and_a; 3: matching; 4: fill_input');
            $table->unsignedInteger('sub_type')->nullable();
            $table->unsignedInteger('category')->index()->comment('1: vocabulary; 2: reading; 3: writing; 4: grammar');
            $table->bigInteger('library_test_id');
            $table->unsignedInteger('order');
            $table->unsignedInteger('scores')->default(5);
            $table->unsignedInteger('picture_bellow_text')->comment('0: uncheck; 1: checked');
            $table->text('content_main_text')->nullable();
            $table->text('content_main_picture')->nullable();
            $table->text('content_main_audio')->nullable();
            $table->text('correct_answer')->nullable();
            $table->text('incorrect_answer')->nullable();
            $table->string('creator_id', 128);
            $table->string('editor_id', 128)->nullable();
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
        Schema::dropIfExists('library_question');
    }
};
