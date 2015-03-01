<?php namespace App\Modules\Installation;

use Illuminate\Database\Eloquent\Model;

class CoreSetting extends Model {

	protected $table    = 'core_settings';
	protected $fillable = ['key', 'value'];

	public function coreModule()
	{
		return $this->belongsTo('App\Modules\Installation\CoreModule');
	}
}
