<?php namespace App\Modules\Installation\Repositories;

use App\AbstractRepositories\AbstractRepository;

class CoreModulePartRepository extends AbstractRepository
{	
	/**
	 * Return the model full namespace.
	 * 
	 * @return string
	 */
	protected function getModel()
	{
		return 'App\Modules\Installation\CoreModulePart';
	}

	/**
	 * Return the module relations.
	 * 
	 * @return array
	 */
	protected function getRelations()
	{
		return [];
	}

	/**
	 * Get module part belongs to module from storage.
	 * 
	 * @return object.
	 */
	public function getModuleParts($slug)
	{
		return $this->findBy('module_key', $slug);
	}

	/**
	 * Get module part from storage.
	 * 
	 * @return object.
	 */
	public function getModulePart($slug)
	{
		return $this->first('part_key', $slug);
	}

	/**
	 * Save the newly installed module parts to storage.
	 *
	 * @param  string $slug
	 * @param  array  $data
	 * @return void.
	 */
	public function saveModuleParts($slug, $data)
	{
		$moduleParts = array();
		foreach ($data as $key => $value) 
		{
			$moduleParts[] = ['part_key' => $value, 'module_key' => $slug];
		}
		$this->insert($moduleParts);
	}
}
