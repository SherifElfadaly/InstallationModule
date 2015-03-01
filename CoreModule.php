<?php namespace App\Modules\Installation;

use Illuminate\Database\Eloquent\Model;

class CoreModule extends Model {

	protected $table    = 'core_modules';
	protected $fillable = ['module_name', 'module_key', 'module_version'];
	protected $catsts   = ['module_version' => 'boolean'];

	public function coreSettings()
	{
		return $this->hasMany('App\Modules\Installation\CoreSetting', 'module_id');
	}
}
