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
            <div class="cust-head"><i class="fas fa-list"></i> Edit Purchase Lead View Setup</div>
        </div>
        <div class="col-md-12 mt-5">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Edit Purchase Lead View Setp</h5>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ route('tenant.purchase-leadview.index') }}" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left"></i></a>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <form action="{{ route('tenant.purchase-leadview.update',$follow_leadview->id) }}" method="post">
                        @csrf
                        @method('put')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Title:</label>
                                    <input type="text" class="form-control title-input" name="title" placeholder="Enter Title" value="{{ $follow_leadview->title }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Order No:</label>
                                    <input type="number" class="form-control orderno-input" name="order_no" placeholder="Enter order No" value="{{ $follow_leadview->order_no }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>View Type:</label>
                                    <select class="form-control viewtype" name="view_type">
                                        <option value="1" {{ $follow_leadview->view_type == 1 ? 'selected':'' }}>Custom</option>
                                        <option value="0" {{ $follow_leadview->view_type == 0 ? 'selected':'' }}>Default</option>
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
                                            <option value="{{ $key }}" {{ $follow_leadview->input_type == $key ? 'selected':'' }}>{{ $value }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 picklistbox">
                                    <label>Pick List Type:</label>
                                    <select name="pick_list_type" class="form-control picklistType">
                                        <option value="">Select Input Type</option>
                                        <option value="1" {{ $follow_leadview->pick_list_type == 1 ? 'selected':'' }}>Custom Pick List</option>
                                        <option value="2" {{ $follow_leadview->pick_list_type == 2 ? 'selected':'' }} >Already Exist Pick List</option>
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
                                            <option value="{{ $key }}"  {{ $follow_leadview->pick_list_content_model == $key ? 'selected':'' }}>{{ $value }}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row m-0 customModelbox">
                                <div class="col-md-8 mb-0 p-0">
                                    <label>Custom Pick List</label>
                                    <div class="subcustombox">
                                        <div class="col-md-12">
                                            <div class="col-md-10 m-0 p-0">
                                                
                                            </div>
                                            <div class="col-md-2 m-0">
                                                <button class="btn btn-info customore-btn" type="button"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                        <div class="custom-list-sort">
                                            @forelse($follow_leadview->customPickListArray as $key=>$value)
                                            @if($loop->first)
                                            <div class="col-md-12 purches-list-dragdrop">
                                                <div class="col-md-10 m-0 p-0">
                                                    <div class="input-group">
                                                      <span class="input-group-addon">
                                                        <i class="fas fa-bars"></i>
                                                        </span>
                                                        </span> 
                                                        <input type="text" class="form-control custompick" name="custompick[{{$key}}]" value="{{ $value }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-2 m-0">
                                                    <button class="btn btn-danger removeDeletePick" type="button"><i class="fas fa-trash-alt"></i></button>
                                                </div>
                                            </div>
                                            @else
                                            <div class="col-md-12 purches-list-dragdrop">
                                                <div class="col-md-10 m-0 p-0">
                                                    <div class="input-group">
                                                        <span class="input-group-addon bg-primary">
                                                              <i class="fas fa-bars"></i>
                                                        </span>
                                                        </span> 
                                                    <input type="text" class="form-control custompick" name="custompick[{{$key}}]" value="{{ $value }}">
                                                        </div>
                                                </div>
                                                <div class="col-md-2 m-0">
                                                    <button class="btn btn-danger removeDeletePick" type="button"><i class="fas fa-trash-alt"></i></button>
                                                </div>
                                            </div>
                                            @endif
                                            @empty
                                            <div class="col-md-12 purches-list-dragdrop">
                                                <div class="col-md-10 m-0 p-0">
                                                    <input type="text" class="form-control" name="custompick[1]">
                                                </div>
                                                <div class="col-md-2 m-0">
                                                    <button class="btn btn-info customore-btn" type="button"><i class="fas fa-plus"></i></button>
                                                </div>
                                            </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn submit-form btn-primary"><i class="fas fa-save"></i> Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="morebox" style="display: none;">
    <div class="col-md-12 mt-0 purches-list-dragdrop">
        <div class="col-md-10 m-0 p-0">
             <div class="input-group">
              <span class="input-group-addon">
                <i class="fas fa-bars"></i>
              </span>
              </span>
            <input type="text" class="form-control custompick" name="custompick[key]">
        </div>
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
	picklistInputForm(picklistType,inputType);

	
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
		picklistInputForm(picklistType,inputType);
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

	function picklistInputForm(value, inputType) {
		if(inputType == 5){
			if(value == 1){
				$('.customModelbox').attr('style','display:block;');
				$('.picklistModelbox').attr('style','display:none;');
			}else{
				$('.picklistModelbox').attr('style','display:block;');
				$('.customModelbox').attr('style','display:none;');
			}
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
		var morebox = $('.morebox').html();
	   morebox = morebox.replace('key', cutomPickNo);

	   $('.custom-list-sort').append(morebox);
       // updateShort();
	});

    function updateShort() {
         $('.custom-list-sort').sortable({
                swapThreshold: 1,
                  animation: 150,
                  update: function(event, ui) {
                         // var sortedIDs = $('tbody').sortable("toArray");

                         // var url = "{{ URL::to('/tenant/purchase/sortable') }}";

                         // ajaxCall('POST', url, {sortedids:sortedIDs}, {}).then(function (res) {
                         //    toastr.success('Follow Up Lead View Setup Order updated successfully.','Success Alert', {timeOut: 5000});
                         // });
                  }
               });
    }
</script>