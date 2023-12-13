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
            $table->text('dropdown_list')->after('word_minimum')->nullable();
            $table->tinyInteger('flag_show_dropdown')->after('dropdown_list')->default(0);
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
            $table->dropColumn('dropdown_list');
            $table->dropColumn('flag_show_dropdown');
        });
    }
};
