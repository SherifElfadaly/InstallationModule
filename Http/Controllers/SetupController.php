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
		if(file_exists(base_path('.env')) && \CMS::groups()->adminCount() > 0)
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

			if($admin = \CMS::users()->find('1'))
			{
				$setupData->name  = $admin->name;
				$setupData->email = $admin->email;
			}
		}
		return view('Installation::setup.home', compact('setupData'));
	}

	/**
	 * Save the setup settings.
	 *
	 * @return Response
	 */
	public function postIndex(SetupFormRequest $request)
	{
		$key     = md5(time() . uniqid());
		$content = "APP_ENV=local
APP_DEBUG=true
APP_KEY={$key}

DB_HOST={$request->get('host_name')}
DB_DATABASE={$request->get('db_name')}
DB_USERNAME={$request->get('db_user')}
DB_PASSWORD={$request->get('db_password')}

CACHE_DRIVER=file
SESSION_DRIVER=file";

		file_put_contents(base_path('.env'), $content);
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
	 * Save the setup settings.
	 *
	 * @return Response
	 */
	public function postSaveadmin(SetupAdminFormRequest $request)
	{	
		$admin = \CMS::users()->first('email', $request->get('email'));
		if ($admin !== false)
		{
			\CMS::users()->update($admin->id, $request->all());	
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
