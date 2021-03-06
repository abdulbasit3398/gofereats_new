<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuItemModifierItemTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'menu_item_modifier_item';

    /**
     * Run the migrations.
     * @table menu_item_modifier_item
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('menu_item_modifier_id')->unsigned()->nullable();
            $table->foreign('menu_item_modifier_id')->references('id')->on('menu_item_modifier');
            $table->string('name', 45)->nullable();
            $table->decimal('price', 11, 2)->nullable();
            $table->string('currency_code', 20)->nullable();
            $table->tinyInteger('is_visible')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists($this->set_schema_table);
     }
}
