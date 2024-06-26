@include('tenant.include.header')
@include('tenant.include.sidebar')
<style type="text/css">
    .bootstrap-select > .dropdown-toggle {
        padding: 0px;
        border: none;
        border-radius: 0px;
        background: #113f85;
        color: white !important;
        padding: 6px 20px;
        font-weight: bold;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    .dropdown-menu>li>a {

        padding: 3px 7px;}
        .mychart{
            margin-right: 10px;
            border-radius: 5px;
        }
        .title-box{
            box-shadow: 0px 0px 1px black;
            margin-top: 10px;
            margin-bottom: 10px;
            padding: 2px 1px;
        }
        .title-box h5{
            margin: 5px;
        }
        .ml-0{
            margin-left: 0px !important;
        }
    </style>
    <div class="right_col" role="main">
        <div class="row nomargin" id="content-heading">
            <!--content-heading here-->
            <div class="col-md-3">
                <div class="dropdown">
                    <button class="btn  dropdown-toggle add-bt" type="button" data-toggle="dropdown"
                    style="background: transparent;">
                    <span class="cust-head">Dashboard</span>
                    <span class="caret" style="color:black;padding-bottom:10px;"></span></button>
                    <ul class="dropdown-menu link-menu">
                        <li><a href="{{ URL::to('/tenant/dashboard') }}">Dashboard</a></li>
                        <li><a href="{{ URL::to('/tenant/team-performance/comm_report') }}">Commission Report</a></li>
                        <li><a href="{{ URL::to('/tenant/team-performance/team_report') }}">Team Report</a></li>
                        <li><a href="{{ URL::to('/tenant/team-performance/user_report') }}">User Lead Report</a></li>
                        <li><a href="{{ URL::to('/tenant/team-performance/user_report_type') }}">Lead Type Report</a></li>
                        <li><a href="{{ URL::to('/tenant/team-performance/user_report_status') }}">Lead Status Report By Historical Knock</a></li>
                        <li><a href="{{ URL::to('/tenant/team-performance/user_report_status_current') }}">Current Active Lead Status Summary</a></li>
                        <li><a href="{{ URL::to('/tenant/team-performance/user_report_followup_status') }}">Follow Up Lead Status Report</a></li>
                        <!--<li><a href="{{ URL::to('/tenant/team-performance/dashboard_knocks_statistics') }}">Dashboard Knocks Statistics Report</a></li>-->
                    </ul>
                </div>
            </div>


            <div class="col-md-9">
                <form class="">
                    <div class="col-md-3 form-group">
                        <input type="text" id="e2" name="time_slot" value="{{ request()->get('date') ?? '' }}" placeholder="Start Date" value="{{ request()->get('start_date') ?? '' }}" class="startDate input date_range1 duration" value="select date" name="date_range" autocomplete="off">

                    {{--     <select class="form-control summary duration" name="time_slot">
                        <option disabled="disabled" selected="selected">Select Time</option>
                    <option value="all_time">All Time</option>
                        <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                                <option value="week">This Week</option>
                                    <option value="last_week">Last week</option>
                                        <option value="month">This Month</option>
                    <opt                    ion value="last_month">Last month</option>
                    <option                     value="year">This Year</option>
                    <option valu                    e="last_year">Last year</option>
                </select> --}}

            </div>
            <div class="col-md-3 form-group">

                @if(count($data['agent']))


                <select class="form-control summary agents_list selectpicker"
                data-live-search="true" data-actions-box="true" title="Select User"
                name="target_user_id" value="" multiple>
                @foreach($data['agent'] as $agent )
                <option value="{{ $agent->id }}">{{$agent->first_name}} {{$agent->last_name}}</option>
                @endforeach
            </select>

            @else
            <select class="form-control summary type_list selectpicker"
            data-live-search="true" data-actions-box="true" title="Select User" name="type_id"
            value="" multiple>

            <option value="" disabled="disabled" class="disabled">No User Found</option>
        </select>

        @endif

    </div>

    <div class="col-md-3 form-group">

        @if(count($data['type']))


        <select class="form-control summary type_list selectpicker"
        data-live-search="true" data-actions-box="true" title="Select Lead Type" name="type_id"
        value="" multiple>

        @foreach($data['type'] as $type )

        <option value="{{ $type->id }}">{{$type->title}}</option>
        @endforeach
    </select>

    @else
    <select class="form-control summary type_list selectpicker"
    data-live-search="true" data-actions-box="true" title="Select Lead Type" name="type_id"
    value="" multiple>

    <option value="" disabled="disabled">No Type Found</option>
</select>
@endif

</div>


<div class="col-md-2 form-group">

<select class="form-control summary value" name="type">
    <option disabled="disabled" selected="selected">Select Unit</option>
    <option value="percentage">Percentage</option>
    <option value="amount"> Unit</option>
</select>

</div>
<div class="col-md-1">

<button class="b1 save"><i class="fas fa-paper-plane"></i></button>
</div>
</form>
</div>

        <!--  <div class="col-md-2">
            <input type="button" name="" class="btn btn-info b1 save" value="Apply">
        </div> -->


    </div>

    <hr class="border">
    <div class="row nomargin" id="pg-content">


        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h5>Lead Status</h5>
                </div>
                <div class="panel-body">

                    <div id="container3" style="width:auto; height: 400px; margin: 0 auto"></div>
                </div>
            </div>
            {{--  <div class="row">
                <div class="col-md-12 ml-0">
                    <div class="title-box">
                    </div>
                </div>
            </div>
            <div class="row  text-right" style="margin-right: 10px;">
                <!-- <div id="container3" style=""></div> -->

            </div> --}}
        </div>

        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h5>User Commissions</h5>
                </div>
                <div class="panel-body">

                    <div id="container2" style="width:auto; height: 400px; margin: 0 auto"></div>
                </div>
            </div>

            {{--  <div class="row">
               <div class="col-md-12">
                           <div class="title-box">
            </div>
                </div>
</div>
<div            class="row" style="">        
</div> --}}
</div>




  </div>
   <div class="row mychart">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h5>Homeowner Contact Performance</h5>
                </div>
                <div class="panel-body">
                    <div id="home-contact-container">
                        
                    </div>

                    <table class="table table-striped jambo_table" id="scroll" id="        " style="position: relative;top: 0px;">
                        <thead>
                            <tr class="headings">
                                <td class="text-left">S.no</td>
                                <td class="text-left">Users</td>
                                <td class="text-left">Knocks</td>
                                <td class="text-left">Appointments</td>

                            </tr>
                        </thead>
                        <tbody class="team_status">
                        </tbody>

                        <tfoot class="tfoot">
                            <tr>
                                <td><b>Total</b></td>
                                <td></td>
                                <td class="text-left" id="knock_sum"></td>
                                <td class="appointment_class text-left" id="appointment_sum"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>           
    </div>

</div>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="{{asset('assets/js/tenant-js/chart.js')}}"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>
<script>
$(document).ready(function () {
    var dateRange = $("#e2").daterangepicker();
    var columns = ['status_agent_name', 'status_lead_count', 'appointment_count'];
    loadGridWitoutAjax('GET', base_url + "/tenant/user/lead/status/report", {}, {}, columns, '.team_status', 'result', false);
    ajaxCall('GET', base_url + "/tenant/user/lead/status/report").then(function (res) {
        var knock_sum = '';
        var appointment_sum = '';
        knock_sum += res.data.lead_count;

        var userName = [];
        var userKhock = [];

        $.each(res.data.result, function(index, val) {
            userName.push(val.agent_name);
            userKhock.push(val.lead_count);
        });

        userKhock = [{'name':'Knocks',data:userKhock}]; 

        ColChart(userName,res.data.knock_details_main);

        appointment_sum += res.data.appointment_count;
        $('#knock_sum').html(knock_sum);
        $('#appointment_sum').html(appointment_sum);
    })

    function ColChart(user_names, status) {

        Highcharts.chart('home-contact-container', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Stacked column chart'
            },
            xAxis: {
                categories: user_names,
                title: {
                    text: 'Homeowner Contact Performance',
                },
            },
            yAxis: {
                title: {
                    text: ''
                },
                stackLabels: {
                    enabled: true,
                    style: {
                        fontWeight: 'bold',
                        color: (// theme
                            Highcharts.defaultOptions.title.style &&
                            Highcharts.defaultOptions.title.style.color
                            ) || '#000'
                    }
                }
            },
            legend: {
                align: 'center',
                x: 10,
                verticalAlign: 'bottom',
                y: 25,
                maxHeight: 40,
                floating: true,
                backgroundColor: Highcharts.defaultOptions.legend.backgroundColor || 'white',
                borderColor: '#CCC',
                borderWidth: 0,
                shadow: false,

            },
            tooltip: {
                headerFormat: '<b>{point.x}</b><br/>',
                pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                    dataLabels: {
                        enabled: true
                    }
                }
            },
            series:status

        });
    }


    loadChart2('GET', base_url + "/tenant/user/commission/report", {}, {});
    loadChart3('GET', base_url + "/tenant/user/lead/stats/report", {}, {});
    $(document).on('click', '.save', function(e) {
        e.preventDefault();
        var user_id = $('.agents_list').selectpicker('val');
        if (Array.isArray(user_id)) {
            user_id = user_id.join();
        }

        var type_id = $('.type_list').selectpicker('val');
        if (Array.isArray(type_id)) {
            type_id = type_id.join();
        }

        var type = $('.value').val();
        var time_slot = $('.duration').val();
        var data = {target_user_id: user_id, type_id: type_id, type: type, time_slot: time_slot};
        loadGridWitoutAjax('GET', base_url + "/tenant/user/lead/status/report", data, {}, columns, '.team_status', 'result', false);
        ajaxCall('GET', base_url + "/tenant/user/lead/status/report", data).then(function (res) {

            var knock_sum = '';
            var appointment_sum = '';
            knock_sum += res.data.lead_count;
            appointment_sum += res.data.appointment_count;
            $('#knock_sum').html(knock_sum);
            $('#appointment_sum').html(appointment_sum);
        })

        loadChart2('GET', base_url + "/tenant/user/commission/report", data, headers = {});
        loadChart3('GET', base_url + "/tenant/user/lead/stats/report", data, headers = {});
    })

    function loadChart2(method, url, data, header) {
        ajaxCall(method, url, data, header).then(function (res) {
            console.log(url);
            if (res.code == 200) {
                var title = [];
                var value = [];
                var total_commission = [];
                var svg = [];
                var record = res.data;
                if (record.length > 0) {
                    for (var i = 0; i < record.length; i++) {
                        var title_key = record[i].title;
                        var total_commission_key = record[i].total_commission;
                        var value_key = record[i].value;
                        var svg_key = record[i].svg.fill;
                        title.push(title_key);
                        total_commission.push(total_commission_key);
                        value.push(value_key);
                        svg.push(svg_key);
                    }
                    highchart('container2', title, value, total_commission, svg);
                }

                else {

                    $("#container2").html("<img style='width:100%;' src='{{asset("assets / images / graph.png")}}''>");
                }


            }

        })
    }

    function loadChart3(method, url, data, header) {
        ajaxCall(method, url, data, header).then(function (res) {

            if (res.code == 200) {
                var title = [];
                var value = [];
                var colour = [];
                var record = res.data;
                if (record.length > 0) {
                    for (var i = 0; i < record.length; i++) {
                        var title_key = record[i].title;
                        var value_key = record[i].value;
                        var colour_key = record[i].colour_code;
                        title.push(title_key);
                        value.push(value_key);
                        colour.push(colour_key);
                    }
                    piechart('container3', title, value, colour);
                }
                else {
                    $("#container3").html("<img style='width:100%;' src='{{asset("assets / images / graph.png")}}''>");
                }
            }
        })
    }

})
</script>
@include('tenant.include.footer')
