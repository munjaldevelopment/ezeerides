<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
	<title> {{ $title }}</title>
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">

	<base href="{{ asset('/') }}" />
	
	<meta name="description" content="{{ $meta_description }}" />
	<meta name="keywords" content="{{ $meta_keywords }}" />

	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<link rel="stylesheet" href="{{ asset('public/assets/') }}/css/bootstrap.css">
	<link rel="stylesheet" href="{{ asset('public/assets/') }}/css/fontawesome-all.min.css">
	<link rel="stylesheet" href="{{ asset('public/assets/') }}/css/flaticon.css">
	
	<!--font family placed by ranveer -->
	<link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
	
	<!-- Google Web Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('public/assets/') }}/css/style_login.css">
	
	<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
	
	<meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
	<!-- header start here -->