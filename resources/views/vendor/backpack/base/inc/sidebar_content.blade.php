<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="nav-icon la la-home"></i> {{ trans('backpack::base.dashboard') }}</a></li>

@php
	$is_admin = backpack_user()->hasRole('Admin');
    if($is_admin):
@endphp
<!-- Users, Roles, Permissions -->
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-users"></i> Master</a>
	<ul class="nav-dropdown-items">
	  <li class='nav-item'><a class='nav-link' href="{{ backpack_url('helmets') }}"><i class='nav-icon la la-hard-hat'></i> Helmets</a></li>
	</ul>
</li>
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-city"></i>Center Management</a>
	<ul class="nav-dropdown-items">
	  <li class='nav-item'><a class='nav-link' href="{{ backpack_url('city') }}"><i class='nav-icon la la-building'></i> Cities</a></li>
	  <li class="nav-item"><a class="nav-link" href="{{ backpack_url('station') }}"><i class="nav-icon la la-city"></i> <span>Station</span></a></li>

	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('stationpremiums') }}'><i class='nav-icon la la-rupee-sign'></i> Station Premiums</a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('stationholidays') }}'><i class='nav-icon la la-user'></i> Station Holiday</a></li>

	</ul>
</li>
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-exclamation-circle"></i>Dispute Management</a>
	<ul class="nav-dropdown-items">
	 <li class='nav-item'><a class='nav-link' href='{{ backpack_url('penalty') }}'><i class='nav-icon la la-rupee-sign'></i> Penalties</a></li>
	 <li class='nav-item'><a class='nav-link' href='{{ backpack_url('damages') }}'><i class='nav-icon la la-clipboard'></i> Damages</a></li>

	</ul>
</li>
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-motorcycle"></i>Fleet Management</a>
	<ul class="nav-dropdown-items">
	  <li class="nav-item"><a class="nav-link" href="{{ backpack_url('vehicle/create') }}"><i class="nav-icon la la-motorcycle"></i> <span>Create Fleet </span></a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('vehiclemodels') }}'><i class='nav-icon la la-motorcycle'></i> Models</a></li>
	  <li class="nav-item"><a class="nav-link" href="{{ backpack_url('vehicle') }}"><i class="nav-icon la la-motorcycle"></i> <span>Fleet </span></a></li>
	  
	</ul>
</li>
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-tools"></i>Service Management</a>
	<ul class="nav-dropdown-items">
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('servicetype') }}'><i class='nav-icon la la-tools'></i> Service Type</a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('vehicleservice/create') }}'><i class='nav-icon la la-tools'></i> Services Request </a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('vehicleservice') }}'><i class='nav-icon la la-tools'></i> ALL Services </a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('pendingservices') }}'><i class='nav-icon la la-tools'></i> Pending Services </a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('completedservices') }}'><i class='nav-icon la la-tools'></i> Completed Services </a></li>
	</ul>
</li>
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-biking"></i>Booking Management</a>
	<ul class="nav-dropdown-items">
	  <li class="nav-item"><a class="nav-link" href="{{ backpack_url('vehicle_register/create') }}"><i class="nav-icon la la-biking"></i> <span>Create Booking</span></a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('vehicle_register') }}'><i class='nav-icon la la-biking'></i> Today's Drop</a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('bookings') }}'><i class='nav-icon la la-biking'></i> Current Bookings</a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('upcoming_bookings') }}'><i class='nav-icon la la-biking'></i> Upcoming Bookings</a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('old_bookings') }}'><i class='nav-icon la la-biking'></i> OLD Bookings</a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('canceled_bookings') }}'><i class='nav-icon la la-biking'></i> Canceled Bookings</a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('overdue_bookings') }}'><i class='nav-icon la la-biking'></i> Overdue Bookings</a></li>
	  
	</ul>
</li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('coupon') }}'><i class='nav-icon la la-wallet'></i> Coupons</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('customers') }}'><i class='nav-icon la la-user'></i> Customers</a></li>

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('employee') }}'><i class='nav-icon la la-user'></i> Employee</a></li>

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('employeeattendance') }}'><i class='nav-icon la la-user'></i> Employee Attendances</a></li>

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('log') }}'><i class='nav-icon la la-terminal'></i> Logs</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('setting') }}'><i class='nav-icon la la-cog'></i> Settings</a></li>
<!-- Users, Roles, Permissions -->
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-users"></i> Authentication</a>
	<ul class="nav-dropdown-items">
	  <li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="nav-icon la la-user"></i> <span>Users</span></a></li>
	  <li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i class="nav-icon la la-id-badge"></i> <span>Roles</span></a></li>
	  <li class="nav-item"><a class="nav-link" href="{{ backpack_url('permission') }}"><i class="nav-icon la la-key"></i> <span>Permissions</span></a></li>
	</ul>
</li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('page') }}'><i class='nav-icon la la-file-o'></i> <span>Pages</span></a></li>
@php
	endif;
@endphp