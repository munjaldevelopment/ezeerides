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
	  <li class="nav-item"><a class="nav-link" href="{{ backpack_url('vehicle') }}"><i class="nav-icon la la-id-badge"></i> <span>Vehicle</span></a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('vehicleservice') }}'><i class='nav-icon la la-motorcycle'></i> Vehicle Services</a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('servicetype') }}'><i class='nav-icon la la-motorcycle'></i> Service Type</a></li>
	  <li class="nav-item"><a class="nav-link" href="{{ backpack_url('station') }}"><i class="nav-icon la la-key"></i> <span>Station</span></a></li>

	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('helmets') }}'><i class='nav-icon la la-hard-hat'></i> Helmets</a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('penalty') }}'><i class='nav-icon la la-rupee-sign'></i> Penalties</a></li>
	</ul>
</li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('vehicle_register') }}'><i class='nav-icon la la-file-o'></i> Vehicle Register</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('customers') }}'><i class='nav-icon la la-user'></i> Customers</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('coupon') }}'><i class='nav-icon la la-wallet'></i> Coupons</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('employee') }}'><i class='nav-icon la la-terminal'></i> Employee</a></li>
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





<li class="nav-item"><a class="nav-link" href="{{ backpack_url('elfinder') }}"><i class="nav-icon la la-files-o"></i> <span>{{ trans('backpack::crud.file_manager') }}</span></a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('vehiclegallery') }}'><i class='nav-icon la la-question'></i> VehicleGalleries</a></li>