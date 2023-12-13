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
        Schema::table('library_question', function (Blueprint $table) {
            $table->unsignedInteger('voice_for_title')->after('title')->default(0)->comment('0: no voice, 1: has voice');
            $table->unsignedInteger('voice_for_content_main')->after('content_main_text')->default(0)->comment('0: no voice, 1: has voice');
            $table->unsignedInteger('voice_for_answer')->after('incorrect_answer')->default(0)->comment('0: no voice, 1: has voice');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('library_question', function (Blueprint $table) {
            $table->dropColumn('voice_for_title');
            $table->dropColumn('voice_for_content_main');
            $table->dropColumn('voice_for_answer');
        });
    }
};
