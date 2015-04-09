<?php namespace App\Modules\Installation\Repositories;

use App\Modules\Installation\CoreModule;
use App\Modules\Installation\Traits\CoreModuleTrait;
use App\Modules\Installation\Traits\SettingTrait;

class InstallationRepository
{
	use CoreModuleTrait;
	use SettingTrait;

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

		$git = new \PHPGit\Git();
		if($version === true)
		{
			$git->clone($link, app_path('Modules/') . ucfirst($jsonData->slug));
			$this->saveModuleData($module_data);
		}
		elseif(is_array($version))
		{
			$this->removeModelDirectory($jsonData->slug);
			$git->clone($link, app_path('Modules/') . ucfirst($jsonData->slug));

			$this->updateModuleData($jsonData->slug, $module_data);
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

			if($version === true) $this->saveModuleData($module_data);
			if(is_array($version)) $this->updateModuleData($moduleProperties['slug'], $module_data);
			
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

		$coreModule = CoreModule::where('module_key', '=', $jsonData->slug)->first();

		if(is_null($coreModule))
		{	
			if($jsonData->type = 'theme')
			{
				$this->disapleAllThemes();
			}

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
	public function removeModelDirectory($slug, $path = false)
	{
		if ($slug) $path  = app_path('Modules/') . ucfirst($slug) . '/';
		$files = glob($path . '{,.git}*', GLOB_BRACE);

		foreach ($files as $file) 
		{
			if (is_dir($file))
			{
				$this->removeModelDirectory(false, $file . '/');
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
		$user      =  $repo_data[0];
		$repoName  =  str_replace('.git', '', $repo_data[1]);
		$repoData  = json_decode($this->get_json("repos/$user/$repoName/contents/module.json"), true);

		if(array_key_exists('message', $repoData) && $repoData['message'] == 'Not Found')
			return false;

		return json_decode(base64_decode($repoData['content']));
	}
}
