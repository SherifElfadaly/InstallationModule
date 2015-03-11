@extends('app')

@section('content')
<div class="container">
	<div class="col-sm-9">

		@if (Session::has('message'))
		<div class="alert alert-warning">
			<ul>
				<li>{{ Session::get('message') }}</li>
			</ul>
		</div>
		@endif

		<table class="table table-striped">
			<thead>
				<tr>
					<th>Module Key</th>
					<th>Module Name</th>
					<th>Module Description</th>
					<th>Module Version</th>
					<th>Options</th>
				</tr>
			</thead>
			<tbody>
				@foreach($modules as $module)
				<tr>
					<td>{{ $module['slug'] }}</td>
					<td>{{ $module['name'] }}</td>
					<td>{{ $module['description'] }}</td>
					<td>{{ $module['version'] }}</td>
					<td>
						<a 
						class="btn btn-default" 
						href='{{ url("/Installation/enabled/{$module['slug']}") }}' 
						role="button"
						>
						@if($module['enabled'])
							Disable
						@else
							Enable
						@endif
						</a>

						<a 
						class="btn btn-default" 
						href='{{ url("/Installation/delete/{$module['slug']}") }}' 
						role="button"
						>
						Delete
						</a>

						@if($module['need_update'])
						<a 
						class="btn btn-default" 
						href='{{ url("/Installation/update/{$module['slug']}") }}' 
						role="button"
						>
						Update
						</a>
						@endif

						<a 
						class="btn btn-default" 
						href='{{ url("/Installation/modulesettings/show/{$module['slug']}") }}' 
						role="button"
						>
						Settings
						</a>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>
@endsection