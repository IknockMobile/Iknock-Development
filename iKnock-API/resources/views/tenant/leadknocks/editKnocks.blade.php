@include('tenant.include.header')
@include('tenant.include.sidebar')
<div class="right_col" role="main">
	<div class="container body">
		<div class="col-md-8">
			<div class="cust-head">Edit knock</div>
		</div>
		<div class="col-md-4 text-right">
			<a href="{{ URL::to('tenant/lead/knocks/user/list') }}" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left"></i></a>
		</div>
		<div class="col-md-12">
			<div class="panel">
				<div class="panel-heading">
					Edit Knock 
				</div>
				<div class="panel-body">
					<form action="{{ URL::to('/tenant/knocks/'.$knock->id.'/edit') }}" method="post">
					@csrf
					<div class="row">
							
						<div class="col-md-6">
							<div class="form-group">
								<label for="">Status:</label>
								<input type="hidden" name="knock_id" value="{{ $knock->id }}"> 
								<select name="status_id" id="" class="form-control">
								<label for="">Status: </label>
									@forelse($status as $key=>$value)
										@if($status_id == $key)
											<option value="{{ $key }}" selected>{{ $value }}</option>
										@else
											<option value="{{ $key }}">{{ $value }}</option>
										@endif
									@empty
									@endforelse
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="">Lead Name:</label>
								<input type="text" class="form-control" value="{{ $lead->title }}"  disabled>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="form-group text-center">
								<button type="button" class="submit_url btn btn-success">Save</button>
								<a href="{{ URL::to('tenant/lead/knocks/user/list') }}" class="btn btn-primary" style="margin-top:6px;">Back</a>
							</div>
						</div>
					</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@include('tenant.include.footer')

<script>
	$('.submit_url').click(function (e) {
		e.preventDefault();
		$(this).parents().find('form').submit();
	});
</script>
