@include('tenant.include.header')
@include('tenant.include.sidebar')
<div class="right_col" role="main">
    <div class="container body">
        <div class="row">
            <div class="col-md-2">
                <div class="cust-head"><i class="fas fa-clipboard-list"></i> Audits Management</div>
            </div>
            <div class="col-md-9">
                <form id="AuditSearchForm" action="{{ route('tenant.audit.index') }}">
                    <div class="row text-right">
                        <div class="col-md-3">
                            <input type="" placeholder="Enter Search" class="form-control search-text" value="{{ request()->get('search_text') ?? '' }}" name="search_text">
                        </div>
                        <div class="col-md-1">
                            <button class="b1 clear_search" type="button" >
                                    X
                            </button>                            
                        </div>
                        <div class="col-md-2">
                            <input type="number" placeholder="Enter Auditable Id" class="form-control" value="{{ request()->get('search_id') ?? '' }}" name="search_id">
                        </div>
                        <div class="col-md-2">
                            <select name="event" class="form-control" id="">
                                <option value="">Search Event</option>
                                <option value="created" {{ request()->get('event') == 'created' ?  'selected':'' }}>CREATED </option>
                                <option value="updated" {{ request()->get('event') == 'updated' ?  'selected':'' }}>UPDATED</option>
                                <option value="deleted" {{ request()->get('event') == 'deleted' ?  'selected':'' }}>DELETED</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="audit_type" class="form-control" id="">
                                <option value="">Search Audit Types</option>
                                @forelse($auditTypes as $key=>$value)
                                <option value="{{ $value }}">{{ $value }}</option>
                                @empty
                                @endforelse 
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-success" type="submit">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="row" style="margin-top: 10px;">
            <div class="col-md-12 ">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Audit List	 	
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Model</th>
                                            <th>Event</th>
                                            <th>Auditable Id</th>
                                            <th>Audit User</th>
                                            <th>Created On</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($audits as $key=>$audit)
                                        <tr>
                                            <td>{{ $audit->id }}</td>
                                            <td>{{ $audit->auditable_type }}</td>
                                            <td>{{ $audit->event }}</td>
                                            <td>{{ $audit->auditable_id }}</td>
                                            <td>{{ getUserNameAudit($audit->user) }}</td>
                                            <td>
                                                {{  dynamicDateFormat(dateTimezoneChange($audit->created_at),3) }}
                                            </td>
                                            <td><a href="{{ route('tenant.audit.view',$audit->id) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a></td>
                                        </tr>
                                        @empty

                                        @endforelse
                                    </tbody>
                                </table>
                                {{ $audits->appends(Request::all())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('tenant.include.footer2')
<script type="text/javascript">
    $(document).ready(function () { 
        $('.clear_search').click(function (e) {
            $('.search-text').val(''); 
            
            $('#AuditSearchForm').submit();             
        });
        
        $('.search-text').focusout(function (e) {
            e.preventDefault();

            $('#AuditSearchForm').submit();         
       });
    });    
</script>
