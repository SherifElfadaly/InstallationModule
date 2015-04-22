<?php namespace App\Modules\Installation\Traits;

use App\Modules\Installation\CoreModule;
use Module;

trait CoreModuleTrait{

	/**
	 * Get all installed modules.
	 * @return array Modules data.
	 */
	public function getAllModules()
	{
		$modulesData = array();
		CoreModule::with('coreSettings')->get()->each(function($module) use (&$modulesData){
			$modulesData[$module->module_key] = Module::getProperties($module->module_key);
		});
		return $modulesData;
	}

	/**
	 * Get module data from storage.
	 * @return Module data.
	 */
	public function getModule($slug)
	{
		return CoreModule::where('module_key', '=', $slug)->first();
	}

	/**
	 * Get the enabled theme data from storage.
	 * @return Module data.
	 */
	public function getActiveTheme()
	{
		$activeTheme = false;
		CoreModule::where('module_type', '=', 'theme')->get()->each(function($theme) use (&$activeTheme){
			if(Module::isEnabled($theme->module_key)) $activeTheme = $theme;
		});
		return $activeTheme;
	}

	/**
	 * Save the newly installed module to
	 * storage.
	 * @param  array $data Module data
	 * @return void.
	 */
	public function saveModuleData($data)
	{
		CoreModule::create($data);
	}

	/**
	 * Save the updated module to storage.
	 * @param  string $slug The slug of the module.
	 * @param  array $data Module data
	 * @return void.
	 */
	public function updateModuleData($slug, $data)
	{
		$this->getModule($slug)->update($data);
	}

	/**
	 * Delete module form storage and
	 * unistall it.
	 * @param  string $slug The slug of the module.
	 * @return void
	 */
	public function deleteModule($slug)
	{
		$this->getModule($slug)->delete();
		\Artisan::call('module:migrate-reset', ['module' => $slug]);

		$this->removeModelDirectory($slug);
	}

	/**
	  * Enable or disaple module.
	  * @param  string $slug The slug of the module.
	  * @return void
	  */
	public function changeEnabled($slug)
	{
		if($this->getModule($slug)->module_type = 'plugin')
		{
			Module::isEnabled($slug) ? Module::disable($slug) : Module::enable($slug);
		}
	}

	/**
	  * Set the main theme.
	  * @param  string $slug The slug of the module.
	  * @return void
	  */
	public function changeTheme($slug)
	{
		if($this->getModule($slug)->module_type = 'theme')
		{
			$this->disapleAllThemes();
			Module::enable($slug);
		}
	}

	/**
	  * Disaple All Themes.
	  * @return void
	  */
	public function disapleAllThemes()
	{
		CoreModule::where('module_type', '=', 'theme')->get()->each(function($module){
				Module::disable($module->module_key);
			});
	}

	/**
	 * Get the module.json file content.
	 * @param  string $moduleName The slug of the module.
	 * @return array module data.
	 */
	public function getModuleProperties($moduleName)
	{
		return Module::getProperties($moduleName);
	}

	/**
	 * Check if the module or modules need update.
	 * @param  string $modules The single or array 
	 * of module object.
	 * @return array module data.
	 */
	public function needUpdate($modules)
	{
		foreach ($modules as &$module) 
		{
			$module['need_update'] = false;
			if( ! array_key_exists('repo_link', $module)) continue;

			$jsonData  = $this->get_repo_data($module['repo_link']);
			if( ! $jsonData) continue;

			if($module['version'] < $jsonData->version) $module['need_update'] = true;
		}
		return $modules;
	}

	/**
	 * Scan for modules whose data isn't stored in
	 * storage and store them.
	 * @return void.
	 */
	public function scanModules()
	{
		foreach (Module::all() as $module) 
		{
			if( ! CoreModule::where('module_key', '=', $module['slug'])->count())
			{
				$module_data['module_name']    = $module['name'];
				$module_data['module_key']     = $module['slug'];
				$module_data['module_version'] = $module['version'];
				$module_data['module_type']    = $module['type'];

				$this->saveModuleData($module_data);
			}
		}	
	}
}