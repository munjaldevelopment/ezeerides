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
        <style>.container {margin-top: 40px;}.btn-primary {width: 100%;}
        .gj-datepicker-md { width: 100%; }
          .gj-datepicker-md [role=right-icon] {
            top: 20%;
          }
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
  <div class="card">
      <div class="card-header text-white bg-primary">
        <strong>Customer Detail</strong>
      </div>
      <div class="card-body">
        <form action="{{url('save_booking_verify')}}" method="post" id="register_Form">
        {!! csrf_field() !!}
        <input type="hidden" name="booking_id" value="{{ $registerRecord->id }}">
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
                  <div class='input-group'>
                     <input type='text' name="pick_up" class="form-control" placeholder="Pick Up Date" id="startdate" required readonly="" value="{{ date('d-m-Y', strtotime($registerRecord->pick_up)) }}" />
                  </div>
                  <div class='input-group mt-3'>
                     <input type='text' name="pick_up_time" class="form-control" placeholder="Expected Drop Time" id="pick_up_time" required readonly="" value="{{ date('H:i A', strtotime($registerRecord->pick_up_time)) }}" />
                  </div>
               </div>
            </div>
            <div class='col-md-6'>
               <div class="form-group">
               		<label class="control-label">Expected Drop</label>
                  	<div class='input-group'>
                     <input type='text' name="expected_drop" class="form-control" placeholder="Expected Drop Date" required readonly="" value="{{ date('d-m-Y', strtotime($registerRecord->expected_drop)) }}" />
                    </div>
                  <div class='input-group mt-3'>
                     <input type='text' name="expected_drop_time" class="form-control" placeholder="Expected Drop Time" id="expected_drop_time" required readonly="" value="{{ date('H:i A', strtotime($registerRecord->expected_drop_time)) }}" />
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
               		<label class="control-label">User OTP</label>
                  <input type='text' name="user_otp" class="form-control" placeholder="User OTP" id="user_otp" required value="" />
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
		
 	});
</script>
</body>
</html>