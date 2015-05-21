<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoreModulePartsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if ( ! Schema::hasTable('core_module_parts'))
		{
			Schema::create('core_module_parts', function(Blueprint $table) {
				$table->bigIncrements('id');
				$table->string('part_key', 100)->index();
				$table->string('module_key', 255)->index();
				$table->timestamps();
			});
		}
	}

	/**
	 * Reverse the migration.
	 *
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('core_module_parts'))
		{
			Schema::drop('core_module_parts');
		}
	}
}