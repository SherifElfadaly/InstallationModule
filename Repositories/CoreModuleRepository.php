<?php namespace App\Modules\Installation\Repositories;

use App\Modules\Core\AbstractRepositories\AbstractRepository;

class CoreModuleRepository extends AbstractRepository
{
	/**
	 * Return the model full namespace.
	 * 
	 * @return string
	 */
	protected function getModel()
	{
		return 'App\Modules\Installation\CoreModule';
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
	 * Clone module form a github repository.
	 * 
	 * @param  string $link Github repository link 
	 * @return True if new module. Array if module 
	 *         updated and the containig old version 
	 *         and new version. Int if module.json
	 *         isn't found in the repository.
	 */
	public function cloneModule($link)
	{
		$jsonData  = $this->get_repo_data($link);
		if( ! $jsonData) return 404;

		$version = $this->checkModuleVersion(false, $jsonData);
		$git     = \App::make('\PHPGit\Git');
		if($version === true)
		{
			$git->clone($link, app_path('Modules/') . ucfirst($jsonData['slug']));
		}
		elseif(is_array($version))
		{
			$this->removeModuleDirectory($jsonData['slug']);
			$git->clone($link, app_path('Modules/') . ucfirst($jsonData['slug']));
		}
		$this->saveModule($jsonData, true);
		return $version;
	}

	/**
	 * Upload module from zip file.
	 * 
	 * @param  file $file The zip file 
	 *                    containing the module
	 * @return True if new module. Array if module 
	 *         update containig old version and 
	 *         new version.
	 */
	public function uploadModule($file)
	{
		$file    = $file->move(app_path('Modules'), $file->getClientOriginalName());
		$version = $this->checkModuleVersion(app_path('Modules/') . $file->getFilename());

		if($version) 
		{
			$this->extractModule(app_path('Modules/') . $file->getFilename());

			$jsonData = $this->getModuleProperties(explode('.', $file->getFilename())[0]);

 			$this->saveModule($jsonData, true);
			\Artisan::call('module:migrate', ['module' => $jsonData['slug']]);
		}

		unlink($file->getRealPath());
		return $version;
	}

	/**
	 * Extract module zip file to Modules directory.
	 * 
	 * @param  string $modulePath Path to the uploaded 
	 *                            module zip file.
	 * @return void.
	 */
	public function extractModule($modulePath)
	{
		$zip    = \App::make('\ZipArchive');
		$result = $zip->open($modulePath);

		$zip->extractTo(app_path('Modules'));
		$zip->close();
	}

	/**
	 * Compare the version of uploaded module or
	 * github module with the version of the installed 
	 * module.
	 * 
	 * @param  string $modulePath Uploaded module zip 
	 *                            file path.
	 * @param  json   $jsonData   Json data from the github
	 *                            repository.
	 * @return True if the module isn't installed. 
	 *         Array if module is installed and 
	 *         needs to be updated. False if module
	 *         is installed and up to date.
	 */
	public function checkModuleVersion($modulePath, $jsonData = false)
	{
		if( ! $jsonData)
		{
			$zip = zip_open($modulePath);

			if ($zip)
			{
				while ($zip_entry = zip_read($zip))
				{
					if(str_contains(zip_entry_name($zip_entry), 'module.json') && zip_entry_open($zip, $zip_entry))
					{
						$jsonData = json_decode(zip_entry_read($zip_entry), true);
					}
				}
				zip_close($zip);
			}
		}

		$coreModule = $this->first('module_key', $jsonData['slug']);

		if ($coreModule === false)
		{	
			return true;
		}
		elseif ($coreModule->module_version < $jsonData['version']) 
		{
			return ['oldVersion' => $coreModule->module_version , 'newVersion' => $jsonData['version']];
		}
		return false;
	}

	/**
	 * Uninstall module from the Module ditectory
	 * 
	 * @param  string $slug The slug of the module.
	 * @param  string $path The path of the sub directory.
	 * @return void.
	 */
	public function removeModuleDirectory($slug, $path = false)
	{
		if ($slug) $path  = app_path('Modules/') . ucfirst($slug) . '/';
		$files = glob($path . '{,.git}*', GLOB_BRACE);

		foreach ($files as $file) 
		{
			if (is_dir($file))
			{
				$this->removeModuleDirectory(false, $file . '/');
			}
			else
			{
				unlink($file);
			}
		}
		rmdir($path);
	}

	/**
	 * Send http request to fetch data from github
	 * 
	 * @param  string $url The data to be fetched
	 * @return json.
	 */
	public function get_json($url)
	{
		$base = "https://api.github.com/";
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $base . $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'User-Agent: SherifElfadaly'
			));
		$content = curl_exec($curl);
		curl_close($curl);
		return $content;
	}

	/**
	 * Get data form module.json file from the githup
	 * repository.
	 * 
	 * @param  string $repoLink The repository link.
	 * @return json.
	 */
	public function get_repo_data($repoLink)
	{	
		$repo_data = explode('/', str_replace('https://github.com/', '', $repoLink));
		$user      = $repo_data[0];
		$repoName  = str_replace('.git', '', $repo_data[1]);
		$repoData  = json_decode($this->get_json("repos/$user/$repoName/contents/module.json?client_id=6f774891be99f71bb759&client_secret=e45113efc862d92f90f146b1382cc5ad02315cc7"), true);

		if(array_key_exists('message', $repoData) && $repoData['message'] == 'Not Found')
			return false;
		return json_decode(base64_decode($repoData['content']), true);
	}

	/**
	 * Get all installed modules.
	 * 
	 * @return collection.
	 */
	public function getAllModules()
	{
		$modulesData = array();
		$this->all()->each(function($module) use (&$modulesData){
			$properties                       = $this->getModuleProperties($module->module_key);
			$properties['id']                 = $module->id;
			$properties['moduleSettings']     = \CMS::coreModuleSettings()->getModuleSettings($module->module_key);
			$properties['moduleParts']        = \CMS::coreModuleParts()->getModuleParts($module->module_key);
			$modulesData[$module->module_key] = $properties;
			});
		return $modulesData;
	}

	/**
	 * Get module data from storage.
	 * 
	 * @return object.
	 */
	public function getModule($slug)
	{
		$module                 =  $this->first('module_key', $slug);
		$module->moduleSettings = \CMS::coreModuleSettings()->getModuleSettings($module->module_key);
		return $module;
	}

	/**
	 * Get the enabled theme data from storage.
	 * 
	 * @return object.
	 */
	public function getActiveTheme()
	{
		$activeTheme = false;
		$this->findBy('module_type', 'theme')->each(function($theme) use (&$activeTheme){
			if(\Module::isEnabled($theme->module_key)) 
			{
				$activeTheme = $theme;
				return;
			}
		});
		return $activeTheme;
	}

	/**
	  * Enable or disaple module.
	  * 
	  * @param  string $slug The slug of the module.
	  * @return void
	  */
	public function changeEnabled($slug)
	{
		if($this->getModule($slug)->module_type == 'theme')
		{
			$this->changeTheme($slug);
		}
		else
		{
			\Module::isEnabled($slug) ? \Module::disable($slug) : \Module::enable($slug);
		}
	}

	/**
	  * Set the main theme.
	  * 
	  * @param  string $slug The slug of the module.
	  * @return void
	  */
	public function changeTheme($slug)
	{
		if($this->getModule($slug)->module_type = 'theme')
		{
			$this->disapleAllThemes();
			\Module::enable($slug);
			\Artisan::call('module:migrate', ['module' => $slug]);
		}
	}

	/**
	  * Disaple All Themes.
	  * 
	  * @return void
	  */
	public function disapleAllThemes()
	{
		$this->findBy('module_type', 'theme')->each(function($module){
				\Module::disable($module->module_key);
			});
	}

	/**
	 * Get the module.json file content.
	 * 
	 * @param  string $moduleName The slug of the module.
	 * @return array
	 */
	public function getModuleProperties($moduleName)
	{
		return \Module::getProperties($moduleName);
	}

	/**
	 * Check if the given modules need update.
	 * 
	 * @param  string $modules The single or array 
	 *                         of module object.
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

			if($module['version'] < $jsonData['version']) $module['need_update'] = true;
		}
		return $modules;
	}

	/**
	 * Scan for modules whose data isn't stored in
	 * storage and store them.
	 * 
	 * @return boolean.
	 */
	public function scanModules()
	{	
		foreach (\Module::all() as $module) 
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
	 * Save and install the given module data , if
	 * update is true then update the module in the 
	 * storage.
	 * 
	 * @param  collection $module
	 * @param  boolean    $update
	 * @return void.
	 */
	public function saveModule($module, $update = false)
	{	
		
		if ($module['slug'] === 'installation') 
		{
			\Artisan::call('module:migrate', ['module' => $module['slug']]);
		}
		if ( ! $this->first('module_key', $module['slug'])) 
		{
			if (array_key_exists('module_parts', $module))
			{	
				\CMS::coreModuleParts()->saveModuleParts($module['slug'], $module['module_parts']);
			}
			\Artisan::call('module:migrate', ['module' => $module['slug']]);	

			$module_data 					 = array();
			$module_data['module_name']      = $module['name'];
			$module_data['module_key']       = $module['slug'];
			$module_data['module_version']   = $module['version'];
			$module_data['module_type']      = $module['type'];

			if ($this->findBy('module_key', $module['slug'])->count() === 0)
			{
				$this->create($module_data);
			}
			elseif ($update)
			{
				$this->update($module['slug'], $module_data, 'module_key');
			}
		}
	}

	/**
	 * Save the newly installed module to
	 * storage.
	 * 
	 * @param  array $data Module data
	 * @return void.
	 */
	public function saveModuleData($data)
	{
		$module = $this->create($data);
	}

	/**
	 * update the module to storage.
	 *
	 * @param  string $slug
	 * @param  array  $data Module data
	 * @return void.
	 */
	public function updateModuleData($slug, $data)
	{
		$module = $this->first('module_key', $slug);
		$this->update($module->id, $data);
	}
	
	/**
	 * Delete module form storage and
	 * unistall it.
	 * 
	 * @param  string $slug The slug of the module.
	 * @return void
	 */
	public function deleteModule($slug)
	{
		$this->getModule($slug)->delete();
		\Artisan::call('module:migrate:reset', ['module' => $slug]);
		$this->removeModuleDirectory($slug);
	}
}
