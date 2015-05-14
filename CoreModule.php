<?php namespace App\Modules\Installation;

use Illuminate\Database\Eloquent\Model;

class CoreModule extends Model {

	protected $table    = 'core_modules';
	protected $fillable = ['module_name', 'module_key', 'module_version', 'module_type'];
	protected $catsts   = ['module_version' => 'boolean'];

	public static function boot()
	{
		parent::boot();

		CoreModule::deleting(function($module)
		{
			CoreSetting::where('module_key', '=', $module->module_key)->delete();
			CoreModulePart::where('module_key', '=', $module->module_key)->delete();
		});
	}
}
