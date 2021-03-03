<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>EZee Ride</title>

		<base href="{{ $base_url }}" />

        <meta name="csrf-token" content="{{ csrf_token() }}" />
        {{-- Encrypted CSRF token for Laravel, in order for Ajax requests to work --}}

		<!-- Fonts -->
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.23/css/jquery.dataTables.min.css">
		<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;600&display=swap" rel="stylesheet">

		<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
	    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
	    <script src="https://unpkg.com/gijgo@1.9.13/js/gijgo.min.js" type="text/javascript"></script>
	    <link href="https://unpkg.com/gijgo@1.9.13/css/gijgo.min.css" rel="stylesheet" type="text/css" />
	    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>
		
		<!-- Styles -->
		<style>.container {margin-top: 40px;}.btn-primary {width: 100%;}
		</style>
	</head>

	<body>
		<div class="container">
			<div class="logo-header">
				<a href="{{ asset('/') }}">
				<img src="{{ asset('/') }}image/ezee.jpg" alt="Ezee Rides" title="Ezee Rides" style="width: 100px;margin-bottom: 12px;">
				</a>
				<a href="{{url('/dashboard')}}" style="float: right;border:1px solid;padding: 10px 10px 10px 10px;text-decoration: none;">Home</a>
			</div>
			<div class="card">
		      <div class="card-header text-white bg-primary">
		      	<strong>Customer History</strong>
		      </div>
		      <div class="card-body">
					<table id="example" class="table">
						<thead>
							<tr>
								<th>ID</th>
								<th>Customer Name</th>
								<th>Phone</th>
								<th>Pick Up</th>
								<th>Pick Up Time</th>
								<th>Expected Drop</th>
								<th>Expected Drop Time</th>
								<th>Station</th>
								<th>Vehicle</th>
								<th>Total Amount</th>
								<th>Out Time</th>
								<th>Action</th>
							</tr>
						</thead>

						<tbody>
	        				@foreach ($histories as $history)
					           <tr><td>{{$history->id}}</td>
					           <td>{{$history->customer_name}}</td>
					           <td>{{$history->phone}}</td>
					           <td>{{$history->pick_up}}</td>
					           <td>{{$history->pick_up_time}}</td>
					           <td>{{$history->expected_drop}}</td>
					           <td>{{$history->expected_drop_time}}</td>
					           <td>{{$history->station}}</td>
					           <td>{{$history->vehicle}}</td>
					           <td>{{$history->total_amount}}</td>
					           <td>{{$history->punchout_time}}</td>
					       	   <td>
					       	   	@if($history->status == "Out")
					       	   	<a class="btn" href="{{ url('/return_vehicle') }}/{{ $history->id }}">Return</a>
					       	   	@else
					       	   	Returned
					       	   	@endif
					       	   </td>
					      	</tr>
					        @endforeach
					    </tbody>
        			</table>
				</div>
			</div>
		</div>

		<script type="text/javascript">
			$(document).ready(function() {
			    $('#example').DataTable();
			} );
		</script>

</body>
</html>