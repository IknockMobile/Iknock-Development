@include('tenant.include.header')
@include('tenant.include.sidebar')
<div class="right_col" role="main">
    <div class="row" id="content-heading">
        <div class="col-md-8">
            <h1 class="cust-head">Edit Alerts</h1>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn b2 delete">Delete</button>
        </div>
    </div>
    <hr class="border">
    <div class="row" id="pg-form">
        @include('tenant.error')
        <form>
            <input type="hidden" name="id" class="id" value=""/>
            <input type="hidden" class="submit_url" value="" />
            <input type="hidden" class="redirect_url" value="{{ URL::to('tenant/lead/appoiment_alerts') }}">
            {{ csrf_field() }}
            <div class="col-md-7">
                <label>ALert Type</label>
                <select name="type" class="input">
                    <option value="1">
                        Email
                    </option>
                    <option value="2">
                        Text SMS
                    </option>
                </select>
            </div>            
            <div class="col-md-7">
                <label>Email / Mobile Number</label>
                <input type="text" placeholder="Enter Value" class="input" name="value">
            </div>
            <div class="col-md-6">
                &nbsp;
            </div>
            <div class="col-md-4" style="margin-top: 15px;">
                <button class="btn  b2 ajax-button">Save</button>
            </div>
        </form>
    </div>
</div>
<script>
    $(function () {
        $('#demo').colorpicker({
            format: 'hex'
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        let current_url = window.location.href;
        current_url = current_url.split('/');
        let id = current_url.slice(-1)[0];
        $('.id').val(id);
        $('.submit_url').val("{{ URL::to('tenant/alerts/edit') }}" + "/" + id);
        var columns = ['type', 'value'];
        getEditRecord('GET', base_url + "/tenant/alerts/detail/" + id, {}, {}, columns);
        $('.delete').on('click', function () {
            var choice = confirm('Do you really want to delete this record?');
            if (choice === true) {
                let deleteRecord = "{{ URL::to('tenant/alerts/delete') }}" + "/" + id;
                ajaxCall('POST', deleteRecord, {id}, {}).then(function (res)
                {
                    if (res.code == 200)
                    {
                        $(".delete").prop('disabled', true);
                        var redirect_url = $('.redirect_url').val();
                        redirect_url = typeof redirect_url == 'undefined' ? window.location.href : redirect_url;
                        setTimeout(function () {
                            window.location.href = redirect_url;
                        }, 1000)
                    }

                })

            }
            return false;
        });
    })
</script>
@include('tenant.include.footer')
