@extends('app')
@section('content')

<div class="container">
	<div class="col-sm-7">
		@if (count($errors) > 0)
			<div class="alert alert-danger">
				<strong>Whoops!</strong> There were some problems with your input.<br><br>
				<ul>
					@foreach ($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif

		@if (Session::has('message'))
			<div class="alert alert-warning">
				<ul>
					<li>{{ Session::get('message') }}</li>
				</ul>
			</div>
		@endif

		<h3>{{ $module->module_name }} Settings</h3>
		<form class="form-horizontal" id="user_form_edit" method="post">
			<input type="hidden" name="_token" value="{{ csrf_token() }}">

			@foreach($module->moduleSettings as $setting)

				<div class="form-group">
					<label for="inputEmail3" class="col-sm-2 control-label">{{ $setting->key }}</label>
					<div class="col-sm-7">
						@if($setting->input_type == 'link')
							<a 
							href  ="{{ $setting->href }}" 
							class ="btn btn-default btn-block" 
							id    ="{{ $setting->key }}" 
							>
							Send
							</a>

						@elseif($setting->input_type == 'file')
							
							@foreach($setting->value as $file)
									<img src="{{ $file->path }}" width="50" height="50" alt="{{ $file->caption }}">
							@endforeach

						@elseif($setting->input_type == 'multiselect')

							<select multiple name="{{ $setting->key }}[]" class="form-control">
								@foreach($setting->select_values as $select_value)
									<option 
									value ="{{ $select_value }}"
									@if(in_array($select_value, $setting->value))
										selected
									@endif
									>
									{{ $select_value }}
									</option>
								@endforeach
							</select>  

						@elseif($setting->input_type == 'select')

							<select name="{{ $setting->key }}[]" class="form-control">
								@foreach($setting->select_values as $select_value)
									<option 
									value ="{{ $select_value }}"
									@if(in_array($select_value, $setting->value))
										selected
									@endif
									>
									{{ $select_value }}
									</option>
								@endforeach
							</select>

						@else
							<input 
							type        ="{{ $setting->input_type }}" 
							class       ="form-control" 
							id          ="{{ $setting->key }}" 
							name        ="{{ $setting->key }}" 
							placeholder ="{{ $setting->key }}" 
							value       ="{{ $setting->value }}"
							>
						@endif
					</div>
				</div>
			@endforeach

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<button type="submit" id="user_submit" class="btn btn-default">Submit</button>
				</div>
			</div>

		</form>
	</div>
	<div class="col-sm-3">
		@foreach($module->moduleSettings as $setting)
			@if($setting->input_type == 'file')

				<div class="form-group row">
					<label>{{ $setting->key }}</label><br>
					{!! $setting->mediaLibrary !!}
					@include('Installation::modulesettings.assets.addsettingsfile')
				</div>

			@endif
		@endforeach
	</div>
</div>
@endsection
