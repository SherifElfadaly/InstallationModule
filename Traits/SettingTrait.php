<?php namespace App\Modules\Installation\Traits;

use App\Modules\Installation\CoreSetting;

trait SettingTrait{

	/**
	 * Get setting data from storage.
	 * @return setting data.
	 */
	public function getSetting($id)
	{
		return CoreSetting::find($id);
	}

	/**
	 * Get setting data belongs to specific
	 * module from storage by it's key and.
	 * @return setting data.
	 */
	public function getSettingValuByKey($key, $module_key)
	{
		return CoreSetting::where('key', '=', $key)->where('module_key', '=', $module_key)->first()->value;
	}

	/**
	 * Get setting data from storage related
	 * to a given module.
	 * @return setting data.
	 */
	public function getModuleSettings($module_key)
	{
		return CoreSetting::where('module_key', '=', $module_key)->get();
	}

	/**
	 * Save the newly created settings to
	 * storage.
	 * @param  array $data Module data
	 * @return array settings.
	 */
	public function saveSetting($data, $module_key)
	{	
		foreach ($data as $key => $value) 
		{
			if (is_array($value)) $value = serialize($value);

			$setting = CoreSetting::where('key', '=', str_replace('_', ' ', $key))->
						            where('module_key', '=', $module_key)->
						            first();

			$setting->value = $value;
			$setting->save();
		}
	}

	/**
	 * Delete setting form storage 
	 * @param  string $slug The slug of the module.
	 * @return void
	 */
	public function deleteSetting($id)
	{	
		$setting = $this->getSetting($id);
		$setting->delete();
	}
}