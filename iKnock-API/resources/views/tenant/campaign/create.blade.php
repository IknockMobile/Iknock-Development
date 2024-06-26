@include('tenant.include.header')
@include('tenant.include.sidebar')
<div class="right_col" role="main">
	<div class="row" id="content-heading">
        <!--content-heading here-->
        <div class="col-md-9">
            <h1 class="cust-head">Create Mail-chimp Campaign</h1>
        </div>
        <div class="col-md-3 text-right">
        	<a href="#" class="btn btn-success"><i class="fas fa-plus"></i></a>
        </div>
   </div>
   <div class="row">
   	<div class="col-md-12">
   		<div class="panel panel-default">
   			<div class="panel-heading">
   				<h5>Create Mail-chimp Campaign</h5>
   			</div>
			<div class="panel-body">
				<form action="{{ route('tenant.campaign.store') }}">
					@csrf
					<div class="row">
						<div class="col-md-6">
							<label>First Name:</label>
							<input name="FNAME" class="form-control" placeholder="Enter First Name">
						</div>
						<div class="col-md-6">
							<label>Last Name:</label>
							<input name="LNAME" class="form-control" placeholder="Enter Last Name">
						</div>
					</div>
					<div class="row">
						<div class="col-md-12 mt-4">
							<label>Email</label>
							<input type="email" name="email" class="form-control" placeholder="Enter Email Address">
						</div>	
					</div>
					<div class="row">
						<div class="col-md-12 mt-4 text-center">
							<button type="submit" class="btn btn-success">Save</button>
						</div>
					</div>
				</form>
			</div>
   		</div>
   	</div>
   </div>
</div>
@include('tenant.include.footer')
