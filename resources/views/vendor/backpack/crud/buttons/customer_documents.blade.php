@if ($crud->hasAccess('create'))
	<a href="{{ backpack_url('customerdocuments') }}?customer_id={{ $entry->getKey() }}" class="btn btn-xs btn-success"><i class="fa fa-eye"></i> Documents</a>
@endif