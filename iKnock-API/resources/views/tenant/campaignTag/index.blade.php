@include('tenant.include.header')
@include('tenant.include.sidebar')

<div class="right_col" role="main">
	<div class="row" id="content-heading">
        <!--content-heading here-->
        <div class="col-md-9">
            <h1 class="cust-head">Mail-chimp Tag Management</h1>
        </div>
   </div>
	<div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Campaign Tag Managment</h5>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ route('tenant.campaign.tag.create') }}"  class="btn btn-info"><i class="fas fa-plus"></i></a>
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
                                    <th width="200">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tags as $key=>$tag)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $tag->tag_name }}</td>
                                        <td>
                                            <a href="{{ route('tenant.campaign.tag.edit',$tag->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-pencil-alt"></i></a>
                                            <a href="#" data-action="{{ route('tenant.campaign.tag.delete',$tag->id) }}" data-id="{{ $tag->id }}" class="btn btn-danger btn-sm remove-crud "><i class="fas fa-trash"></i></a>
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
    $("body").on('click', '.remove-crud', function(event) {
        
        $.ajaxSetup({
            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        event.preventDefault();

        var url = $(this).attr('data-action'); 
        var id = $(this).attr('data-id'); 

        var data = {id:id,_method:'DELETE'};

        if(confirm("Are you sure you want to delete this tag?")){

             ajaxCall('POST', url, data, {}).then(function (res) {

                toastr.success('Tag delete successfully.','Success Alert', {timeOut: 5000});
                location.reload();

            });
        }
        else{
            return false;
        }
    });``    
</script>