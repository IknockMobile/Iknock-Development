@include('tenant.include.header')
@include('tenant.include.sidebar')
<style>
    .mt-2{
        margin-top: 15px;
    }
    .submit-form{
        margin: 0px;
    }
</style>
<div class="right_col" role="main">
    <div class="container body">
        <div class="col-md-2">
            <div class="cust-head"><i class="fas fa-clipboard-list"></i> knock List</div>
        </div>
        <div class="col-md-6">
            <form action="{{ URL::to('tenant/lead/knocks/user/list') }}" method="get" class="fillter-form">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Search:</label>
                        <input type="text" id="search" name="search" value="{{ request()->get('search') ?? '' }}" placeholder="" value="{{ request()->get('search') ?? '' }}" class="input" >
                        
                    </div>
                    
                </div>	
                <div class="col-md-1">
                    <label>&nbsp;</label>
                    <button class="b1 clear_search" type="button" >
                            X
                        </button>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Date Range:</label>
                        <input type="text" id="e2" name="date" value="{{ request()->get('date') ?? '' }}" placeholder="Start Date" value="{{ request()->get('start_date') ?? '' }}" class="startDate input date_range1" value="select date" name="date_range" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>User:</label>
                        <select name="userId" id="" class="form-control user-serach-knock">
                            <option value="" >Select Search User</option>
                            @forelse($users as $key=>$user)
                            <option value="{{ $user->id }}" {{ request()->get('userId') == $user->id ? 'selected':'' }}>{{ $user->fullName }}</option>
                            @empty
                            @endforelse
                        </select>
                    </div>
                </div>
                
                
            </form>
            
        </div>
        <div class="col-md-4">
            <button data-date="{{ request()->get('date') ?? ''  }}" data-user="{{ request()->get('userId') ?? '' }}" class="mt-4 export-btn btn btn-primary">
                <i class="fas fa-file-export"></i> Export
            </button>
            <div class="form-check-selectall">
                <span class="btn btn-danger delete-bulk mt-4" style="margin-left: 10px;"><i class="fas fa-trash"></i> Delete Bulk</span>
            </div>
            <a href="{{ route('tenant.knock.index.import') }}" class="btn import-btn mt-4 btn-dark">
                <i class="fas fa-file-import"></i> Import
            </a>
        </div>
    </div>
    <div class="col-md-12 mt-2">
        <div class="panel panel-default">
            <div class="panel-heading">
                knocks List
            </div>
            <div class="panel-body">
                <?php
                if (env('APP_ENV') == 'local') {
                    $last_is_verified_data = 11777;
                } elseif (env('APP_ENV') == 'staging') {
                    $last_is_verified_data = 9459;
                } elseif (env('APP_ENV') == 'production') {
                    $last_is_verified_data = 12060;
                } else {
                    $last_is_verified_data = 0;
                }
                ?>
                <table class="table table-bordered">
                    <thead>
                    <th>
                        <input type="checkbox" class="selectAllKnocks" name="select_all" {{ empty(request()->get('select_all')) ? '':'checked' }}  value="1">
                    </th>
                    <th>ID</th>
                    <th>User Name</th>
                    <th>Homeowner Name</th>
                    <th>Homeowner Address</th>
                    <th>Status</th>
                    <th>Is Verified</th>
                    <th>Distance</th>
                    <th>Lead Lat/Long</th>
                    <th>App Lat/Long</th>
                    <th>Backend distance</th>
                    <th>Created on</th>
                    <th width="10%">Action</th>
                    </thead>
                    <tbody>
                        @forelse($userLeadKnocks as $key=>$userLeadKnock)
                        <tr>
                            <td width="5px">
                                <input type="checkbox" class="khock-list-checkbox" data-id="{{ $userLeadKnock->id }}">
                            </td>
                            <td>
                                {{ $userLeadKnock->id }}
                            </td>
                            <td>
                                {{ $userLeadKnock->user->first_name }} {{ $userLeadKnock->user->last_name }}
                            </td>
                            <td>
                                {{ $userLeadKnock->lead->title ?? '-' }}
                            </td>
                            <td>
                                @if(isset($userLeadKnock->lead->formatted_address) AND $userLeadKnock->lead->formatted_address != '' )
                                    {{ $userLeadKnock->lead->formatted_address ?? '-' }}
                                @else
                                    {{ $userLeadKnock->lead->address ?? '-' }}
                                @endif                                
                            </td>
                            <td>
                                {{ $userLeadKnock->status->title }}
                            </td>
                            <td class="text-center">
                                @if($last_is_verified_data != 0 AND $last_is_verified_data <= $userLeadKnock->id)
                                @if($userLeadKnock->is_verified == 1)
                                <label class="label label-success">Yes</label>
                                @else
                                <label class="label label-danger">No</label>
                                @endif
                                @else
                                @if($userLeadKnock->lead->is_verified == 1)
                                <label class="label label-success">Yes</label>
                                @else
                                <label class="label label-danger">No</label>
                                @endif
                                @endif
                            </td>
                            <td >
                                @if($userLeadKnock->distance != null)
                                {{ round($userLeadKnock->distance) }}
                                @endif
                            </td>
                            <td >
                                {{ $userLeadKnock->lead_lat }} / {{ $userLeadKnock->lead_long }}                                                                            
                            </td>
                            <td >
                                {{ $userLeadKnock->application_lat }} / {{ $userLeadKnock->application_long }}
                            </td>  
                            <td >
                                @if($userLeadKnock->backend_distance != null)
                                {{ round($userLeadKnock->backend_distance) }}
                                @endif
                            </td>
                            <td>
                                {{ dateTimezoneChange($userLeadKnock->created_at)  }}
                            </td>
                            <td>
                                <a href="{{ URL::to('tenant/knocks/'.$userLeadKnock->id.'/edit') }}" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i></a>
                                <a href="#" data-action="{{ URL::to('tenant/knocks/'.$userLeadKnock->id.'/delete') }}" data-id="{{ $userLeadKnock->id }}" class="btn btn-danger btn-sm remove-crud "><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center"> No Data Found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if(empty(request()->get('select_all')))
                {!! $userLeadKnocks->appends(request()->query())->links() !!}
                @endif
            </div>
        </div>
    </div>
</div>
</div>
@include('tenant.include.footer')
<script src="https://cdn.rawgit.com/Eonasdan/bootstrap-datetimepicker/e8bddc60e73c1ec2475f827be36e1957af72e2ea/src/js/bootstrap-datetimepicker.js"> </script>
<link href='https://cdn.rawgit.com/Eonasdan/bootstrap-datetimepicker/e8bddc60e73c1ec2475f827be36e1957af72e2ea/build/css/bootstrap-datetimepicker.css' rel='stylesheet'/>
<script>
	var selectAllvalue = '{{ request()->get('select_all') }}'; 

	if(selectAllvalue == 1){
		$('.khock-list-checkbox').attr('checked','checked');
	}

	$("body").on('click', '.remove-crud', function(event) {
		
		$.ajaxSetup({
		    headers: {

		        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		    }
		});

		event.preventDefault();

		var url = $(this).attr('data-action'); 
		var id = $(this).attr('data-id'); 

		var data = {id:id};

	    if(confirm("Are you sure you want to delete this?")){

	    	 ajaxCall('POST', base_url+'/tenant/knocks/'+id+'/delete', data, {}).then(function (res) {
                location.reload();
                // $('.ui-dialog').hide();
                // var newDiv = '';
                // newDiv += '<div class="col-md-4 text-right show_all" style=""></div>';
                // $('.new_div').html(newDiv);

            });
	    }
	    else{
	        return false;
	    }
	});

  var dateRange = $("#e2").daterangepicker();

    var searchInput = document.getElementById('search');
    searchInput.addEventListener('keydown', function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();         
            selectInputpage();        
            return false;
        }
    });
        
        
        $('.clear_search').click(function (e) {
            $('#search').val('');
            e.preventDefault();
            selectInputpage();            
        });
$('#search').focusout(function (e) {
    e.preventDefault();

  	selectInputpage();
});

  $('.user-serach-knock').change(function (e) {
  	e.preventDefault();

  	selectInputpage();
  });

  $('#e2').change(function (e) {
  	e.preventDefault();

  	selectInputpage();
  });

   $('.selectAllKnocks').change(function (e) {
  	e.preventDefault();

	selectInputpage();
  });

  function selectInputpage() {
  	
  	var url = '{{ URL::to('tenant/lead/knocks/user/list') }}';
  	var userid = $('.user-serach-knock option:selected').val();

  	var date = $('.startDate').val();
        var search = $('#search').val();

  	if ($('.selectAllKnocks').is(':checked')){
	  	var selectAllValue = 1;
  	}else{
	  	var selectAllValue = 0;
  	}


  	if(date != '' || userid != '' || search != ''){
	  	url = url+'?date='+date+'&select_all='+selectAllValue+'&userId='+userid+'&search='+search;
  	
  		window.location.href = url;
  	}

 		window.location.href = url;
  }


$('.delete-bulk').click(function (e) {
	e.preventDefault();

	var idlist = [];

	$('.khock-list-checkbox').each(function(index, el) {
		var obj  = $(this);
		var inputChecked = obj.prop("checked");		

		if(inputChecked == true){
			var id	= obj.attr('data-id');
			idlist.push(id);
		}
	});


	
	if(idlist.length != 0){
		if(confirm("Are you sure you want to delete knocks this?")){
			$.ajax({
					url: '{{ URL::to('tenant/lead/knocks/user/list') }}',
					type: 'post',
					data: {
						_token:$('meta[name="csrf-token"]').attr('content'),
						idsList:idlist
					},
					success: function (data) {
						if(data.success == 1){
							toastr.success('Success Alert', 'knocks Deleted successfully', {timeOut: 5000,progressBar: true});

							const myInterval = setInterval(myTimer, 1000);
						}
					}
				});

		}else{

		}
	}else{
		alert('Please Select delete knocks..');
	}

});

function myTimer() {
  	var url = '{{ URL::to('tenant/lead/knocks/user/list') }}';

	window.location.href = url;
}

$('.export-btn').click(function (e) {
	e.preventDefault();

	var date = $(this).attr('data-date');
	var userId = $(this).attr('data-user');
        var search = $('#search').val();
  	var url = '{{ route('tenant.knocks.export') }}';

  	var idlist = [];

	$('.khock-list-checkbox').each(function(index, el) {
		var obj  = $(this);
		var inputChecked = obj.prop("checked");		

		if(inputChecked == true){
			var id	= obj.attr('data-id');
			idlist.push(id);
		}
	});
 
    url = url+'?date='+date+'&idlist='+idlist+'&userId='+userId+'&search='+search;

	window.location.href = url;
});

$('body').on('change', '.isChecked', function(event) {
	event.preventDefault();
	obj = $(this);
	var id = obj.attr('data-id');

	var value = 0;

	if(obj.prop('checked')){
		value = 1;
	}

	$.ajax({
		url: '{{ route('tenant.knocks.isverified') }}',
		type: 'post',
		data: {
			_token:$('meta[name="csrf-token"]').attr('content'),
			id:id,
			is_verified:value,
		},
		success: function (data) {
			toastr.success('Success Alert','Is verified upadted successfully', {timeOut: 5000,progressBar: true});
		}
	});

});

</script>