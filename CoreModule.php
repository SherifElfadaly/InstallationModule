<?php namespace App\Modules\Installation;

use Illuminate\Database\Eloquent\Model;

class CoreModule extends Model {

	/**
	 * Spescify the storage table.
	 * 
	 * @var table
	 */
	protected $table    = 'core_modules';

	/**
	 * Specify the fields allowed for the mass assignment.
	 * 
	 * @var fillable
	 */
	protected $fillable = ['module_name', 'module_key', 'module_version', 'module_type'];

	/**
	 * Specify what field should be castet to what.
	 * 
	 * @var casts
	 */
	protected $catsts   = ['module_version' => 'boolean'];

	public static function boot()
	{
		parent::boot();

		/**
		 * Remove the CoreSetting and CoreModulePart
		 * related to the deleted module.
		 */
		CoreModule::deleting(function($module)
		{
			\CMS::coreModuleSettings()->delete($module->module_key, 'module_key');
			\CMS::coreModuleParts()->delete($module->module_key, 'module_key');
		});
	}
}
