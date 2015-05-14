<?php namespace App\Modules\Installation\Repositories;

use App\AbstractRepositories\AbstractRepository;
use App\Modules\Installation\CoreModuleSetting;

class CoreModuleSettingRepository extends AbstractRepository
{
	protected function getModel()
	{
		return 'App\Modules\Installation\CoreModuleSetting';
	}

	protected function getRelations()
	{
		return [];
	}

	/**
	 * Get setting data belongs to specific
	 * module from storage by it's key and.
	 * @return setting data.
	 */
	public function getSettingValuByKey($key, $module_key)
	{
		return CoreModuleSetting::where('key', '=', $key)->where('module_key', '=', $module_key)->first()->value;
	}

	/**
	 * Get setting data from storage related
	 * to a given module.
	 * @return setting data.
	 */
	public function getModuleSettings($module_key)
	{
		return $this->findBy('module_key', $module_key);
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

			$setting = CoreModuleSetting::where('key', '=', str_replace('_', ' ', $key))->
						            where('module_key', '=', $module_key)->
						            first();

			$setting->value = $value;
			$setting->save();
		}
	}
}
