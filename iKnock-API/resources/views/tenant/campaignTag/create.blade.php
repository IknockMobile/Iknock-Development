@include('tenant.include.header')
@include('tenant.include.sidebar')

<div class="right_col" role="main">
    <div class="row" id="content-heading">
        <!--content-heading here-->
        <div class="col-md-9">
            <h1 class="cust-head">Create Mail-chimp Tag</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h5>Create Mail-chimp Tag</h5>
                </div>
                <div class="panel-body">
                    <form action="{{ route('tenant.campaign.tag.store') }}" method="post">
                        @csrf
                        <div class="col-md-6">
                            <label>Name:</label>
                            <input type="text" name="name"  required  class="form-control" value="{{ $campaign_tag->tag_name }}">
                        </div>                        
                        <div class="col-md-12 text-center mt-4">
                            <button class="btn btn-success">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('tenant.include.footer2')
