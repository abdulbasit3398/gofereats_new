<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFoodReceiverTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('food_receiver_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('food_receiver_id')->unsigned();
            $table->string('locale',5)->index();
            $table->string('name');   
            $table->unique(['food_receiver_id','locale']);
            $table->foreign('food_receiver_id')->references('id')->on('food_receiver')->onDelete('cascade');
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
        Schema::dropIfExists('food_receiver_translations');
    }
}
