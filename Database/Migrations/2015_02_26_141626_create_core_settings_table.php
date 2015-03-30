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
				$table->bigIncrements('id');
				$table->string('key', 100)->index();
				$table->text('value');

				$table->bigInteger('module_id')->unsigned();
				$table->foreign('module_id')->references('id')->on('core_modules');
				
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