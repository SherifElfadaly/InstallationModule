<?php namespace App\Modules\Installation\Http\Controllers;

use App\Modules\Core\Http\Controllers\BaseController;
use App\Modules\Installation\Http\Requests\InstallationFormRequest;

class ModuleController extends BaseController {

	/**
	 * Specify that this controller should be 
	 * accessed by the admin users only.
	 * @var adminOnly
	 */
	protected $adminOnly = true;

	/**
	 * Create new ModuleController instance.
	 */
	public function __construct()
	{
		parent::__construct('Modules');
	}

	/**
	 * Display a listing of the modules,
	 * scan for unistalled modules and
	 * check for modules that need
	 * updates.
	 * 
	 * @return Response
	 */
	public function getIndex()
	{
		$modules = \CMS::coreModules()->getAllModules();
		$modules = \CMS::coreModules()->needUpdate($modules);

		return view('Installation::modules.modules', compact('modules'));
	}

	/**
	 * Update module from git repository
	 *
	 * @return Response
	 */
	public function getUpdate($slug)
	{
		$result  = \CMS::coreModules()->cloneModule(\CMS::coreModules()->getModuleProperties($slug)['repo_link']);
		
		$message = 'Your module already exists and up to date';
		if (is_array($result)) 
		{
			$message = 'Your module ' . $slug . ' had been updated From ' . $result['oldVersion'] . ' to ' . $result['newVersion'];
		}
		elseif ($result == 404)
		{
			$message = "module.json file not found";	
		}
		return 	redirect()->back()->with('message', $message);
	}

	/**
	 * Show the form for creating a new module.
	 *
	 * @return Response
	 */
	public function getCreate()
	{	
		return view('Installation::modules.addmodule');
	}

	/**
	 * Store a newly created module in storage.
	 *
	 * @return Response
	 */
	public function postCreate(InstallationFormRequest $request)
	{
		if ( ! is_null($request->file('module'))) 
		{
			$result = \CMS::coreModules()->uploadModule($request->file('module'));
		}
		else
		{
			$result = \CMS::coreModules()->cloneModule($request->get('repo_link'));	
		}

		$message = 'Your module already exists and up to date';
		if ($result === true) 
		{
			$message = 'Your module had been created';
		}
		elseif (is_array($result)) 
		{
			$message = 'Your module had been updated From ' . $result['oldVersion'] . ' to ' . $result['newVersion'];
		}
		elseif ($result == 404)
		{
			$message = "module.json file not found";	
		}
		return 	redirect()->back()->with('message', $message);
	}

	/**
	 * Enable or Disaple module
	 *
	 * @param  string  $slug
	 * @return Response
	 */
	public function getEnabled($slug)
	{
		\CMS::coreModules()->changeEnabled($slug);
		return 	redirect()->back();
	}

	/**
	 * Remove the specified module from storage.
	 *
	 * @param  int  $slug
	 * @return Response
	 */
	public function getDelete($slug)
	{
		\CMS::coreModules()->deleteModule($slug);
		return 	redirect()->back();
	}

	/**
	 * Show the module parts.
	 *
	 * @param  int  $slug
	 * @return Response
	 */
	public function getModuleparts($slug)
	{
		$moduleParts = \CMS::coreModuleParts()->getModuleParts($slug);
		return view('Installation::modules.moduleparts', compact('moduleParts'));
	}

}
