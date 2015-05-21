<?php namespace App\Modules\Installation\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class ModuleSettingsController extends BaseController {

	/**
	 * Specify that this controller should be 
	 * accessed by the admin users only.
	 * @var adminOnly
	 */
	protected $adminOnly = true;
	
	/**
	 * Create new ModuleSettings instance.
	 */
	public function __construct()
	{
		parent::__construct('Modules');
	}

	/**
	 * Display the settings.
	 * 
	 * @param  string  $module_key
	 * @return Response
	 */
	public function getShow($module_key)
	{
		$module = \CMS::coreModules()->getModule($module_key);
		foreach ($module->moduleSettings as $settings) 
		{
			if ($settings->input_type == 'file') 
			{
				$settings->mediaLibrary     = \CMS::galleries()->getMediaLibrary('all', false, $settings->name . 'mediaLibrary');
				$settings->mediaLibraryName = $settings->name . 'mediaLibrary';
			}
		}
		return view('Installation::modulesettings.modulesettings', compact('module'));
	}

	/**
	 * Handle the request for save the settings of type file.
	 *
	 * @param  Request $request
	 * @param  string  $module_key
	 * @return Response
	 */
	public function getAddfiles(Request $request, $module_key)
	{
		$data = [$request->get('settingKey') => serialize($request->get('ids'))];
		\CMS::coreModuleSettings()->saveSetting($data, $module_key);

		return 	redirect()->back();
	}

	/**
	 * Store the module settings in storage.
	 *
	 * @param  Request  $request
	 * @param  string   $module_key
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

		\CMS::coreModuleSettings()->saveSetting($request->except('_token'), $module_key);

		return 	redirect()->back()->with('message', 'Your settings had been saved');
	}
}
