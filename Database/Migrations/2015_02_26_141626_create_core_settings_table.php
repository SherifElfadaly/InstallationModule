<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoreSettingsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if ( ! Schema::hasTable('core_settings'))
		{
			Schema::create('core_settings', function(Blueprint $table) {
				$table->increments('id');
				$table->string('key');
				$table->text('value');
				$table->integer('module_id');
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
		if (Schema::hasTable('core_settings'))
		{
			Schema::drop('core_settings');
		}
	}
}