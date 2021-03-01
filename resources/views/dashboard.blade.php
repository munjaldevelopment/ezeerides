<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>EZee Ride</title>

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
                     <input type='text' name="pick_up" class="form-control" placeholder="Pick Up Date" id="pick_up" required/>
                     <span class="input-group-addon">
                     <span class="glyphicon glyphicon-calendar"></span>
                     </span>
                  </div>
               </div>
            </div>
            <div class='col-md-6'>
               <div class="form-group">
                  <label class="control-label">Drop</label><br>
                     <input type='radio' name="drop_time" class="drop_time" value="4" required onchange="return display()" /> 4 Hours 
                     <input type='radio' name="drop_time" class="drop_time" value="7" required onchange="return display()"/> 7 Days
                     <input type='radio' name="drop_time" class="drop_time" value="15" required onchange="return display()"/> 15 Days
                     <input type='radio' name="drop_time" class="drop_time" value="30" required onchange="return display()"/> 30 Days
               </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
               <div class="form-group">
                  <label class="control-label">Vehicle Type</label><br />
                  <select name="vehicle" class="form-control" id="vehicle" onchange="return display()">
                    <option value="">-- Select Vehicle --</option>
                    @foreach($vehicles as $vehicle)
                    <option data_model="{{ $vehicle->vehicle_number }}" value="{{ $vehicle->charges }}" > {{ $vehicle->vehicle_number}} 
                    ({{ $vehicle->vehicle_model}}) </option>
                    @endforeach  
                  </select>
                  
               </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label class="control-label">Id Proof</label>
                  <input type="file" name="proof" id="proof">
               </div>
            </div>
         </div>

         <div class="row">
            <div class="col-md-6">
               <div class="form-group">
                  <label class="control-label">Station</label>
                  <select name="station" class="form-control">
                    <option value="">-- Select Station --</option>
                    @foreach($stations as $station)
                    <option value="{{ $station->station_name }}">{{ $station->station_name }}</option>
                    @endforeach
                  </select>
               </div>
            </div>
            <div class="col-md-6">
               <div class="form-group">
                  <label class="control-label">Dropping Time</label>
                  <input type="text" name="dropping" id="dropping" class="form-control" readonly>
               </div>
            </div>
         </div>
         <div class="row">
          <div class="col-md-6">
               <div class="form-group">
                  <label class="control-label">Total Amount</label>
                  <input type="text" name="total_amount" id="total_amount" class="form-control">
               </div>
            </div>
         </div>
         <input type="submit" class="btn btn-primary" value="Submit">
     </form>
      </div>
   </div>
</div>
<script type="text/javascript">
  $(function () {
    $('#datetimepicker1').datetimepicker();
 });
</script>
<script type="text/javascript">

</script>
<script type="text/javascript">
  ;(function($, window, document, undefined){
    $(".drop_time").on("change", function(){
        var date = new Date($("#pick_up").val());
        var dropTime = $(".drop_time:checked").val();
        if(dropTime == 4)
        {
          drop_time = parseInt(0, 30);
        }
        else
        {
          drop_time = parseInt(dropTime, 30);
        }
        
        if(!isNaN(date.getTime())){
            date.setDate(date.getDate() + drop_time);
            
            $("#dropping").val(date.toInputFormat());
        } else {
            alert("Invalid Date");  
        }
    });
    
    
    Date.prototype.toInputFormat = function() {
       var yyyy = this.getFullYear().toString();
       var mm = (this.getMonth()+1).toString(); // getMonth() is zero-based
       var dd  = this.getDate().toString();
       return yyyy + "-" + (mm[1]?mm:"0"+mm[0]) + "-" + (dd[1]?dd:"0"+dd[0]); // padding
    };
})(jQuery, this, document);

</script>
<script>
  function display(view){  
      
      var dropValue = parseFloat($("input[name='drop_time']:checked").val()) || 0;
      var vehicleValue = parseFloat($("#vehicle").val()) || 0;
      $('#total_amount').val(dropValue * vehicleValue * 21 * 30 /100);
      if(dropValue == '4')
      {
        var dropValue = parseFloat($("input[name='drop_time']:checked").val()) || 0;
        var vehicleValue = parseFloat($("#vehicle").val()) || 0;
        $('#total_amount').val(dropValue * vehicleValue);
      }
      else if(dropValue == '30')
      {
        var dropValue = parseFloat($("input[name='drop_time']:checked").val()) || 0;
        var vehicleValue = parseFloat($("#vehicle").val()) || 0;
        $('#total_amount').val(dropValue * vehicleValue * 21 * 60 /100);
      }
  }
</script>
</body>
</html>