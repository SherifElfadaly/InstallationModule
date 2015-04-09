<?php
namespace App\Modules\Installation\Providers;

use App;
use Config;
use Lang;
use View;
use Illuminate\Support\ServiceProvider;

class InstallationServiceProvider extends ServiceProvider
{
	/**
	 * Register the Installation module service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// This service provider is a convenient place to register your modules
		// services in the IoC container. If you wish, you may make additional
		// methods or service providers to keep the code more focused and granular.
		App::register('App\Modules\Installation\Providers\RouteServiceProvider');

		//Bind InstallationRepository Facade to the IoC Container
		App::bind('InstallationRepository', function()
		{
			return new App\Modules\Installation\Repositories\InstallationRepository;
		});

		$this->registerNamespaces();
	}

	/**
	 * Register the Installation module resource namespaces.
	 *
	 * @return void
	 */
	protected function registerNamespaces()
	{
		Lang::addNamespace('Installation', __DIR__.'/../Resources/Lang/');

		View::addNamespace('Installation', __DIR__.'/../Resources/Views/');
	}
}
