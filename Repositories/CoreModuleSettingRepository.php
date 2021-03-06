<?php namespace App\Modules\Installation\Repositories;

use App\Modules\Core\AbstractRepositories\AbstractRepository;

class CoreModuleSettingRepository extends AbstractRepository
{	
	/**
	 * Return the model full namespace.
	 * 
	 * @return string
	 */
	protected function getModel()
	{
		return 'App\Modules\Installation\CoreModuleSetting';
	}

	/**
	 * Return the module relations.
	 * 
	 * @return array
	 */
	protected function getRelations()
	{
		return [];
	}

	/**
	 * Get settings value belongs to specific
	 * module by it's key from storage.
	 * 
	 * @param  string $key
	 * @param  string $module_key
	 * @return array if the setting type is select ,
	 *         multi select or file else string.
	 */
	public function getSettingValuByKey($key, $module_key, $language = false)
	{
		$moduleSetting = $this->model->where('key', '=', $key)->where('module_key', '=', $module_key)->first();
		return is_null($moduleSetting) ? '' : $moduleSetting->value;
	}

	/**
	 * Get setting data from storage related
	 * to a given module.
	 * 
	 * @param  string $module_key
	 * @return collection.
	 */
	public function getModuleSettings($module_key, $language = false)
	{
		$moduleSettings =  $this->findBy('module_key', $module_key);
		return $moduleSettings;
	}

	/**
	 * Save the newly created settings to storage.
	 * 
	 * @param  array  $data
	 * @param  string $module_key
	 * @return void.
	 */
	public function saveSetting($data, $module_key)
	{		
		foreach ($data as $key => $value) 
		{
			$setting = $this->model->where('key', '=', str_replace('_', ' ', $key))->
				                     where('module_key', '=', $module_key)->
				                     first();
			if (is_array($value['value'])) $value['value'] = serialize($value['value']);

			$setting->value = $value['value'];
			$setting->save();
		}
	}
}
