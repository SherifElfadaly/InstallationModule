<?php namespace App\Modules\Installation\Traits;

use App\Modules\Installation\CoreModulePart;
use Module;

trait CoreModulePartTrait{

	/**
	 * Get module part belongs to module
	 * data from storage.
	 * @return Module data.
	 */
	public function getModuleParts($slug)
	{
		return CoreModulePart::where('module_key', '=', $slug)->get();
	}

	/**
	 * Get module part from storage.
	 * @return Module data.
	 */
	public function getModulePart($slug)
	{
		return CoreModulePart::where('part_key', '=', $slug)->first();
	}

	/**
	 * Save the newly installed module parts to
	 * storage.
	 * @param  array $data Module parts data
	 * @return void.
	 */
	public function saveModuleParts($slug, $data)
	{
		$moduleParts = array();
		foreach ($data as $key => $value) 
		{
			$moduleParts[] = ['part_key' => $value, 'module_key' => $slug];
		}
		CoreModulePart::insert($moduleParts);
	}
}