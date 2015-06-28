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
					<th>Module Part ID</th>
					<th>Module Part Key</th>
					<th>Options</th>
				</tr>
			</thead>
			<tbody>
				@foreach($moduleParts as $modulePart)
					<tr>
						<td>{{ $modulePart->id }}</td>
						<td>{{ $modulePart->part_key }}</td>
						<td>
							<a 
							class ="btn btn-default" 
							href  ='{{ url("admin/Acl/permissions/show/{$modulePart->part_key}/{$modulePart->id}") }}'
							role  ="button"
							>
							Permissions
							</a>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>
@endsection