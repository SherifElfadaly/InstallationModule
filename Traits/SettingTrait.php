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
	 * prepare setting data for saving
	 * @param  array $data Module data
	 * @return array settings.
	 */
	public function prepareSettingData($data)
	{
		$settings = array();
		for ($i = 0 ; $i < count($data['key']) ; $i++) 
		{ 
			$settings[] =  new CoreSetting(['key' => $data['key'][$i], 'value' => $data['value'][$i]]);
		}
		return $settings;
	}

	/**
	 * Save the newly created setting to
	 * module.
	 * @param  array $data Module data
	 * @return void.
	 */
	public function createSetting($data, $moduleId)
	{	
		$this->getModule($moduleId)->coreSettings()->saveMany($data);
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

	/**
	 * Delete settings related to module
	 * @param  string $slug The slug of the module.
	 * @return void
	 */
	public function deleteSettings($obj)
	{
		$obj->coreSettings()->delete();
	}
}