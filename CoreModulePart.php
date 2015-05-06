<?php namespace App\Modules\Installation;

use Illuminate\Database\Eloquent\Model;

class CoreModulePart extends Model {

	protected $table    = 'core_module_parts';
	protected $fillable = ['part_key', 'module_key'];
}
