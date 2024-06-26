@include('tenant.include.header')
@include('tenant.include.sidebar')
<style type="text/css">
    .summary, .b1{
        margin-top:0px;
    }
    ::placeholder {
        color: #fff !important;
        opacity: 1; 
    }
    ::-webkit-input-placeholder {
        color: #fff !important;
    }
    :-ms-input-placeholder { /* Internet Explorer */
        color: #fff !important;
    }
    .tab {
        overflow: hidden;
        border: 1px solid #ccc;
        background-color: #113f85 !important;
    }
    .tab button {
        background-color: inherit;
        float: left;
        border: none;
          outline: none;
          cursor: pointer;
          padding: 14px 16px;
          transition: 0.3s;
    }
    .tab button:hover {
        background-color: #ddd;
    }
    .tab button.active {
          background-color: #ccc;
    }
    .tabcontent {
        display: none;
        padding: 6px 12px;
          border: 1px solid #ccc;
          border-top: none;
    } 
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
    <div class="row" id="content-heading">
        <div class="col-md-8">
            <div class="dropdown">
                <button class="btn  dropdown-toggle add-bt" type="button" data-toggle="dropdown" style="background: transparent;">
                    <span class="cust-head">FollowUp Lead Status Report</span>
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
        <div class="col-md-3 text-right" style="margin-top:6px;">
        </div>
        <div class="col-md-1 text-right" style="margin-top:6px;">
            <button class="b1" id="export-btn">Export</button>
        </div>
    </div>
    <div class="row show-toggle" id="content-heading" >
        <form class="comm_form">
            <div class="col-md-2">
                <div class="form-group">
                    <label>Select Date</label>
                    <input type="text" id="e2" name="time_slot" value="{{ request()->get('date') ?? '' }}" placeholder="Start Date" value="{{ request()->get('start_date') ?? '' }}" class="startDate input date_range1 duration" value="select date" name="date_range" autocomplete="off">
                </div>
            </div>
            <div class="col-md-2 form-group">
                <label>Select Followup Status</label>
                @if(count($data['type']))
                <select class="form-control type_list selectpicker"
                        data-live-search="true" name="type_id" value="" data-actions-box="true"
                        title="Select Lead Status" multiple>
                    @foreach($data['status'] as $type )
                    <option value="{{ $type->id }}">{{$type->title}}</option>
                    @endforeach
                </select>
                @endif
            </div>
            <div class="col-md-2 form-group">
                <label>Select Unit</label>
                <select class="form-control summary value" name="type">
                    <option disabled="disabled" selected="selected">Select Unit</option>
                    <option value="percentage">Percentage</option>
                    <option value="amount"> Unit</option>
                </select>
            </div>
            <div class="col-md-2 form-group">
                <label>Select Unit</label>
                <select class="form-control summary datetype" name="timetype">
                    <option value="day">Day</option>
                    <option value="week">Week</option>
                    <option value="month">Month</option>
                    <option value="year" selected=""> Year</option>
                </select>
            </div>
            <div class="col-md-2 form-group">
                <label style="visibility:hidden;">Submit</label><br/>
                <button class="b1 save"><i class="fas fa-paper-plane"></i></button>
            </div>
        </form>
    </div>
    <hr class="border">
    <div id="chartWidth"><!-- For chart width only --></div>
    <div class="row">
        <div class="tab">
            <button class="tablinks" onclick="openCity(event, 'London')">Bar Chart</button>
            <button class="tablinks" onclick="openCity(event, 'Paris')">Pie Chart</button>
        </div>
        <div id="London" class="tabcontent">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="row" id="content-heading">
                            <div class="col-md-10">
                                <h5>Bar Chart FollowUp Lead Status Report</h5>
                            </div>
                            <div class="col-md-2 text-right">
                                <div onclick="openFullscreenBar();"><i class="fa fa-arrows-alt"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">                        
                        <div id="chartContainer" style="height: 600px; margin: 0 auto"></div>
                    </div>
                </div>           
            </div>
        </div>

        <div id="Paris" class="tabcontent">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="row" id="content-heading">
                            <div class="col-md-10">
                                <h5>Pie Chart FollowUp Lead Status Report</h5>
                            </div>
                            <div class="col-md-2 text-right">
                                <div onclick="openFullscreen();"><i class="fa fa-arrows-alt"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div id="container3" style="width:auto; height: 400px; margin: 0 auto"></div>
                    </div>
                </div>           
            </div>    
        </div>  
        <div id="OldBarChart" class="tabcontent">
            <div id="container" ></div>
        </div>
    </div>
    <br><br>
    <div class="row" id="pg-content">
        <div class="col-md-12">
            <div class="">
                <div class="panel-heading">
                    <h5>FollowUp Lead Status Report</h5>
                </div>
                <div class="table-responsive mt-20">
                    <table class="table table-bordered">
                        <thead id="thead_month">
                        </thead>
                        <tbody id="tbody_month">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div>
        <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
    </div>
</div>
<script src="https://code.highcharts.com/highcharts.js"></script>    
<script src="{{asset('assets/js/tenant-js/chart.js')}}"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>
<script>
    function openCity(evt, cityName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
          }
          tablinks = document.getElementsByClassName("tablinks");
          for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
          document.getElementById(cityName).style.display = "block";
          evt.currentTarget.className += " active";          
    }

    var elem1 = document.getElementById("chartContainer");
    function openFullscreenBar() {
        if (elem1.requestFullscreen) {
            elem1.requestFullscreen();
        } else if (elem1.webkitRequestFullscreen) { /* Safari */
            elem1.webkitRequestFullscreen();
        } else if (elem1.msRequestFullscreen) { /* IE11 */
            elem1.msRequestFullscreen();
        }
    }

    var elem = document.getElementById("container3");
    function openFullscreen() {
        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if (elem.webkitRequestFullscreen) { /* Safari */
            elem.webkitRequestFullscreen();
        } else if (elem.msRequestFullscreen) { /* IE11 */
            elem.msRequestFullscreen();
        }
    }
</script>
<script>
    $(document).ready(function () {
        var dateRange = $("#e2").daterangepicker();
        
        
        loadChart3('GET', base_url + "/tenant/user/lead/status/report/followup/pie", {}, {});
        $(document).on('click', '.save', function (e) {
            e.preventDefault();
            var type_id = $('.type_list').selectpicker('val');
            if (Array.isArray(type_id)) {
                type_id = type_id.join();
            }
            var type = $('.value').val();
            var time_slot = $('.duration').val();
            var start_date = $('.startDate').val();
            var time_slot = $('.duration').val();
            var end_date = $('.endDate').val();
            var data = {type_id: type_id, type: type, time_slot: time_slot, start_date: start_date, end_date: end_date};
            loadChart3('GET', base_url + "/tenant/user/lead/status/report/followup/pie", data, headers = {});
        })
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
                    } else {
                        $("#container3").html("<img style='width:100%;' src='{{asset("assets/images/graph.png")}}''>");
                    }
                }
            })
        }

    })
</script>
<script type="text/javascript">
    $(document).ready(function () {
        function ColChart(user_names, status, types) {
            console.log("status", status);
            Highcharts.chart('container', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: 'Stacked column chart'
                },
                xAxis: {
                    categories: user_names,
                    title: {
                        text: 'Bar Chart FollowUp Lead Status Report',
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
                                    ) || 'gray'
                        }
                    }
                },
                legend: {
                    align: 'center',
                    x: 15,
                    verticalAlign: 'bottom',
                    y: 15,
                    maxHeight: 50,
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
                series: status

            });
            var tatol_width = $('#chartWidth').width() - 56;
            
             var chart = new CanvasJS.Chart("chartContainer", {
                animationEnabled: true,
                theme: "light2",
                height: 600,
                width: tatol_width,
                title: {
                    text: "FollowUp Lead Status Report",
                    fontSize: 12,
                },
                axisY: {
                    title: "Number of Records",
                    includeZero: true,                    
                    crosshair: {
                        enabled: true
                    }
                },
                toolTip: {
                },
                legend: {
                    fontSize: 8,
                    reversed: true,
                    verticalAlign: "top",
                    horizontalAlign: "top"
                },
                data: types
            });
            chart.render();
            function toogleDataSeries(e) {
                if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                    e.dataSeries.visible = false;
                } else {
                    e.dataSeries.visible = true;
                }
                chart.render();
            }
        }


        function ajaxRequest(method, url, data = {}) {
            ajaxCall(method, url, data).then(function (res) {
                if (res.code == 200) {
                    var record = res.data;
                    var user_names = record.user_names;
                    var status = record.status;
                    var month_status = record.month_status;
                    var types = record.types;
                    var months = record.months;
                    ColChart(user_names, status, types);
                    if (months.length > 0) {
                        var thead = '';
                        thead += '<tr>';
                        thead += '<th>S.No</th>';
                        thead += '<th>Status</th>';
                        for (var i = 0; i < months.length; i++) {
                            thead += '<th style="text-transform:capitalize;">' + months[i] + '</th>';
                        }
                        thead += '</tr>';
                        $('#thead_month').html(thead);
                    }
                    if (month_status.length > 0) {
                        var tbody1 = '';
                        var index = 1;
                        for (var i = 0; i < month_status.length; i++) {
                            tbody1 += '<tr>';
                            tbody1 += '<td>' + index + '</td>';
                            tbody1 += '<td>' + month_status[i].name + '</td>';
                            if (month_status[i].data.length > 0) {
                                var type = $('.value').val();
                                for (var a = 0; a < months.length; a++) {
                                    if (month_status[i].data[a]) {
                                        tbody1 += '<td>' + month_status[i].data[a] + '</td>';
                                    } else {
                                        tbody1 += '<td>0</td>';
                                    }
                                }
                            }
                            index++;
                            tbody1 += '</tr>';
                        }
                        $('#tbody_month').html(tbody1);
                    }
                }
            })
        }
        var data = {
        };
        $( window ).load(function() {
        ajaxRequest('GET', base_url + "/tenant/lead/status/user/report/followup", data);
        });
        
        $('.save').click(function (e) {
            e.preventDefault();
            var type_id = $('.type_list').selectpicker('val');
            if (Array.isArray(type_id)) {
                type_id = type_id.join();
            }
            var start_date = $('.startDate').val();
            var end_date = $('.endDate').val();
            var time_slot = $('.duration').val();
            if (new Date(end_date) < new Date(start_date))
            {
                alert("Please ensure that the End Date is greater than or equal to the Start Date.");
                return false;
            }
            var type = $('.value').val();
            var datetype = $('.datetype').val();
            var data = {type_id: type_id, type: type, start_date: start_date, end_date: end_date, time_slot: time_slot,datetype:datetype};
            ajaxRequest('GET', base_url + "/tenant/lead/status/user/report/followup", data);
        })
        $(document).on('click', '#export-btn', function () {
            var type_id = $('.type_list').selectpicker('val');
            if (Array.isArray(type_id)) {
                type_id = type_id.join();
            }
            var start_date = $('.startDate').val();
            var time_slot = $('.startDate').val();
            var end_date = $('.endDate').val();
            if (new Date(end_date) < new Date(start_date))
            {
                alert("Please ensure that the End Date is greater than or equal to the Start Date.");
                return false;
            }
            var type = $('.value').val();
            var datetype = $('.datetype').val();
            var data = {type_id: type_id, type: type,time_slot:time_slot,start_date: start_date, end_date: end_date, export: true,datetype:datetype};
            console.log('data', data);
            var qString = $.param(data);
            var url = "{{URL::to('/tenant/lead/status/user/report/followup?')}}" + qString;
            document.location.href = url;
        });
    })
    $('.toggle-btn').click(function () {
        $('.show-toggle').fadeToggle();
    })
</script>
@include('tenant.include.footer')