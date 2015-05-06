<?php namespace App\Modules\Installation\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Modules\Installation\Repositories\InstallationRepository;
use App\Modules\Installation\Http\Requests\InstallationFormRequest;

class ModuleController extends BaseController {

	/**
	 * Create new ModuleController instance.
	 * @param InstallationRepository
	 */
	public function __construct(InstallationRepository $installation)
	{
		parent::__construct($installation, 'Modules');
		$this->middleware('AclAuthenticate');
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
		$this->repository->scanModules();
		$modules = $this->repository->getAllModules();
		$modules = $this->repository->needUpdate($modules);

		return view('Installation::modules.modules', compact('modules'));
	}

	/**
	 * Update module from git repository
	 *
	 * @return Response
	 */
	public function getUpdate($slug)
	{
		$result  = $this->repository->cloneModule($this->repository->getModuleProperties($slug)['repo_link']);
		
		$message = 'Your module already exists and up to date';
		if (is_array($result)) 
		{
			$message = 'Your module ' . $slug . ' had been Updated From ' . $result['oldVersion'] . ' to ' . $result['newVersion'];
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
			$result = $this->repository->uploadModule($request->file('module'));
		}
		else
		{
			$result = $this->repository->cloneModule($request->get('repo_link'));	
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
		$this->repository->changeEnabled($slug);
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
		$this->repository->deleteModule($slug);
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
		$moduleParts = $this->repository->getModuleParts($slug);
		return view('Installation::modules.moduleparts', compact('moduleParts'));
	}

}
