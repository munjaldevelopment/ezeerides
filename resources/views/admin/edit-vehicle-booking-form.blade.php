@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => backpack_url('dashboard'),
    $crud->entity_name_plural => url($crud->route),
    trans('backpack::crud.edit') => false,
  ];

  // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
	<section class="container-fluid">
	  <h2>
        <span class="text-capitalize">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</span>
        <small>{!! $crud->getSubheading() ?? trans('backpack::crud.edit').' '.$crud->entity_name !!}.</small>

        @if ($crud->hasAccess('list'))
          <small><a href="{{ url($crud->route) }}" class="d-print-none font-sm"><i class="la la-angle-double-{{ config('backpack.base.html_direction') == 'rtl' ? 'right' : 'left' }}"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a></small>
        @endif
	  </h2>
	</section>
@endsection

@section('content')
<div class="row">
	<div class="{{ $crud->getEditContentClass() }}">
		<!-- Default box -->

		@include('crud::inc.grouped_errors')

		  <form method="post"
		  		action="{{ url($crud->route.'/'.$entry->getKey()) }}"
				@if ($crud->hasUploadFields('update', $entry->getKey()))
				enctype="multipart/form-data"
				@endif
		  		>
		  {!! csrf_field() !!}
		  {!! method_field('PUT') !!}

		  	@if ($crud->model->translationEnabled())
		    <div class="mb-2 text-right">
		    	<!-- Single button -->
				<div class="btn-group">
				  <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				    {{trans('backpack::crud.language')}}: {{ $crud->model->getAvailableLocales()[request()->input('locale')?request()->input('locale'):App::getLocale()] }} &nbsp; <span class="caret"></span>
				  </button>
				  <ul class="dropdown-menu">
				  	@foreach ($crud->model->getAvailableLocales() as $key => $locale)
					  	<a class="dropdown-item" href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}?locale={{ $key }}">{{ $locale }}</a>
				  	@endforeach
				  </ul>
				</div>
		    </div>
		    @endif
		      <!-- load the view from the application if it exists, otherwise load the one in the package -->
		      @if(view()->exists('vendor.backpack.crud.form_content'))
		      	@include('vendor.backpack.crud.form_content', ['fields' => $crud->fields(), 'action' => 'edit'])
		      @else
		      	@include('crud::form_content', ['fields' => $crud->fields(), 'action' => 'edit'])
		      @endif

            @include('crud::inc.form_save_buttons')
		  </form>
	</div>
</div>
@endsection

@push('after_scripts')
<script>
 function getEmployee(station_id)
	{
		if(station_id != "")
		{
			$("#user_id").html('<option value="">--Select--</option>');
			$.ajax({
				type:"GET",
				url:"{{ backpack_url('getEmployee') }}/"+station_id,
				success:function(res){ 
					if(res){
						$("#user_id").append('<option value="0">None</option>');
						$.each(res,function(key,value){
							$("#user_id").append('<option value="'+key+'">'+value+'</option>');
						});
				  	}
				}
			});
		}
		else
		{
			$("#user_id").html('<option value="">--Select--</option><option value="0">None</option>');
		}
	}

	function getVehicle(station_id)
	{
		if(station_id != "")
		{
			$("#vehicle").html('<option value="">--Select--</option>');
			$.ajax({
				type:"GET",
				url:"{{ backpack_url('getVehicle') }}/"+station_id,
				success:function(res){ 
					//alert(res);
					if(res){
						$("#vehicle").append('<option value="0">None</option>');
						$.each(res,function(key,value){
							$("#vehicle").append('<option value="'+key+'">'+value+'</option>');
						});
				  	}
				}
			});
		}
		else
		{
			$("#vehicle").html('<option value="">--Select--</option><option value="0">None</option>');
		}
	}

function showEmployee(station_id, user_id)
	{
		if(station_id != "")
		{
			$("#user_id").html('<option value="">--Select--</option>');
			$.ajax({
				type:"GET",
				url:"{{ backpack_url('getEmployee') }}/"+station_id,
				success:function(res){ 
					if(res){
						
						$.each(res,function(key,value){
							if(key == user_id)
							{
								$("#user_id").append('<option value="'+key+'" selected="selected">'+value+'</option>');
							}
							else
							{
								$("#user_id").append('<option value="'+key+'">'+value+'</option>');
							}
						});
				  	}
				}
			});
		}
		else
		{
			$("#user_id").html('<option value="">--Select--</option><option value="0">None</option>');
		}
	}

	function showVehicle(station_id, vehicle)
	{
		if(station_id != "")
		{
			$("#user_id").html('<option value="">--Select--</option>');
			$.ajax({
				type:"GET",
				url:"{{ backpack_url('getVehicle') }}/"+station_id,
				success:function(res){ 
					if(res){
						
						$.each(res,function(key,value){
							if(key == vehicle)
							{
								$("#user_id").append('<option value="'+key+'" selected="selected">'+value+'</option>');
							}
							else
							{
								$("#user_id").append('<option value="'+key+'">'+value+'</option>');
							}
						});
				  	}
				}
			});
		}
		else
		{
			$("#user_id").html('<option value="">--Select--</option><option value="0">None</option>');
		}
	}
	
	showStations('{{ $entry->station }}', '{{ $entry->user_id }}');

	showVehicle('{{ $entry->station }}', '{{ $entry->vehicle }}');

</script>
@endpush

