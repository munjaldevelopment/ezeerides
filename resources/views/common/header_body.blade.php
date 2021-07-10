<!DOCTYPE html>
<html lang="en">
<head>
	<title> {{ $title }}</title>
	<meta charset="utf-8">

	<base href="{{ asset('/') }}" />
	
	<meta name="description" content="{{ $meta_description }}" />
	<meta name="keywords" content="{{ $meta_keywords }}" />

	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="{{ asset('public/assets/') }}/css/style.css">
	<link rel="stylesheet" href="{{ asset('public/assets/') }}/css/bootstrap.min.css">
	<link rel="stylesheet" href="{{ asset('public/assets/') }}/css/swiper.min.css">
	<link rel="stylesheet" href="{{ asset('public/assets/') }}/css/esskay-swiper.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	
	<!--font family placed by ranveer -->
	<link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>

	<script src="{{ asset('public/assets/') }}/js/jquery.min.js"></script>
	<script src="{{ asset('public/assets/') }}/js/bootstrap.min.js"></script>
	<script src="{{ asset('public/assets/') }}/js/swiper.jquery.js"></script>
	<script src="{{ asset('public/assets/') }}/js/custom.js"></script>
	
	<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
	
	<script src="{{ asset('public/assets/') }}/js/highcharts.js"></script>
	<script src="//code.highcharts.com/modules/series-label.js"></script>
	<script src="https://code.highcharts.com/modules/exporting.js"></script>
	<script src="https://code.highcharts.com/modules/export-data.js"></script>

	<meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
	<!-- header start here -->

	<header>
		<nav class="navbar navbar-expand-md">
			<a class="navbar-brand" href="{{ asset('/') }}"><img src="../image/ezee.jpg" style="width:40%;" alt="Ezeerides"></a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
				<span class=""><i class="fa fa-bars"></i></span>
			</button>
			<div class="collapse navbar-collapse" id="collapsibleNavbar">
				<ul class="navbar-nav ml-auto nav-custome">
					@if($customer_name)
					{{--<li class="nav-item">
						<a class="nav-link" href="#">Dashboard</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#">transaction</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#">client</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#">resources</a>
					</li>--}} 
					<li class="operation-li"><h5 class="custome-syle">{{ $lenderData->name }}<span class="inner-inner">({{ $lenderData->code }})</span></h5>
						<div class="onbrd-btn">
							<button id="lender_banking" class="lender_blankingbtn-1">{{ $lenderData->is_onboard }} </button>
						</div> 
					</li>
					<li class="nav-item dropdown pr-0">
						<a class="nav-link dropdown-toggle" href="#" id="navbardrop" data-toggle="dropdown">
							<span class="mr-1 rounded-circle heading-jhone">{{ $lenderCode }}</span>
						</a>
						
						<div class="dropdown-menu dropdown-menu-right mt-2">
							{{--<a class="dropdown-item" href="#">My Profile</a>
							<a class="dropdown-item" href="#">Dashboard</a>--}}
							<a class="dropdown-item" href="{{ asset('/edit-password') }}">Change Password</a>
							<a class="dropdown-item" href="{{ asset('/logout') }}">Logout</a>
							</div>
					</li>
					<!-- <li><button id="lender_banking" class="lender_blankingbtn-1">{{ $lenderData->is_onboard }} </button> </li>  -->
					@endif
				</ul>

			</div> 

		</nav>
	</header>