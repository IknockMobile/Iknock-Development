@include('tenant.include.header')
@include('tenant.include.sidebar')

<div class="right_col" role="main">
	<div class="row" id="content-heading">
        <!--content-heading here-->
        <div class="col-md-9">
            <h1 class="cust-head">Mail-chimp Campaign Management</h1>
        </div>
   </div>
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h5>Campaign List</h5>
				</div>
				<div class="panel-body">
					<table class="table table-bordered">
						<thead>
							<th width="10px">Id</th>
							<th>Name</th>
							<th width="150">Type</th>
							<th width="150">Status</th>
							<th width="200">Create On</th>
							{{-- <th width="200">Create By</th> --}}
							<th width="200">Action</th>
						</thead>
						<tbody>
							@forelse($campaigns as $key=>$campaign)
								<tr>
									<td >{{ $campaign->campaign_id }}</td>
									<td>{{ $campaign->title }}</td>
									<td>{{ $campaign->type }}</td>
									<td style="  text-transform: uppercase;">
										@if($campaign->status == 'save')
											<label for="" class="label label-primary"><i class="fas fa-envelope"></i> Active</label>
										@elseif($campaign->status == 'sent')
											<label class="label label-success"><i class="fas fa-envelope-open-text"></i> Sent</label>
										@endif
									</td>
									<td>{{ dynamicDateFormat($campaign->created_at, 3) }}</td>
									{{-- <td>{{ $campaign->response->content_edited_by }}</td> --}}
									<td>
										<a href="{{ route('tenant.campaign.segment',$campaign->id) }}" class="btn btn-primary"><i class="fas fa-users"></i> Segments</a>
										<a href="{{ $campaign->response->archive_url }}" target="_black" class="btn btn-dark"><i class="fas fa-eye"></i> View Content</a>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="7" class="text-center">No Data Found!</td>
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
