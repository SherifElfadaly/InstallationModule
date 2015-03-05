@extends('app')

@section('content')
<div class="container">
	<div class="col-sm-9">
	<h3>{{ $module->module_name }}'s Settings</h3>
	<a class="btn btn-default" href='{{ url("/Installation/modulesettings/create/$module->module_key") }}' role="button">Add Module Settings</a>
		<table class="table table-striped">
			<thead>
				<tr>
					<th>#</th>
					<th>Key</th>
					<th>Value</th>
					<th>Options</th>
				</tr>
			</thead>
			<tbody>
				@foreach($module->coreSettings as $moduelSetting)
				<tr>
					<th scope="row">{{ $moduelSetting->id }}</th>
					<td>{{ $moduelSetting->key }}</td>
					<td>{{ $moduelSetting->value }}</td>
					<td>
						<a 
						class="btn btn-default" 
						href='{{ url("/Installation/modulesettings/delete/$moduelSetting->id") }}' 
						role="button"
						>
						Delete
						</a>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>
@endsection