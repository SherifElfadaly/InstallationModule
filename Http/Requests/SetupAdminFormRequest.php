<?php namespace App\Modules\Installation\Http\Requests;

use App\Http\Requests\Request;

class SetupAdminFormRequest extends Request {

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
			'name'     => 'required',
			'email'    => 'required|email|unique:users,id,'.$this->get('id'),
			'password' => 'required|min:6',
		];
	}

}
