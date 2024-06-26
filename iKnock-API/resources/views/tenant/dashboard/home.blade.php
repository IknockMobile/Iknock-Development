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
        .mt-4{
            margin-left: 12px !important;
        }
        .label-counts{
            font-size: 15px !important;
            text-shadow: 0px 0px 3px black;
            box-shadow: 0px 0px 1px black;
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
                        <li><a href="{{ URL::to('/tenant/team-performance/knock_dashboard') }}">Knocks Statistics Report</a></li>
                        <li><a href="{{ URL::to('/tenant/team-performance/knock_dashboard/not/contacted') }}">Not Contacted Knocks Statistics Report</a></li>
                        <li><a href="{{ URL::to('/tenant/team-performance/knock_dashboard/day_report') }}">Best Time Of Day Knocks Statistics Report</a></li>
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
   <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-6">
                            Homeowner Contact Performance
                        </div>
                        <div class="col-md-6 text-right">
                            <h5 class="label label-primary label-counts"><i class="fas fa-chart-bar"></i> Total: <span id="totalChartCount"></span></h5>
                        </div>
                    </div>                    
                </div>
                <div class="panel-body">
                    <div id="home-contact-container">
                        
                    </div>
                </div>
            </div>
        </div>           
    </div>

    <div class="row">
        
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-6">
                            Homes Purchased
                        </div>
                        <div class="col-md-6 text-right">
                            
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <table class="table table-striped jambo_table" id="scroll" id="" style="position: relative;top: 0px;">
                        <thead>
                            <tr class="headings">
                                <td class="text-left">Homeowner Address</td>
                                <td class="text-left">Purchase date</td> 
                                <td class="text-left">Invester Name</td>       
                            </tr>
                        </thead>
                        <tbody class="purchase_closed">
                        </tbody>                                
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-6">
                            Contracts Closed
                        </div>
                        <div class="col-md-6 text-right">
                            
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <table class="table table-striped jambo_table" id="scroll" id="" style="position: relative;top: 0px;">
                        <thead>
                            <tr class="headings">
                                <td class="text-left">Homeowner Address</td>
                                <td class="text-left">Contract date</td> 
                                <td class="text-left">Invester Name</td>       
                            </tr>
                        </thead>
                        <tbody class="contracts_closed">
                        </tbody>                                
                    </table>
                </div>
            </div>
        </div>
    </div>
    
     <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-6">
                            Commission Payments by Month
                        </div>
                        <div class="col-md-6 text-right">
                            <h5 class="label label-primary label-counts"><i class="fas fa-file-invoice-dollar"></i> Total Payments: <span class="commission_new_count_month"></span></h5>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <div id="container-by-month"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-6">
                            Commission Payments by Users
                        </div>
                        <div class="col-md-6 text-right">
                            <label for="" class="label label-primary label-counts"><i class="fas fa-hand-holding-usd"></i> Total Payments: <span class="commission_new_count"></span></label>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <div id="container-by-user"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <a href="{{url('tenant/dashboard/purchase/list')}}" id="PurchaseLink"> Purchase Conversion Rate </a>
                </div>
                <div class="panel-body purchase-box text-center"></div>                
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <a href="{{url('tenant/dashboard/contract/list')}}" id="ContractLink"> Contract Conversion Rate</a>
                </div>
                <div class="panel-body contract-box text-center"></div>
                
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <a href="{{url('tenant/dashboard/appointments_requested/list')}}" id="AppointmentsRequestedLink">  Appointments Requested Conversion Rate <a>
                </div>
                <div class="panel-body scheduled-box text-center"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading ">
                    <a href="{{url('tenant/dashboard/appointments_kept/list')}}" id="AppointmentsKEPTLink">Appointments KEPT Conversion Rate</a>
                </div>
                <div class="panel-body kept-box text-center"></div>                
            </div>
        </div>
    </div>
    <div class="row mychart">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Homeowner Contact Performance
                </div>
                <div class="panel-body">
                    <table class="table table-striped jambo_table" id="scroll" id="        " style="position: relative;top: 0px;">
                        <thead>
                            <tr class="headings">
                                <td class="text-left">S.no</td>
                                <td class="text-left">Users</td>
                                <td class="text-left">Knocks</td>
                                <td class="text-left">Appt Requests</td>
                                <td class="text-left">Appt Kept</td>

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
                                <td class="appointment_class text-left" id="appointment_kept_sum"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>
{{-- <script src="{{asset('assets/js/tenant-js/chart.js')}}"></script> --}}
<script>
$(document).ready(function () {
    
    var dateRange = $("#e2").daterangepicker().daterangepicker('setRange', {
      start: new Date(new Date().getFullYear(), 0, 1), 
      end: new Date()
    });
    
    var time_slot = $('.duration').val();
    var data = {time_slot: time_slot};
    
    var PurchaseLink = $("#PurchaseLink");
    PurchaseLink.attr("href", "<?php echo url('tenant/dashboard/purchase/list'); ?>" + "?date=" + time_slot);

    var ContractLink = $("#ContractLink");
    ContractLink.attr("href", "<?php echo url('tenant/dashboard/contract/list'); ?>" + "?date=" + time_slot);

    var AppointmentsRequestedLink = $("#AppointmentsRequestedLink");
    AppointmentsRequestedLink.attr("href", "<?php echo url('tenant/dashboard/appointments_requested/list'); ?>" + "?date=" + time_slot);

    var AppointmentsKEPTLink = $("#AppointmentsKEPTLink");
    AppointmentsKEPTLink.attr("href", "<?php echo url('tenant/dashboard/appointments_kept/list'); ?>" + "?date=" + time_slot);
        
    var columns = ['status_agent_name', 'status_lead_count', 'appointment_count', 'appointment_kept_count'];

    loadGridWitoutAjaxNew('GET', base_url + "/tenant/user/lead/status/report", data, {}, columns, '.team_status', 'result', false);
//    chatCall(data);

    function chatCall(columns = {}){
        
        $('#container-by-user').html('<h1><div class="row"><div class="col-md-12 text-center"><img src="{{ asset('image/loder.gif') }}" width="100px"></div></div>');
        $('#home-contact-container').html('<h1><div class="row"><div class="col-md-12 text-center"><img src="{{ asset('image/loder.gif') }}" width="100px"></div></div>');
        $('.purchase-box').html('<h1><div class="row"><div class="col-md-12 text-center"><img src="{{ asset('image/loder.gif') }}" width="100px"></div></div>');
        $('.contract-box').html('<h1><div class="row"><div class="col-md-12 text-center"><img src="{{ asset('image/loder.gif') }}" width="100px"></div></div>');
        $('.scheduled-box').html('<h1><div class="row"><div class="col-md-12 text-center"><img src="{{ asset('image/loder.gif') }}" width="100px"></div></div>');
        $('.kept-box').html('<h1><div class="row"><div class="col-md-12 text-center"><img src="{{ asset('image/loder.gif') }}" width="100px"></div></div>');
        $('#container-by-month').html('<h1><div class="row"><div class="col-md-12 text-center"><img src="{{ asset('image/loder.gif') }}" width="100px"></div></div>');
        $('.commission_new_count').html('');
        $('.commission_new_count_month').html('');

//        ajaxCall('GET', base_url + "/tenant/user/lead/status/report",columns).then(function (res) {
//            
//        });
    }

    function ColChart(user_names, status) {

        Highcharts.chart('home-contact-container', {
            chart: {
                type: 'column',
                height: 450,
            },
            title: {
                text: 'Stacked column chart'
            },
            xAxis: {
                categories: user_names,
                title: {
                    text: '',
                },
            },
            yAxis: {
                min: 1,
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
            showInLegend: true,
            legend: {
                align: 'center',
                verticalAlign: 'bottom',
                y: 20,
                navigation: {
                    activeColor: '#3E576F',
                    animation: true,
                    arrowSize: 12,
                    inactiveColor: '#CCC',
                    style: {
                        fontWeight: 'bold',
                        color: '#333',
                        fontSize: '12px'
                    }
                }
            },
            tooltip: {
                headerFormat: '<b>{point.x}</b><br/>',
                pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                },
            },
            series:status

        });

        
    }

    function ColMonthChart(user_names, status) {

        //  $.each(status, function(index, val) {
        //     console.log(val);
        //      /* iterate through array or object */
        // });

        Highcharts.chart('container-by-month', {
            chart: {
                type: 'column',
                height:500
            },
            title: {
                text: 'Commission Payments by Month'
            },
            xAxis: {
                categories: user_names,
                title: {
                    text: 'Commission Payments Month Performance',
                },
            },
            yAxis: {
                title: {
                    text: ''
                },
                labels: {
                  format: '${value:,.1f}'
                },
                stackLabels: {
                    enabled: true,
                    style: {
                        fontWeight: 'bold',
                        color: (// theme
                            Highcharts.defaultOptions.title.style &&
                            Highcharts.defaultOptions.title.style.color
                            ) || '#000'
                    },
                    formatter: function () {
                         return '$'+Highcharts.numberFormat(this.total, 0, '.', ',');
                    },
                }
            },
           legend: {
                align: 'center',
                verticalAlign: 'bottom',
                y: 20,
                navigation: {
                    activeColor: '#3E576F',
                    animation: true,
                    arrowSize: 12,
                    inactiveColor: '#CCC',
                    style: {
                        fontWeight: 'bold',
                        color: '#333',
                        fontSize: '12px'
                    }
                }
            },
            tooltip: {
                headerFormat: '<b>{point.x}</b><br/>',
                pointFormat: '{series.name}: ${point.y}<br/>Total: {point.stackTotal}'
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                    minPointLength: 1,  
                    dataLabels: {
                        enabled: false
                    }
                }
            },
            series: status

        });
    }

    function ColUserChart(user_names, status) {

        Highcharts.chart('container-by-user', {
            chart: {
                type: 'column',
                height:500
            },
            title: {
                text: 'Commission Payments by Users'
            },
            xAxis: {
                categories: user_names,
                title: {
                    text: 'Commission Payments Performance',
                },
            },
            yAxis: {
                title: {
                    text: ''
                },
                labels: {
                  format: '${value:,.1f}'
                },
                stackLabels: {
                    enabled: true,
                    style: {
                        fontWeight: 'bold',
                        color: (// theme
                            Highcharts.defaultOptions.title.style &&
                            Highcharts.defaultOptions.title.style.color
                            ) || '#000'
                    },
                    formatter: function () {
                         return '$'+Highcharts.numberFormat(this.total, 0, '.', ',');
                    }
                }
            },
            legend: {
                align: 'center',
                verticalAlign: 'bottom',
                y: 20,
                navigation: {
                    activeColor: '#3E576F',
                    animation: true,
                    arrowSize: 12,
                    inactiveColor: '#CCC',
                    style: {
                        fontWeight: 'bold',
                        color: '#333',
                        fontSize: '12px'
                    }
                }
            },
            tooltip: {
                headerFormat: '<b>{point.x}</b><br/>',
                pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                    minPointLength: 1,  
                    dataLabels: {
                        enabled: false
                    }
                }
            },
            series: status

        });
    }
    
    function loadGridWitoutAjaxNew(method, url, params = {}, headers = {}, columns = [], element = 'tbody', readData = '', redirect = true, pagination = false, check = true, filtered, indexing = true, api_resp = '') {
        $('#container-by-user').html('<h1><div class="row"><div class="col-md-12 text-center"><img src="{{ asset('image/loder.gif') }}" width="100px"></div></div>');
        $('#home-contact-container').html('<h1><div class="row"><div class="col-md-12 text-center"><img src="{{ asset('image/loder.gif') }}" width="100px"></div></div>');
        $('.purchase-box').html('<h1><div class="row"><div class="col-md-12 text-center"><img src="{{ asset('image/loder.gif') }}" width="100px"></div></div>');
        $('.contract-box').html('<h1><div class="row"><div class="col-md-12 text-center"><img src="{{ asset('image/loder.gif') }}" width="100px"></div></div>');
        $('.scheduled-box').html('<h1><div class="row"><div class="col-md-12 text-center"><img src="{{ asset('image/loder.gif') }}" width="100px"></div></div>');
        $('.kept-box').html('<h1><div class="row"><div class="col-md-12 text-center"><img src="{{ asset('image/loder.gif') }}" width="100px"></div></div>');
        $('#container-by-month').html('<h1><div class="row"><div class="col-md-12 text-center"><img src="{{ asset('image/loder.gif') }}" width="100px"></div></div>');
        $('.commission_new_count').html('');
        $('.commission_new_count_month').html('');
        
        
        
        
        return new Promise(function(resolve, reject) {
        

        ajaxCall(method, url, params, headers).then(function(res) {
            if (res.code == 200) {    
                
                
            var resultPurchased = res.data.resultPurchased;
            
            var tbody = document.querySelector('.purchase_closed');
            tbody.innerHTML = '';
            
            resultPurchased.forEach(function(contract) {
                 var row = document.createElement('tr');

        var homeownerNameCell = document.createElement('td');
        
//        homeownerNameCell.textContent = contract.lead_formatted_address;
        if (contract.lead_formatted_address) {
    homeownerNameCell.textContent = contract.lead_formatted_address;
} else {
    homeownerNameCell.textContent = contract.lead_address;
}
        row.appendChild(homeownerNameCell);

        var contractDateCell = document.createElement('td');
        // Using contract object instead of data object
        contractDateCell.textContent = (contract.purchase_date && contract.purchase_date !== null) ? contract.purchase_date : contract.p_purchase_date;
        row.appendChild(contractDateCell);
        
        var contractDateCell1 = document.createElement('td');
        contractDateCell1.textContent = contract.invester_first_name + contract.invester_last_name;
        row.appendChild(contractDateCell1);

        tbody.appendChild(row);
            });
            
            var resultContract = res.data.resultContract;
            
            var tbody = document.querySelector('.contracts_closed');
            tbody.innerHTML = '';
        
            resultContract.forEach(function(contract) {
                 var row = document.createElement('tr');

        var homeownerNameCell = document.createElement('td');
        if (contract.lead_formatted_address) {
            homeownerNameCell.textContent = contract.lead_formatted_address;
        } else {
            homeownerNameCell.textContent = contract.lead_address;
        }
//        if (contract.lead_formatted_address === null) {
//            homeownerNameCell.textContent = contract.lead_formatted_address;
//        } else {
//            alert('123');
//            homeownerNameCell.textContent = contract.address; // Assuming address is the property to fallback to
//        }
        row.appendChild(homeownerNameCell);

        var contractDateCell = document.createElement('td');
        // Using contract object instead of data object
        contractDateCell.textContent = (contract.contract_date && contract.contract_date !== null) ? contract.contract_date : contract.p_contract_date;
        row.appendChild(contractDateCell);
        
        var homeownerNameCell1 = document.createElement('td');
        homeownerNameCell1.textContent = contract.invester_first_name + contract.invester_last_name;
        row.appendChild(homeownerNameCell1);

        tbody.appendChild(row);
            });
            var knock_sum1 = '';
            var appointment_sum1 = '';
            var appointment_kept_sum1 = '';
            knock_sum1 += res.data.lead_count;
            appointment_sum1 += res.data.appointment_count;
            
            $('#knock_sum').html(knock_sum1);
            $('#appointment_sum').html(appointment_sum1);
            appointment_kept_sum1 += res.data.appointment_kept_count;
            $('#appointment_kept_sum').html(appointment_kept_sum1);

            var knock_sum = '';
            var appointment_sum = '';
            var appointment_kept_sum = '';
            
            knock_sum += res.data.lead_count;


            ColChart(res.data.knocks_event_users,res.data.knock_details_main);

            if(res.data.knocks_event_users.length == 0){
                $('#home-contact-container').html('<h4><div class="row"><div class="col-md-12 text-center">No Data Found!</div></div></h4>');
            }

            ColUserChart(res.data.commission_event_users,res.data.commission_event_main);
            $('#totalChartCount').html(res.data.totalChartCount);
            var userCommissionMonth = []; 

            $.each(res.data.commission_event_month, function(index, val) {
                
                var arr = Object.keys(val.data).map(function (key) { return val.data[key]; });
                
                val.data = arr;

                userCommissionMonth.push(val);
            });

            if(res.data.result_main_purchased_by_count != 0){
                $('.purchase-box').html('<h1>'+res.data.result_main_purchased+'</h1><h4 class="mt-4">1 Purchase for every '+res.data.result_main_purchased_by_count+' knocks</h4><h4>'+res.data.result_main_purchased_count+' Total Purchases from '+res.data.total_knocks_count+' knocks</h4>');
            }else{
                $('.purchase-box').html('<h1>'+res.data.result_main_purchased+'</h1><h4>'+res.data.result_main_purchased_count+' Total Purchases from '+res.data.total_knocks_count+' knocks</h4>');
            }

            if(res.data.result_main_contract_by_count != 0){
                $('.contract-box').html('<h1>'+res.data.result_main_contract+'</h1><h4 class="mt-4">1 Contract for every '+res.data.result_main_contract_by_count+' knocks</h4><h4>'+res.data.result_main_contract_count+' Total Contracts from '+res.data.total_knocks_count+' knocks</h4>');
            }else{
                $('.contract-box').html('<h1>'+res.data.result_main_contract+'</h1><h4>'+res.data.result_main_contract_count+' Total Contracts from '+res.data.total_knocks_count+' knocks</h4>');
            }

            if(res.data.result_main_apptrequest_by_count != 0){
                $('.scheduled-box').html('<h1>'+res.data.result_main_apptrequest+'</h1><h4 class="mt-4">1 Appointments Requested for every '+res.data.result_main_apptrequest_by_count+' knocks</h4><h4>'+res.data.result_main_apptrequest_count+' Total Appointments Requested from '+res.data.total_knocks_count+' knocks</h4>');
            }else{
                $('.scheduled-box').html('<h1>'+res.data.result_main_apptrequest+'</h1><h4>'+res.data.result_main_apptrequest_count+' Total Appointments Requested from '+res.data.total_knocks_count+' knocks</h4>');
            }

            if(res.data.result_main_apptnotkept_by_count != 0){
                $('.kept-box').html('<h1>'+res.data.result_main_apptnotkept+'</h1><h4 class="mt-4">1 Appointments KEPT for every '+res.data.result_main_apptnotkept_by_count+' knocks</h4><h4>'+res.data.result_main_apptnotkept_count+' Total Appointments KEPT from '+res.data.total_knocks_count+' knocks</h4>');
            }else{
                $('.kept-box').html('<h1>'+res.data.result_main_apptnotkept+'</h1><h4>'+res.data.result_main_apptnotkept_count+' Total Appointments KEPT from '+res.data.total_knocks_count+' knocks</h4>');
            }

            ColMonthChart(res.data.month_name,res.data.commission_event_month);

            var commission_new_count = '';
            commission_new_count += res.data.commission_count;
            $('.commission_new_count_month').html('$'+$.number(res.data.commission_event_month_total));
            $('.commission_new_count').html('$'+$.number(res.data.commission_event_total));

            appointment_sum += res.data.appointment_count;
            $('#knock_sum').html(knock_sum);
            $('#appointment_sum').html(appointment_sum);
            appointment_kept_sum += res.data.appointment_kept_count;
            $('#appointment_kept_sum').html(appointment_kept_sum);
            
                
                if(api_resp == "lead_management" ){
                    var newLocalArray = [];
                    $('.setting-dropdown').removeAttr("disabled");
                    
                }
                var totalRecord = res.recordsTotal;
                if(api_resp){
                    api_response_collection[api_resp] = totalRecord;                    
                }
                //console.log("totalRecord",totalRecord);
                var tbodyHtml = '';
                if (readData == '') {
                    var record = res.data;

                } else {
                    var record = res.data[readData];

                }

                if (record.length > 0) {
                    
                        if(localStorage.getItem("myNewArray")  != null)
                        {
                            var localArray = JSON.parse(localStorage.getItem("myNewArray"));
                        }
                                    
                        else
                        {
                            var localArray = [];
                        }

                    if (pagination == false) {
                        var index = 1;
                    } else {
                        var pagination_meta = res.meta;

                        
                        var index = ((page_size * (pagination_meta.current_page - 1)) + 1);

                        $('#checkAll').click(function() {


                            if ($(this).is(':checked')) {
                                $(".chkboxes").prop("checked", true);
                                $('.show_all').css('display', 'inline-block');
                                $('.setting').addClass('col-md-2').removeClass('col-md-6');

                                //         $("#txtAge").dialog({
                                //             close: function() {
                                //                 $('.chkboxes').prop('checked', false);
                                //                 $('#checkAll').prop('checked', false);
                                //                 $('.show_all').hide();
                                var newDiv = '';
                                newDiv += '<div class="col-md-4 text-right show_all" style=""></div>';
                                $('.new_div').html(newDiv);

                                //         });

                            } else {
                                $(".show_all").hide();
                                $('.setting').removeClass('col-md-2');
                                $(".chkboxes").prop("checked", false);
                            }

                        });


                    }

                    for (var i = 0; i < record.length; i++) {
                        // console.log(record[i].lead_type);


                        if (redirect == true) {


                            tbodyHtml += '<tr class="redirect" data-href="' + window.location.href + '/edit/' + record[i].id + '" id="' + record[i].id + '">';
                        } else {


                            tbodyHtml += '<tr id="' + record[i].id + '">';

                        }

                        if (pagination == true) {

                            var lead_id = res.data[i].id;
                            var checkbox = '<input type="checkbox" class="chkboxes abc"  id="checkbox' + lead_id + '" name="lead_ids" value="' + lead_id + '">';

                            tbodyHtml += '<tr id="redirect2">';
                            tbodyHtml += '<td>' + checkbox + '</td>';

                            $(document).on('click', '.abc', function(e) {

                                if ($(this).is(':checked')) {


                                    $('.show_all').css('display', 'inline-block');

                                }

                                if ($('.abc:checked').length == 0) {
                                    $('.show_all').hide();
                                    $("#txtAge").dialog('close');


                                }

                            })

                        }

                        if (indexing == true) {
                            tbodyHtml += '<td>' + index + '</td>';
                        }
                        

                        for (var c = 0; c < columns.length; c++) {
                            // console.log(record[i]);
                            if(api_resp == "lead_management" ){
                                
                                    
                                let hidetr = localArray.includes(columns[c])
                                
                                if(hidetr){
                                    var dynamicShow = 'dynamicHide';
                                }
                                else{
                                    var dynamicShow = 'dynamicShow';
                                    
                                }
                                    
                            }
                            // console.log();

//                            $.each(record[i].lead_query_data, function(index, val) {
////                                if(val.query != null  && columns[c] == val.query){
//                                if(val.query != null  && val.query_id == 8){                                
//                                    tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '" title="' + val.query + '" class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'">' + val.response + '</td>';
////                                }
//                                }
//                                });

                            // sorting_filed_step_2
                            
                            // console.log('--');
                            
                            if (columns[c] == 'lead_name' || columns[c] =='Homeowner Name') {
                                tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '" title="' + record[i].title + '" class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'">' + record[i].title + '</td>';
                            }else if (columns[c] == 'user_status' || columns[c] == 'User Status') {
                                if(record[i].user_status ==  'Active'){
                                    tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '"  class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'"><input type="checkbox" data-id="'+record[i].id+'" name="is_user_status_'+record[i].id+'" class="isUserStatusUpdate" checked value="0"></td>';
                                }else{
                                    tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '"  class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'"><input type="checkbox" data-id="'+record[i].id+'" name="is_user_status_'+record[i].id+'" class="isUserStatusUpdate value="1"></td>';
                                }
                            }else if (columns[c] == 'startup_paid') {
                                if(record[i].startup_paid ==  '0'){
                                    tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '"  class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'">No</td>';
                                }else{
                                    tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '"  class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'">Yes</td>';
                                }
                            }else if (columns[c] == 'startup_reimbursed') {
                                if(record[i].startup_reimbursed ==  '0'){
                                    tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '"  class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'">No</td>';
                                }else{
                                    tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '"  class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'">Yes</td>';
                                }
                            }else if (columns[c] == 'created_at') {
                                if(element == '.history tbody'){
                                    tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].lead_history_id + '" title="' + record[i].created_at + '" class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'"><a href="#" data-name="created_at" class="detailupdateHistory " data-mode="inline" data-type="combodate" data-value="' + record[i].created_at + '"  data-pk="' + record[i].lead_history_id + '" data-original-title="Enter When:" title="">' + record[i].created_at + '</a></td>';
                                }else{
                                    tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '" title="' + record[i].created_at + '" class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'">' + record[i].created_at + '</td>';
                                }
                            }else if (columns[c] == 'Notes' || columns[c] == 'Notes_Add_to_Top_Include_Date_Your_Name_and_Notes') {
                                tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '" title="' + record[i].Notes_Add_to_Top_Include_Date_Your_Name_and_Notes + '" class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'">' + record[i].Notes_Add_to_Top_Include_Date_Your_Name_and_Notes + '</td>';
                            }else if (columns[c] == 'is_verified' || columns[c] == 'Is Verified') {
                                
                                var verified = '';

                                if(record[i].is_verified == 1){
                                    verified = '<label class="label label-success">Yes</label>';
                                }else{
                                    verified = '<label class="label label-danger">No</label>';
                                }

                                tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '" title="' + record[i].is_verified + '" class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'">' +verified+ '</td>';
                                
                            }else if (columns[c] == 'address' || columns[c] == 'Address') {
                                tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '" title="' + record[i].address + '" class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'">' + record[i].address + '</td>';
                            }else if (columns[c] == 'Is Follow Up' || columns[c] == 'is_follow_up' || columns[c] == 'Is_follow_up') {
                                    tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '"  class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'"><button class="btn btn-dark isfollowup btn-sm" data-id="'+record[i].id+'"><i class="fas fa-level-up-alt"></i> Follow-up</button></td>';
                             }else if (columns[c] == 'Is Retired' || columns[c] == 'is_retired') {
                                if(record[i].is_expired ==  1){
                                    tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '"  class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'"><input type="checkbox" data-id="'+record[i].id+'" name="is_expired_'+record[i].id+'" class="isretRiedUpdate" checked value="0"></td>';
                                }else{
                                    tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '"  class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'"><input type="checkbox" data-id="'+record[i].id+'" name="is_expired_'+record[i].id+'" class="isretRiedUpdate" value="1"></td>';
                                }
                            }else if (columns[c] == 'city' || columns[c] == 'City') {
                                tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '" title="' + record[i].city + '" class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'">' + record[i].city + '</td>';
                            }else if (columns[c] == 'State' || columns[c] == 'state') {
                                tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '" title="' + record[i].state + '" class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'">' + record[i].state + '</td>';
                            }else if (columns[c] == 'Assigned' || columns[c] == 'Assigned To' || columns[c] == 'assigned to') {
                                if(record[i].assignee != undefined){
                                    tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '" title="' + record[i].assignee + '" class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'">' + record[i].assignee + '</td>';
                                }else{
                                    tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '" title="" class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'">---</td>';
                                }
                            }else if (columns[c] == 'County' || columns[c] == 'county') {
                                tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '" title="' + record[i].county + '" class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'">' + record[i].county + '</td>';
                            }else if (columns[c] == 'zip_code' || columns[c] == 'zip' || columns[c] == 'Zip Code' || columns[c] == 'Zip') {
                                tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '" title="' + record[i].zip_code + '" class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'">' + record[i].zip_code + '</td>';
                            }else if (columns[c] == 'color_code') {
                                // < iclass="fas fa-circle" style="color:;"></i>
                                tbodyHtml += '<td id="' + columns[c] + '" class="' + columns[c] + ' text-left"><i class="fas fa-circle" style="color:' + record[i][columns[c]] + ';"></i>' + record[i][columns[c]] + '</td>';
                            } else if (columns[c] == 'latitude'){
                                tbodyHtml += '<td id="' + columns[c] + '" class="' + columns[c] + ' text-left">' + record[i].coordinate.latitude + '</td>';
                            } else if (columns[c] == 'last_app_activity') {

                                if(record[i].last_app_activity != null){
                                    tbodyHtml += '<td id="' + columns[c] + '" class="' + columns[c] + ' text-left">' +record[i].last_app_activity+ '</td>';
                                }else{
                                    tbodyHtml += '<td>---</td>';

                                }

                            } else if (columns[c] == 'longitude') {

                                tbodyHtml += '<td id="' + columns[c] + '" class="' + columns[c] + ' text-left">' + record[i].coordinate.longitude + '</td>';

                            } else if (columns[c] == 'field') {
                                tbodyHtml += '<td style="text-align:center;"><a style="padding-right:2px;" href="' + base_url + '/tenant/template/update/' + record[i].template_id + '/' + record[i].field + '"><i class="fas fa-edit edit"></i></a> <i style="color:#d11a2a;" class="far fa-trash-alt delete" id="' + record[i].field + '"></i></td>';
                            } else if (columns[c] == 'Lead Type' || columns[c] == 'lead type' || columns[c] == 'lead_type') {
                                if(record[i].lead_type != undefined){
                                    tbodyHtml += '<td>'+record[i].lead_type+'</td>';
                                }else{
                                    tbodyHtml += '<td>--</td>';
                                }
                            } else if (columns[c] == 'Admin Notes' || columns[c] == 'admin notes' || columns[c] == 'admin_notes') {
                                if(record[i].admin_notes != ' ' || record[i].admin_notes != undefined){
                                    tbodyHtml += '<td>'+record[i].admin_notes+'</td>';
                                }else{
                                    tbodyHtml += '<td>--</td>';
                                }
                            } else if (columns[c] == 'Lead Status' || columns[c] == 'lead status' || columns[c] == 'lead_status') {
                                tbodyHtml += '<td><span class="leadstatusbox" style="background-color:'+record[i].lead_color+'">'+record[i].lead_status+'</span></td>';
                            } else if (columns[c] == 'Auction' || columns[c] == 'auction') {
                                if(record[i].auction !== null){
                                    tbodyHtml += '<td>'+record[i].auction+'</td>';
                                }else{
                                    tbodyHtml += '<td>---</td>';
                                }
                            } else if (columns[c] == 'Source' || columns[c] == 'source') {
                                if(record[i].source !== null){
                                    tbodyHtml += '<td>'+record[i].source+'</td>';
                                }else{
                                    tbodyHtml += '<td>---</td>';
                                }
                            }else if (columns[c] == 'Mortgagee' || columns[c] == 'mortgagee') {
                                
                                if(record[i].mortgagee !== null){
                                    tbodyHtml += '<td>'+record[i].mortgagee+'</td>';
                                }else{
                                    tbodyHtml += '<td>---</td>';
                                }
                            }else if (columns[c] == 'Loan Type' || columns[c] == 'loan_type') {
                                
                                if(record[i].loan_type !== null){
                                    tbodyHtml += '<td>'+record[i].loan_type+'</td>';
                                }else{
                                    tbodyHtml += '<td>---</td>';
                                }
                             }else if (columns[c] == 'Loan Mod' || columns[c] == 'loan_mod') {
                                
                                if(record[i].loan_mod !== null){
                                    tbodyHtml += '<td>'+record[i].loan_mod+'</td>';
                                }else{
                                    tbodyHtml += '<td>---</td>';
                                }
                             }else if (columns[c] == 'Trustee' || columns[c] == 'trustee') {
                                
                                if(record[i].trustee !== null){
                                    tbodyHtml += '<td>'+record[i].trustee+'</td>';
                                }else{
                                    tbodyHtml += '<td>---</td>';
                                }
                             }else if (columns[c] == 'Owner Address' || columns[c] == 'Owner Address - If Not Owner Occupied' || columns[c] == 'owner_address' || columns[c] == 'Owner_Address_-_If_Not_Owner_Occupied HideArrow ui-resizable') {
                                if(record[i].owner_address !== null){
                                    tbodyHtml += '<td>'+record[i].owner_address+'</td>';
                                }else{
                                    tbodyHtml += '<td>---</td>';
                                }
                            }else if (columns[c] == 'Original Loan' || columns[c] == 'original loan') {
                                if(record[i].original_loan !== null){
                                    tbodyHtml += '<td>'+record[i].original_loan+'</td>';
                                }else{
                                    tbodyHtml += '<td>---</td>';
                                }
                            // }else if (columns[c] == 'Admin Notes' || columns[c] == 'admin_notes') {
                            //     console.log(record[i].admin_notes);
                            //     if(record[i].admin_notes != undefined){
                            //         tbodyHtml += '<td>'+record[i].admin_notes+'</td>';
                            //     }else{
                            //         tbodyHtml += '<td>---</td>';
                            //     }
                            } else if (columns[c] == 'Loan Date' || columns[c] == 'Loan Date') {
                                if(record[i].loan_date !== null){
                                    tbodyHtml += '<td>'+record[i].loan_date.replace(",", "")+'</td>';
                                }else{
                                    tbodyHtml += '<td>---</td>';
                                }
                            } else if (columns[c] == 'Sq Ft' || columns[c] == 'sq ft') {
                                if(record[i].sq_ft !== null){
                                    tbodyHtml += '<td>'+record[i].sq_ft+'</td>';
                                }else{
                                    tbodyHtml += '<td>---</td>';
                                }
                            } else if (columns[c] == 'Yr Blt' || columns[c] == 'yr blt') {
                                if(record[i].yr_blt !== null){
                                    tbodyHtml += '<td>'+record[i].yr_blt+'</td>';
                                }else{
                                    tbodyHtml += '<td>---</td>';
                                }
                             } else if (columns[c] == 'EQ' || columns[c] == 'eq') {
                                if(record[i].eq !== null){
                                    tbodyHtml += '<td>'+record[i].eq+'  </td>';
                                }else{
                                    tbodyHtml += '<td>---</td>';
                                }
                            }else if (columns[c] == 'created_by' || columns[c] == 'created_by') {
                                if(record[i].created_by !== null){
                                    tbodyHtml += '<td>'+record[i].created_by+'.</td>';
                                }else{
                                    tbodyHtml += '<td>---</td>';
                                }
                            }else if (columns[c] == 'updated_by' || columns[c] == 'updated_by') {
                                if(record[i].updated_by !== null){
                                    tbodyHtml += '<td>'+record[i].updated_by+'.</td>';
                                }else{
                                    tbodyHtml += '<td>---</td>';
                                }
                            } else if (columns[c] == 'Loan Date' || columns[c] == 'Loan Date') {
                                if(record[i].loan_date !== null){
                                    tbodyHtml += '<td>'+record[i].loan_date+'</td>';
                                }else{
                                    tbodyHtml += '<td>---</td>';
                                }
                            } else if (columns[c] == 'Lead Value' || columns[c] == 'lead value') {
                                if(record[i].lead_value !== null){
                                    tbodyHtml += '<td>$'+$.number(record[i].lead_value)+'</td>';
                                }else{
                                    tbodyHtml += '<td>---</td>';
                                }
                            } else if (columns[c] == 'actions') {

                                tbodyHtml += '<td style="text-align:center;"><a style="padding-right:2px;" href="' + base_url + '/tenant/lead-default/edit/' + record[i].id + '/"><i class="fas fa-edit edit"></i></a> <i style="color:#d11a2a;" class="far fa-trash-alt delete" id="' + record[i].id + '"></i></td>';
                            }
                            // else if (columns[c] == 'template_name')
                            // {

                            //     tbodyHtml += '<td name="common" style="text-align:center;"><a style="padding-right:2px;" href="'+base_url+'/tenant/lead-default/edit/'+record[i].id+'/"><i class="fas fa-edit edit"></i></a> <i style="color:#d11a2a;" class="far fa-trash-alt delete" name="common" id="'+ record[i].id+'"></i></td>';
                            // }
                            else if (columns[c] == 'is_expired') {

                                var is_expired = record[i].is_expired;

                                if (is_expired == 1) {

                                    tbodyHtml += '<td  class="' + columns[c] + ' text-left '+dynamicShow+'">Yes</td>';
                                } else {
                                    tbodyHtml += '<td class="' + columns[c] + ' text-left '+dynamicShow+'">No</td>';
                                }


                            } else if (columns[c] == 'user_id') {

                                var new_user_id = res.data[i].id;
                                var new_status_user_id = res.data[i].user_status_id;

                                if (new_status_user_id == 0) {
                                    tbodyHtml += '<td id="' + new_user_id + '" class="unlink" style="text-decoration:none;">---</td>';
                                    $('.un-link').html('Password Reset Link');
                                } else {
                                    tbodyHtml += '<td id="' + new_user_id + '" class="link">Sent</td>';

                                }


                            } else if (columns[c] == 'template-action') {

                                var new_user_id = res.data[i].id;
                                var new_status_user_id = res.data[i].user_status_id;

                                tbodyHtml += '<td id="' + new_user_id + '" class="delete text-center"><i style="color:#d11a2a;" class="far fa-trash-alt"></i></td>';
                                tbodyHtml += '<td id="' + new_user_id + '" class="copy text-center"><a href="tenant/template/copy/' + new_user_id + '"><i style="color:#d11a2a;" class="far fa-copy"></i></a></td>';
                                
                            } else if (columns[c] == 'lead_count') {

                                var new_count = record[i].lead_count;
                                var new_color = record[i].color_code;

                                tbodyHtml += '<td id="' + columns[c] + '" class="' + columns[c] + ' text-left lead_count_' + new_id + '">' + new_count + '  <i class="fas fa-map-marker-alt" style="color:' + new_color + ';"></i></td>';

                            } else if (columns[c] == 'lead_percentage') {
                                var new_id = record[i].id;

                                var lead_per = record[i].lead_percentage;
                                tbodyHtml += '<td id="' + lead_per + '" class="' + lead_per + ' text-left lead_percentage_' + new_id + '   ">' + lead_per + '%</td>';

                            } else if (columns[c] == 'query') {
                                var new_query = record[i].query;

                                tbodyHtml += '<td>' + new_query + '</td>';
                            }
                            else if (columns[c] == 'City') {
                                        tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '" title="' + record[i].city + '" class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'">' + record[i].city + '</td>';
                            }
//                            else if (columns[c] == 'updated_by') {
//                                    
//                                    var updatedData = '<td >---</td>';
//
//                                    if(record[i].updated_by != null){
//                                        
//                                        // updatedData = '<td id="' + columns[c] + '" data-id="' + record[i].id + '" title="updated_by" class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'">' + record[i].updated_by !== undefined ?  record[i].updated_by.name : '-' + '</td>';
//                                        updatedData = '<td >'+record[i].updated_by.name+'</td>';
//                                    }
//
//                                    tbodyHtml += updatedData;
//                            }
//                            else if (columns[c] == 'created_by') {
//                                    var updatedData = '<td>---</td>';
//
//                                    if(record[i].created_by != null){
//                                        
//                                        // updatedData = '<td id="' + columns[c] + '" data-id="' + record[i].id + '" title="updated_by" class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'">' + record[i].updated_by !== undefined ?  record[i].updated_by.name : '-' + '</td>';
//                                        updatedData = '<td >'+record[i].created_by.name+'</td>';
//                                    }
//
//                                    tbodyHtml += updatedData;
//                            }
                            else if (columns[c] == 'status_lead_count') {
                                var new_lead = record[i].lead_count;

                                tbodyHtml += '<td id="' + columns[c] + '' + index + '" class="' + columns[c] + ' text-left lead_count_' + new_id + '">' + new_lead + '</td>';

                            } else if (columns[c] == 'status_agent_name') {
                                var new_agent = record[i].agent_name;

                                tbodyHtml += '<td id="' + columns[c] + '' + index + '" class="' + columns[c] + ' text-left lead_count_' + new_id + '">' + new_agent + '</td>';

                            } else if (columns[c] == 'template_key') {
                                var new_key = record[i].key;

                                tbodyHtml += '<td id="' + columns[c] + '' + index + '" class="' + columns[c] + ' text-left">' + new_key.split('_').join(' ') + '</td>';

                            } else if (columns[c] == 'field_name_detail') {

                                tbodyHtml += '<td id="' + columns[c] + '' + index + '" class="' + columns[c] + ' text-left"> <a href="#" class="detailupdate"  data-type="text" data-pk="'+record[i].id+'" data-title="Enter Field Name">' + record[i].key_mask + '</a></td>';

                            }
                             else if (columns[c] == 'is_active') {

                                if (record[i].is_active  == 1) {
                                    activeStatus = "<input class='isCheckField' type='checkbox' checked value='0' name='is_check_field' data-id="+record[i].id+"> <span class='labelactive'>Yes<span>";
                                }else{
                                    activeStatus = "<input class='isCheckField' type='checkbox' value='1' name='is_check_field' data-id="+record[i].id+"> <span class='labelactive'>No<span>";
                                }

                                tbodyHtml += '<td id="' + columns[c] + '' + index + '" class="' + columns[c] + ' text-left">'+activeStatus+ '</td>';

                            } else if (columns[c] == 'test_title')

                            {

                                var status_title = record[i].title;
                                var status_ids = record[i].id;


                                var legchecked = record.length;
                                if (check == true)

                                {

                                    var default_checked = 'checked';
                                } else {

                                    if (filtered[i] == record[i].id)

                                    {
                                        var default_checked = '';
                                    } else {
                                        var default_checked = 'checked';
                                    }
                                }

                                // tbodyHtml += '<option value="">'+ status_title +'</option>'

                                // $('.lead_type_id').append(tbodyHtml);
                                // $('.selectpicker').selectpicker('refresh')

                                tbodyHtml += '<td class="' + status_title + ' text-left" title="' + status_title + '"><input type="checkbox" name="status_id" value="' + record[i].id + '" id="' + status_ids + '"/>' + status_title + '</td>';
                            } else {
                                if (columns[c].includes('.')) {
                                    var innerKey = columns[c].split('.');
                                    var td_value = '';
                                    for (var k = 0; k < innerKey.length; k++) {
                                        if (k == 0) {
                                            td_value = record[i][innerKey[k]];

                                        } else {
                                            if (td_value != null && td_value != '') {
                                                td_value = td_value[innerKey[k]];
                                            }else{
                                                td_value = '---';
                                            }
                                            
                                        }
                                    }
                                } else {

                                    if (record[i][columns[c]] != null) {
                                         var td_value = record[i][columns[c]];
                                    }else{
                                        
                                        var td_value = '---';

                                    }

                                }
                                if (typeof td_value === 'undefined') {
                                    td_value = '---';
                                }

                                tbodyHtml += '<td id="' + columns[c] + '" data-id="' + record[i].id + '" title="' + record[i][columns[c]] + '" class="' + columns[c].split(' ').join('_') + ' text-left '+dynamicShow+'" >' + td_value + '</td>';
                            }
                        }


                        tbodyHtml += '</tr>';
                        index++;

                    }

                    $(element).html(tbodyHtml);

                    //pagination
                    if (pagination) {

                        var pagination_obj = res.meta;
                        var last_page_number = pagination_obj.last_page;

                        if (last_page_number > 1) {
                            var pagination_html = '<nav aria-label="Page navigation example">';
                            pagination_html += '<ul class="pagination">';
                            if (pagination_obj.current_page > 1) {
                                pagination_html += '<li data-page_number="1" class="page-item"><a class="page-link" > << </a></li>';

                            }
                            pagination_html += '<li data-page_number="' + (parseInt(pagination_obj.current_page) - 1) + '" class="page-item"><a class="page-link"> < </a></li>';
                            var index = 1;

                            for (var p = pagination_obj.current_page; p <= last_page_number; p++) {
                                if (index <= page_size) {
                                    if (index == 1) {
                                        var active_class = 'active_page';
                                    } else {
                                        var active_class = '';
                                    }
                                    pagination_html += '<li data-page_number="' + p + '" class="page-item"><a class="' + active_class + '  page-link">' + p + '</a></li>';

                                }

                                index++;

                            }

                            if (pagination_obj.current_page != last_page_number) {
                                pagination_html += '<li data-page_number="' + (parseInt(pagination_obj.current_page) + 1) + '" class="page-item"><a class="page-link"> > </a></li>';

                            }


                            if (pagination_obj.current_page < last_page_number) {
                                pagination_html += '<li data-page_number="' + last_page_number + '" class="page-item"><a class="page-link"> >> </a></li>';
                            }
                            pagination_html += '</ul>';
                            pagination_html += '</nav>';
                            $('.pagination_cont').html(pagination_html)
                        }

                        if (last_page_number == 1) {
                            $('.pagination_cont').html('')
                        }
                    }


                } else {

                    tbodyHtml += '<tr>';
                    tbodyHtml += '<td colspan ="100" class="text-center"> No record found </td>';
                    tbodyHtml += '</tr>';
                    $(element).html(tbodyHtml);
                    $('.pagination_cont').html('')

                }
            } else {
                tbodyHtml += '<tr>';
                tbodyHtml += '<td colspan ="100" class="text-center"> No record found </td>';
                tbodyHtml += '</tr>';
                $(element).html(tbodyHtml);
                $('.pagination_cont').html('')
            }

              $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
              });
  
             $('.detailupdate').editable({
               send: 'always',
               url: "/tenant/deal/update/editable",
               success: function(response) {
                 console.log('hello');
               }
             });

             $('.detailupdateHistory').editable({
               send: 'always',
               format: 'MM/DD/YYYY HH:mm',    
               template: 'MM/DD/YYYY HH:mm',
               viewformat: 'MM/DD/YYYY hh:mm A',    
               url: "/tenant/field/update/editable/history",
               success: function(response) {
                    if(response.error && response.error != ''){
                         toastr.error(response.error);
                         return false;
                    }else{
                         toastr.success('When date is updated.');
                    }
               }
             });
            
            resolve(true);
        })
    })
}

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
        
        
        var PurchaseLink = $("#PurchaseLink");
        PurchaseLink.attr("href", "<?php echo url('tenant/dashboard/purchase/list'); ?>" + "?date=" + time_slot  + "&filter_user_id=" + user_id);
        
        var ContractLink = $("#ContractLink");
        ContractLink.attr("href", "<?php echo url('tenant/dashboard/contract/list'); ?>" + "?date=" + time_slot + "&filter_user_id=" + user_id);
        
        var AppointmentsRequestedLink = $("#AppointmentsRequestedLink");
        AppointmentsRequestedLink.attr("href", "<?php echo url('tenant/dashboard/appointments_requested/list'); ?>" + "?date=" + time_slot + "&filter_user_id=" + user_id);
        
        var AppointmentsKEPTLink = $("#AppointmentsKEPTLink");
        AppointmentsKEPTLink.attr("href", "<?php echo url('tenant/dashboard/appointments_kept/list'); ?>" + "?date=" + time_slot + "&filter_user_id=" + user_id);
        
      
        var data = {target_user_id: user_id, type_id: type_id, type: type, time_slot: time_slot};

        $('.team_status').html('<h1><div class="row"><div class="col-md-12 text-center"><img src="{{ asset('image/loder.gif') }}" width="100px"></div></div>');

        $('.purchase_closed').html('<h1><div class="row"><div class="col-md-12 text-center"><img src="{{ asset('image/loder.gif') }}" width="100px"></div></div>');
        $('.contracts_closed').html('<h1><div class="row"><div class="col-md-12 text-center"><img src="{{ asset('image/loder.gif') }}" width="100px"></div></div>');
        
        loadGridWitoutAjaxNew('GET', base_url + "/tenant/user/lead/status/report", data, {}, columns, '.team_status', 'result', false);
//        ajaxCall('GET', base_url + "/tenant/user/lead/status/report", data).then(function (res) {
//
//            var knock_sum = '';
//            var appointment_sum = '';
//            var appointment_kept_sum = '';
//            knock_sum += res.data.lead_count;
//            appointment_sum += res.data.appointment_count;
//            
//            $('#knock_sum').html(knock_sum);
//            $('#appointment_sum').html(appointment_sum);
//            appointment_kept_sum += res.data.appointment_kept_count;
//            $('#appointment_kept_sum').html(appointment_kept_sum);
//            
//        });
//
//      chatCall(data);

    })    
})
</script>
@include('tenant.include.footer')
