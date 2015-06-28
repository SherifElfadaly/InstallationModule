@extends('core::app')
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
					<th>Module Type</th>
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
					<td>{{ $module['type'] }}</td>
					<td>
						@if($module['type'] === 'theme' && ! $module['enabled'])
							<a 
							class ="btn btn-default" 
							href  ='{{ url("admin/Installation/enabled/{$module['slug']}") }}' 
							role  ="button"
							>
							Enable
							</a>
						@elseif($module['type'] !== 'theme')
							<a 
							class ="btn btn-default" 
							href  ='{{ url("admin/Installation/enabled/{$module['slug']}") }}' 
							role  ="button"
							>
							@if($module['enabled'])
								Disable
							@else
								Enable
							@endif
							</a>
						@endif

						<a 
						class ="btn btn-default" 
						href  ='{{ url("admin/Installation/delete/{$module['slug']}") }}' 
						role  ="button"
						>
						Delete
						</a>

						@if($module['need_update'])
							<a 
							class ="btn btn-default" 
							href  ='{{ url("admin/Installation/update/{$module['slug']}") }}' 
							role  ="button"
							>
							Update
							</a>
						@endif

						@if($module['moduleSettings']->count())
							<a 
							class ="btn btn-default" 
							href  ='{{ url("admin/Installation/modulesettings/show/{$module['slug']}") }}' 
							role  ="button"
							>
							Settings
							</a>
						@endif
						
						@if($module['moduleParts']->count())
							<a 
							class ="btn btn-default" 
							href  ='{{ url("admin/Installation/moduleparts/{$module['slug']}") }}'
							role  ="button"
							>
							Module Parts
							</a>
						@endif
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>
@endsection