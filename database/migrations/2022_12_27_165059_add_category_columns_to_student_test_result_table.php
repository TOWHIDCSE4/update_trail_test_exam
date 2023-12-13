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
            $table->double('vocabulary_score')->after('scores')->nullable()->default(null);
            $table->double('reading_score')->after('vocabulary_score')->nullable()->default(null);
            $table->double('writing_score')->after('reading_score')->nullable()->default(null);
            $table->double('grammar_score')->after('writing_score')->nullable()->default(null);
            $table->double('listening_score')->after('grammar_score')->nullable()->default(null);
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
            $table->dropColumn('vocabulary_score');
            $table->dropColumn('reading_score');
            $table->dropColumn('writing_score');
            $table->dropColumn('grammar_score');
            $table->dropColumn('listening_score');
        });
    }
};
