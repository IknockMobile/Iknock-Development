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
.hidebox, .picklistModelbox, .customModelbox, .picklistbox{
	display:none;
}
.mt-3{
	margin-top:30px;
}
</style>
<div class="right_col" role="main">
	<div class="container body">
		<div class="col-md-12">
			<div class="cust-head"><i class="fas fa-list"></i> Create Deal Lead View Setp</div>
		</div>
		<div class="col-md-12 mt-5">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="row">
						<div class="col-md-6">
							<h5>Create Deal Lead View Setup</h5>
						</div>
						<div class="col-md-6 text-right">
							<a href="{{ route('tenant.dealead-viewsetp.index') }}" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left"></i></a>
						</div>
					</div>
				</div>
				<div class="panel-body">
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
					<form action="{{ route('tenant.dealead-viewsetp.store') }}" method="post">
						@csrf
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label>Title:</label>
									<input type="text" class="form-control title-input" name="title" placeholder="Enter Title">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label>Order No:</label>
									<input type="number" class="form-control orderno-input" name="order_no" placeholder="Enter order No">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label>View Type</label>
									<select class="form-control viewtype" name="view_type">
										<option value="1" selected>Custom</option>
										<option value="0">Default</option>
									</select>
								</div>
							</div>
						</div>
						<div class="hidebox">
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label>Input Type:</label>
										<select name="input_type" id="" class="inputType form-control">
											@forelse($inputType as $key=>$value)
												<option value="{{ $key }}">{{ $value }}</option>
											@empty
											@endforelse
										</select>
									</div>
								</div>
								<div class="col-md-6 picklistbox">
									<label>Pick List Type:</label>
									<select name="pick_list_type" class="form-control picklistType">
										<option value="">Select Input Type</option>
										<option value="1" >Custom Pick List</option>
										<option value="2" >Already Exist Pick List</option>
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6 picklistModelbox">
									<div class="form-group">
										<label>Pick List Content Model:</label>
										<select name="pick_list_content_model" class="form-control">
											<option value="">Select Pick List Content Model</option>
											@forelse($pickListTypes as $key=>$value)
												<option value="{{ $key }}">{{ $value }}</option>
											@empty
											@endforelse
										</select>
									</div>
								</div>
							</div>
							<div class="row m-0 customModelbox">
								<div class="col-md-8 mb-0 p-0">
									<label>Custom Pick List</label>
                                                                        
									
										<div class="col-md-12">
											<div class="col-md-10 m-0 p-0">
												
											</div>
											<div class="col-md-2 m-0">
												<button class="btn btn-info customore-btn" type="button"><i class="fas fa-plus"></i></button>
											</div>
										</div>
									<div class="subcustombox">
										<div class="col-md-12">
											<div class="col-md-10 m-0 p-0">
												<input type="text" class="form-control" name="custompick[1]">
											</div>
											<div class="col-md-2 m-0">
												<button class="btn btn-danger removeDeletePick" type="button"><i class="fas fa-trash-alt"></i></button>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row mt-3">
							<div class="col-md-12 text-center">
								<div class="form-group">
									<button type="submit" class="btn submit-form btn-primary"><i class="fas fa-save"></i> Save</button>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="morebox" style="display: none;">
	<div class="col-md-12 mt-1">
		<div class="col-md-10 m-0 p-0">
			<input type="text" class="form-control custompick" name="custompick[key]">
		</div>
		<div class="col-md-2 m-0">
			<button class="btn btn-danger removeDeletePick" type="button"><i class="fas fa-trash-alt"></i></button>
		</div>
	</div>
</div>
@include('tenant.include.footer2')
<script>

	var inputvalue = $('.viewtype option:selected').val();
	var inputType = $('.inputType option:selected').val();
	var picklistType = $('.picklistbox option:selected').val();
	viewForm(inputvalue);
	viewInputForm(inputType);
	
	$('.viewtype').change(function(event) {
		inputvalue = $('.viewtype option:selected').val();
		viewForm(inputvalue);
	});

	$('.inputType').change(function(event) {
		inputType = $('.inputType option:selected').val();
		viewInputForm(inputType);
	});

	$('.picklistType').change(function(event) {
		picklistType = $('.picklistType option:selected').val();
		picklistInputForm(picklistType);
	});

	$('body').on('click', '.removeDeletePick', function(event) {
		$(this).parent().parent().remove();
	});

	function viewInputForm(value){
		if(value == 5){
			$('.picklistbox').attr('style','display:block;');
		}else{
			$('.picklistbox').attr('style','display:none;');
		}
	}

	function picklistInputForm(value) {
		console.log(value);
		if(value == 1){
			$('.customModelbox').attr('style','display:block;');
			$('.picklistModelbox').attr('style','display:none;');
		}else{
			$('.picklistModelbox').attr('style','display:block;');
			$('.customModelbox').attr('style','display:none;');
		}
	}

	function viewForm(value) {
		if(value == 0){
			$('.hidebox').attr('style','display:none;');
		}else{
			$('.hidebox').attr('style','display:block;');
		}
	}

	$('.customore-btn').click(function(event) {
		var cutomPickNo = $('.custompick').length;
		cutomPickNo =  parseInt(cutomPickNo) + parseInt(1);
		var morebox = $('.morebox').html();
	   morebox = morebox.replace('key', cutomPickNo);

	   $('.subcustombox').append(morebox);
	});

</script>
