@include('tenant.include.header')
@include('tenant.include.sidebar')
<style>
 .leadstatusbox{
    padding: 7px;
    font-size: 12px;
    color: #fff;
    text-shadow: 0px 0px 2px #000;
    border-radius: 19px;
    box-shadow: 0px 1px 1px black;
}
.ui-sortable-handle{
	   cursor: move; /* fallback if grab cursor is unsupported */
    cursor: grab;
    cursor: -moz-grab;
    cursor: -webkit-grab;
}
</style>
<div class="right_col" role="main">
	<div class="container body">
		<div class="col-md-12">
			<div class="cust-head"><i class="fas fa-list"></i> Deal Lead View Setup</div>
		</div>
		<div class="col-md-12 mt-5">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="row">
						<div class="col-md-6">
							<h5>Deal Up Lead View Setup</h5>
						</div>
						<div class="col-md-6 text-right">
							<a href="{{ route('tenant.dealead-viewsetp.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i></a>
						</div>
					</div>
				</div>
				<div class="panel-body">
					<table class="table table-bordered">
						<thead>
							<th width="5%">No</th>
							<th>Title</th>
							<th width="10%">Type</th>
                     <th width="15%">Slug</th>
							<th width="10%">Is Show?</th>
							<th width="10%">Action</th>
						</thead>
						<tbody>
							@forelse($dealLeadViewSetps as $key => $dealLeadViewSetp)
								<tr id="{{ $dealLeadViewSetp->id }}">
									<td>{{ ++$key }}</td>
									<td>{{ $dealLeadViewSetp->title }}</td>
									<td>
										@if($dealLeadViewSetp->view_type == 1)
											<label class="label label-success">Custom - ({{ $dealLeadViewSetp->inputTypeName }}) </label> 
										@else
											<label class="label label-primary"> Default </label>
										@endif
									</td>
                            <td>{{ $dealLeadViewSetp->title_slug }}</td>
									<td><input type="checkbox" class="setupview" {{ $dealLeadViewSetp->is_show == 1 ? 'checked':''  }} data-toggle="toggle" data-id="{{ $dealLeadViewSetp->id }}"  data-offstyle="danger" data-size="sm"> </td>
									<td>
										<a href="{{ route('tenant.dealead-viewsetp.edit',$dealLeadViewSetp->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i></a>
										<a href="#" data-action="{{ route('tenant.dealead-viewsetp.destroy',$dealLeadViewSetp->id) }}" data-id="{{ $followStatus->id }}" class="btn btn-danger btn-sm remove-crud "><i class="fas fa-trash"></i></a>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="4" class="text-center"> No data found!</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				{{-- 	@if(empty(request()->get('select_all')))
			    		{!! $userLeadKnocks->appends(request()->query())->links() !!}
			    	@endif --}}
				</div>
			</div>
		</div>
	</div>
</div>
@include('tenant.include.footer')

<script>
	
	$("body").on('click', '.remove-crud', function(event) {
		
		$.ajaxSetup({
		    headers: {

		        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		    }
		});

		event.preventDefault();

		var url = $(this).attr('data-action'); 
		var id = $(this).attr('data-id'); 

		var data = {id:id,_method:'DELETE'};

	    if(confirm("Are you sure you want to delete this?")){

	    	 ajaxCall('POST', url, data, {}).then(function (res) {
                location.reload();
                // $('.ui-dialog').hide();
                // var newDiv = '';
                // newDiv += '<div class="col-md-4 text-right show_all" style=""></div>';
                // $('.new_div').html(newDiv);

            });
	    }
	    else{
	        return false;
	    }
	});



   $('tbody').sortable({
   	swapThreshold: 1,
      animation: 150,
      update: function(event, ui) {
    		 var sortedIDs = $('tbody').sortable("toArray");

    		 var url = "{{ URL::to('/tenant/deal/sortable') }}";

	    	 ajaxCall('POST', url, {sortedids:sortedIDs}, {}).then(function (res) {
		      	toastr.success('Deal Lead View Setup Order updated successfully.','Success Alert', {timeOut: 5000});
	    	 });
      }
   });

    $('body').on('change', '.setupview', function(event) {
		event.preventDefault();
		
		var id = $(this).attr('data-id');

		if($(this).prop('checked')){
	    	var value = 1;
		}else{
	    	var value = 0;
		}
			
		var data = {id:id,value:value};

	    ajaxCall('POST', "{{ route('tenant.deal.field.update.show') }}",data, {}).then(function (res) {
		      	toastr.success('followup updated successfully.','Success Alert', {timeOut: 5000});
	    });
	});
</script>