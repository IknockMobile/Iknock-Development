@include('tenant.include.header')
@include('tenant.include.sidebar')

<div class="right_col" role="main">
	<div class="row" id="content-heading">
        <!--content-heading here-->
        <div class="col-md-9">
            <h1 class="cust-head"><i class="fas fa-users"></i> Mail-chimp Campaign ( {{ $campaignSegment->segment_name }} ) Segment Management</h1>
        </div> 
   </div>
   <div class="row">
   		<div class="col-md-12">
   			<div class="panel panel-default">
   				<div class="panel-heading">
   					<h5>Mail-chimp Campaign <b>{{ $campaignSegment->segment_name }}</b> Segment User List</h5>
   				</div>
   				<div class="panel-body">
   					<table class="table table-bordered table-striped">
						<thead>
							<th width="5">#</th>
							<th>Email</th>
							<th width="150">Action</th>
						</thead>
						<tbody>
							@forelse($campaignUsers as $key=>$user)
								<tr>
									<td>{{ ++$key }}</td>
									<td>{{ $user->email_address }}</td>
									<td>
										<input class="campaign-user-edit" type="checkbox" data-toggle="toggle" {{ getSegmentStatus($campaignSegment->id,$user->id) == 1 ? 'checked':'' }} data-segment-id="{{ $campaignSegment->id }}" data-id="{{ $user->id }}"  data-offstyle="danger" data-size="md">
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="3" class="text-center">No Data Found!</td>
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
$('body').on('change', '.campaign-user-edit', function(event) {
    event.preventDefault();
    var  obj = $(this);
    var id = obj.attr('data-id');
    var segmentId = obj.attr('data-segment-id');

    if(obj.prop('checked') == true){
    	var value = 1;
    }else{
    	var value = 0;
    }

    $.ajax({
    	url: '{{ route('tenant.campaign.segment.status.user') }}',
    	type: 'post',
    	dataType: 'json',
    	data: { 
    			_token:$('meta[name="csrf-token"]').attr('content'),
    			id:id,
    			segment_id:segmentId,
    			value:value,
			 },
    })
    .done(function() {
    	toastr.success('Segment User status updated successfully');
    });
    
});
</script>