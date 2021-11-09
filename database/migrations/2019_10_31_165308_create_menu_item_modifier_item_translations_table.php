<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuItemModifierItemTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_item_modifier_item_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('menu_item_modifier_item_id')->unsigned();
            $table->string('name');
            $table->string('locale',5)->index();
            $table->unique(['menu_item_modifier_item_id','locale'],'mimi_locale');            
            $table->foreign('menu_item_modifier_item_id','mimi_translation')->references('id')->on('menu_item_modifier_item')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu_item_modifier_item_translations');
    }
}
