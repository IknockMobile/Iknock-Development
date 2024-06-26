@include('tenant.include.header')
@include('tenant.include.sidebar')
<div class="right_col" role="main">
	<div class="row">
		<div class="col-md-5">
			<div class="cust-head"><i class="fas fa-file-upload"></i> Import Deal Lead Management</div>
		</div>
		<div class="col-md-7 text-right">
			<a class="btn btn-primary" href="{{ route('tenant.deals.index') }}"><i class="fas fa-arrow-left"></i> Back</a>
		</div>
	</div>
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				 <div class="panel-heading">
					Import Deals Lead 
				</div>
				<div class="panel panel-body">
					@if (count($errors) > 0)
					      <div class="row">
					        <div class="col-md-12">
					          <div class="alert alert-danger alert-dismissible">
					              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
					              @foreach($errors->all() as $error)
					              {{ $error }} <br>
					             @endforeach      
					          </div>
					        </div>
					      </div>
					  @endif
					<form action="{{ route('tenant.deal.import.file') }}" class="impotform" method="post" enctype="multipart/form-data">
						@csrf
						<div class="row">
							<div class="col-md-12">
								<label>File:</label>
								<input type="file" name="deal_file" accept=".csv,.xlsx" class="form-control">	
							</div>
						</div>
						<div class="row">
							<div class="col-md-12 text-center submit-box">
								<button class="btn btn-primary btn-formsubmit" style="margin-top: 15px;" type="submit"><i class="fas fa-save"></i> Save</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		<div>
	</div>
</div>


@include('tenant.include.footer2')

<script>
	$(document).ready(function() {
		$('.impotform').submit(function(event) {
			$('.submit-box').html('<h1><div class="row"><div class="col-md-12 text-center"><img src="{{ asset('image/loder.gif') }}" width="100px"></div></div>');		
		});
	});
</script>