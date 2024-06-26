@include('tenant.include.header')
@include('tenant.include.sidebar')
<div class="right_col" role="main">
    <div class="row" id="content-heading">
        <div class="col-md-10">
            <h1 class="cust-head">Lead Type</h1>
        </div>
        <div class="col-md-2 text-right">
            <a href="{{ URL::to('/tenant/lead/lead_type/create') }}" class="btn btn-info add-bt">Add</a>
        </div>
    </div>
    <hr class="border">
    <!--content-heading-end-->
    <div class="row" id="pg-content">
        <!--content-table here-->
        <table class="table table-striped jambo_table" id="scroll">
            <thead>
                <tr class="headings">
                    <td class="text-left">S.No</td>
                    <td class="text-left">Lead Type Name <span class="sort sort_asc" style="cursor:pointer;" title="asc" data-column = "type"><i class="fas fa-sort-up" style="position:relative;left:10px;"></i></span> <span class="sort sort_desc" title="desc" style="margin-left:-2px;cursor:pointer;position:relative;top:4px;" data-column = "type" title="desc"><i class="fas fa-sort-down"></i></span></td>
                    <td class="text-left">Code <span class="sort sort_asc" style="cursor:pointer;" title="asc" data-column = "code"><i class="fas fa-sort-up" style="position:relative;left:10px;"></i></span> <span class="sort sort_desc" title="desc" style="margin-left:-2px;cursor:pointer;position:relative;top:4px;" data-column = "code" title="desc"><i class="fas fa-sort-down"></i></span></td>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
<script src="{{asset('assets/js/tenant-js/lead_type.js')}}"></script>
<script type="text/javascript">
$(document).ready(function () {
    var columns = ['title', 'code'];
    loadGridWitoutAjax('GET', base_url + "/tenant/type/list", {}, {}, columns);

    $(document).on('click', '.sort', function () {

        var title = $(this).attr('title');
        var column_name = $(this).data('column');
        $('.title').val(title);
        $('.column_name').val(column_name);

        // if(title == 'asc'){
        //      $(this).hide(); 
        //      $('.sort[title="desc"]').show(); 
        // }else
        // {
        //    $(this).hide(); 
        //    $('.sort[title="asc"]').show();      
        // }
        data = {order_by: column_name, order_type: title}
        loadGridWitoutAjax('GET', base_url + "/tenant/type/list", data, {}, columns);

    })
})
</script>

@include('tenant.include.footer')
