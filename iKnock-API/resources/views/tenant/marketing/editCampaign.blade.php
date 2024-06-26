@include('tenant.include.header')
@include('tenant.include.sidebar')
<div class="right_col" role="main">
	<div class="container body">
		<div class="col-md-5">
			<div class="cust-head"><i class="fas fa-store"></i> Edit {{ $marketing->title }} lead Campaign</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="panel mt-4 panel-default">
				<div class="panel-heading">Edit Lead Campaign List</div>
				<div class="panel-body"> 
					<table class="table table-bordered table-striped">
						<thead>
							<th width="5">#</th>
							<th>Name Campaign</th>
							<th width="150">Action</th>
						</thead>
						<tbody>
							@forelse($marketing->marketingCampaign as $key=>$marketingCampaign)
								<tr>
									<td>{{ ++$key }}</td>
									<td>{{ $marketingCampaign->campaign->title }}</td>
									<td>
										<input class="campaignedit" type="checkbox" {{ $marketingCampaign->status == 1 ? 'checked':'' }}  data-toggle="toggle" data-id="{{ $marketingCampaign->id }}"  data-offstyle="danger" data-size="md">
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="3" class="text-center">No Data Found</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
@include('tenant.include.footer')
<script type="text/javascript">
$('body').on('change', '.campaignedit', function(event) {
    event.preventDefault();
    var  obj = $(this);
    var id = obj.attr('data-id');

    if(obj.prop('checked') == true){
    	var value = 1;
    }else{
    	var value = 0;
    }

    $.ajax({
    	url: '{{ route('tenant.marketing.campaign.status.update') }}',
    	type: 'post',
    	dataType: 'json',
    	data: { 
    			_token:$('meta[name="csrf-token"]').attr('content'),
    			id:id,
    			value:value,
			 },
    })
    .done(function() {
    	toastr.success('Campaign status updated successfully');
    });
    
});
</script>
