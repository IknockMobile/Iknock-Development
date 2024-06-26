$(document).ready(function() {

    $('button, .ajax-button, .submit').addClass('b1').removeClass('b2');
    $('.btn-info').addClass('b1').removeClass('add-bt');
    $('.select-all, .deselect-all').removeClass('b1');

    $('form').submit(function(e) {

        var redirect_url = $('.redirect_url').val();
        redirect_url = typeof redirect_url == 'undefined' ? window.location.href : redirect_url;
        e.preventDefault();
        var formData = new FormData(this);

        var btntext = $('.ajax-button').text();
        $('.ajax-button').html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> '+btntext);

        $.ajax({
            type: "POST",
            url: $('.submit_url').val(),
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $(this).attr('disabled', 'disabled');
                $('.clear').attr('disabled', 'disabled');
                $('.add_field').attr('disabled', 'disabled');
                $('.btn-circle').attr('disabled', 'disabled');
                $('.import_wizard').attr('disabled', 'disabled');

                $('.btn-circle').off('click');
                $('.error').hide();
                $("#loader").show();

                $(".ajax-button").prop('disabled', true);

            },
            success: function(res) {
                $("#loader").hide();
                // $("input, select, textarea, button").prop("disabled", false);

                $(this).removeAttr('disabled');
                if (res.code == 200) {
                    $("#loader").hide();
                    success_html = '<li>' + res.message + '</li>';
                    $('.success').html(success_html);
                    $('.success').show();
                    $(".ajax-button").prop('disabled', true);
                        window.location.href = redirect_url;
                } else {
                    $("#loader").hide();
                    $(".ajax-button").prop('disabled', false);
                    let error_html = '';
                    if(res.data != undefined){
                        var messages = res.data[0];
                        for (message in messages) {
                            error_html += '<li>' + messages[message] + '</li>';
                        }
                        $('.error').html(error_html);

                       toastr.error('Please check the error', 'Error Alert', {timeOut: 5000,progressBar: true});

                        $('.error').show();
                        $('.add_field').removeAttr('disabled');

                        $('.import_wizard').removeAttr('disabled');
                        $('.clear').removeAttr('disabled');
                        $('.ajax-button i').removeClass('fa fa-spinner fa-spin');
                    }else{

                        success_html = '<li>user created successfully </li>';
                        $('.success').html(success_html);

                        window.location.href = redirect_url;
                        $('.ajax-button i').removeClass('fa fa-spinner fa-spin');
                    }
                }
            }
        });

    })

    $(document).on('click', '.link', function() {
        var id = $(this).attr('id');
        var data = {
            id: id
        };
        ajaxCall('POST', base_url + "/tenant/agent/reset/" + id, data).then(function(res) {
            if (res.code == 200) {

                success_html = '<li>' + res.message + '</li>';
                $('.success').html(success_html);
                $('.success').show();
                setTimeout(function() {
                    $(".success").hide('blind', {}, 500)
                }, 2000);
            } else {

                let error_html = '';

                var messages = res.data[0];
                for (message in messages) {
                    error_html += '<li>' + messages[message] + '</li>';
                }
                $('.error').html(error_html);

                $('.error').show();
            }
        })

        return false;
    })


    $(document).on('click', '#redirect2', function(e) {

        if (window.event.target.name == 'lead_ids') {

        } else {

            var id = $(this).closest('tr').find('input[type="checkbox"]').val();

            var getUrl = new_param_url + '/edit/' + id;

            // var redirect_url = getUrl .protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1]+'/lead/edit/'+id;
            let redirect_url = getUrl;
            window.location.href = redirect_url;
        }

    })

    //redirect edit page
    $(document).on('click', '.redirect', function(e) {


        e.preventDefault();
        let redirect_url = $(this).data('href');


        window.location.href = redirect_url;



    });

    //delete media script
    $(document).on('click', '._delete_media', function() {

        var msg = confirm("Are you sure you want to continue?");
        if (msg) {
            var mediaID = $(this).data('mediaid');
            var get_all_media_id = $('.delete_media').val();
            if (get_all_media_id == '') {
                $('.delete_media').val(mediaID);
            } else {
                $('.delete_media').val(get_all_media_id + ',' + mediaID);
            }
            $(this).parent().hide('slow');
        } else {
            return false
        }
    })

});
