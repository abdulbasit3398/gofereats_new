<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCusineLang extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cuisine_lang', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cuisine_id')->unsigned();
            $table->string('name'); 
            $table->string('description'); 
            $table->string('locale',5)->index();
            $table->unique(['cuisine_id','locale']);            
            $table->foreign('cuisine_id')->references('id')->on('cuisine')->onDelete('cascade');
        });   
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cuisine_lang');
    }
}
