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
</style>
	<div class="right_col" role="main">
		<div class="container body">
			<div class="col-md-12">
				<div class="cust-head"><i class="fas fa-list"></i> Follow Up Lead Status Management</div>
			</div>
		<div class="col-md-12 mt-5">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="row">
						<div class="col-md-6">
							<h5>Follow Up Lead Status Management</h5>
						</div>
						<div class="col-md-6 text-right">
							<a href="{{ route('tenant.follow-status.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i></a>
						</div>
					</div>
				</div>
				<div class="panel-body">
					<table class="table table-bordered">
						<thead>
							<th width="5%">No</th>
							<td>Title</td>
							<td>Color</td>
							<td width="10%">Action</td>
						</thead>
						<tbody>
							@forelse($followStatuses as $key => $followStatus)
								<tr>
									<td>{{ ++$key }}</td>
									<td>{{ $followStatus->title }}</td>
									<td><i class="fas fa-circle" style="color:{{ $followStatus->color_code }};"></i> {{ $followStatus->color_code ?? '-' }}</td>
									<td>
										<a href="{{ route('tenant.follow-status.edit',$followStatus->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i></a>
										<a href="#" data-action="{{ route('tenant.follow-status.destroy',$followStatus->id) }}" data-id="{{ $followStatus->id }}" class="btn btn-danger btn-sm remove-crud "><i class="fas fa-trash"></i></a>
									</td>
								</tr>
							@empty
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

</script>