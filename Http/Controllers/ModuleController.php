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
		$this->installation->scanModules();
		$modules = $this->installation->getAllModules();
		$modules = $this->installation->needUpdate($modules);

		return view('Installation::modules.modules', compact('modules'));
	}

	/**
	 * Update module from git repository
	 *
	 * @return Response
	 */
	public function getUpdate($slug)
	{
		$result  = $this->installation->cloneModule($this->installation->getModuleProperties($slug)['repo_link']);
		
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
