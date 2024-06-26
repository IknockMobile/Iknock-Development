@include('tenant.include.header')
@include('tenant.include.sidebar')
<div class="right_col" role="main">
	<div class="container body">
		<div class="col-md-8">
			<div class="cust-head">knocks List</div>
		</div>
		<div class="col-md-4 text-right">
			<a href="{{ URL::to('tenant/lead/knocks/user/list') }}" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left"></i></a>
		</div>
		<div class="col-md-12">
			<table class="table table-bordered">
				<thead>
					<th>#</th>
					<td>Lead Title</td>
					<td>Lead Address</td>
					<td>Status</td>
					<td width="10%">Action</td>
				</thead>
				<tbody>
					@forelse($userLeadKnocks as $key=>$userLeadKnock)
						<tr>
							<td width="5px">
								{{ ++$key }}
							</td>
							<td>
								{{ $userLeadKnock->lead->title ?? '-' }}
							</td>
							<td>
								{{ $userLeadKnock->lead->formatted_address ?? '-' }}
							</td>
							<td>
								{{ $userLeadKnock->status->title }}
							</td>
							<td>
								<a href="{{ URL::to('tenant/knocks/'.$userLeadKnock->id.'/edit') }}" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i></a>
		                        <button type="submit" data-action="{{ URL::to('tenant/knocks/'.$userLeadKnock->id.'/delete') }}" data-id="{{ $userLeadKnock->id }}" class="btn btn-danger btn-sm remove-crud "><i class="fas fa-trash"></i></button>
							</td>
						</tr>
					@empty
					<tr>
						<td colspan="5" class="text-center"> No Data Found</td>
					</tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>
</div>
@include('tenant.include.footer')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" referrerpolicy="no-referrer"></script>
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

		var data = {id:id};

	    if(confirm("Are you sure you want to delete this?")){

	    	 ajaxCall('POST', base_url+'/tenant/knocks/'+id+'/delete', data, {}).then(function (res) {
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
</script>