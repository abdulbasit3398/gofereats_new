<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuisineTable extends Migration {
	/**
	 * Schema table name to migrate
	 * @var string
	 */
	public $set_schema_table = 'cuisine';

	/**
	 * Run the migrations.
	 * @table cuisine
	 *
	 * @return void
	 */
	public function up() {
		if (Schema::hasTable($this->set_schema_table)) {
			return;
		}

		Schema::create($this->set_schema_table, function (Blueprint $table) {
			$table->increments('id');
			$table->string('name', 50)->nullable();
			$table->text('description')->nullable();
			$table->tinyInteger('status')->nullable();
			$table->tinyInteger('is_top')->nullable();
			$table->tinyInteger('is_dietary')->nullable();
			$table->tinyInteger('most_popular')->nullable();
			$table->Integer('service_type')->nullable();
			$table->nullableTimestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists($this->set_schema_table);
	}
}
