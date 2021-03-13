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
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;600&display=swap" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
	    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
	    <script src="https://unpkg.com/gijgo@1.9.13/js/gijgo.min.js" type="text/javascript"></script>
	    <link href="https://unpkg.com/gijgo@1.9.13/css/gijgo.min.css" rel="stylesheet" type="text/css" />
        <!-- Styles -->
        <style>
        	.container {margin-top: 40px;}.btn-primary {width: 100%;}
        	.gj-datepicker-md { width: 100%; }
        	.gj-datepicker-md [role=right-icon] {
        		top: 20%;
        	}
        </style>
    </head>
    <body>
<div class="container">
  <div class="logo-header">
    <a href="{{ asset('/dashboard') }}">
      <img src="{{ asset('/') }}image/ezee.jpg" alt="Ezee Rides" title="Ezee Rides" style="width: 100px;margin-bottom: 12px;">
    </a>
    <a href="{{url('/history')}}" style="float: right;border:1px solid;padding: 10px 10px 10px 10px;text-decoration: none;">History</a>
  </div>
   <div class="card">
      <div class="card-header text-white bg-primary">
      	<strong>Customer Detail</strong>
      </div>
      <div class="card-body">
        <form action="{{url('register_result')}}" method="post" id="register_Form">
        {!! csrf_field() !!}
        <div class="row">
            <div class="col-md-6">
               <div class="form-group">
                  <label class="control-label">Customer Name</label>
                  <input type="text" class="form-control" name="customer_name" id="Customer Name" placeholder="Customer Name" required>
               </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label class="control-label">Mobile Number</label>
                  <input type="tel" class="form-control" name="phone" id="phone" placeholder="Contact Number" maxlength="10">
               </div>
            </div>
         </div>
         <div class="row">
            <div class='col-md-6'>
               <div class="form-group">
                  <label class="control-label">Pick Up</label>
                  <div class='input-group'>
                     <input type='text' name="pick_up" class="form-control" placeholder="Pick Up Date" id="startdate" value="{{ $current_date }}" required/>
                     <span class="input-group-addon">
                     <span class="glyphicon glyphicon-calendar"></span>
                     </span>
                  </div>
                  <div class='input-group mt-3'>
                     <select name="pick_up_time" id="pick_up_time" class="form-control">
                     	@foreach($timeRange as $time)
                     	<option value="{{ $time }}" {{ $selected[$time] }}>{{ $time }}</option>
                     	@endforeach
                     </select>
                  </div>
               </div>
            </div>
            <div class='col-md-6'>
               <div class="form-group">
               		<label class="control-label">Expected Drop</label>
                  	<div class='input-group' >
                     <input type='text' name="expected_drop" class="form-control" placeholder="Expected Drop Date" id="enddate" required value="{{ $next_date }}" />
                     <span class="input-group-addon">
                     <span class="glyphicon glyphicon-calendar"></span>
                     </span>
                    </div>
                    <div class='input-group mt-3'>
                     <select name="expected_drop_time" id="expected_drop_time" class="form-control">
                     	@foreach($timeRange as $time)
                     	<option value="{{ $time }}">{{ $time }}</option>
                     	@endforeach
                     </select>
                  	</div>
               </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
               <div class="form-group">
                   <label class="control-label">Station</label>
                   <select name="station" required class="form-control" id="station-station" onchange="showVehicle(this.value);">
                  	<option value="">Select</option>
                    @foreach($stations as $station)
                    <option value="{{ $station->station_name }}">{{ $station->station_name }}</option>
                    @endforeach
                  </select>
               </div>
            </div>

            <div class="col-md-6">
               <div class="form-group">
                  <label class="control-label">Vehicle</label><br />
                  <select name="vehicle" required class="form-control" id="station-vehicle">
                    <option value="" data-charge="0">-- Select Vehicle --</option>
                  </select>
               </div>
            </div>
          </div>

          <div class="row">
          	<div class="col-md-6">
               <div class="form-group">
                  <label class="control-label">Total Amount</label>
                  <input type="text" required="" readonly="" name="total_amount" id="total_amount" class="form-control">
               </div>
            </div>
         </div>
         <input type="submit" disabled="" class="btn btn-primary submit-btn" value="Submit">
     </form>
      </div>
   </div>
</div>
<script type="text/javascript">
	function calculateAmount()
	{
		var fromDate = new Date($("#startdate").val()+" "+$('#pick_up_time').val());
		var toDate = new Date($("#enddate").val()+" "+$('#expected_drop_time').val());
		var vehicle_amount = $('#station-vehicle option:selected').attr('data-charge');

		var hours = calculateHours(fromDate, toDate);
    
		
		if(hours > 4)
		{
			if(hours < 24)
			{
				var amount1 = (vehicle_amount * 1.5) * 4; 
    			var diff = hours - 4;
				var total = diff * vehicle_amount;
				var amount = amount1 + total;
			}
			else if(hours == 24)
			{
				var amount1 = (vehicle_amount * 1.5) * 4; 
    			var diff = 17;
				var total = diff * vehicle_amount;
				var amount = amount1 + total;
			}
			else if(hours > 24 && hours < 48)
			{
				var amount1 = (vehicle_amount * 1.5) * 4; 
    		var diff = 17;
				var total = diff * vehicle_amount;

        var total2 = (hours - 24) * vehicle_amount;
        console.log(hours);
        console.log(amount1);
        console.log(total);
        console.log(total2);
				var amount = amount1 + total + total2;
			}
			else if(hours >= 48 && hours < 72)
			{
				var amount1 = (vehicle_amount * 1.5) * 4; 
    			var diff = 17;
				var total = diff * vehicle_amount;

				var total2 = (hours - 24) * 2 * vehicle_amount;
				var amount = amount1 + total + total2;
			}
			else if(hours >= 72 && hours < 96)
			{
				var amount1 = (vehicle_amount * 1.5) * 4; 
    			var diff = 17;
				var total = diff * vehicle_amount;

				var total2 = (hours - 24) * 3 * vehicle_amount;
				var amount = amount1 + total + total2;
			}
			else if(hours >= 72 && hours < 96)
			{
				var amount1 = (vehicle_amount * 1.5) * 4; 
    			var diff = 17;
				var total = diff * vehicle_amount;

				var total2 = (hours - 24) * 3 * vehicle_amount;
				var amount = amount1 + total + total2;
			}
			else if(hours >= 96 && hours < 120)
			{
				var amount1 = (vehicle_amount * 1.5) * 4; 
    			var diff = 17;
				var total = diff * vehicle_amount;

				var total2 = (hours - 24) * 4 * vehicle_amount;
				var amount = amount1 + total + total2;
			}
			else if(hours >= 120 && hours < 144)
			{
				var amount1 = (vehicle_amount * 1.5) * 4; 
    			var diff = 17;
				var total = diff * vehicle_amount;

				var total2 = (hours - 24) * 5 * vehicle_amount;
				var amount = amount1 + total + total2;
			}
			else if(hours >= 144 && hours < 168)
			{
				var amount1 = (vehicle_amount * 1.5) * 4; 
    			var diff = 17;
				var total = diff * vehicle_amount;

				var total2 = (hours - 24) * 6 * vehicle_amount;
				var amount = amount1 + total + total2;
			}
			else if(hours == 144)
			{
				var amount1 = (vehicle_amount * 1.5) * 4; 
    			var diff = 17;
				var total = diff * vehicle_amount;

				var total2 = (hours - 24) * 7 * vehicle_amount;
				var amount = amount1 + total + total2;

				amount = amount - (amount * 30) / 100;
			}
		}
		else
		{
    		var amount = hours * (vehicle_amount * 1.5);
    }
    	
    	if(vehicle_amount > 0)
    	{
	    	$('#total_amount').val(amount);
	    	$('.submit-btn').removeAttr('disabled');
	    }
	}

	$(function () {
		var today = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());
        $('#startdate').datepicker({
            uiLibrary: 'bootstrap4',
            iconsLibrary: 'fontawesome',
            minDate: today,
            maxDate: function () {
                return $('#enddate').val();
            }
        });
        $('#enddate').datepicker({
            uiLibrary: 'bootstrap4',
            iconsLibrary: 'fontawesome',
            minDate: function () {
                return $('#startdate').val();
            }
        });

        
        $('#pick_up_time').on('change', function() {
    		calculateAmount();
    	});

    	$('#expected_drop_time').on('change', function() {
    		calculateAmount();
    	});

    	$('#station-vehicle').on('change', function() {
    		calculateAmount();
    	});

    	$('#startdate').on('change', function() {
    		calculateAmount();
    	});

    	$('#enddate').on('change', function() {
    		calculateAmount();
    	});
 	});

 	$("#datetimepicker1").trigger('dp.change');

 	function calculateHours(dt2, dt1) 
	{
		var diff =(dt2.getTime() - dt1.getTime()) / 1000;
		diff /= (60 * 60);
		return Math.abs(diff);
	}

 	function showVehicle(station_id)
 	{
 		var base_url = $('base').attr('href');

		var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

 		if(station_id != "")
 		{
 			$.ajax({
 				url: base_url+'showVehicle',
				type: 'post',
				data: {_token: CSRF_TOKEN, station_id: station_id},
				
				success: function(output) {
					$('#station-vehicle').html(output);
				}
			});
		}
 	}
</script>
</body>
</html>