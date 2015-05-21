<?php namespace App\Modules\Installation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Installation\Http\Requests\SetupFormRequest;
use App\Modules\Installation\Http\Requests\SetupAdminFormRequest;

class SetupController extends Controller {

	/**
	 * Create new SetupController instance.
	 */
	public function __construct()
	{
		if (file_exists(base_path('.env')) && \CMS::groups()->adminCount() > 0)
		{
			$this->middleware('AclAuthenticate');
		}
	}

	/**
	 * Display the setup settings.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		$envPath    = base_path('.env');
		$setupData  = new \stdClass;
		if (file_exists($envPath)) 
		{
			$autodetect = ini_get('auto_detect_line_endings');
			ini_set('auto_detect_line_endings', '1');
			$lines      = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			ini_set('auto_detect_line_endings', $autodetect);
			
			foreach ($lines as $line) 
			{
            	// Disregard comments
				if (strpos(trim($line), '#') === 0) 
				{
					continue;
				}
            	// Only use non-empty lines that look like setters
				if (strpos($line, '=') !== false) 
				{
					list($name, $value) = array_map('trim', explode('=', $line, 2));
					$setupData->$name = $value;
				}
			}

			if (\Auth::check() && $admin = \CMS::users()->find(\Auth::user()->id))
			{
				$setupData->name  = $admin->name;
				$setupData->email = $admin->email;
			}
		}
		return view('Installation::setup.home', compact('setupData'));
	}

	/**
	 * Create new env file with the given configurations then
	 * check for the connection.
	 *
	 * @param  SetupFormRequest $request
	 * @return Response
	 */
	public function postIndex(SetupFormRequest $request)
	{
		$key     = ['key' => md5(time() . uniqid())];
		$content = view('Installation::setup.parts.env', array_merge($request->all(), $key))->render();

		/**
		 * save the env file with the new configurations.
		 */
		file_put_contents(base_path('.env'), $content);

		/**
		 * Setup the configuration for the db connection.
		 */
		\Config::set('database.connections.mysql.host', $request->get('host_name'));
		\Config::set('database.connections.mysql.database', $request->get('db_name'));
		\Config::set('database.connections.mysql.username', $request->get('db_user'));
		\Config::set('database.connections.mysql.password', $request->get('db_password'));

		if(\DB::connection())
		{
			return 	redirect()->back()->with('step', '2');
		}
	}

	/**
	 * If the user is logged in then update his data
	 * and if not then create new admin.
	 *
	 * @param  SetupAdminFormRequest $request
	 * @return Response
	 */
	public function postSaveadmin(SetupAdminFormRequest $request)
	{	
		if (\Auth::check() && $admin = \CMS::users()->find(\Auth::user()->id))
		{
			\CMS::users()->update(\Auth::user()->id, $request->all());	
		}
		else
		{
			$admin = \CMS::users()->create($request->all());
		}
		\CMS::groups()->addGroups($admin, '1');
		\Auth::loginUsingId($admin->id);
		
		return 	redirect()->back()->with('step', '3');
	}
}
