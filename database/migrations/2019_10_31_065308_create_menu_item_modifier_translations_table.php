<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuItemModifierTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_item_modifier_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('menu_item_modifier_id')->unsigned();
            $table->string('name');
            $table->string('locale',5)->index();
            $table->unique(['menu_item_modifier_id','locale'],'mim_locale');
            $table->foreign('menu_item_modifier_id','mim_translation')->references('id')->on('menu_item_modifier')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu_item_modifier_translations');
    }
}
