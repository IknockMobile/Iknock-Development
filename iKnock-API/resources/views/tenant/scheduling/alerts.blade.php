@include('tenant.include.header')
<style>
    tbody> tr{
        cursor: row-resize !important;
        font-size: 13px;
    }
    .template_key{
        text-transform: capitalize;
    }

</style>
@include('tenant.include.sidebar')
<div class="right_col" role="main">
    <div class="row" id="content-heading">
        <div class="col-md-10">
            <h1 class="cust-head">Alerts Lists</h1>
        </div>
        <div class="col-md-2 text-right">
            <a href="{{ URL::to('/tenant/lead/appoiment_alerts/create') }}" class="btn btn-info add-bt">Add</a>

        </div>
    </div>
    <hr class="border">
    <div class="row" id="pg-content">
        <table class="table table-striped jambo_table" id="scroll">
            <thead>
                <tr class="headings">
                    <td class="text-left">S.No</td>
                    <td class="text-left">Type <span class="sort sort_asc" style="cursor:pointer;" title="asc" data-column = "status"><i class="fas fa-sort-up" style="position:relative;left:10px;"></i></span> <span class="sort sort_desc" title="desc" style="margin-left:-2px;cursor:pointer;position:relative;top:4px;" data-column = "status" title="desc"><i class="fas fa-sort-down"></i></span></td>                    
                    <td class="text-left">Value <span class="sort sort_asc" style="cursor:pointer;" title="asc" data-column = "status"><i class="fas fa-sort-up" style="position:relative;left:10px;"></i></span> <span class="sort sort_desc" title="desc" style="margin-left:-2px;cursor:pointer;position:relative;top:4px;" data-column = "status" title="desc"><i class="fas fa-sort-down"></i></span></td>                    
                </tr>
            </thead>
            <tbody>

            </tbody>

        </table>
    </div> 
</div>
@include('tenant.include.footer')
<script src="{{asset('assets/js/tenant-js/lead_status.js')}}"></script>
<script type="text/javascript">
$(document).ready(function () {
    var columns = ['type', 'value'];
    loadGridWitoutAjax('GET', base_url + "/tenant/alerts/list", {}, {}, columns);
    $(document).on('click', '.sort', function () {
        var title = $(this).attr('title');
        var column_name = $(this).data('column');
        $('.title').val(title);
        $('.column_name').val(column_name);
        data = {order_by: column_name, order_type: title}
        loadGridWitoutAjax('GET', base_url + "/tenant/alerts/list", data, {}, columns);

    })
})
sortingTable('tbody', 'lead_detail', base_url + "/tenant/status/sorting/update");
function sortingTable(element = 'tbody', field_type = "", url) {
    $(element).on("sortbeforestop", function (event, ui) {
        var type = field_type;
        var sortedIDs = $(element).sortable("toArray");
        if (Array.isArray(sortedIDs)) {
            sortedIDs = sortedIDs.join();
        }
        data = {ids: sortedIDs};
        ajaxCall('POST', url, data)
    });
}
$('tbody').sortable();
</script>
