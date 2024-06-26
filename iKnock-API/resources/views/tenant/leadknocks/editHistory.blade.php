@include('tenant.include.header')
@include('tenant.include.sidebar')
<div class="right_col" role="main">
    <div class="container body">
        <div class="col-md-8">
            <div class="cust-head">Edit History</div>
        </div>

        <div class="col-md-4 text-right">
            <?php
            if (isset($_GET['type']) AND $_GET['type'] == 'contract') {
                ?>
                <a href="{{ URL::to('tenant/dashboard/contract/list') }}" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left"></i></a>
                <?php
            } elseif (isset($_GET['type']) AND $_GET['type'] == 'purchase') {
                ?>
                <a href="{{ URL::to('tenant/dashboard/purchase/list') }}" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left"></i></a>
                <?php
            } elseif (isset($_GET['type']) AND $_GET['type'] == 'appointments_kept') {
                ?>
                <a href="{{ URL::to('tenant/dashboard/appointments_kept/list') }}" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left"></i></a>
            <?php } elseif (isset($_GET['type']) AND $_GET['type'] == 'appointments_requested') {
                ?>
                <a href="{{ URL::to('tenant/dashboard/appointments_requested/list') }}" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left"></i></a>
            <?php } else { ?>
                <a href="{{ URL::to('tenant/dashboard') }}" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left"></i></a>
            <?php } ?>



        </div>
        <div class="col-md-12">
            <div class="panel">                
                <div class="panel-body">
                    <form action="{{ URL::to('/tenant/history/'.$history->id.'/edit') }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Lead Name:</label>
                                    <input type="text" class="form-control" value="{{ $lead->title }}"  disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Status:</label>
                                    <input type="hidden" name="history_id" value="{{ $history->id }}"> 
                                    <select name="status_id" id="" class="form-control">
                                        <label for="">Status: </label>
                                        @forelse($status as $key=>$value)
                                        @if($status_id == $key)
                                        <option value="{{ $key }}" selected>{{ $value }}</option>
                                        @else
                                        <option value="{{ $key }}">{{ $value }}</option>
                                        @endif
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group text-center">
                                    <button type="submit" class="submit_url btn btn-success">Save</button>
                                    <?php
                                    if (isset($_GET['type']) AND $_GET['type'] == 'contract') {
                                        ?>
                                        <a href="{{ URL::to('tenant/dashboard/contract/list') }}" class="btn btn-primary" style="margin-top:6px;">Back</a>
                                        <?php
                                    } elseif (isset($_GET['type']) AND $_GET['type'] == 'purchase') {
                                        ?>
                                        <a href="{{ URL::to('tenant/dashboard/purchase/list') }}" class="btn btn-primary" style="margin-top:6px;">Back</a>
                                        <?php
                                    } elseif (isset($_GET['type']) AND $_GET['type'] == 'appointments_kept') {
                                        ?>
                                        <a href="{{ URL::to('tenant/dashboard/appointments_kept/list') }}" class="btn btn-primary" style="margin-top:6px;">Back</a>
                                    <?php } elseif (isset($_GET['type']) AND $_GET['type'] == 'appointments_requested') {
                                        ?>
                                        <a href="{{ URL::to('tenant/dashboard/appointments_requested/list') }}" class="btn btn-primary" style="margin-top:6px;">Back</a>
                                    <?php } else { ?>
                                        <a href="{{ URL::to('tenant/dashboard') }}" class="btn btn-primary" style="margin-top:6px;">Back</a>
                                    <?php } ?>
                                </div>
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
    $('.submit_url').click(function (e) {
        e.preventDefault();
        $(this).parents().find('form').submit();
    });
</script>
