@include('tenant.include.header')
@include('tenant.include.sidebar')
<div class="right_col" role="main">
    <div class="row" id="content-heading">
        <div class="col-md-9">
            <h1 class="cust-head">Mail-chimp Subscriber Management</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Mail-chimp Subscriber Management</h5>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ route('tenant.campaign.user.create') }}"  class="btn btn-info"><i class="fas fa-plus"></i></a>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <table>
                        <table class="table table-bordered table-hover table-inverse">
                            <thead>
                                <tr>
                                    <th width="200">Id</th>
                                    <th>Name</th>
                                    <th>Email address</th>
                                    <th>Status</th>
                                    <th width="200">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $key=>$user)                                
                                <?php
                                    $user_data = json_decode($user->user_data);                                                                        
                                ?>
                                <tr>                                    
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email_address }}</td>
                                    <td>{{ $user_data->status }}</td>
                                    <td>
                                        <a href="{{ route('tenant.campaign.user.edit',$user->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-pencil-alt"></i></a>
                                        <a href="#" data-action="{{ route('tenant.campaign.user.delete',$user->id) }}" data-id="{{ $user->id }}" class="btn btn-danger btn-sm remove-crud "><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('tenant.include.footer2')
<script>
    $("body").on('click', '.remove-crud', function (event) {

        $.ajaxSetup({
            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        event.preventDefault();

        var url = $(this).attr('data-action');
        var id = $(this).attr('data-id');

        var data = {id: id, _method: 'DELETE'};

        if (confirm("Are you sure you want to delete this tag?")) {

            ajaxCall('POST', url, data, {}).then(function (res) {

                toastr.success('Tag delete successfully.', 'Success Alert', {timeOut: 5000});
                location.reload();

            });
        } else {
            return false;
        }
    });``
</script>