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
        Schema::create('section_question', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('section_id');
            $table->bigInteger('question_id');
            $table->integer('question_order')->nullable();
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
        Schema::dropIfExists('section_question');
    }
};
