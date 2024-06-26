@include('tenant.include.header')
@include('tenant.include.sidebar')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css">
<style>
    
    .input_area{
        height: 30px;
    }
    .label-status{
        padding: 7px;
        font-size: 12px;
        color: #fff;
        border-radius: 6px;
        box-shadow: 0px 1px 1px black;
    }
    .mt-2{
        margin-bottom: 10px;
    }
    .label-status:hover{
        color: #fff;
        box-shadow: 1px 2px 2px black;
    }
    .box-leadfollowup{
        overflow-x:auto;
    }
    .table{
        width: auto;
        overflow-x: scroll;
        display: inline-block;
        white-space: nowrap;
    }
    .toggle{
        min-width: 55px !important;
        min-height: 27px !important;
    }
    .lead-table th, 
    .lead-table td {
        white-space: nowrap;
        padding: 3px 6px;
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
    .ui-sortable-handle{
        cursor: move; /* fallback if grab cursor is unsupported */
        cursor: grab;
        cursor: -moz-grab;
        cursor: -webkit-grab;
    }
</style>

<div class="right_col" role="main">
    <div class="container body">
        <div class="row">
            <div class="col-md-4">
                <div class="cust-head"><i class="fas fa-level-up-alt"></i> Follow Up Lead Management</div>
            </div>
            <div class="col-md-8 text-right">
                <a href="{{ route('tenant.follow-leadview.index') }}" class="btn btn-warning"><i class="fas fa-sort"></i> Follow Lead View Setup</a>
                <button type="button" class="btn btn-info" id="export-lead"> <i class="fas fa-file-export"></i> Follow Lead Export </button>
                <button type="button" class="btn btn-dark" id="export-history"> <i class="fas fa-file-export"></i> Export History </button>
            </div>
            <div class="col-md-12 mt-2">
                <form action="{{ route('tenant.followup-lead.index') }}" method="get" class="searchform">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Search:</label>
                            <input id="search_text" type="text" class="input_area form-control search-text" value="{{ request()->get('search_text') }}" name="search_text" placeholder="Search..">
                        </div>
                    </div>
                    <div class="col-md-1">
                <div class="form-group margintop"><button class="b1 clear_search" type="button" >
                                X
                            </button></div> </div>
                    <div class="col-md-2">
                        <label>User:</label>
                        <select id="lead_users" class="input_area form-control selectpicker lead_users" data-live-search="true"
                                name="user_ids" value="" data-actions-box="true" multiple>
                            @php
                                $user_id_search =request()->get('user_id_search');
                                if(!is_array($user_id_search)){
                                    $user_id_search = explode(',',$user_id_search);
                                }
                            
                            @endphp
                            @foreach ($users as $agent)
                            
                            @if(!empty($user_id_search) && in_array($agent->id, $user_id_search))
                            <option data-tokens="{{ $agent->title }}" value="{{ $agent->id }}" selected="">{{ $agent->first_name }} {{ $agent->last_name }}</option>
                            @else

                            <option data-tokens="{{ $agent->title }}" value="{{ $agent->id }}">{{ $agent->first_name }} {{ $agent->last_name }}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Follow Up Status:</label>
                        <select id="lead_status" class="form-control selectpicker lead_status" data-live-search="true"
                                name="status_ids" data-actions-box="true" multiple>
                            @php
                                $lead_status_id =request()->get('lead_status_id');
                                if(!is_array($lead_status_id)){
                                    $lead_status_id = explode(',',$lead_status_id);
                                }
                            
                            @endphp
                            @foreach ($statusList as $status)
                            @if(!empty($lead_status_id) && in_array($status->id, $lead_status_id))
                            <option data-tokens="{{ $status->title }}" value="{{ $status->id }}" selected>{{ $status->title }}</option>
                            @else
                            <option data-tokens="{{ $status->title }}" value="{{ $status->id }}">{{ $status->title }}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Select Date Range:</label>
                            <input type="text" id="e2" name="e2"  value="{{ request()->get('status_date_range') ?? '' }}" class="input date_range1 statusDateRange" value="select date" name="date_range">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Select Auction Date Range:</label>
                            <input type="text" id="actionDateInput" value="{{ request()->get('auction_date') ?? '' }}" name="actionDateInput" class="input date_range2 auction_date" value="select date">
                        </div>
                    </div> 

                </form>
                <div class="col-md-2 mt-4">
                    <div class="form-group">
                        <button class="btn btn-delete followingLeadsAllDelete">Bulk Delete</button>
                        <button class="btn btn-primary setting-dropdown dropdown-toggle" type="button" data-toggle="dropdown" ><i
                                class="fa fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right check-menu menu-drop">
                            @forelse($followUpLeadViewSetpsDropDown as $key=>$value)
                            <li id="{{$value->id}}" class="drop-li"><label><input type="checkbox" class="setupview" {{ $value->is_show == 1 ? 'checked':''  }} data-toggle="toggle" data-id="{{ $value->id }}"  data-offstyle="danger" data-size="sm"> {{ $value->title }}</label></li>
                            @empty
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div><?php
            $PaginationlimitData = App\Models\Paginationlimit::where('id', '=', 1)->first();
            ?>
            <form method="post" enctype="multipart/form-data" id="PageOrder">
                <input type="hidden" class="submit_url" value="{{ URL::to('tenant/setting/create') }}" />
                <input type="hidden" id="redirect_url" class="redirect_url" value="{{ URL::to('tenant/followup-lead') }}">
                {{ csrf_field() }}
                <div class="col-md-1">   <label for="page_number_list">Records per screen</label>              
                    <select class="input" name="followup_lead_management" id="page_number">
                        <option <?php if (isset($PaginationlimitData->followup_lead_management) AND $PaginationlimitData->followup_lead_management == 10) {
                echo "selected";
            } ?> value="10">10</option>
                        <option <?php if (isset($PaginationlimitData->followup_lead_management) AND $PaginationlimitData->followup_lead_management == 20) {
                echo "selected";
            } ?> value="20">20</option>
                        <option <?php if (isset($PaginationlimitData->followup_lead_management) AND $PaginationlimitData->followup_lead_management == 50) {
                echo "selected";
            } ?> value="50">50</option>
                        <option <?php if (isset($PaginationlimitData->followup_lead_management) AND $PaginationlimitData->followup_lead_management == 100) {
                echo "selected";
            } ?> value="100">100</option>
                    </select>
                </div>            

                <div class="col-md-2">
                    <!--<button class="btn margintop ajax-button b1">Save</button>-->
                </div><div class="col-md-12"> &nbsp;
                </div>

            </form>
        </div>
        <div class="col-md-12 mt-5">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Follow Up Lead Management
                </div>
                <div class="panel-body">
                    <div class="box-leadfollowup">                                            
                        @include('tenant.followinglead.followingLeadsMain',['followUpLeadViewSetps'=>$followUpLeadViewSetps,'followingLeadsData'=>$followingLeadsData,'followingLeads'=>$followingLeads])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('tenant.include.footer')
<script>
	$(document).ready(function($) {
		let currentXHR = null;
                $('body').on('change','#page_number', function () {
                    var redirectUrl = $("#redirect_url").val(); 
                    
                    var search = $('#search_text').val();                    
                    var newText = "?search_text=" + search ; 
                    redirectUrl += newText; 
                    
                    var lead_users = $('.lead_users option:selected').map((_, e) => e.value).get();
                    var lead_status = $('.lead_status option:selected').map((_, e) => e.value).get();
                                                                  
                    var newText = "&user_id_search=" + lead_users ; 
                    redirectUrl += newText; 
                    
                                
                    var newText = "&lead_status_id=" + lead_status ; 
                    redirectUrl += newText; 
                    
                    var e2 = $('#e2').val();                    
                    var newText = "&status_date_range=" + e2 ; 
                    redirectUrl += newText; 
                    
                    var actionDateInput = $('#actionDateInput').val();                    
                    var newText = "&auction_date=" + actionDateInput ; 
                    redirectUrl += newText; 
                    
                    var sortColumn = localStorage.getItem("sortColumn");
                    var sortType = localStorage.getItem("sortType");
        
                    var sortColumn = "&sort_column=" + sortColumn ; 
                    redirectUrl += sortColumn; 
                    
                    var sortType = "&sort_type=" + sortType ; 
                    redirectUrl += sortType; 
                    
                    $("#redirect_url").val(redirectUrl); 
                    
                    var redirectUrl = $("#redirect_url").val();                     
                    
                    
            $("#PageOrder").submit();
        });
        
	$("#e2").daterangepicker({
		initialText : 'Select Status Update Range...',
			datepickerOptions: {
         minDate: null,
         maxDate: null,
         numberOfMonths:2
     },
	});
	
	$("#actionDateInput").daterangepicker({
		initialText : 'Select Auction Date Range...',
		datepickerOptions: {
         minDate: null,
         maxDate: null,
         numberOfMonths:2
     },
	});
	edittablefun();

	var columnsList = @php echo $followUpLeadViewSetpsSlug;  @endphp;

	$.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

	$('body').on('click','.isMarketing', function () {
		var obj = $(this);
		var id = obj.attr('data-id');

		$.ajax({
	        url: '{{ route('tenant.followup-lead.marketing') }}',
	        type: 'post',
	        data:{
	        	_token:$('meta[name="csrf-token"]').attr('content'),
	        	 id:id,
	        },
	        success: function (data) {
                    obj.parent().parent().hide(1000)
	        	toastr.success('Marketing lead moved successfull.','Success Alert', {timeOut: 5000});
	        }
	    });
	});
        
        $('body').on('click','.isPurchase', function () {
		var obj = $(this);
		var id = obj.attr('data-id');

		$.ajax({
	        url: '{{ route('tenant.followup-lead.purchase') }}',
	        type: 'post',
	        data:{
	        	_token:$('meta[name="csrf-token"]').attr('content'),
	        	 id:id,
	        },
	        success: function (data) {
                    obj.parent().parent().hide(1000)
	        	toastr.success('Purchase lead moved successfull.','Success Alert', {timeOut: 5000});
	        }
	    });
	});

	$('body').on('click','.isDeal', function () {
		var obj = $(this);
		var id = obj.attr('data-id');

		$.ajax({
	        url: '{{ route('tenant.followup-lead.deal') }}',
	        type: 'post',
	        data:{
	        	_token:$('meta[name="csrf-token"]').attr('content'),
	        	 id:id,
	        },
	        success: function (data) {
	        	obj.parent().parent().hide(1000);
	        	toastr.success('Deal lead moved successfull.','Success Alert', {timeOut: 5000});
	        }
	    });
	});

	function edittablefun() {
            $('.detailupdate').editable({
                send: 'always',
                method:'POST',
                placement: 'bottom',
                format: 'mm/dd/yyyy', 
                url: "{{ route('tenant.followup.field.update.editable') }}",
                success: function(data) {
                   searchDataLead(1);
                    toastr.success('Follow-up lead updated successfull.','Success Alert', {timeOut: 5000});
                }
            });

	   $('.detailCustomUpdate').editable({
	     send: 'always',
	     method:'POST',
	     placement: 'bottom',
	     format: 'mm/dd/yyyy', 
	     url: "{{ route('tenant.followup.custom.field.update.editable') }}",
	     success: function(response) {
            searchDataLead(1);
	        toastr.success('Follow-up lead updated successfull.','Success Alert', {timeOut: 5000});
	     }
	   });
	}
  
   var searchInput = document.getElementById('search_text');
    searchInput.addEventListener('keydown', function(event) {        
        if (event.keyCode === 13) {
            event.preventDefault();
            searchDataLead();                   
            return false;
        }
    });
    
   $('.clear_search').click(function (e) {
            $('.search-text').val('');
            e.preventDefault();
            searchDataLeadWithourSort();            
        });
        
   $('.search-text').focusout(function (e) {
   	e.preventDefault();

   	searchDataLead();
   });

   $('.lead_users').change(function (e) {
   	e.preventDefault();

   	searchDataLead();
   });

	$('.lead_status').change(function (e) {
   	e.preventDefault();

   	searchDataLead();
   });

   $('.auction_date').change(function (e) {
   	e.preventDefault();

   	searchDataLead();
   });

   $('.statusDateRange').change(function (e) {
   	e.preventDefault();

   	searchDataLead();
   });

   $('body').on('click','.sort-columns', function () {
   	var type = $(this).attr('data-type');
   	var column = $(this).attr('data-column');

   	localStorage.setItem('sortType', type);
   	localStorage.setItem('sortColumn', column);

   	searchDataLead();
   });
   
   $(document).on('click', '#export-history', function () {
        var searchText = $('.search-text').val();
   	var userIdSearch = $('.lead_users option:selected').map((_, e) => e.value).get();
   	var leadStatusId = $('.lead_status option:selected').map((_, e) => e.value).get();
   	var auctionDate = $('.auction_date').val();
   	var statusDateRange = $('.statusDateRange').val();
   	var sortColumn = localStorage.getItem("sortColumn");
   	var sortType = localStorage.getItem("sortType");
	var leadsIds = $('.followUpDeleteLead:checked').map((_, e) => e.value).get();

        data = {
            search_text:searchText,
	        	 user_id_search:userIdSearch,
	        	 leads_ids:leadsIds,
	        	 lead_status_id:leadStatusId,
	        	 auction_date:auctionDate,
	        	 status_date_range:statusDateRange,
	        	 sort_column:sortColumn,
	        	 sort_type:sortType,
            export: true,
            is_history_export: 1
        };


        var qString = $.param(data);
        var url = "{{URL::to('tenant/followup/leads/history/export?')}}" + qString;
        window.open(url, '_self')
    });

    $(document).on('click', '#export-lead', function () {
        var searchText = $('.search-text').val();
   	var userIdSearch = $('.lead_users option:selected').map((_, e) => e.value).get();
   	var leadStatusId = $('.lead_status option:selected').map((_, e) => e.value).get();
   	var auctionDate = $('.auction_date').val();
   	var statusDateRange = $('.statusDateRange').val();
   	var sortColumn = localStorage.getItem("sortColumn");
   	var sortType = localStorage.getItem("sortType");
	var leadsIds = $('.followUpDeleteLead:checked').map((_, e) => e.value).get();

        data = {
            search_text:searchText,
	        	 user_id_search:userIdSearch,
	        	 leads_ids:leadsIds,
	        	 lead_status_id:leadStatusId,
	        	 auction_date:auctionDate,
	        	 status_date_range:statusDateRange,
	        	 sort_column:sortColumn,
	        	 sort_type:sortType,
            export: true,
            is_history_export: 1
        };


        var qString = $.param(data);
        var url = "{{URL::to('tenant/followup/export?')}}" + qString;
        window.open(url, '_self')
    });
    
    
     $(document).on('click', 'td', function(e) {
        // Check if the clicked element is indeed the <td> itself
        if (e.target === this) {
            // Get the value of data-href attribute of the <td> element
            var dataHrefValue = $(this).data('tdhref');
            if (dataHrefValue) {
                // Redirect to the URL specified by data-href
                window.location.href = dataHrefValue;
            } else {
                console.error('data-href attribute not found or empty.');
            }
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

	    ajaxCall('POST', "{{ route('tenant.followup.field.update.show') }}",data, {}).then(function (res) {
	            searchDataLead();
		      	toastr.success('followup updated successfully.','Success Alert', {timeOut: 5000});

	    });
	});

    $('.menu-drop').sortable({
	   	swapThreshold: 1,
	      animation: 150,
	      update: function(event, ui) {
    		 var sortedIDs = $('.menu-drop').sortable("toArray");

    		 var url = "{{ URL::to('/tenant/follow/sortable') }}";

	    	 ajaxCall('POST', url, {sortedids:sortedIDs}, {}).then(function (res) {
		            searchDataLead();
		      	toastr.success('Follow Up Lead View Setup Order updated successfully.','Success Alert', {timeOut: 5000});
	    	 });
	      }
	   });

   function searchDataLeadWithourSort(edittable = 0) {

   	var searchText = $('.search-text').val();
   	var userIdSearch = $('.lead_users option:selected').map((_, e) => e.value).get();
   	var leadStatusId = $('.lead_status option:selected').map((_, e) => e.value).get();
   	var auctionDate = $('.auction_date').val();
   	var statusDateRange = $('.statusDateRange').val();
        var count = 0;

      jQuery.each(columnsList, function (index, value) {
             count++;
      });

      var loadImage = '{{ asset('image/loder.gif') }}';

      if(edittable == 0){
	      $('.body-table').html('<tr><td colspan="'+count+'" class="text-center"><img src="'+loadImage+'" width="100px"></td></tr>');
   	

      }else{
   		localStorage.setItem('sortType', '');
   		localStorage.setItem('sortColumn', '');
      }
   	
   	var sortColumn = localStorage.getItem("sortColumn");
   	var sortType = localStorage.getItem("sortType");

            if (currentXHR !== null) {
                
                currentXHR.abort();
            }
    
            currentXHR = $.ajax({
	        url: '{{ route('tenant.followup-lead.list.index') }}',
	        type: 'post',
	        data:{
	        	_token:$('meta[name="csrf-token"]').attr('content'),
	        	 search_text:searchText,
	        	 user_id_search:userIdSearch,
	        	 lead_status_id:leadStatusId,
	        	 auction_date:auctionDate,
	        	 status_date_range:statusDateRange
	        },
	        success: function (data) {
	        	$('.box-leadfollowup').html(data.success);
	        	 edittablefun();
	        }
	    });
	}
        
   function searchDataLead(edittable = 0) {

   	var searchText = $('.search-text').val();
   	var userIdSearch = $('.lead_users option:selected').map((_, e) => e.value).get();
   	var leadStatusId = $('.lead_status option:selected').map((_, e) => e.value).get();
   	var auctionDate = $('.auction_date').val();
   	var statusDateRange = $('.statusDateRange').val();
        var count = 0;

      jQuery.each(columnsList, function (index, value) {
             count++;
      });

      var loadImage = '{{ asset('image/loder.gif') }}';

      if(edittable == 0){
	      $('.body-table').html('<tr><td colspan="'+count+'" class="text-center"><img src="'+loadImage+'" width="100px"></td></tr>');
   	

      }else{
   		localStorage.setItem('sortType', '');
   		localStorage.setItem('sortColumn', '');
      }
   	
   	var sortColumn = localStorage.getItem("sortColumn");
   	var sortType = localStorage.getItem("sortType");

            if (currentXHR !== null) {
                
                currentXHR.abort();
            }
    
            currentXHR = $.ajax({
	        url: '{{ route('tenant.followup-lead.list.index') }}',
	        type: 'post',
	        data:{
	        	_token:$('meta[name="csrf-token"]').attr('content'),
	        	 search_text:searchText,
	        	 user_id_search:userIdSearch,
	        	 lead_status_id:leadStatusId,
	        	 auction_date:auctionDate,
	        	 status_date_range:statusDateRange,
	        	 sort_column:sortColumn,
	        	 sort_type:sortType,
	        },
	        success: function (data) {
	        	$('.box-leadfollowup').html(data.success);
	        	 edittablefun();
	        }
	    });
	}


		    $('.selectfollowUpLeadAll').change(function (e) {
		  		e.preventDefault();

		  		var checked = $('.selecatfollowUpLeadAll').prop('checked');


		  		if(checked){
		  				$('.followUpDeleteLead').prop('checked','checked');
		  		}else{
		  				$('.followUpDeleteLead').removeAttr('checked','checked');
		  		}

		  });

		 $('.followingLeadsAllDelete').click(function (e) {
		 	e.preventDefault();

	   	var leadsIds = $('.followUpDeleteLead:checked').map((_, e) => e.value).get();

	   	if(leadsIds.length == 0){
	   		alert('please select leads.');

	   	}else{
	   		if(confirm("Are you sure you want to delete this?")){

	    	 ajaxCall('POST', "{{ route('tenant.followup-lead.list.delete') }}", {ids:leadsIds}, {}).then(function (res) {
                
				searchDataLead();

		      toastr.success('Follow-up leads deleted successfull.','Success Alert', {timeOut: 5000});

          });
	    }
	    else{
	        return false;
	    }
	   	}

		 });

		 $('body').on('change','.followUpIsRetired',function (e) {
		 	e.preventDefault();
		 		var id = $(this).attr('data-id');
		   	var value = $(this).prop('checked');

		   	var data = {id:id,is_retired:value};

		   	ajaxCall('POST', "{{ route('tenant.followup-lead.is.retired') }}",data, {}).then(function (res) {
                
		      	toastr.success('Is retired updated successfully.','Success Alert', {timeOut: 5000});

         	});


		 });

   });

$(function() {
  var thHeight = $(".lead-table th:first").height();
  $(".lead-table th").resizable({
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
});

</script>