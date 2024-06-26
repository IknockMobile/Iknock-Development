@include('tenant.include.header')
@include('tenant.include.sidebar')
<div class="right_col" role="main">
    <div class="row" id="content-heading">
        <div class="col-md-12">
            <h1 class="cust-head">Add Alerts</h1>
        </div>
    </div>
    <hr class="border">
    <div class="row" id="pg-form">
        @include('tenant.error')
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" class="submit_url" value="{{ URL::to('tenant/alerts/create') }}" />
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
            <div class="col-md-2">
                <button class="btn margintop ajax-button b1">Save</button>
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
@include('tenant.include.footer')