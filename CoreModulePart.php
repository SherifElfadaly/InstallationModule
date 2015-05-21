<?php namespace App\Modules\Installation;

use Illuminate\Database\Eloquent\Model;

class CoreModulePart extends Model {

	/**
	 * Spescify the storage table.
	 * 
	 * @var table
	 */
	protected $table    = 'core_module_parts';

	/**
	 * Specify the fields allowed for the mass assignment.
	 * 
	 * @var fillable
	 */
	protected $fillable = ['part_key', 'module_key'];
}
