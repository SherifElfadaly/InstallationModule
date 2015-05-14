<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Laravel</title>

	<link href="{{asset('assets/css/app.css')}}" rel="stylesheet">
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

	<!-- Fonts -->
	<link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>
	<nav class="navbar navbar-default">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" setupData-toggle="collapse" setupData-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle Navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#">CMS Setup</a>
			</div>
		</div>
	</nav>
	<div class="container">
		<div class="row form-group">
			<div class="col-xs-12">
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
				<ul class="nav nav-pills nav-justified thumbnail setup-panel">
					<li class="active">
						<a href="#step-1">
							<h4 class="list-group-item-heading">Step 1</h4>
							<p class="list-group-item-text">DataBase Configurations</p>
						</a>
					</li>
					<li class="@if(Session::get('step') == '2') active @endif">
						<a href="#step-2">
							<h4 class="list-group-item-heading">Step 2</h4>
							<p class="list-group-item-text">Admin Informations</p>
						</a>
					</li>
					<li class="@if(Session::get('step') == '3') active @endif">
						<a href="#step-3">
							<h4 class="list-group-item-heading">Step 3</h4>
							<p class="list-group-item-text">Site Informations</p>
						</a>
					</li>

				</ul>
			</div>
		</div>
		<div class="row setup-content" id="step-1">
			<div class="col-xs-12">
				<div class="col-md-12 well text-center">
					<h1> Enter DataBase Configurations</h1>
					<form class="form-horizontal col-sm-8 col-sm-offset-2" id="step-1_form" method="post">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">
						
						<div class="form-group">
							<div>
								<label class="control-label">DataBase Name</label>
								<input 
								required type ="text" 
								class         ="form-control" 
								value         ="@if(property_exists($setupData, 'DB_DATABASE')){{ $setupData->DB_DATABASE }}@endif" 
								name          ="db_name" 
								id            ="db_name" 
								placeholder   ="DataBase Name">
							</div>
						</div>

						<div class="form-group">
							<div>
								<label class="control-label">DataBase Host Name</label>
								<input 
								required type ="text" 
								class         ="form-control" 
								value         ="@if(property_exists($setupData, 'DB_HOST')){{ $setupData->DB_HOST }}@endif" 
								name          ="host_name" 
								id            ="host_name" 
								placeholder   ="DataBase Host Name">
							</div>
						</div>

						<div class="form-group">
							<div>
								<label class="control-label">DataBase User Name</label>
								<input 
								required type ="text" 
								class         ="form-control" 
								value         ="@if(property_exists($setupData, 'DB_USERNAME')){{ $setupData->DB_USERNAME }}@endif" 
								name          ="db_user" 
								id            ="db_user" 
								placeholder   ="DataBase User Name">
							</div>
						</div>

						<div class="form-group">
							<div>
								<label class="control-label">DataBase Password</label>
								<input 
								required type ="password" 
								class         ="form-control" 
								value         ="@if(property_exists($setupData, 'DB_PASSWORD')){{ $setupData->DB_PASSWORD }}@endif" 
								name          ="db_password" 
								id            ="db_password" 
								placeholder   ="DataBase Password">
							</div>
						</div>

						<div class="form-group">
							<div>
								<button type="submit" id="step-1_submit" class="btn btn-primary btn-lg">Next</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="row setup-content" id="step-2">
			<div class="col-xs-12">
				<div class="col-md-12 well text-center">
					<h1 class="text-center"> Enter Admin Informations</h1>
					<form class="form-horizontal col-sm-8 col-sm-offset-2" id="step-2_form" method="post" action="{{ url('admin/Installation/setup/saveadmin') }}">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">
						<input type="hidden" name="user_groups[]" value="1">

						<div class="form-group">
							<div>
								<label class="control-label">Admin Email</label>
								<input 
								required type ="text" 
								class         ="form-control" 
								value         ="@if(property_exists($setupData, 'email')){{ $setupData->email }}@endif" 
								name          ="email" 
								id            ="email" 
								placeholder   ="Admin Email">
							</div>
						</div>

						<div class="form-group">
							<div>
								<label class="control-label">Admin User Name</label>
								<input 
								required type ="text" 
								class         ="form-control" 
								value         ="@if(property_exists($setupData, 'name')){{ $setupData->name }}@endif" 
								name          ="name" 
								id            ="name" 
								placeholder   ="Admin User Name">
							</div>
						</div>

						<div class="form-group">
							<div>
								<label class="control-label">Admin Password</label>
								<input 
								required type ="password" 
								class         ="form-control" 
								name          ="password" 
								id            ="password" 
								placeholder   ="Admin Password">
							</div>
						</div>

						<div class="form-group">
							<div>
								<button type="submit" id="step-2_submit" class="btn btn-primary btn-lg">Next</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="row setup-content" id="step-3">
		<div class="col-xs-12">
			<div class="col-md-12 well text-center">
				<h1 class="text-center"> Finish</h1>
				<div class="col-sm-8 col-sm-offset-2">
					<a href="{{ url('admin') }}" class="btn btn-primary btn-lg">Goto admin panel</a>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Scripts -->
<script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/js/bootstrap.min.js"></script>
@include('Installation::setup.assets.setup-js')
</body>
</html>