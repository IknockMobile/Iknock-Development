var table;
var api_response_collection = [];

$(function(){
    $('#last_seen').editable({
        format: 'yyyy-mm-dd hh:ii',    
        viewformat: 'dd/mm/yyyy hh:ii',    
        datetimepicker: {
                weekStart: 1
           }
    });
});
$.ajaxSetup({
    headers: {

        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});


window.onload = function() {


    // Select your input element.
    var number = document.getElementById('input-number');

    // Listen for input event on numInput.

    if(number !== null){
        number.onkeydown = function(e) {
            if (!((e.keyCode > 95 && e.keyCode < 106) ||
                    (e.keyCode > 47 && e.keyCode < 58) ||
                    e.keyCode == 8)) {
                return false;
            }
        }
    }


}


function getEditRecord(method, url, data = {}, headers = {}, columns = [], element = "tbody") {
    ajaxCall(method, url, data = {}, headers = {}).then(function(res) {
        if (res.code == 200) {
            let record = res.data;

            // sorting_filed_step_3

            $('.house-name-input').val(record.title);
            $('.address-input').val(record.address);
            $('.city-input').val(record.city);
            $('.state-input').val(record.state);
            $('.county-input').val(record.county);
            $('.zip-code-input').val(record.zip_code);
            $('.auction-input').val(record.auction);
            $('.lead_value-input').val('$'+$.number(record.lead_value));
            $('.original_loan-input').val('$'+$.number(record.original_loan));
            $('.loan_date-input').val(record.loan_date);
            $('.sq_ft-input').val(record.sq_ft);
            $('.yr_blt-input').val(record.yr_blt);
            $('.admin_notes-input').val(record.admin_notes);
            $('.eq-input').val(record.eq);
          
            $('.mortgagee-input').val(record.mortgagee);
            $('.loan_type-input').val(record.loan_type);
            $('.loan_mod-input').val(record.loan_mod);
            $('.trustee-input').val(record.trustee);
            $('.owner_address-input').val(record.owner_address);
            $('.source-input').val(record.source);

            for (var c = 0; c < columns.length; c++) {

                if (columns[c] == 'media') {

                    var ulHtml = '<ul class="navbar-nav cust-nav" style="padding-left:0px;">';
                    if (record[columns[c]].length > 0) {
                        for (var i = 0; i < record[columns[c]].length; i++) {
                            ulHtml += '<li>';
                            if (record[columns[c]][i].media_type != "image") {

                                ulHtml += '<a href="' + record[columns[c]][i].path + '" target="_blank"><img src="' + record[columns[c]][i].thumb + '" style="width: 100px;height: 100px;" class="image-url"></a>';
                                ulHtml += '<a data-mediaId="' + record[columns[c]][i].id + '" class="btn cross _delete_media" style="color:black !important;font-size: 20px;">x</a>';
                                ulHtml += '</li>';
                            } else {
                                ulHtml += '<img src="' + record[columns[c]][i].thumb + '" style="width: 100px;height: 100px;" class="image-url">';
                                ulHtml += '<a data-mediaId="' + record[columns[c]][i].id + '" class="btn cross _delete_media" style="color:black !important;font-size: 20px;">x</a>';
                                ulHtml += '</li>';
                            }
                        }
                        $('.view_image').html(ulHtml);
                    }
                    ulHtml += '</ul>';
                } else if (columns[c] == 'image_url') {
                    var ulHtml = '<ul class="navbar-nav" style="float:none;">';
                    ulHtml += '<img src="' + record[columns[c]] + '" style="border-radius:50%;width:200px;height:200px;margin: 0 auto;" class="img-responsive">';
                    ulHtml += '<a data-mediaId="' + record[columns[c]].id + '" class="btn cross _delete_media" style="color:black !important;font-size: 20px;">x</a>';
                    ulHtml += '</li>';
                    $('.view_image').html(ulHtml);
                    ulHtml += '</ul>';

                } else if (columns[c] == 'nav_user_name') {

                    var new_user_image = record.image_url;
                    var new_name = record.name;
                    var aHtml = '';
                    aHtml += '<a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false"> <img src="' + new_user_image + '" alt="">' + new_name + '<span class=" fa fa-angle-down" style="display:inline-block;padding-left:10px;"></span> </a>';
                    $('.view_user').prepend(aHtml);


                } else if (columns[c] == 'comm_target_id') {
                    $('.target_id').val(record.user_id);

                } else if (columns[c] == 'old_status') {
                    var old_status_id = record.status.id;
                    $('select[name="status_id"] option[value="' + old_status_id + '"]').attr('selected', 'selected')

                    $('.selectpicker').selectpicker('refresh')
                } else if (columns[c] == 'is_expired') {
                    var is_expired = record.is_expired;
                    $('select[name="is_expired"] option[value="' + is_expired + '"]').attr('selected', 'selected')

                    $('.selectpicker').selectpicker('refresh')
                } else if (columns[c] == 'old_type') {
                    var old_type_id = record.type.id;

                    $('select[name="type_id"] option[value="' + old_type_id + '"]').attr('selected', 'selected')

                    $('.selectpicker').selectpicker('refresh')
                } else if (columns[c] == 'assignee') {
                    var old_assignee_id = record.assignee.id;
                    $('select[name="target_id"] option[value="' + old_assignee_id + '"]').attr('selected', 'selected')

                    $('.selectpicker').selectpicker('refresh')
                } else if (columns[c] == 'user_status_id') {


                    var user_status_id = record.user_status_id;
                    $('select[name="user_status_id"] option[value="' + user_status_id + '"]').attr('selected', 'selected')

                    $('.selectpicker').selectpicker('refresh')
                }else if (columns[c] == 'startup_paid') {
                    var startup_paid = record.startup_paid;
                    
                    $('select[name="startup_paid"] option[value="' + startup_paid + '"]').attr('selected', 'selected')

                    $('.selectpicker').selectpicker('refresh')
                }else if (columns[c] == 'startup_reimbursed') {


                    var startup_reimbursed = record.startup_reimbursed;
                    $('select[name="startup_reimbursed"] option[value="' + startup_reimbursed + '"]').attr('selected', 'selected')

                    $('.selectpicker').selectpicker('refresh')
                } else if (columns[c] == 'user_group_id') {
                    var new_user_type = record.user_group_id;

                    $('select[name="user_group_id"] option[value="' + new_user_type + '"]').attr('selected', 'selected');
                    $('.selectpicker').selectpicker('refresh');
                } else if (columns[c] == 'template_title') {
                    var new_template_title = record[c].title;


                    $('[name="' + columns[c] + '"]').val(new_template_title);
                } else if (columns[c] == 'new_commission_event') {
                    var new_comm_title = record.commission_event;

                    $('select option[value="' + new_comm_title + '"]').attr('selected', 'selected');
                    $('.selectpicker').selectpicker('refresh');
                }

                // else if(columns[c] == 'month')
                // {
                //     var new_month = record.month;
                //     console.log("hey", new_month);

                //     $('[name="' + columns[c] + '"]').val(new_month);
                // }
                else {


                    $('[name="' + columns[c] + '"]').val(record[columns[c]]);
                }

            }
        } else {
            alert(res.message);
        }
    });
}

function ajaxCall(method, url, data = {}, headers = {}, async = true) {

    return new Promise(function(resolve, reject) {
        $.ajax({
            type: method,
            url: url,
            data: data,
            headers: headers,
            async: async,
        beforeSend: function() {
        
              
            },
            success: function(res) {
                 
                if (res.code >= 200 || res.code <= 300) {
                    resolve(res);

                } else {
                    console.log("res",res);
                    let error_html = '';
                    var messages = res.data[0];
                    for (message in messages) {
                        error_html += '<li>' + messages[message] + '</li>';

                    }
                    $('.error').html(error_html);
                    $('.error').show();
                    $(".delete").prop('disabled', true);
                }


            }
        });
    })
}

function loadSelectBox(method, url, params = {}, headers = {}, columns = []) {
    var options_html = '';
    ajaxCall(method, url, params, headers).then(function(res) {
        if (res.code == 200) {
            var record = res.data;

            if (record.length > 0) {
                for (var i = 0; i < record.length; i++) {
                    var new_count = record[i].lead_count;
                    var new_color = record[i].color_code;
                    var new_id = record[i].id;
                    var lead_per = record[i].lead_percentage;
                    options_html += '<option value="' + record[i].id + '">' + record[i].title + ' ' + lead_per + '% <span class="unicode">' + new_count + '</span></option>';
                }
                $('[name="lead_type_status"]').append(options_html);
                $('.selectpicker').selectpicker('refresh')
            }
        }
    });
}

// ajax call
function loadGridWitoutAjax(method, url, params = {}, headers = {}, columns = [], element = 'tbody', readData = '', redirect = true, pagination = false, check = true, filtered, indexing = true, api_resp = '') {
    return new Promise(function(resolve, reject) {
        

        ajaxCall(method, url, params, headers).then(function(res) {
            if (res.code == 200) {                
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

//ajax datatable
var ajaxDatatable = (element, source_url, pageLength = 25, columns = [], field = '') => {

    var columnJson = [];
    var tbodyHtml = '';
    var page_count = 1;

    for (var c = 0; c < columns.length; c++) {
        columnJson.push({
            "data": columns[c]
        });

    }
    var ids;
    var action;
    table = $(element).DataTable({
        "processing": true,
        "serverSide": true,
        "ordering": false,
        searching: false,
        "lengthChange": false,
        createdRow: function(row, data, dataIndex) {

            if (field == 'type') {
                $(row).attr('data-href', window.location.href + '/edit/' + data.id + '?type=' + data.type);
                $(row).attr('class', 'redirect');
                $(row).attr('id', data.id);

            } else {
                $(row).attr('data-href', window.location.href + '/edit/' + data.id);
                $(row).attr('class', 'redirect');
                $(row).attr('id', data.id);
            }


        },
        "ajax": {
            url: source_url,
            type: "GET",
            beforeSend: function() {
                //$('button').attr('disbaled','disabled');
            },
            data: function(d) {
                var current_page_index = (d.start / 15) + 1.;;;
                d.current_page_index = current_page_index;
                d.agent_ids =  $('.agent_ids').val();
                d.start_date =  $('.start').val();
                d.end_date =  $('.end').val();
                d.commission_events =  $('.commission_events').val();
                d.order_by = $('.column_name').val();
                d.order_type = $('.title').val();
                $('#current_page_index').val(current_page_index);
                delete d.columns;
            },
            error: function() { // error handling

            }
        },
        drawCallback: function(settings) {
            // other functionality
            // $('button').removeAttr('disbaled');
        },
        lengthMenu: [
            [10, 20, 50, 100, 200],
            [10, 20, 50, 100, 200] // change per page values here
        ],
        pageLength: pageLength, // default record count per page

        "columns": columnJson
    });

}

$('body').on('change', '.isCheckField', function(event) {
    event.preventDefault();
    var obj = $(this);
    var value = $(this).attr('checked','checked').val();
    var id = $(this).attr('data-id');
   $.ajax({
        url: '/tenant/tenant-custom-field/in-active',
        type: 'post',
        data: {
            _token:$('meta[name="csrf-token"]').attr('content'),
            id:id, 
            value:value, 
        },
        success: function (data) {

            if(data.success == 1){
                if(value == 1){
                    obj.val(0);
                    obj.parent().find('.labelactive').text('Yes');
                }else{
                    obj.parent().find('.labelactive').text('No');
                    obj.val(1);
                }
            }

        }
    });
});

$('body').on('click', '.isretRiedUpdate', function(event) {
    event.preventDefault();
    var obj = $(this);
    var lead_id = obj.attr('data-id');

    var value = obj.attr('checked','checked').val();

    // if(value == 1){
    //     var isConfirm = confirm('Are you sure lead is retired');
    // }else{
    //     var isConfirm = confirm('Are you sure lead is unretired');
    // }

       $.ajax({
        url: '/tenant/leads/wizard/is-exprired',
        type: 'post',
        data: {
            _token:$('meta[name="csrf-token"]').attr('content'),
            lead_id:lead_id, 
            value:value, 
        },
        success: function (data) {

            if(data.success == 1){
                if(value == 1){
                    obj.attr('checked','checked');
                    obj.prop('checked', true);
                    // obj.parent().find('.labelactive').text('No');
                    obj.val(0);
                }else{
                    obj.val(1);
                    obj.removeAttr('checked');
                    // obj.parent().find('.labelactive').text('Yes');
                }
            }

            toastr.success('Is Retired updated successfully','Success Alert', {timeOut: 5000});

        }
    });

    return false;
});


$('body').on('click', '.isUserStatusUpdate', function(event) {
    event.preventDefault();
    var obj = $(this);

    var id = obj.attr('data-id');
    var value = $(this).prop('checked');

    if(value){
        value = 1;
    }else{
        value = 0;
    }

    var data = {id:id,value:value};

    ajaxCall('POST', "/tenant/agent/is/status",data, {}).then(function (res) {
        
        if(value){
            obj.prop('checked', true);
            toastr.success('User activated successfully.','Success Alert', {timeOut: 5000});
        }else{
            obj.removeAttr('checked');
            toastr.success('User deactivated successfully.','Success Alert', {timeOut: 5000});
        }


    });

    return false;
});

$('body').on('click', '.isfollowup', function(event) {
    event.preventDefault();
    var obj = $(this);
    var lead_id = obj.attr('data-id');

       $.ajax({
        url: '/tenant/leads/wizard/is-followup',
        type: 'post',
        data: {
            _token:$('meta[name="csrf-token"]').attr('content'),
            lead_id:lead_id, 
            value:1, 
        },
        success: function (data) {
            obj.parent().parent().hide(1000)
            toastr.success('Lead Follow-up Successfully','Success Alert', {timeOut: 5000});
        }
    });

    return false;
});

$('body').on('click', '.isLeadup', function(event) {
    event.preventDefault();
    var obj = $(this);
    var followup_lead_id = obj.attr('data-id');

       $.ajax({
        url: '/tenant/leads/wizard/is-leadup',
        type: 'post',
        data: {
            _token:$('meta[name="csrf-token"]').attr('content'),
            lead_id:followup_lead_id, 
            value:1, 
        },
        success: function (data) {
            obj.parent().parent().hide(1000)
            toastr.success('Lead Follow-up Successfully','Success Alert', {timeOut: 5000});
        }
    });

    return false;
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

     $('.detailupdate').editable({
       send: 'always',
       url: "/tenant/deal/update/editable",
       success: function(response) {
         console.log('hello');
       }
     });
}



 $('.custom-list-sort').sortable({
    swapThreshold: 1,
      animation: 150,
      update: function(event, ui) {
             // var sortedIDs = $('tbody').sortable("toArray");

             // var url = "{{ URL::to('/tenant/purchase/sortable') }}";

             // ajaxCall('POST', url, {sortedids:sortedIDs}, {}).then(function (res) {
             //    toastr.success('Follow Up Lead View Setup Order updated successfully.','Success Alert', {timeOut: 5000});
             // });
      }
   });