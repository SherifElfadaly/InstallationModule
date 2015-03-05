<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoreModulesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if ( ! Schema::hasTable('core_modules'))
		{
			Schema::create('core_modules', function(Blueprint $table) 
			{
				$table->bigIncrements('id');
				$table->string('module_name');
				$table->string('module_key')->unique();
				$table->double('module_version');
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
		if (Schema::hasTable('core_modules'))
		{
			Schema::drop('core_modules');
		}
	}
}