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
        <form action="{{url('save_return')}}" method="post" id="register_Form">
        {!! csrf_field() !!}
        <input type="hidden" name="return_id" value="{{ $registerRecord->id }}">
        <div class="row">
            <div class="col-md-6">
               <div class="form-group">
                  <label class="control-label">Customer Name</label>
                  <input type="text" class="form-control" name="customer_name" id="customer_name" placeholder="Customer Name" required readonly="" value="{{ $registerRecord->customer_name}}">
               </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label class="control-label">Mobile Number</label>
                  <input type="tel" class="form-control" name="phone" id="phone" placeholder="Contact Number" maxlength="10" readonly="" value="{{ $registerRecord->phone}}">
               </div>
            </div>
         </div>
         <div class="row">
            <div class='col-md-6'>
               <div class="form-group">
                  <label class="control-label">Pick Up</label>
                  <div class='input-group date' id='datetimepicker1'>
                     <input type='text' name="pick_up" class="form-control" placeholder="Pick Up Date" id="startdate" required readonly="" value="{{ date('d-m-Y H:i', strtotime($registerRecord->pick_up)) }}" />
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
                     <input type='text' name="expected_drop" class="form-control" placeholder="Expected Drop Date" id="enddate" required readonly="" value="{{ date('d-m-Y H:i', strtotime($registerRecord->expected_drop)) }}" />
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
                   <input type="text" class="form-control" name="station" id="station" placeholder="Station" required readonly="" value="{{ $registerRecord->station}}">
               </div>
            </div>

            <div class="col-md-6">
               <div class="form-group">
                  <label class="control-label">Vehicle</label>
                  <input type="text" class="form-control" name="vehicle" id="vehicle" placeholder="Vehicle" required readonly="" value="{{ $registerRecord->vehicle}}">
               </div>
            </div>
          </div>

          <div class="row">
          	<div class="col-md-6">
               <div class="form-group">
                  <label class="control-label">Total Amount</label>
                  <input type="text" class="form-control" name="total_amount" id="total_amount" placeholder="Total Amount" required readonly="" value="{{ $registerRecord->total_amount}}">
               </div>
            </div>

            <div class='col-md-6'>
               <div class="form-group">
               		<label class="control-label">Punchout Time</label>
                  	<div class='input-group date' id='datetimepicker3'>
                     <input type='text' name="punchout_time" class="form-control" placeholder="Punchout Drop Date" id="punchout_time" required readonly="" value="{{ date('d-m-Y H:i', strtotime($registerRecord->punchout_time)) }}" />
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
                  <label class="control-label">Return Time</label>
                  	<div class='input-group date' id='datetimepicker4'>
                     <input type='text' name="return_time" class="form-control" placeholder="Return Time" id="return_time" required value="" />
                     <span class="input-group-addon">
                     <span class="glyphicon glyphicon-calendar"></span>
                     </span>
                  	</div>

               </div>
            </div>

            <div class='col-md-6'>
               <div class="form-group">
               		<label class="control-label">Additional Hours</label>
                  <input type="number" class="form-control" name="additional_hours" id="additional_hours" placeholder="Additional Hours" required value="">
               </div>
            </div>
         </div>

         <div class="row">
          	<div class='col-md-6'>
               <div class="form-group">
               		<label class="control-label">Additional Amount</label>
                  <input type="text" class="form-control" name="additional_amount" id="additional_amount" placeholder="Additional Amount" required value="">
               </div>
            </div>
         </div>
         <input type="submit" class="btn btn-primary submit-btn" value="Submit">
     </form>
      </div>
   </div>
</div>
<script type="text/javascript">
	$(function () {
		var today = "{{ $today }}";
		$("#datetimepicker1").datetimepicker({
			format : 'DD-MMM-YYYY HH:mm',
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

    	$("#datetimepicker3").datetimepicker({
    		format : 'DD-MMM-YYYY HH:mm'
    	});

    	$("#datetimepicker4").datetimepicker({
    		format : 'DD-MMM-YYYY HH:mm',
    		minDate:new Date()
    	});
 	});
</script>
</body>
</html>