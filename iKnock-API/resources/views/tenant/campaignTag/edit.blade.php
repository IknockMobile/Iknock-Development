@include('tenant.include.header')
@include('tenant.include.sidebar')

<div class="right_col" role="main">
    <div class="row" id="content-heading">
        <!--content-heading here-->
        <div class="col-md-9">
            <h1 class="cust-head">Edit Mail-chimp Tag</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h5>Edit Mail-chimp Tag</h5>
                </div>
                <div class="panel-body">
                    <form action="{{ route('tenant.campaign.tag.update',$campaign_tag->id) }}" method="post">
                        @csrf
                        <div class="col-md-6">
                            <label>Name:</label>
                            <input type="text" name="name"  required  class="form-control" value="{{ $campaign_tag->tag_name }}">
                        </div>
                        <div class="col-md-6">
                            <label>Is Marketing</label>
                            <select name="is_show_marketing" class="form-control">
                                <option value="0" <?php
                                if ($campaign_tag->is_show_marketing == 0) {
                                    echo "selected";
                                }
                                ?>>No</option>
                                <option value="1" <?php
                                if ($campaign_tag->is_show_marketing == 1) {
                                    echo "selected";
                                }
                                ?>>Yes</option>
                            </select>
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
