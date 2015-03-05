<?php namespace App\Modules\Installation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Installation\Repositories\InstallationRepository;
use Illuminate\Http\Request;

class ModuleSettingsController extends Controller {

	/**
	 * The InstallationRepository implementation.
	 *
	 * @var InstallationRepository
	 */
	protected $installation;

	/**
	 * Create new ModuleSettingsController instance.
	 * @param InstallationRepository
	 */
	public function __construct(InstallationRepository $installation)
	{
		$this->installation = $installation;
	}

	/**
	 * Display the settings.
	 *
	 * @return Response
	 */
	public function getShow($slug)
	{
		$module = $this->installation->getModule($slug);
		return view('Installation::modulesettings.modulesettings', compact('module'));
	}


	/**
	 * Show the form for creating a new module.
	 *
	 * @return Response
	 */
	public function getCreate()
	{	
		return view('Installation::modulesettings.addmodulesettings');
	}

	/**
	 * Store a newly created module in storage.
	 *
	 * @return Response
	 */
	public function postCreate(Request $request, $id)
	{
		$errors = array();
		foreach ($request->input('key') as $key) 
		{
			if (strlen($key) == 0) 
			{
				$errors[] = "Key Required";
				break;
			}
		}

		foreach ($request->input('value') as $value) 
		{
			if (strlen($value) == 0) 
			{
				$errors[] = "Value Required";
				break;
			}
		}
		if ( ! empty($errors)) 	return redirect()->back()->withErrors($errors);

		$data    = $this->installation->prepareSettingData($request->all());
		$profile = $this->installation->createSetting($data, $id);

		return 	redirect()->back()->with('message', 'Your profile had been created');
	}

	/**
	 * Remove the specified module from storage.
	 *
	 * @param  int  $slug
	 * @return Response
	 */
	public function getDelete($id)
	{
		$this->installation->deleteSetting($id);
		return 	redirect()->back();
	}

}
