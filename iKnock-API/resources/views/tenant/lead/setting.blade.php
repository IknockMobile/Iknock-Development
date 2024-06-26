@include('tenant.include.header')
@include('tenant.include.sidebar')
<div class="right_col" role="main">
    <div class="row" id="content-heading">
        <div class="col-md-12">
            <h1 class="cust-head">Setting for records displayed per screen</h1>
        </div>
    </div>
    <hr class="border">
    <div class="row" id="pg-form">
        @include('tenant.error')
        <?php 
        $PaginationlimitData = App\Models\Paginationlimit::where('id', '=', 1)->first();
        ?>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" class="submit_url" value="{{ URL::to('tenant/setting/create') }}" />
            <input type="hidden" class="redirect_url" value="{{ URL::to('tenant/lead/setting/create') }}">
            {{ csrf_field() }}
            <div class="col-md-3">
                <label>
                    Lead Management
                </label>
                <select class="input" name="lead_management">
                    <option <?php if(isset($PaginationlimitData->lead_management) AND $PaginationlimitData->lead_management == 10){ echo "selected"; } ?> value="10">10</option>
                    <option <?php if(isset($PaginationlimitData->lead_management) AND $PaginationlimitData->lead_management == 20){ echo "selected"; } ?> value="20">20</option>
                    <option <?php if(isset($PaginationlimitData->lead_management) AND $PaginationlimitData->lead_management == 50){ echo "selected"; } ?> value="50">50</option>
                    <option <?php if(isset($PaginationlimitData->lead_management) AND $PaginationlimitData->lead_management == 100){ echo "selected"; } ?> value="100">100</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>
                    Follow Up Lead Management
                </label>
                <select class="input" name="followup_lead_management">
                    <option <?php if(isset($PaginationlimitData->followup_lead_management) AND $PaginationlimitData->followup_lead_management == 10){ echo "selected"; } ?> value="10">10</option>
                    <option <?php if(isset($PaginationlimitData->followup_lead_management) AND $PaginationlimitData->followup_lead_management == 20){ echo "selected"; } ?> value="20">20</option>
                    <option <?php if(isset($PaginationlimitData->followup_lead_management) AND $PaginationlimitData->followup_lead_management == 50){ echo "selected"; } ?> value="50">50</option>
                    <option <?php if(isset($PaginationlimitData->followup_lead_management) AND $PaginationlimitData->followup_lead_management == 100){ echo "selected"; } ?> value="100">100</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>
                    Purchase Lead Management
                </label>
                <select class="input" name="purchase_lead_management">
                    <option <?php if(isset($PaginationlimitData->purchase_lead_management) AND $PaginationlimitData->purchase_lead_management == 10){ echo "selected"; } ?> value="10">10</option>
                    <option <?php if(isset($PaginationlimitData->purchase_lead_management) AND $PaginationlimitData->purchase_lead_management == 20){ echo "selected"; } ?> value="20">20</option>
                    <option <?php if(isset($PaginationlimitData->purchase_lead_management) AND $PaginationlimitData->purchase_lead_management == 50){ echo "selected"; } ?> value="50">50</option>
                    <option <?php if(isset($PaginationlimitData->purchase_lead_management) AND $PaginationlimitData->purchase_lead_management == 100){ echo "selected"; } ?> value="100">100</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>
                    Deal Lead Management
                </label>
                <select class="input" name="deal_management">
                    <option <?php if(isset($PaginationlimitData->deal_management) AND $PaginationlimitData->deal_management == 10){ echo "selected"; } ?> value="10">10</option>
                    <option <?php if(isset($PaginationlimitData->deal_management) AND $PaginationlimitData->deal_management == 20){ echo "selected"; } ?> value="20">20</option>
                    <option <?php if(isset($PaginationlimitData->deal_management) AND $PaginationlimitData->deal_management == 50){ echo "selected"; } ?> value="50">50</option>
                    <option <?php if(isset($PaginationlimitData->deal_management) AND $PaginationlimitData->deal_management == 100){ echo "selected"; } ?> value="100">100</option>
                </select>
            </div>
            <div class="col-md-12">
                &nbsp;
            </div>
            <div class="col-md-3">
                <label>
                    Marketing Lead Management
                </label>
                <select class="input" name="marketing_lead_management">
                    <option <?php if(isset($PaginationlimitData->marketing_lead_management) AND $PaginationlimitData->marketing_lead_management == 10){ echo "selected"; } ?> value="10">10</option>
                    <option <?php if(isset($PaginationlimitData->marketing_lead_management) AND $PaginationlimitData->marketing_lead_management == 20){ echo "selected"; } ?> value="20">20</option>
                    <option <?php if(isset($PaginationlimitData->marketing_lead_management) AND $PaginationlimitData->marketing_lead_management == 50){ echo "selected"; } ?> value="50">50</option>
                    <option <?php if(isset($PaginationlimitData->marketing_lead_management) AND $PaginationlimitData->marketing_lead_management == 100){ echo "selected"; } ?> value="100">100</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>
                    knock List
                </label>
                <select class="input" name="knock_list">
                    <option <?php if(isset($PaginationlimitData->knock_list) AND $PaginationlimitData->knock_list == 10){ echo "selected"; } ?> value="10">10</option>
                    <option <?php if(isset($PaginationlimitData->knock_list) AND $PaginationlimitData->knock_list == 20){ echo "selected"; } ?> value="20">20</option>
                    <option <?php if(isset($PaginationlimitData->knock_list) AND $PaginationlimitData->knock_list == 50){ echo "selected"; } ?> value="50">50</option>
                    <option <?php if(isset($PaginationlimitData->knock_list) AND $PaginationlimitData->knock_list == 100){ echo "selected"; } ?> value="100">100</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>
                     Purchase Conversion Rate 
                </label>
                <select class="input" name="purchase_conversion_rate">
                    <option <?php if(isset($PaginationlimitData->purchase_conversion_rate) AND $PaginationlimitData->purchase_conversion_rate == 10){ echo "selected"; } ?> value="10">10</option>
                    <option <?php if(isset($PaginationlimitData->purchase_conversion_rate) AND $PaginationlimitData->purchase_conversion_rate == 20){ echo "selected"; } ?> value="20">20</option>
                    <option <?php if(isset($PaginationlimitData->purchase_conversion_rate) AND $PaginationlimitData->purchase_conversion_rate == 50){ echo "selected"; } ?> value="50">50</option>
                    <option <?php if(isset($PaginationlimitData->purchase_conversion_rate) AND $PaginationlimitData->purchase_conversion_rate == 100){ echo "selected"; } ?> value="100">100</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>
                      Contract Conversion Rate 
                </label>
                <select class="input" name="contract_conversion_rate">
                    <option <?php if(isset($PaginationlimitData->contract_conversion_rate) AND $PaginationlimitData->contract_conversion_rate == 10){ echo "selected"; } ?> value="10">10</option>
                    <option <?php if(isset($PaginationlimitData->contract_conversion_rate) AND $PaginationlimitData->contract_conversion_rate == 20){ echo "selected"; } ?> value="20">20</option>
                    <option <?php if(isset($PaginationlimitData->contract_conversion_rate) AND $PaginationlimitData->contract_conversion_rate == 50){ echo "selected"; } ?> value="50">50</option>
                    <option <?php if(isset($PaginationlimitData->contract_conversion_rate) AND $PaginationlimitData->contract_conversion_rate == 100){ echo "selected"; } ?> value="100">100</option>
                </select>
            </div>
            <div class="col-md-12">
                &nbsp;
            </div>
            <div class="col-md-3">
                <label>
                       Appointments Requested Conversion Rate 
                </label>
                <select class="input" name="appointments_requested_conversion_rate">
                    <option <?php if(isset($PaginationlimitData->appointments_requested_conversion_rate) AND $PaginationlimitData->appointments_requested_conversion_rate == 10){ echo "selected"; } ?> value="10">10</option>
                    <option <?php if(isset($PaginationlimitData->appointments_requested_conversion_rate) AND $PaginationlimitData->appointments_requested_conversion_rate == 20){ echo "selected"; } ?> value="20">20</option>
                    <option <?php if(isset($PaginationlimitData->appointments_requested_conversion_rate) AND $PaginationlimitData->appointments_requested_conversion_rate == 50){ echo "selected"; } ?> value="50">50</option>
                    <option <?php if(isset($PaginationlimitData->appointments_requested_conversion_rate) AND $PaginationlimitData->appointments_requested_conversion_rate == 100){ echo "selected"; } ?> value="100">100</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>
                        Appointments KEPT Conversion Rate 
                </label>
                <select class="input" name="appointments_kept_conversion_rate">
                    <option <?php if(isset($PaginationlimitData->appointments_kept_conversion_rate) AND $PaginationlimitData->appointments_kept_conversion_rate == 10){ echo "selected"; } ?> value="10">10</option>
                    <option <?php if(isset($PaginationlimitData->appointments_kept_conversion_rate) AND $PaginationlimitData->appointments_kept_conversion_rate == 20){ echo "selected"; } ?> value="20">20</option>
                    <option <?php if(isset($PaginationlimitData->appointments_kept_conversion_rate) AND $PaginationlimitData->appointments_kept_conversion_rate == 50){ echo "selected"; } ?> value="50">50</option>
                    <option <?php if(isset($PaginationlimitData->appointments_kept_conversion_rate) AND $PaginationlimitData->appointments_kept_conversion_rate == 100){ echo "selected"; } ?> value="100">100</option>
                </select>
            </div>
            <div class="col-md-12">
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