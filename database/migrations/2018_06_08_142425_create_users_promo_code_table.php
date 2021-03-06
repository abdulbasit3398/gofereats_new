<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersPromoCodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_promo_code', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('promo_code_id');
            $table->integer('order_id');
            $table->tinyInteger('promo_default')->default('0');   
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
        Schema::dropIfExists('users_promo_code');
    }
}
