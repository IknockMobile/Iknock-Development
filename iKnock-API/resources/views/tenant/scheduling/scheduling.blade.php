@include('tenant.include.header')
@include('tenant.include.sidebar')

<link href='{{ URL::to('fullcalendar/packages/core/main.css') }}' rel='stylesheet'/>
<link href='{{ URL::to('fullcalendar/packages/daygrid/main.css')}}' rel='stylesheet'/>
<link href='{{ URL::to('fullcalendar/packages/timegrid/main.css')}}' rel='stylesheet'/>
<link href='{{ URL::to('fullcalendar/packages/list/main.css')}}' rel='stylesheet'/>
<script src="{{ URL::to('fullcalendar/packages/core/main.js') }}"></script>
<script src="{{ URL::to('fullcalendar/packages/interaction/main.js') }}"></script>
<script src="{{ URL::to('fullcalendar/packages/daygrid/main.js') }}"></script>
<script src="{{ URL::to('fullcalendar/packages/timegrid/main.js') }}"></script>
<script src="{{ URL::to('fullcalendar/packages/list/main.js') }}"></script>

<div class="right_col" role="main">
    <div class="row" id="content-heading">
        <div class="col-md-10">
            <h1 class="cust-head">Scheduling</h1>
        </div>
        <div class="col-md-2 text-right">
            <a href="{{ URL::to('/tenant/scheduling/create') }}" class="btn btn-info add-bt">Add</a>
        </div>
    </div>
    <hr class="border">
    <div class="row">
        <div class="col-lg-3">
            <select class="form-control selectpicker lead_users" data-live-search="true" data-actions-box="true"
                    name="user_ids" value="" multiple>
                @foreach ($data['agent'] as $agent)
                <option value="{{ $agent->id }}">{{ $agent->first_name .' '. $agent->last_name }}</option>
                @if(!is_null(request()->get('userIds')) && request()->get('userIds') == $agent->id)
                <option data-tokens="{{ $agent->title }}" value="{{ $agent->id }}" selected > {{ $agent->first_name }} {{ $agent->last_name }} </option>
                @endif
                @endforeach
            </select>
        </div>
        <div class="col-lg-3">
            <button class="btn btn-primary filter-btn">Filter</button>
        </div>
    </div>
    <div class="row" id="pg-content">
        <input type="hidden" id="userIds">
        <div id='calendar'></div>
    </div>
</div>
<script>
$(document).ready(function () {
    var url = window.location.href;
    var param = getUrlParams(url);
    console.log(param);
    var userIds = [];

    $('.filter-btn').on('click', function () {
        userIds = userIds.join(',');
        var param = getUrlParams(url);
        var newUrl = "{{ URL::to('tenant/scheduling') }}";
        if (userIds) {
            newUrl += "?userIds=" + userIds;
        }
        window.location.href = newUrl;
    });

    function getUrlParams(url) {
        var params = {};
        (url + '?').split('?')[1].split('&').forEach(function (pair) {
            pair = (pair + '=').split('=').map(decodeURIComponent);
            if (pair[0].length) {
                params[pair[0]] = pair[1];
            }
        });
        return params;
    }

    var leadAUrl;
    if (param.userIds) {
        leadAUrl = "{{ URL::to('tenant/user/lead/appointment/list') }}?userIds=" + param.userIds;
    } else {
        leadAUrl = "{{ URL::to('tenant/user/lead/appointment/list') }}";
    }

    var calendarEl = $('#calendar');
    var calendar = new FullCalendar.Calendar(calendarEl[0], {
        plugins: ['interaction', 'dayGrid', 'timeGrid', 'list', 'bootstrap'],
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth'
        },
        aspectRatio: 1.5,
        displayEventEnd: {
            month: false,
            basicWeek: true,
            "default": true
        },
        navLinks: true, // can click day/week names to navigate views
        businessHours: false, // display business hours
        editable: false,
        events: {
            url: leadAUrl,
            dataType: 'json',
            extraParams: {},
            failure: function () {
                alert('There was an error while fetching events!');

            },
            success: function (response) {
                var data = response.data;
                var events = [];
                $(data).each(function (key, item) {
                    var title = item.address;
                    if (item.is_out_bound == 1 && item.appointment_result != null) {
                        title = item.appointment_result;
                    } else if (item.address == null) {
                        title = '';
                    }
                    const inputDateStr = item.appointment_date;
                    const [datePart, timePart] = inputDateStr.split(' ');
                    const [month, day, year] = datePart.split('-');
                    const [hours, minutes] = timePart.split(':');
                    const paddedHours = String(Number(hours)).padStart(2, '0');
                    const outputDateStr = `${year}-${month}-${day}T${paddedHours}:${minutes}:00`;

                    const inputDateStr1 = item.appointment_end_date;
                    const [datePart1, timePart1] = inputDateStr1.split(' ');
                    const [month1, day1, year1] = datePart1.split('-');
                    const [hours1, minutes1] = timePart1.split(':');
                    const paddedHours1 = String(Number(hours1)).padStart(2, '0');
                    const outputDateStr1 = `${year1}-${month1}-${day1}T${paddedHours1}:${minutes1}:00`;

                    events.push({
                        title: title,
                        id: item.appointment_id,
                        start: outputDateStr,
                        end: outputDateStr1,
                        is_out_bound: item.is_out_bound,
                        lead_id: item.id,
                    });

                    console.log(events);
                });

                return events;
            }
        },
        eventClick: function (info) {
            var appointmentUrl = "{{ URL::to('tenant/scheduling') }}" + "/" + info.event.id;
            var leadDetailUrl = "{{ URL::to('tenant/lead/edit') }}" + "/" + info.event.extendedProps.lead_id;
            if (info.event.extendedProps.is_out_bound == 1) {
                window.open(appointmentUrl);
            } else {
                window.open(appointmentUrl);
            }
            info.el.style.borderColor = 'red';
        },
    });

    calendar.render();

    $('.lead_users').on('change', function () {
        userIds = $(this).val();
    });
});
</script>
@include('tenant.include.footer')
