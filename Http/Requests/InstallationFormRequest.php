<?php namespace App\Modules\Installation\Http\Requests;

use App\Http\Requests\Request;

class InstallationFormRequest extends Request {

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
			'module'    => 'required_without:repo_link|mimes:zip',
			'repo_link' => 'required_without:module|url|regex:/^(https?:\\/\\/)?github\\.com\\/([\\da-zA-Z\\.-]+)\\/([\\da-zA-Z\\.-]+)\\.git$/'
		];
	}

}
