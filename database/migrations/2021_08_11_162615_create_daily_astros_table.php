<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyAstrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_astros', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name');
            
            // 整體運勢
            $table->integer('general_score');
            $table->text('general_fortune');
            // 戀愛運勢
            $table->integer('love_score');
            $table->text('love_fortune');
            // 事業運勢
            $table->integer('career_score');
            $table->text('career_fortune');
            // 財運運勢
            $table->integer('wealth_score');
            $table->text('wealth_fortune');

            $table->timestamps();

            $table->unique(['date', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_astros');
    }
}
