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
			Schema::create('core_settings', function(Blueprint $table) 
			{
				$table->bigIncrements('id');
				$table->string('key', 100)->index();
				$table->text('value');
				$table->enum('input_type', ['select', 'multiselect', 'link', 'color', 'date', 'datetime', 'datetime-local', 'email', 'file', 'month', 'number', 'password', 'range', 'search', 'tel', 'text', 'time', 'url', 'week'])->index();;
				$table->string('select_values', 255)->index();
				$table->string('href', 255)->index();
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
		if (Schema::hasTable('core_settings'))
		{
			Schema::drop('core_settings');
		}
	}
}

