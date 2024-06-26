@include('tenant.include.header')
@include('tenant.include.sidebar')
<style>
    .leadstatusbox{
        padding: 7px;
        font-size: 12px;
        color: #fff;
        text-shadow: 0px 0px 2px #000;
        border-radius: 19px;
        box-shadow: 0px 1px 1px black;
    }
</style>
<div class="right_col" role="main">
    <div class="container body">
        <div class="col-md-12">
            <div class="cust-head"><i class="fas fa-list"></i> Create Follow Up Lead Status</div>
        </div>
        <div class="col-md-12 mt-5">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Create Follow Up Lead Status</h5>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ route('tenant.follow-status.index') }}" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left"></i></a>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <form action="{{ route('tenant.follow-status.store') }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Title:</label>
                                    <input type="text" class="form-control title-input" placeholder="Enter Title" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Color Code:</label>
                                    <input type="color" class="form-control color-input" placeholder="Enter Color" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Is Followup:</label>
                                    <input type="checkbox" value="1" name="is_followup" class="is_followup">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Is Purchase:</label>
                                    <input type="checkbox" value="1" name="is_purchase"  class="is_purchase">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="button" class="btn submit-form btn-primary"><i class="fas fa-save"></i> Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('tenant.include.footer')
<script>
    $('.submit-form').click(function (e) {
        e.preventDefault();

        var title = $('.title-input').val();
        var color = $('.color-input').val();
        if ($(".is_purchase").is(":checked")) {
            var is_purchase = $('.is_purchase').val();
        } else {
            var is_purchase = 0;
        }
        
        if ($(".is_followup").is(":checked")) {
            var is_followup = $('.is_followup').val();
        } else {
            var is_followup = 0;
        }
        
        

        if (title == '') {
            alert('Please Enter Title.');
        } else {
            $.ajax({
                url: '/tenant/follow-status',
                type: 'post',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    title: title,
                    color: color,
                    is_purchase: is_purchase,
                    is_followup: is_followup
                },
                success: function (data) {
                    if (data.success) {
                        window.location.href = "/tenant/follow-status";
                    }
                }
            });
        }

    });
</script>
