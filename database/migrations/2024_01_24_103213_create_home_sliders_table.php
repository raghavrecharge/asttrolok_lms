<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHomeSlidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('home_sliders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->string('home_hero2')->nullable();
            $table->string('personalization')->nullable();
            $table->string('locale')->nullable();
            $table->string('description')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable(); 
            $table->string('button_color')->nullable();
            $table->string('hero_background')->nullable();
            $table->string('hero_vector')->nullable();
            $table->integer('has_lottie')->default(0);
            $table->string('status')->nullable();
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
        Schema::dropIfExists('home_sliders');
    }
}
