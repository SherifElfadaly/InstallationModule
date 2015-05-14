<?php namespace App\Modules\Installation\Repositories;

use App\AbstractRepositories\AbstractRepository;
use App\Modules\Installation\CoreModule;

class CoreModuleRepository extends AbstractRepository
{
	protected function getModel()
	{
		return 'App\Modules\Installation\CoreModule';
	}

	protected function getRelations()
	{
		return [];
	}

	/**
	 * Clone module form a github repository.
	 * @param  string $link Github repository link 
	 * @return True if new module. Array if module 
	 *         update containig old version and 
	 *         new version. Int if module.json
	 *         isn't found in the repository.
	 */
	public function cloneModule($link)
	{
		$jsonData  = $this->get_repo_data($link);
		if( ! $jsonData) return 404;

		$version   = $this->checkModuleVersion(false, $jsonData);

		$module_data['module_name']    = $jsonData->name;
		$module_data['module_key']     = $jsonData->slug;
		$module_data['module_version'] = $jsonData->version;
		$module_data['module_type']    = $jsonData->type;

		if (property_exists($jsonData, 'module_parts'))
		{
			$module_data['module_parts']   = $jsonData->module_parts;
		}

		$git = \App::make('\PHPGit\Git');
		if($version === true)
		{
			$git->clone($link, app_path('Modules/') . ucfirst($jsonData->slug));
			$this->saveModuleData($module_data);
		}
		elseif(is_array($version))
		{
			$this->removeModuleDirectory($jsonData->slug);
			$git->clone($link, app_path('Modules/') . ucfirst($jsonData->slug));
			$this->$this->update($moduleProperties['slug'], $module_data, 'module_key');
		}

		\Artisan::call('module:migrate', ['module' => $jsonData->slug]);
		return $version;
	}

	/**
	 * Upload module from zip file.
	 * @param  file $file The zip file 
	 *         containing the module
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

			$moduleProperties              = $this->getModuleProperties(explode('.', $file->getFilename())[0]);
			$module_data['module_name']    = $moduleProperties['name'];
			$module_data['module_key']     = $moduleProperties['slug'];
			$module_data['module_version'] = $moduleProperties['version'];
			$module_data['module_type']    = $moduleProperties['type'];

			if(array_key_exists('module_parts', $moduleProperties))
			{
				$module_data['module_parts']   = $moduleProperties['module_parts'];
			}

			if($version === true) $this->saveModuleData($module_data);
			if(is_array($version)) $this->update($moduleProperties['slug'], $module_data, 'module_key');
			
			unlink($file->getRealPath());
			\Artisan::call('module:migrate', ['module' => $moduleProperties['slug']]);
		}

		return $version;
	}

	/**
	 * Extract module zip file to Modules directory.
	 * @param  string $modulePath Path to the uploaded 
	 *         module zip file.
	 * @return void.
	 */
	public function extractModule($modulePath)
	{
		$zip    = new \ZipArchive;
		$result = $zip->open($modulePath);

		$zip->extractTo(app_path('Modules'));
		$zip->close();
	}

	/**
	 * Copmare the version of uploaded module or
	 * github module with the version of the installed 
	 * module.
	 * @param  string  $modulePath Uploaded module zip 
	 *         file path.
	 * @param  json $jsonData   Json data from the github
	 *         repository.
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
						$jsonData = json_decode(zip_entry_read($zip_entry));
					}
				}
				zip_close($zip);
			}
		}

		$coreModule = $this->findBy('module_key', $jsonData->slug)[0];

		if(is_null($coreModule))
		{	
			return true;
		}
		elseif ($coreModule->module_version < $jsonData->version) 
		{
			return ['oldVersion' => $coreModule->module_version , 'newVersion' => $jsonData->version];
		}
		return false;
	}

	/**
	 * Uninstall module from the Module ditectory
	 * @param  string  $slug The slug of the module.
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

		return json_decode(base64_decode($repoData['content']));
	}

	/**
	 * Get all installed modules.
	 * @return array Modules data.
	 */
	public function getAllModules()
	{
		$modulesData = array();
		$this->all()->each(function($module) use (&$modulesData){
			$properties                       = $this->getModuleProperties($module->module_key);
			$properties['id']                 = $module->id;
			$properties['moduleSettings']     = \CMS::coreModuleSettings()->getModuleSettings($module->module_key);
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
		$module                 =  $this->findBy('module_key', $slug)[0];
		$module->moduleSettings = \CMS::coreModuleSettings()->getModuleSettings($module->module_key);
		return $module;
	}

	/**
	 * Get the enabled theme data from storage.
	 * @return Module data.
	 */
	public function getActiveTheme()
	{
		$activeTheme = false;
		$this->findBy('module_type', 'theme')->each(function($theme) use (&$activeTheme){
			if(\Module::isEnabled($theme->module_key)) $activeTheme = $theme;
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
			\CMS::coreModuleParts()->saveModuleParts($module->module_key, $data['module_parts']);
		}
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
		$this->removeModuleDirectory($slug);
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
			\Module::isEnabled($slug) ? \Module::disable($slug) : \Module::enable($slug);
		}
		else
		{
			\Module::isEnabled($slug) ? \Module::disable($slug) : $this->changeTheme($slug);
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
			\Module::enable($slug);
		}
	}

	/**
	  * Disaple All Themes.
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
	 * @param  string $moduleName The slug of the module.
	 * @return array module data.
	 */
	public function getModuleProperties($moduleName)
	{
		return \Module::getProperties($moduleName);
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
		if( ! $this->findBy('module_key', 'installation')->count())
		{
			$this->saveModule($this->getModuleProperties('acl'));
		}

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
	 * Scan for modules whose data isn't stored in
	 * storage and store them.
	 * @return void.
	 */
	public function saveModule($module)
	{	
		$module_data = array();
		if( ! $this->findBy('module_key', $module['slug'])->count())
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
