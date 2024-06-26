@include('tenant.include.header')
@include('tenant.include.sidebar')
<style type="text/css">
	table{
		width: auto;
	    white-space: nowrap;
	}
	.mt-1{
		margin-top:14px;
	}
	.mt-5{
		margin-top:38px;
	}
	.toggle{
	min-width: 55px !important;
    min-height: 27px !important;
   }
   .ui-sortable-handle , .ui-sortable-handle label{
	   cursor: move; /* fallback if grab cursor is unsupported */
	    cursor: grab;
	    cursor: -moz-grab;
	    cursor: -webkit-grab;
	}
	.table{
	width: auto;
   overflow-x: scroll;
   display: inline-block;
   white-space: nowrap;
}
.sort-box{
	display: inline;
	width: 15px;
}
.sort-columns{
	cursor: pointer;
	width: 15px;
}
.sort-columns:hover{
	text-shadow: 0px 1px 1px black;
}
.sort-columns span{
	text-shadow: 0px 0px 1px black;
}
.note-width{
	min-width:610px;
}
</style>
<div class="right_col" role="main">
	<div class="row">
		<div class="col-md-5">
			<div class="cust-head"><i class="fas fa-handshake"></i> Deal Lead Management</div>
		</div>
		<div class="col-md-7 text-right">
			<a href="{{ route('tenant.dealead-viewsetp.index') }}" class="btn btn-primary"><i class="fas fa-sort"></i> Deal Lead View Setup</a>
			<button class="btn btn-warning deal-export" id="export-lead" href="{{ $dealExportUrl }}"><i class="fas fa-file-export"></i> Export</button>
			<a class="btn btn-dark" href="{{ route('tenant.deal.import') }}"><i class="fas fa-file-import"></i> Import</a>
			<button class="dealAllDelete btn btn-primary">Delete</button>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					FILTER <i class="fas fa-filter"></i>
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-2">
							<label for="">Search</label>
							<input id="search-input" type="text" placeholder="Seach.." value="{{ request()->get('search') ?? '' }}"  name="search" class="search-input form-control">
                                                        <button class="b1 clear_search" type="button" >
                                X
                            </button>
						</div>
						<div class="col-md-2">
			                <label>
			                 Status:</label>
							<select name="deal_status" id="" class="dealstatus-input form-control">
								<option value="">Search Deal Status</option>
								@forelse($dealLeadObj->dealStatus as $key=>$value)
									@if(!empty(request()->get('deal_status')) && request()->get('deal_status') == $key)
										<option value="{{ $key }}" selected>{{ $value }}</option>
									@else
										<option value="{{ $key }}">{{ $value }}</option>
									@endif
								@empty
								@endforelse
							</select>
						</div>
						<div class="col-md-2">
			                <label>Investor:</label>
							<select name="investor_id" id="" class="investor-input form-control">
								<option value="">Search Investor</option>
								@forelse($mobileusersArray as $key=>$value)
									@if(!empty(request()->get('investor_id')) && request()->get('investor_id') == $value->id)
										<option value="{{ $value->id }}" selected>{{ $value->fullName }}</option>
									@else
										<option value="{{ $value->id }}">{{ $value->fullName }}</option>
									@endif
								@empty
								@endforelse
							</select>
						</div>
						<div class="col-md-2">
			                <label>Closer:</label>
							<select name="closer_id" id="" class="closer-input form-control">
								<option value="">Search Closer</option>
								@forelse($mobileusersArray as $key=>$value)
									@if(!empty(request()->get('closer_id')) && request()->get('closer_id') == $value->id)
										<option value="{{ $value->id }}" selected>{{ $value->fullName }}</option>
									@else
										<option value="{{ $value->id }}">{{ $value->fullName }}</option>
									@endif
								@empty
								@endforelse
							</select>
						</div>
						<div class="col-md-2">
			                <label>Deal Type:</label>
							<select name="deal_type" id="" class="dealtype-input form-control">
								<option value="">Search Deal Type</option>
								@forelse($dealLeadObj->dealType as $key=>$value)
									@if(!empty(request()->get('deal_type')) && request()->get('deal_type') == $key)
										<option value="{{ $key }}" selected>{{ $value }}</option>
									@else
										<option value="{{ $key }}">{{ $value }}</option>
									@endif
								@empty
								@endforelse
							</select>
						</div>
						<div class="col-md-2 ">
			                <label>Purchase Finance:</label>
							<select name="purchase_finance" id="" class="purchasefinance-input form-control">
								<option value="">Search Purchase Finance</option>
								@forelse($dealLeadObj->purchaseFinance as $key=>$value)
									@if(!empty(request()->get('purchase_finance')) && request()->get('purchase_finance') == $key)
										<option value="{{ $key }}" selected>{{ $value }}</option>
									@else
										<option value="{{ $key }}">{{ $value }}</option>
									@endif
								@empty
								@endforelse
							</select>
						</div>
						<div class="col-md-2 mt-1">
			                <label>Ownership:</label>
							<select name="ownership" id="" class="ownership-input form-control">
								<option value="">Search Ownership</option>
								@forelse($dealLeadObj->ownershipList as $key=>$value)
									@if(!empty(request()->get('ownership')) && request()->get('ownership') == $key)
										<option value="{{ $key }}" selected>{{ $value }}</option>
									@else
										<option value="{{ $key }}">{{ $value }}</option>
									@endif
								@empty
								@endforelse
							</select>
						</div>

     
						<div class="col-md-2 mt-1">
			                <div class="form-group">
			                	<label>Purchase Date:</label>
			                    <input type="text" name="purchase_date" value="{{ request()->get('purchase_date') }}" class="daterangepicker input purchaseDate form-control purchaseDateInput" value="" placeholder="Search Purchase Date">
			                </div>
			            </div>
			            <div class="col-md-2 mt-1">
			                <div class="form-group">
			                	<label>Sell Date:</label>
			                    <input type="text" name="sell_date" value="{{ request()->get('sell_date') }}" class="daterangepicker input sellDate form-control sellDateInput" value="" placeholder="Search Sell Date">
			                </div>
			            </div>
			          <div class="col-md-2 mt-1">
               	<?php 
						        $PaginationlimitData = App\Models\Paginationlimit::where('id', '=', 1)->first();
						        ?>
						        <form method="post" enctype="multipart/form-data" id="PageOrder">
						            <input type="hidden" class="submit_url" value="{{ URL::to('tenant/setting/create') }}" />
						            <input type="hidden" class="redirect_url" value="{{ URL::to('tenant/deals') }}">
						            {{ csrf_field() }}
						              <label for="page_number_list">Records per screen</label>              
						                <select class="input" name="deal_management" id="page_number">
						                    <option <?php if(isset($PaginationlimitData->deal_management) AND $PaginationlimitData->deal_management == 10){ echo "selected"; } ?> value="10">10</option>
						                    <option <?php if(isset($PaginationlimitData->deal_management) AND $PaginationlimitData->deal_management == 20){ echo "selected"; } ?> value="20">20</option>
						                    <option <?php if(isset($PaginationlimitData->deal_management) AND $PaginationlimitData->deal_management == 50){ echo "selected"; } ?> value="50">50</option>
						                    <option <?php if(isset($PaginationlimitData->deal_management) AND $PaginationlimitData->deal_management == 100){ echo "selected"; } ?> value="100">100</option>
						                </select>
						            <div class="col-md-2">
						                <!--<button class="btn margintop ajax-button b1">Save</button>-->
						            </div><div class="col-md-12"> &nbsp;
						            </div>
						            
						        </form>
               </div>
			            <div class="col-md-2 mt-5">
							{{-- <button class="btn btn-primary search-btn" type="button" style="margin-top: 0px !important;">Search <i class="fas fa-search"></i></button> --}}
							<button class="btn btn-primary setting-dropdown dropdown-toggle" type="button" data-toggle="dropdown" ><i
			                        class="fa fa-cog"></i>
			                </button>
			                <ul class="dropdown-menu dropdown-menu-right check-menu menu-drop">
			                	@forelse($dealLeadViewSetpDropDown as $key=>$value)
			                		<li id="{{$value->id}}" class="drop-li"><label><input type="checkbox" class="setupview" {{ $value->is_show == 1 ? 'checked':''  }} data-toggle="toggle" data-id="{{ $value->id }}"  data-offstyle="danger" data-size="sm"> {{ $value->title }}</label></li>
			                	@empty
			                	@endforelse
			                </ul>

			   
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					Deal Lead List	<i class="fas fa-list"></i>	
				</div>
				<div class="panel-body main-body">
					{!! $tableBodyView !!}
				</div>
			</div>
		</div>
	</div>
</div>
@include('tenant.include.footer')
<script>
	$(document).ready(function() {
            
             $(document).on('click', '#export-lead', function () {
            var searchText = $('.search-text').val();            
            var leadsIds = $('.deal_delete:checked').map((_, e) => e.value).get();

            data = {
                search_text:searchText,                             
                leads_ids:leadsIds
            };

            var qString = $.param(data);
            var url = "{{URL::to('tenant/deals/export?user_id=12&company_id=4&session_user_group_id=3&call_mode=admin?')}}" + qString;
            window.open(url, '_self')
        });
        
        
       $('body').on('change','#page_number', function () {
            $("#PageOrder").submit();
        });
	});
    $('.menu-drop').sortable({
	   	swapThreshold: 1,
	      animation: 150,
	      update: function(event, ui) {
    		 var sortedIDs = $('.menu-drop').sortable("toArray");

    		 var url = "{{ URL::to('/tenant/deal/sortable') }}";

	    	 ajaxCall('POST', url, {sortedids:sortedIDs}, {}).then(function (res) {
		         loadTableView();
		      	toastr.success('Deal Up Lead View Setup Order updated successfully.','Success Alert', {timeOut: 5000});
	    	 });
	      }
	   });

     $('body').on('change', '.setupview', function(event) {
		event.preventDefault();
		
		var id = $(this).attr('data-id');

		if($(this).prop('checked')){
	    	var value = 1;
		}else{
	    	var value = 0;
		}

			
		var data = {id:id,value:value};

	    ajaxCall('POST', "{{ route('tenant.deal.field.update.show') }}",data, {}).then(function (res) {
		    toastr.success('Deal updated successfully.','Success Alert', {timeOut: 5000});
            loadTableView();
	    });
	});

  loadJsScript();

var searchInput = document.getElementById('search-input');
    searchInput.addEventListener('keydown', function(event) {        
        if (event.keyCode === 13) {
            event.preventDefault();
            loadTableView();             
            return false;
        }
    });
    
$('.clear_search').click(function (e) {
            $('.search-input').val('');
            e.preventDefault();
            loadTableViewClear(0); 
            
        });
        
//$('.search-input').focusout(function(event) {
//	loadTableView(0); 
//});

$('.dealstatus-input').change(function(event) {
	loadTableView(0); 
});

$('.investor-input').change(function(event) {
	loadTableView(0); 
});


$('.closer-input').change(function(event) {
	loadTableView(0); 
});

$('.dealtype-input').change(function(event) {
	loadTableView(0); 
});


$('.purchasefinance-input').change(function(event) {
	loadTableView(0); 
});

$('.ownership-input').change(function(event) {
	loadTableView(0); 
});

$('.purchaseDateInput').change(function(event) {
	loadTableView(0); 
});

$('.sellDateInput').change(function(event) {
	loadTableView(); 
});

	var columnsList = @php echo $dealLeadViewSetp->count();  @endphp;

function loadTableViewClear(edittable = 0) {
    
    var search = '';
    var dealstatus  = $('.dealstatus-input option:selected').val();
    var investor  = $('.investor-input option:selected').val();
    var closer  = $('.closer-input option:selected').val();
    var dealtype  = $('.dealtype-input option:selected').val();
    var purchasefinance  = $('.purchasefinance-input option:selected').val();
    var ownership  = $('.ownership-input option:selected').val();
    var purchaseDateInput  = $('.purchaseDateInput').val();
    var sellDateInput  = $('.sellDateInput').val();

      var loadImage = '{{ asset('image/loder.gif') }}';

      if(edittable == 0){

	      $('.body-table').html('<tr><td colspan="'+columnsList+'" class="text-center"><img src="'+loadImage+'" width="100px"></td></tr>');

	     }else{
   				localStorage.setItem('sortType', '');
   				localStorage.setItem('sortColumn', '');
     }

    var sortColumn = localStorage.getItem("sortColumn");
   	var sortType = localStorage.getItem("sortType");

		var data = {
				search:search,
				deal_status:dealstatus,
				investor_id:investor,
				closer_id:closer,
				sort_column:sortColumn,
				sort_type:sortType,
				deal_type:dealtype,
				purchase_finance:purchasefinance,
				ownership:ownership,
				purchase_date:purchaseDateInput,
				sell_date:sellDateInput,
				is_ajax:1,
			};

	 $.ajax({
	        url: '{{ route('tenant.deals.index') }}',
	        type: 'get',
	        data:data,
	        success: function (data) {
						$('.main-body').html(data.success);
						$('.deal-export').attr('href',data.dealExportUrl);
						loadJsScript();
	        }
	    });
}

function loadTableView(edittable = 0) {
    
    var search = $('.search-input').val();
    var dealstatus  = $('.dealstatus-input option:selected').val();
    var investor  = $('.investor-input option:selected').val();
    var closer  = $('.closer-input option:selected').val();
    var dealtype  = $('.dealtype-input option:selected').val();
    var purchasefinance  = $('.purchasefinance-input option:selected').val();
    var ownership  = $('.ownership-input option:selected').val();
    var purchaseDateInput  = $('.purchaseDateInput').val();
    var sellDateInput  = $('.sellDateInput').val();

      var loadImage = '{{ asset('image/loder.gif') }}';

      if(edittable == 0){

	      $('.body-table').html('<tr><td colspan="'+columnsList+'" class="text-center"><img src="'+loadImage+'" width="100px"></td></tr>');

	     }else{
   				localStorage.setItem('sortType', '');
   				localStorage.setItem('sortColumn', '');
     }

    var sortColumn = localStorage.getItem("sortColumn");
   	var sortType = localStorage.getItem("sortType");

		var data = {
				search:search,
				deal_status:dealstatus,
				investor_id:investor,
				closer_id:closer,
				sort_column:sortColumn,
				sort_type:sortType,
				deal_type:dealtype,
				purchase_finance:purchasefinance,
				ownership:ownership,
				purchase_date:purchaseDateInput,
				sell_date:sellDateInput,
				is_ajax:1,
			};

	 $.ajax({
	        url: '{{ route('tenant.deals.index') }}',
	        type: 'get',
	        data:data,
	        success: function (data) {
						$('.main-body').html(data.success);
						$('.deal-export').attr('href',data.dealExportUrl);
						loadJsScript();
	        }
	    });
}

$(".daterangepicker").daterangepicker({
		initialText : 'Select Date Range...',
			datepickerOptions: {
         minDate: null,
         maxDate: null,
         numberOfMonths:2
     },
	});

 $('body').on('click','.sort-columns', function () {
   	var type = $(this).attr('data-type');
   	var column = $(this).attr('data-column');

   	localStorage.setItem('sortType', type);
   	localStorage.setItem('sortColumn', column);

   	loadTableView();
   });

function loadJsScript(){
	var thHeight = $(".deal-table th:first").height();
    
    $(".deal-table th").resizable({
      handles: "e",
      minHeight: thHeight,
      maxHeight: thHeight,
      minWidth: 40,
      resize: function (event, ui) {
        var sizerID = "#" + $(event.target).attr("id");
        $('.sort-active').attr('style', '');
        $(sizerID).attr('style','min-width:'+ui.size.width+'px;border: 2px solid #e2e2e2;');
      }
   });

	$.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

     $('.detailupdateDeal').editable({
       send: 'always',
       viewformat: 'mm/dd/yyyy',    
       url: "/tenant/deal/update/editable",
       success: function(response) {
       	loadTableView(1); 
		    	toastr.success('Deal updated successfully.','Success Alert', {timeOut: 5000});
       }
     });

	     $('.detailCustomUpdate').editable({
		     send: 'always',
		     method:'POST',
		     placement: 'bottom',
		     format: 'mm/dd/yyyy', 
		     url: "{{ route('tenant.deal.custom.field.update.editable') }}",
		     success: function(response) {
	            loadTableView(1);
		        toastr.success('Deal lead updated successfull.','Success Alert', {timeOut: 5000});
		     }
		   });

       $('.all_delete_deal').change(function (e) {
		  		e.preventDefault();

		  		var checked = $('.all_delete_deal').prop('checked');


		  		if(checked){
		  				$('.deal_delete').prop('checked','checked');
		  		}else{
		  				$('.deal_delete').removeAttr('checked','checked');
		  		}

		  });

       $('.dealAllDelete').click(function (e) {
		 	e.preventDefault();

	   	var leadsIds = $('.deal_delete:checked').map((_, e) => e.value).get();

	   	if(leadsIds.length == 0){
	   		alert('please select Deal leads.');

	   	}else{
	   		if(confirm("Are you sure you want to delete this?")){

	    	 ajaxCall('POST', "{{ route('tenant.deallead.list.delete') }}", {ids:leadsIds}, {}).then(function (res) {
                
				loadTableView();

		     	toastr.success('Deal Lead leads deleted successfull.','Success Alert', {timeOut: 5000});
          });
	    }
	    else{
	        return false;
	    }
	   	}

		 });
}
</script>