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
		CoreModule::all()->each(function($module) use (&$modulesData){
			$properties                       = $this->getModuleProperties($module->module_key);
			$properties['id']                 = $module->id;
			$properties['moduleSettings']     = $this->getModuleSettings($module->module_key);
			$modulesData[$module->module_key] = $properties;
		});
		return $modulesData;
	}

	/**
	 * Get module data from storage.
	 * @return Module data.
	 */
	public function getModule($slug)
	{
		$module                 =  CoreModule::where('module_key', '=', $slug)->first();
		$module->moduleSettings = $this->getModuleSettings($module->module_key);
		return $module;
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
		$module = CoreModule::create($data);
		if (array_key_exists('module_parts', $data))
		{
			$this->saveModuleParts($module->module_key, $data['module_parts']);
		}
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
		\Artisan::call('module:migrate:reset', ['module' => $slug]);

		$this->removeModelDirectory($slug);
	}

	/**
	  * Enable or disaple module.
	  * @param  string $slug The slug of the module.
	  * @return void
	  */
	public function changeEnabled($slug)
	{
		if($this->getModule($slug)->module_type == 'plugin')
		{
			Module::isEnabled($slug) ? Module::disable($slug) : Module::enable($slug);
		}
		else
		{
			Module::isEnabled($slug) ? Module::disable($slug) : $this->changeTheme($slug);
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
	 * @return boolean.
	 */
	public function scanModules()
	{	
		\Artisan::call('module:migrate', ['module' => 'installation']);
		if( ! CoreModule::where('module_key', '=', 'installation')->count())
		{
			$this->saveModule(Module::getProperties('acl'));
		}

		foreach (Module::all() as $module) 
		{
			$this->saveModule($module);
			if($module['type'] == 'theme' && $module['enabled'] == true)
			{
				$this->changeTheme($module['slug']);
			}
		}
		return true;
	}

	/**
	 * Scan for modules whose data isn't stored in
	 * storage and store them.
	 * @return void.
	 */
	public function saveModule($module)
	{	
		$module_data = array();
		if( ! CoreModule::where('module_key', '=', $module['slug'])->count())
		{
			$module_data['module_name']      = $module['name'];
			$module_data['module_key']       = $module['slug'];
			$module_data['module_version']   = $module['version'];
			$module_data['module_type']      = $module['type'];

			if(array_key_exists('module_parts', $module))
			{
				$module_data['module_parts'] = $module['module_parts'];
			}

			$this->saveModuleData($module_data);
			\Artisan::call('module:migrate', ['module' => $module['slug']]);
		}
	}
}