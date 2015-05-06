<?php namespace App\Modules\Installation\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Modules\Installation\Repositories\InstallationRepository;
use Illuminate\Http\Request;

class ModuleSettingsController extends BaseController {

	/**
	 * Create new ModuleSettings instance.
	 * @param InstallationRepository
	 */
	public function __construct(InstallationRepository $installation)
	{
		parent::__construct($installation, 'Modules');
		$this->middleware('AclAuthenticate');
	}

	/**
	 * Display the settings and handle the ajax request
	 * for save the file type settings.
	 * 
	 * @param  int  $module_key
	 * @return Response
	 */
	public function getShow(Request $request, $module_key)
	{
		if($request->ajax()) 
		{
			$data = [$request->get('settingKey') => serialize($request->get('ids'))];
			$this->repository->saveSetting($data, $module_key);

			return 'done';
		}

		$module = $this->repository->getModule($module_key);
		foreach ($module->moduleSettings as $settings) 
		{
			if ($settings->input_type == 'file') 
			{
				$module->mediaLibrary     = \GalleryRepository::getMediaLibrary('all', false, $settings->name . 'mediaLibrary');
				$module->mediaLibraryName = $settings->name . 'mediaLibrary';
			}
		}
		return view('Installation::modulesettings.modulesettings', compact('module'));
	}

	/**
	 * Store the module settings in storage.
	 *
	 * @param  Request  $request the request holding the form data
	 * @param  int  $module_key
	 * @return Response
	 */
	public function postShow(Request $request, $module_key)
	{
		$errors = array();
		foreach ($request->except('_token') as $key => $value) 
		{
			if ( ! is_array($value) && strlen(trim($value)) == 0) 
			{
				$errors[] = $key . " Required";
			}
		}
		if ( ! empty($errors)) 	return redirect()->back()->withErrors($errors);

		$this->repository->saveSetting($request->except('_token'), $module_key);

		return 	redirect()->back()->with('message', 'Your settings had been created');
	}
}
