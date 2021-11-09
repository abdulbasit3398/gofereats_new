<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCurrencyTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'currency';

    /**
     * Run the migrations.
     * @table currency
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('code', 7)->unique();
            $table->string('symbol', 10);
            $table->decimal('rate', 10, 3);
            $table->tinyInteger('status')->default('1');
            $table->tinyInteger('default_currency')->default('0');
            $table->tinyInteger('paypal_currency')->default('0');
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
       Schema::dropIfExists($this->set_schema_table);
     }
}
