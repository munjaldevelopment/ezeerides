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
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/momentjs/2.14.1/moment.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css">
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
    <a target="_blank" href="{{url('/history')}}" style="float: right;border:1px solid;padding: 10px 10px 10px 10px;text-decoration: none;">History</a>
  </div>
   <div class="panel panel-primary">
      <div class="panel-heading">Customer Detail</div>
      <div class="panel-body">
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
                  <div class='input-group date' id='datetimepicker1'>
                     <input type='text' name="pick_up" class="form-control" placeholder="Pick Up Date" id="startdate" required/>
                     <span class="input-group-addon">
                     <span class="glyphicon glyphicon-calendar"></span>
                     </span>
                  </div>
               </div>
            </div>
            <div class='col-md-6'>
               <div class="form-group">
               		<label class="control-label">Expected Drop</label>
                  	<div class='input-group date' id='datetimepicker2'>
                     <input type='text' name="expected_drop" class="form-control" placeholder="Expected Drop Date" id="enddate" required/>
                     <span class="input-group-addon">
                     <span class="glyphicon glyphicon-calendar"></span>
                     </span>
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
                    <option value="">-- Select Vehicle --</option>
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
	$(function () {
		var today = "{{ $today }}";
		$("#datetimepicker1").datetimepicker({
			format : 'DD-MMM-YYYY HH:mm',
			minDate:new Date()
	    }).on('dp.change', function (event) {
	        var minDate = new Date(event.date.valueOf());
	        $('#datetimepicker2').data("DateTimePicker").minDate(minDate);
    	});
    
        $("#datetimepicker2").datetimepicker({
			format : 'DD-MMM-YYYY HH:mm',
	    }).on('dp.change', function (event) {
	        var minDate1 = new Date(event.date.valueOf());
	        $('#datetimepicker1').data("DateTimePicker").maxDate(minDate1);
    	});

    	$('#station-vehicle').on('change', function() {
    		var fromDate = new Date($("#startdate").val());
    		var toDate = new Date($("#enddate").val());
    		var vehicle_amount = $('#station-vehicle option:selected').attr('data-charge');

    		var hours = calculateHours(fromDate, toDate);
    		
    		if(hours > 4)
    		{
    			var amount1 = vehicle_amount * 4; 
    			var diff = hours - 4;
				var total = (vehicle_amount * 1.5);
    			var amount = amount1 + (diff * total) ;
    		}
    		else
    		{
	    		var amount = hours * (vehicle_amount * 1.5);
	    	}
	    	
	    	$('#total_amount').val(amount);
	    	$('.submit-btn').removeAttr('disabled');

    	});
 	});

 	$("#datetimepicker1").trigger('dp.change');

 	function calculateHours(dt2, dt1) 
	{
		var diff =(dt2.getTime() - dt1.getTime()) / 1000;
		diff /= (60 * 60);
		return Math.abs(Math.round(diff));
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