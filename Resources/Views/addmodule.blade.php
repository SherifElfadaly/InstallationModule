@extends('Installation::app')

@section('content')

<div class="container">
	<div class="col-sm-9">
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

		<h3>Add New Module</h3>
		<form class="form-horizontal" id="user_form_edit" method="post" enctype="multipart/form-data">
			<input type="hidden" name="_token" value="{{ csrf_token() }}">

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<input type="file" name="module" id="module">
				</div>
			</div>

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-5">
					<div class="form-group">
						<label class="control-label">Or type module link from github</label>
						<div class="input-group">
							<span class="input-group-addon">@</span>
							<input type="text" class="form-control" name="repo_link" id="repo_link">
						</div>
						<small>https://github.com/[YourGitUserName]/[YourRepoName].git</small>
					</div>
				</div>
			</div>

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" id="user_submit" class="btn btn-default">Submit</button>
				</div>
			</div>

		</form>
	</div>
</div>
@endsection
