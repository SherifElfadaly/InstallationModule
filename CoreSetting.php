<?php namespace App\Modules\Installation;

use Illuminate\Database\Eloquent\Model;

class CoreSetting extends Model {

	protected $table    = 'core_settings';
	protected $fillable = ['key', 'value', 'input_type', 'href', 'module_key', 'select_values'];

	public function getNameAttribute()
	{
		return preg_replace('/\s+/', '', $this->attributes['key']);
	}

	public function getSelectValuesAttribute()
	{
		return unserialize($this->attributes['select_values']);
	}

	public function getValueAttribute()
	{
		if($this->attributes['input_type'] == 'file')
		{	
			$files = unserialize($this->attributes['value']) ?: [];
			$files = array_map(
					function($value)
					{
						return \GalleryRepository::getGallery($value);
					} , 
					$files
				);

			return $files;
		}
		elseif(in_array($this->attributes['input_type'],  ['select', 'multiselect']))
		{
			return unserialize($this->attributes['value']) ?: [];
		}
		return $this->attributes['value'];
	}
}
