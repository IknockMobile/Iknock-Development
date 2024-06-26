@include('tenant.include.header')
@include('tenant.include.sidebar')
<div class="right_col" role="main">
    <div class="row" id="content-heading">
        <div class="col-md-9">
            <h1 class="cust-head">Create Mail-chimp Subscriber</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h5>Create Mail-chimp Subscriber</h5>
                </div>
                <div class="panel-body">
                    <form action="{{ route('tenant.campaign.user.store') }}" method="post">
                        @csrf
                        <div class="col-md-6">
                            <label>Email Address:</label>
                            <input type="text" name="email" required class="form-control" value="">
                        </div>
                        <div class="col-md-6">
                            <label>First Name:</label>
                            <input type="text" name="FNAME"  class="form-control" value="">
                        </div>
                        <div class="col-md-12"><br></div>
                        <div class="col-md-6">
                            <label>Last name:</label>
                            <input type="text" name="LNAME"  class="form-control" value="">
                        </div>
                        <div class="col-md-6">
                            <label>Phone number:</label>
                            <input type="text" name="PHONE"  class="form-control" value="">
                        </div>
                        <div class="col-md-12"><br></div>
                        <div class="col-md-6">
                            <label>Address:</label>
                            <input type="text" name="addr1"  class="form-control" value="">
                        </div>
                        <div class="col-md-6">
                            <label>City:</label>
                            <input type="text" name="city"  class="form-control" value="">
                        </div>
                        <div class="col-md-12"><br></div>
                        <div class="col-md-6">
                            <label>State:</label>
                            <input type="text" name="state"  class="form-control" value="">
                        </div>
                         <div class="col-md-6">
                            <label>Zip:</label>
                            <input type="text" name="zip"  class="form-control" value="">
                        </div>
                        <div class="col-md-12"><br></div>
                        <div class="col-md-6">
                            <label>Country:</label>
                            <input type="text" name="country"  class="form-control" value="">
                        </div>
                        <div class="col-md-6">
                            <label>Tags:</label>
                            <select name="tags[]" class="form-control" multiple="">
                                @foreach($tags as $tag)
                                <option value="{{$tag->tag_id}}">
                                    {{$tag->tag_name}}
                                </option>
                                @endforeach
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
