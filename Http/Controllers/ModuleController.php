<?php namespace App\Modules\Installation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Installation\Repositories\InstallationRepository;
use App\Modules\Installation\Http\Requests\InstallationFormRequest;

class ModuleController extends Controller {

	/**
	 * The InstallationRepository implementation.
	 *
	 * @var InstallationRepository
	 */
	protected $installation;

	/**
	 * Create new ModuleController instance.
	 * @param InstallationRepository
	 */
	public function __construct(InstallationRepository $installation)
	{
		$this->installation = $installation;
	}

	/**
	 * Display a listing of the modules
	 * and check for modules who needs 
	 * updates.
	 * 
	 * @return Response
	 */
	public function getIndex()
	{
		$modules = $this->installation->getAllModules();
		foreach ($modules as &$module) 
		{
			$module['need_update'] = false;
			if( ! array_key_exists('repo_link', $module)) continue;

			$jsonData  = $this->installation->get_repo_data($module['repo_link']);

			if($module['version'] < $jsonData->version) $module['need_update'] = true;
		}
		return view('Installation::modules', compact('modules'));
	}

	/**
	 * Update module from git repository
	 *
	 * @return Response
	 */
	public function getUpdate($slug)
	{
		$result = $this->installation->cloneModule($this->installation->getModuleProperties($slug)['repo_link']);

		$message = 'Your module already exists and up to date';
		if (is_array($result)) 
		{
			$message = 'Your module had been Updated From ' . $result['oldVersion'] . ' to ' . $result['newVersion'];
		}
		elseif ($result = 404)
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
		return view('Installation::addmodule');
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
			$result = $this->installation->uploadModule($request->file('module'));
		}
		else
		{
			$result = $this->installation->cloneModule($request->get('repo_link'));	
		}

		$message = 'Your module already exists and up to date';
		if ($result === true) 
		{
			$message = 'Your module had been created';
		}
		elseif (is_array($result)) 
		{
			$message = 'Your module had been Updated From ' . $result['oldVersion'] . ' to ' . $result['newVersion'];
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
		$this->installation->changeEnabled($slug);
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
		$this->installation->deleteModule($slug);
		return 	redirect()->back();
	}

}
