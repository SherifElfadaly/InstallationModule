<?php namespace App\Modules\Installation;

use Illuminate\Database\Eloquent\Model;

class CoreModuleSetting extends Model {

	/**
	 * Spescify the storage table.
	 * 
	 * @var table
	 */
	protected $table    = 'core_settings';

	/**
	 * Specify the fields allowed for the mass assignment.
	 * 
	 * @var fillable
	 */
	protected $fillable = ['key', 'value', 'input_type', 'href', 'module_key', 'select_values'];

	/**
	 * Remove all white spaces from the key.
	 * 
	 * @return string
	 */
	public function getNameAttribute()
	{
		return preg_replace('/\s+/', '', $this->attributes['key']);
	}

	/**
	 * Return the selected values array of 
	 * the settings of type select or multi select.
	 * 
	 * @return [type] [description]
	 */
	public function getSelectValuesAttribute()
	{
		return unserialize($this->attributes['select_values']);
	}

	/**
	 * If the settings of type file then return
	 * an array of galleries matches the stored
	 * ids else Return the values array of the 
	 * settings of type select or multi select.
	 * 
	 * @return [type] [description]
	 */
	public function getValueAttribute()
	{
		if($this->attributes['input_type'] == 'file')
		{	
			$files = unserialize($this->attributes['value']) ?: [];
			$files = array_map(
					function($value)
					{
						return \CMS::galleries()->find($value);
					} , $files);
			return $files;
		}
		elseif(in_array($this->attributes['input_type'],  ['select', 'multiselect']))
		{
			return unserialize($this->attributes['value']) ?: [];
		}
		return $this->attributes['value'];
	}
}
