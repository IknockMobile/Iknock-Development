@include('tenant.include.header')
@include('tenant.include.sidebar')
<style type="text/css">
    table{
        width: auto;
        overflow-x: scroll;
        display: inline-block;
        white-space: nowrap;
    }
</style>
<div class="right_col" role="main">
    <div class="container body">        
        <div class="row">
            <div class="col-md-4">
                <div class="cust-head"><i class="fas fa-level-up-alt"></i> Marketing Lead Management</div>
            </div>
            <div class="col-md-8 text-right">                
                <button type="button" class="btn btn-info" id="export-lead"> <i class="fas fa-file-export"></i>Export </button>    
                <button type="button"  class="btn btn-delete followingLeadsAllDelete">Bulk Delete</button>							                            
            </div>
            <div class="col-md-12 mt-2">
                <form id="searchFormMarketing" action="{{ route('tenant.followup-lead.index') }}" method="get" class="searchform">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Search:</label>
                            <input type="text" id="marketing_search_text" class="form-control search-text" value="{{ request()->get('search_text') }}" name="search_text" placeholder="Search..">                           
                        </div>                         
                    </div> 
                    <div class="col-md-1 mt-4">
                        <div class="form-group">
                            <label>&nbsp;</label>                        
                        <button class="b1 clear_search" type="button" >
                                X
                            </button>
                        </div>
                        
                    </div>
                    <div class="col-md-2">
                        <label>User:</label>
                        <select class="form-control selectpicker lead_users" data-live-search="true"
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
                    <div class="col-md-2 mt-4">
                        <div class="form-group">
                            <label>&nbsp;</label>
                        <button class="b1 filter_marketing"><i class="fas fa-paper-plane"></i></button>                                                
                    </div>
                </form>
            </div><?php
            $PaginationlimitData = App\Models\Paginationlimit::where('id', '=', 1)->first();
            ?>
            <form method="post" enctype="multipart/form-data" id="PageOrder">
                <input type="hidden" class="submit_url" value="{{ URL::to('tenant/setting/create') }}" />
                <input type="hidden" id="redirect_url" class="redirect_url" value="{{ URL::to('tenant/marketing') }}">
                {{ csrf_field() }}
                <div class="col-md-1">   <label for="page_number_list">Records per screen</label>              
                    <select class="input" name="marketing_lead_management" id="page_number">
                        <option <?php if (isset($PaginationlimitData->marketing_lead_management) AND $PaginationlimitData->marketing_lead_management == 10) {
                                echo "selected";
                            } ?> value="10">10</option>
                        <option <?php if (isset($PaginationlimitData->marketing_lead_management) AND $PaginationlimitData->marketing_lead_management == 20) {
                                echo "selected";
                            } ?> value="20">20</option>
                        <option <?php if (isset($PaginationlimitData->marketing_lead_management) AND $PaginationlimitData->marketing_lead_management == 50) {
                                echo "selected";
                            } ?> value="50">50</option>
                        <option <?php if (isset($PaginationlimitData->marketing_lead_management) AND $PaginationlimitData->marketing_lead_management == 100) {
                                echo "selected";
                            } ?> value="100">100</option>
                    </select>
                </div>            
                <div class="col-md-2">
                </div>
            </form>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h5>Marketing Lead List</h5> 
                </div>
                <div class="panel-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="checkfollowUpLeadAll" name="checkfollowUpLeadAll" class="selectfollowUpLeadAll"></th>
                                <th>Is Followup</th>
                                <th>
                                    @if($input['sort_type'] == 'asc' || request()->get('sort_type') == 'asc')
                                    <span class="sort-columns" data-type="desc" data-toggle="tooltip" data-placement="top" title="Desc" data-id="1" data-column="homeowner_name">
                                        <span>Homeowner Name</span>
                                        <i class="fas fa-sort-down"></i>
                                    </span>
                                    @else
                                    <span class="sort-columns" data-type="asc" data-toggle="tooltip" data-placement="top" title="Asc" data-id="1" data-column="homeowner_name">
                                        <span>Homeowner Name</span>
                                        <i class="fas fa-sort-up"></i>
                                    </span>
                                    @endif                                    
                                </th>
                                <th>
                                    @if($input['sort_type'] == 'asc' || request()->get('sort_type') == 'asc')
                                    <span class="sort-columns" data-type="desc" data-toggle="tooltip" data-placement="top" title="Desc" data-id="2" data-column="address">
                                        <span>Homeowner Address</span>
                                        <i class="fas fa-sort-down"></i>
                                    </span>
                                    @else
                                    <span class="sort-columns" data-type="asc" data-toggle="tooltip" data-placement="top" title="Asc" data-id="2" data-column="address">
                                        <span>Homeowner Address</span>
                                        <i class="fas fa-sort-up"></i>
                                    </span>
                                    @endif 
                                </th>
                                <th>
                                    @if($input['sort_type'] == 'asc' || request()->get('sort_type') == 'asc')
                                    <span class="sort-columns" data-type="desc" data-toggle="tooltip" data-placement="top" title="Desc" data-id="2" data-column="lead">
                                        <span>Lead</span>
                                        <i class="fas fa-sort-down"></i>
                                    </span>
                                    @else
                                    <span class="sort-columns" data-type="asc" data-toggle="tooltip" data-placement="top" title="Asc" data-id="2" data-column="lead">
                                        <span>Lead</span>
                                        <i class="fas fa-sort-up"></i>
                                    </span>
                                    @endif 
                                    
                                </th>
                                <th>
                                    @if($input['sort_type'] == 'asc' || request()->get('sort_type') == 'asc')
                                    <span class="sort-columns" data-type="desc" data-toggle="tooltip" data-placement="top" title="Desc" data-id="2" data-column="investor">
                                        <span>Investor</span>
                                        <i class="fas fa-sort-down"></i>
                                    </span>
                                    @else
                                    <span class="sort-columns" data-type="asc" data-toggle="tooltip" data-placement="top" title="Asc" data-id="2" data-column="investor">
                                        <span>Investor</span>
                                        <i class="fas fa-sort-up"></i>
                                    </span>
                                    @endif 
                                    
                                    </th>
                                <th>
                                    @if($input['sort_type'] == 'asc' || request()->get('sort_type') == 'asc')
                                    <span class="sort-columns" data-type="desc" data-toggle="tooltip" data-placement="top" title="Desc" data-id="2" data-column="notes_and_actions">
                                        <span>Notes and Actions</span>
                                        <i class="fas fa-sort-down"></i>
                                    </span>
                                    @else
                                    <span class="sort-columns" data-type="asc" data-toggle="tooltip" data-placement="top" title="Asc" data-id="2" data-column="notes_and_actions">
                                        <span>Notes and Actions</span>
                                        <i class="fas fa-sort-up"></i>
                                    </span>
                                    @endif 
                                </th>
                                <th>
                                    @if($input['sort_type'] == 'asc' || request()->get('sort_type') == 'asc')
                                    <span class="sort-columns" data-type="desc" data-toggle="tooltip" data-placement="top" title="Desc" data-id="2" data-column="investor_notes">
                                        <span>Investor Notes</span>
                                        <i class="fas fa-sort-down"></i>
                                    </span>
                                    @else
                                    <span class="sort-columns" data-type="asc" data-toggle="tooltip" data-placement="top" title="Asc" data-id="2" data-column="investor_notes">
                                        <span>Investor Notes</span>
                                        <i class="fas fa-sort-up"></i>
                                    </span>
                                    @endif 
                                    
                                    </th>
                                <th>
                                    @if($input['sort_type'] == 'asc' || request()->get('sort_type') == 'asc')
                                    <span class="sort-columns" data-type="desc" data-toggle="tooltip" data-placement="top" title="Desc" data-id="2" data-column="appt_email">
                                        <span>Appt Email</span>
                                        <i class="fas fa-sort-down"></i>
                                    </span>
                                    @else
                                    <span class="sort-columns" data-type="asc" data-toggle="tooltip" data-placement="top" title="Asc" data-id="2" data-column="appt_email">
                                        <span>Appt Email</span>
                                        <i class="fas fa-sort-up"></i>
                                    </span>
                                    @endif 
                                    
                                    </th>
                                <th>
                                    @if($input['sort_type'] == 'asc' || request()->get('sort_type') == 'asc')
                                    <span class="sort-columns" data-type="desc" data-toggle="tooltip" data-placement="top" title="Desc" data-id="2" data-column="appt_phone">
                                        <span>Appt Phone</span>
                                        <i class="fas fa-sort-down"></i>
                                    </span>
                                    @else
                                    <span class="sort-columns" data-type="asc" data-toggle="tooltip" data-placement="top" title="Asc" data-id="2" data-column="appt_phone">
                                        <span>Appt Phone</span>
                                        <i class="fas fa-sort-up"></i>
                                    </span>
                                    @endif 
                                    
                                    </th>

                                <th>
                                    @if($input['sort_type'] == 'asc' || request()->get('sort_type') == 'asc')
                                    <span class="sort-columns" data-type="desc" data-toggle="tooltip" data-placement="top" title="Desc" data-id="2" data-column="marketing_email">
                                        <span>Marketing Email</span>
                                        <i class="fas fa-sort-down"></i>
                                    </span>
                                    @else
                                    <span class="sort-columns" data-type="asc" data-toggle="tooltip" data-placement="top" title="Asc" data-id="2" data-column="marketing_email">
                                        <span>Marketing Email</span>
                                        <i class="fas fa-sort-up"></i>
                                    </span>
                                    @endif 
                                    
                                    </th>
                                <th>
                                    @if($input['sort_type'] == 'asc' || request()->get('sort_type') == 'asc')
                                    <span class="sort-columns" data-type="desc" data-toggle="tooltip" data-placement="top" title="Desc" data-id="2" data-column="marketing_address">
                                        <span>Marketing Address</span>
                                        <i class="fas fa-sort-down"></i>
                                    </span>
                                    @else
                                    <span class="sort-columns" data-type="asc" data-toggle="tooltip" data-placement="top" title="Asc" data-id="2" data-column="marketing_address">
                                        <span>Marketing Address</span>
                                        <i class="fas fa-sort-up"></i>
                                    </span>
                                    @endif 
                                    
                                    </th>

                                @forelse($tags as $key=>$tag)
                                <th title="{{ $tag->tag_id }}">{{ $tag->tag_name }}</th>
                                @empty
                                @endif
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($marketings as $key=>$marketing)
                            <tr>
                                <td>
                                    <input type="checkbox" class="followUpDeleteLead" id="checkbox{{ $marketing->id }}" name="followup_ids" value="{{ $marketing->id }}">
                                </td>
                                <td>
                                    <button class="btn btn-primary isFollowUp btn-sm" data-id="{{ $marketing->id }}"><i class="fas fa-level-up-alt"></i> Follow-Up</button>
                                </td>
                                <td>{{ $marketing->title }}</td>
                                <td>{{ $marketing->address }}</td>
                                <td>{{$marketing->lead_first_name}} {{$marketing->lead_last_name}}</td>
                                <td>{{$marketing->in_first_name}} {{$marketing->in_last_name}}</td>
                                <td>
                                    <?php
                                    if ($marketing->admin_notes != '') {
                                        echo substr($marketing->admin_notes, 0, 30);
                                    }
                                    ?>
                                </td>
                                <td>
                                <?php
                                if ($marketing->investore_note != '') {
                                    echo substr($marketing->investore_note, 0, 30);
                                }
                                ?>
                                </td>
                                <td>{{ $marketing->appt_email }}</td>
                                <td>{{ $marketing->appt_phone }}</td>
                                <td><a href="#" data-name="marketing_mail" class="detailEmailupdate editable editable-click" data-type="email" data-value="{{ $marketing->marketing_mail }}" data-pk="{{ $marketing->id }}" data-original-title="Enter Email:" title="">{{ $marketing->marketing_mail }}</a></td>
                                <td><a href="#" data-name="marketing_address" class="detailEmailupdate editable editable-click" data-type="text" data-mail="{{ $marketing->marketing_mail }}"  data-value="{{ $marketing->marketing_address }}" data-pk="{{ $marketing->id }}" data-original-title="Enter Address:" title="">{{ $marketing->marketing_address }}</a></td>
                                <?php
                                $user_email = $marketing->marketing_mail;
                                if ($user_email == '') {
                                    $user_email = $marketing->appt_email;
                                }
                                ?>

                                @forelse($tags as $key=> $tag)


                                <?php
                                $cuser = App\Models\CampaignUser::where('email_address', '=', $user_email)->first();
                                $user_data = json_decode($cuser->user_data);
                                $tags_data = [];
                                if (isset($user_data->tags)) {
                                    foreach ($user_data->tags as $exiting_tag) {
                                        $tags_data[] = $exiting_tag->id;
                                    }
                                }
                                ?>
                                <td>                                    
                                    <input class="tagEdit" type="checkbox" name="{{ $marketing->id }}[]" 
                                    <?php
                                    if (in_array($tag->tag_id, $tags_data)) {
                                        echo "checked";
                                    }
                                    ?> 
                                           data-toggle="toggle"   value="{{$tag->tag_id}}" data-offstyle="danger" data-size="md"
                                           @if($user_email == '')
                                           data-id="false"
                                           @else
                                           data-id="{{ $marketing->id }}"
                                           @endif

                                           data-revertid="{{ $marketing->id }}{{$tag->tag_id}}"

                                           >
                                </td>

                                @empty
                                @endforelse
                                <td>  
                                    <?php
                                    $marketing_details = \App\Models\FollowingLead::where('lead_id', '=', $marketing->lead_id)->first();
                                    ?>
                                    <a href="{{ url('tenant/marketing-lead/'.$marketing_details->id.'/edit')}}">
                                        Edit
                                    </a>                                    
                                </td>
                            </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                    {{ $marketings->appends(request()->input())->links() }}                    
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Marketing Email / Appt Email</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                If you need to set tags for this lead then you have to enter Marketing Email or Appt Email.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>        
            </div>
        </div>
    </div>
</div>
@include('tenant.include.footer')
<script type="text/javascript">
    
              
        
    $(document).ready(function () {  
        var searchInput = document.getElementById('marketing_search_text');
        searchInput.addEventListener('keydown', function(event) {
                if (event.keyCode === 13) {
                     return false;
                }
        });

        $('.followingLeadsAllDelete').click(function (e) {
            e.preventDefault();

            var leadsIds = $('.followUpDeleteLead:checked').map((_, e) => e.value).get();

            if(leadsIds.length == 0){
                alert('please select leads.');
            }else{
                if(confirm("Are you sure you want to delete this?")){
                    ajaxCall('POST', "{{ route('tenant.marketing-lead.list.delete') }}", {ids:leadsIds}, {}).then(function (res) {
                        searchDataLead();
                        toastr.success('Follow-up leads deleted successfull.','Success Alert', {timeOut: 5000});
                    });
                }
                else{
                    return false;
                }
            }
        });
    
        $('.search-text').focusout(function (e) {
            e.preventDefault();

            searchDataLead();
       });
       
        $('#marketing_search_text').click(function (e) {
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
        
        $('.clear_search').click(function (e) {
            $('.search-text').val('');
            e.preventDefault();
            searchDataLead();
        });
        
        function searchDataLead(edittable = 0) {         
            var loadImage = '{{ asset('image/loder.gif') }}';            
            var searchText = $('.search-text').val();
            var userIdSearch = $('.lead_users option:selected').map((_, e) => e.value).get();
            var leadStatusId = $('.lead_status option:selected').map((_, e) => e.value).get();
            var sortColumn = localStorage.getItem("sortColumn");
            var sortType = localStorage.getItem("sortType");
            var leadsIds = $('.followUpDeleteLead:checked').map((_, e) => e.value).get();

            data = {
                search_text:searchText,
                user_id_search:userIdSearch,
                leads_ids:leadsIds,
                lead_status_id:leadStatusId,
                sort_column:sortColumn,
                sort_type:sortType
                
            };
            
            var qString = $.param(data);
            var url = "{{URL::to('tenant/marketing?')}}" + qString;
            window.open(url, '_self')
	}                
        
        $(document).on('click', '#export-lead', function () {
            var searchText = $('.search-text').val();
            var userIdSearch = $('.lead_users option:selected').map((_, e) => e.value).get();
            var leadStatusId = $('.lead_status option:selected').map((_, e) => e.value).get();
            var sortColumn = localStorage.getItem("sortColumn");
            var sortType = localStorage.getItem("sortType");
            var leadsIds = $('.followUpDeleteLead:checked').map((_, e) => e.value).get();            

            data = {
                search_text:searchText,
                user_id_search:userIdSearch,
                leads_ids:leadsIds,
                lead_status_id:leadStatusId,
                sort_column:sortColumn,
                sort_type:sortType,
                export: true,
                is_history_export: 1
            };
            
            var qString = $.param(data);
            var url = "{{URL::to('tenant/marketing/export?')}}" + qString;
            window.open(url, '_self')
        });
    
        $('body').on('change','#page_number', function () {
            
            var redirectUrl = $("#redirect_url").val(); 
                    
                    var search = $('#marketing_search_text').val();                    
                    var newText = "?search_text=" + search ; 
                    redirectUrl += newText; 
                    
                    var lead_users = $('.lead_users option:selected').map((_, e) => e.value).get();
                    var newText = "&user_id_search=" + lead_users ; 
                    redirectUrl += newText; 
                    
                    $("#redirect_url").val(redirectUrl); 
                    
                    var redirectUrl = $("#redirect_url").val();      
                                        
                    
                    
            $("#PageOrder").submit();
        });
        
        $('.selectfollowUpLeadAll').change(function (e) {            
            e.preventDefault();
            var checked = $(this).prop('checked');            
            if(checked){
                $('.followUpDeleteLead').prop('checked','checked');
            }else{
                $('.followUpDeleteLead').removeAttr('checked','checked');
            }

        });
                  
        $('body').on('click','.isFollowUp', function () {
		var obj = $(this);
		var id = obj.attr('data-id');

		$.ajax({
	        url: '{{ route('tenant.marketing-lead.followup') }}',
	        type: 'post',
	        data:{
	        	_token:$('meta[name="csrf-token"]').attr('content'),
	        	 id:id,
	        },
	        success: function (data) {
                    obj.parent().parent().hide(1000)
	        	toastr.success('Followup lead moved successfull.','Success Alert', {timeOut: 5000});
	        }
	    });
	});
        
        $('body').on('change', '.tagEdit', function (event) {
            var itemId = $(this).data('id');
            var itemRevertId = $(this).data('revertid');
            
            if(itemId == false){
                $('input[type="checkbox"][data-revertid="' + itemRevertId + '"]').prop('checked', false);         
                $('input[data-revertid="' + itemRevertId + '"]').click();          
                $('#exampleModal').modal('show');                
                return false;
            }
            var selectedValues = [];
            $("input[name='" + itemId + "[]']:checked").each(function () {
                selectedValues.push($(this).val());
            });
            
            $.ajax({
            url: '{{ route('tenant.marketing.tag.status.update') }}',
            type: 'post',
            dataType: 'json',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                marketing_id: itemId,
                checked_tag: selectedValues,
            },
            })
            .done(function () {
                toastr.success('Tags updated successfully in Marketing email or Appt Email.');
            });

        });
    });

    $('body').on('change', '.campaignedit', function (event) {
        event.preventDefault();
        var obj = $(this);
        var id = obj.attr('data-id');
        var champid = obj.attr('data-champid');

        if (obj.prop('checked') == true) {
            var value = 1;
        } else {
            var value = 0;
        }

        $.ajax({
            url: '{{ route('tenant.marketing.campaign.status.update') }}',
            type: 'post',
            dataType: 'json',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: id,
                champid: champid,
                value: value,
            },
        })
        .done(function () {
            toastr.success('Campaign status updated successfully');
        });

    });
    $('.detailEmailupdate').editable({
        send: 'always',
        url: "/tenant/marketing/email/editable",
        success: function (response) {
            toastr.success('Marketing mail updated successfully.');
        }
    });
</script>
