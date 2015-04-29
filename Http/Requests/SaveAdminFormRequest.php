<?php namespace App\Modules\Installation\Http\Requests;

use App\Http\Requests\Request;

class SaveAdminFormRequest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'db_name'     => 'required',
			'host_name'   => 'required',
			'db_user'     => 'required',
			'db_password' => 'required',
		];
	}

}
