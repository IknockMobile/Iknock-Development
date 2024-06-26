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
            <div class="cust-head"><i class="fas fa-clipboard-list"></i> Purchase Conversion Rate</div>
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
                    <th>SR NO</th>
                    <th>Lead Id</th>
                    <th>Homeowner Name</th>
                    <th>Homeowner Address</th>                    
                    <th>Purchase Date</th>
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
                            <td>
                                {{$x}}
                            </td>
                            <td>
                                {{$data->lead_id}}
                            </td>
                            <td>
                                {{$data->lead_title}}
                            </td>
                            <td>
                                @if(!empty($data->lead_formatted_address))
                                {{$data->lead_formatted_address}}
                                @else
                                {{$data->lead_address}}
                                @endif

                            </td>                            
                            <td>
                                <?php
                                $data_source = '';
                                $purchaeData = \App\Models\PurchaseLead::where('lead_id', '=', $data->lead_id)
                                        ->join('purchase_custom_fields', 'purchase_leads.id', 'purchase_custom_fields.followup_lead_id')
                                        ->where('purchase_custom_fields.followup_view_id', '=', 29)
                                        ->first();
                                if (!empty($purchaeData->field_value)) {
                                    echo '<span title="Purchase screen">' . $purchaeData->field_value . '</span>';
                                    $data_source = 'Purchase Management';
                                } else {
                                    $FollowingLead = \App\Models\FollowingLead::where('lead_id', '=', $data->lead_id)
                                            ->join('following_custom_fields', 'following_leads.id', 'following_custom_fields.followup_lead_id')
                                            ->where('following_custom_fields.followup_view_id', '=', 29)
                                            ->first();
                                    if (!empty($FollowingLead->field_value)) {
                                        echo '<span title="Following screen">' . $FollowingLead->field_value . '</span>';
                                        $data_source = 'Following Management';
                                    } else {
                                        echo '<span title="From History Status screen">' . date('m/d/Y', strtotime($data->created_at)) . '</span>';
                                        $data_source = 'History Status';
                                    }
                                }
                                ?>                                                                                               
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
$('.agents_list').change(function (e) {
e.preventDefault();
selectInputpage();
});
$('.selectAllKnocks').change(function (e) {
e.preventDefault();
selectInputpage();
});
function selectInputpage() {

var url = '{{ URL::to('tenant/dashboard/purchase/list') }}';
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
var url = '{{ URL::to('tenant/dashboard/purchase/list') }}';
window.location.href = url;
}

$('.export-btn').click(function (e) {
e.preventDefault();
var date = $(this).attr('data-date');
var user_id = $('.agents_list').selectpicker('val');
if (Array.isArray(user_id)) {
user_id = user_id.join();
}

var url = '{{ URL::to('tenant/dashboard/purchase/list/export') }}';
var idlist = [];
$('.khock-list-checkbox').each(function (index, el) {
var obj = $(this);
var inputChecked = obj.prop("checked");
if (inputChecked == true) {
var id = obj.attr('data-id');
idlist.push(id);
}
});
url = url + '?date=' + date + '&idlist=' + idlist + '&filter_user_id=' + user_id;
window.location.href = url;
});


</script>