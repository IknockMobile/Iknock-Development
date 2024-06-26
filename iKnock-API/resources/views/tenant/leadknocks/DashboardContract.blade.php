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
            <div class="cust-head"><i class="fas fa-clipboard-list"></i>  Contract Conversion Rate </div>
        </div>
        <div class="col-md-8">
            <form action="{{ URL::to('tenant/lead/knocks/user/list') }}" method="get" class="fillter-form">
                <div class="col-md-3">
                    <div class="form-group">                        
                        <input type="text" id="e2" name="date" value="{{ request()->get('date') ?? '' }}" placeholder="Start Date" value="{{ request()->get('start_date') ?? '' }}" class="startDate input date_range1" value="select date" name="date_range" autocomplete="off">
                    </div>
                </div>                    
            </form>
        </div>
        <div class="col-md-2">
            <button data-date="{{ request()->get('date') ?? ''  }}" data-user="{{ request()->get('userId') ?? '' }}" class="mt-4 export-btn btn btn-primary"><i class="fas fa-file-export"></i> Export</button>
        </div>
    </div>
    <div class="col-md-12 mt-2">
        <div class="panel panel-default">
            <div class="panel-heading">
                knocks List
            </div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <thead>         
                    <th>Id</th>
                    <th>Lead Id</th>
                    <th>Homeowner Name</th>
                    <th>Homeowner Address</th>                    
                    <th>Contract Date</th>  
                    <th>Purchase Date</th>  
                    <th>Status History Created</th> 
                    <th>Status</th> 
                    <th>Data Source</th> 
                    </thead>
                    <tbody>
                        <?php
                        if (isset($_GET['page'])) {
                            $page = $_GET['page'];
                            $page = $page - 1;
                            $x = $page * 10;
                        } else {
                            $page = $_GET['page'];
                            $x = 0;
                        }
                        ?>
                        @forelse($dashboardData as $key=>$data)

                        <tr><?php $x = $x + 1; ?>
                            <td title="{{$data->id}}">
                                {{$x}}
                            </td>
                            <td>
                                {{$data->lead_id}}
                            </td>
                            <td>
                                {{$data->lead_title}}
                            </td>
                            <td>
                                @if($data->lead_formatted_address != null)
                                    {{$data->lead_formatted_address}}
                                @else
                                    {{$data->lead_address}}
                                @endif
                                
                            </td>                            
                            <td>     
                                <?php $data_source = ''; ?>
                                
                                @if(isset($data->p_contract_date) AND $data->p_contract_date != null)
                                    {{date('m/d/Y',strtotime($data->p_contract_date))}} 
                                @php $data_source = 'Purchase Management'; @endphp
                                @else
                                    {{date('m/d/Y',strtotime($data->contract_date))}}                                 
                                @php  $data_source = 'Following Management'; @endphp
                                @endif 
                            </td> 
                            <td>                                     
                                @if(isset($data->pl_purchase_date) AND $data->pl_purchase_date != null)
                                {{$data->pl_purchase_date}}                                
                                @else
                                {{$data->fl_purchase_date}}                                
                                @endif 
                            </td> 
                            <td> 
                                @if($data->followup_status_id == 11 OR $data->followup_status_id == 1)
                                {{date('m/d/Y',strtotime($data->created_at))}}
                                @endif
                            </td>                            
                            <td> 
                                @if($data->followup_status_id == 11)
                                    Purchased
                                @elseif($data->followup_status_id == 1)
                                    Contract
                                @else
                                
                                @endif
                            </td>                                                        
                            <td>
                                <?php echo $data_source; ?>
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
                {!! $dashboardData->appends(request()->query())->links() !!}
                @endif
            </div>
        </div>
    </div>
</div>
</div>
@include('tenant.include.footer')
<script src="https://cdn.rawgit.com/Eonasdan/bootstrap-datetimepicker/e8bddc60e73c1ec2475f827be36e1957af72e2ea/src/js/bootstrap-datetimepicker.js"></script>
<link href='https://cdn.rawgit.com/Eonasdan/bootstrap-datetimepicker/e8bddc60e73c1ec2475f827be36e1957af72e2ea/build/css/bootstrap-datetimepicker.css' rel='stylesheet'/>
<script>
var selectAllvalue = '{{ request()->get('select_all') }}';
if (selectAllvalue == 1) {
$('.khock-list-checkbox').attr('checked', 'checked');
}

$("body").on('click', '.remove-crud', function (event) {
$.ajaxSetup({
headers: {
'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
}
});
event.preventDefault();
var url = $(this).attr('data-action');
var id = $(this).attr('data-id');
var data = {id: id};
if (confirm("Are you sure you want to delete this?")) {
ajaxCall('POST', base_url + '/tenant/history/' + id + '/delete', data, {}).then(function (res) {
location.reload();
});
} else {
return false;
}
});
var dateRange = $("#e2").daterangepicker();
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
$('.agents_list').change(function (e) {
e.preventDefault();
selectInputpage();
});
function selectInputpage() {

var url = '{{ URL::to('tenant/dashboard/contract/list') }}';
var user_id = $('.agents_list').selectpicker('val');
if (Array.isArray(user_id)) {
user_id = user_id.join();
}

var date = $('.startDate').val();
if ($('.selectAllKnocks').is(':checked')) {
var selectAllValue = 1;
} else {
var selectAllValue = 0;
}


if (date != '' || userid != '') {
url = url + '?date=' + date + '&select_all=' + selectAllValue + '&filter_user_id=' + user_id;
window.location.href = url;
}

window.location.href = url;
}



function myTimer() {
var url = '{{ URL::to('tenant/lead/knocks/user/list') }}';
window.location.href = url;
}

$('.export-btn').click(function (e) {
e.preventDefault();
var date = $(this).attr('data-date');
var url = '{{ URL::to('tenant/dashboard/contract/list/export') }}';
var user_id = $('.agents_list').selectpicker('val');
if (Array.isArray(user_id)) {
user_id = user_id.join();
}

var date = $('.startDate').val();
if ($('.selectAllKnocks').is(':checked')) {
var selectAllValue = 1;
} else {
var selectAllValue = 0;
}


if (date != '' || user_id != '') {
url = url + '?date=' + date + '&select_all=' + selectAllValue + '&filter_user_id=' + user_id;
window.location.href = url;
}

window.location.href = url;
});


</script>