<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAstroFortunesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'astro_fortunes', function (Blueprint $table) {
                $table->id();
                $table->enum(
                    'type', [
                    '當日',
                    '當周',
                    '當月'
                    ]
                );
                $table->string('time_range');
                $table->integer('code');
                $table->string('name');
            
                // 整體運勢
                $table->integer('general_score')->nullable();
                $table->text('general_fortune')->nullable();
                // 戀愛運勢
                $table->integer('love_score')->nullable();
                $table->text('love_fortune')->nullable();
                // 事業運勢
                $table->integer('career_score')->nullable();
                $table->text('career_fortune')->nullable();
                // 財運運勢
                $table->integer('wealth_score')->nullable();
                $table->text('wealth_fortune')->nullable();

                $table->timestamps();

                $table->unique(['code', 'type', 'time_range']);
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('astro_fortunes');
    }
}
