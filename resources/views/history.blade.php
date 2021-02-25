<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>EZee Ride</title>

		<!-- Fonts -->
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.23/css/jquery.dataTables.min.css">
		<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;600&display=swap" rel="stylesheet">

		<script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
		<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
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
				<a target="_blank" href="{{url('/dashboard')}}" style="float: right;border:1px solid;padding: 10px 10px 10px 10px;text-decoration: none;">Home</a>
			</div>
			<div class="panel panel-primary">
				<div class="panel-heading">Customer Detail</div>
				<div class="panel-body">
					<table id="example" class="table">
						<thead>
							<tr><th>ID</th>
							<th>Customer Name</th>
							<th>Phone</th>
							<th>Pick Up</th>
							<th>Drop</th>
							<th>Vehicle</th>
							<th>Status</th>
							<th>Dropping</th>
							<th>Total Amount</th>
							<th>Station</th></tr>
						</thead>

						<tbody>
	        				@foreach ($histories as $history)
					           <tr><td>{{$history->id}}</td>
					           <td>{{$history->customer_name}}</td>
					           <td>{{$history->phone}}</td>
					           <td>{{$history->pick_up}}</td>
					           <td>{{$history->drop_time}}</td>
					           <td>{{$history->vehicle}}</td>
					           <td>{{$history->status}}</td>
					           <td>{{$history->dropping}}</td>
					           <td>{{$history->total_amount}}</td>
					           <td>{{$history->station}}</td></tr>
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