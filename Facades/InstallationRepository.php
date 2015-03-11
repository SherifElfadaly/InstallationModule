<?php namespace App\Modules\Installation\Facades;

use Illuminate\Support\Facades\Facade;

class InstallationRepository extends Facade
{
	protected static function getFacadeAccessor() { return 'InstallationRepository'; }
}