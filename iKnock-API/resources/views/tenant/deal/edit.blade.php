@include('tenant.include.header')
@include('tenant.include.sidebar')
<div class="right_col" role="main">
	<div class="row">
		<div class="col-md-5">
			<div class="cust-head"><i class="fas fa-handshake"></i> Edit Deal Lead</div>
		</div>
	</div>
	<div class="row" id="pg-content">
        @include('tenant.error')

        <div class="col-md-5" style="padding-left:0px;">
            <div class="jumbotron">
                
                <form action="{{ route('tenant.deals.update',$deal->id) }}" class="dealEditForm" method="post">
                    @csrf	
                    @method('put')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Homeowner Name:</label>
                                <input type="text" name="title" class="form-control" placeholder="Enter Home Owener Name" value="{{ $deal->title ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Address:</label>
                                <input type="text" name="address"  class="form-control" placeholder="Enter Address" value="{{ $deal->address ?? ''}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>City:</label>
                                <input type="text" name="city"  class="form-control" placeholder="Enter City" value="{{ $deal->city ?? ''}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>State:</label>
                                <input type="text" name="state"  class="form-control" placeholder="Enter State" value="{{ $deal->state ?? ''}}">
                            </div>
                        </div>
                          <div class="col-md-6">
                            <div class="form-group">
                                <label>Zip Code:</label>
                                <input type="text" name="zip_code"  class="form-control" placeholder="Enter Zip Code" value="{{ $deal->zip_code ?? ''}}">
                            </div>
                        </div>
                         <div class="col-md-6">
                            <div class="form-group">
                                <label>County:</label>
                                <input type="text" name="county"  class="form-control" placeholder="Enter County" value="{{ $deal->county ?? ''}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Sq Ft:</label>
                                <input type="text" name="sq_ft"  class="form-control" placeholder="Enter Sq Ft" value="{{ $deal->sq_ft ?? ''}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Yr Blt:</label>
                                <input type="text" name="yr_blt"  class="form-control" placeholder="Enter Yr Blt" value="{{ $deal->yr_blt ?? ''}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Deal Status:</label>
                                <select name="deal_status" class="form-control">
                                    <option value="" >Select Status</option>
                                    @forelse($deal->dealStatus as $key=>$dealStatus)
                                    @if($key == $deal->deal_status)
                                        <option value="{{ $key }}" selected>{{ $dealStatus }}</option>
                                    @else
                                        <option value="{{ $key }}">{{ $dealStatus }}</option>
                                    @endif
                                    @empty
                                    @endforelse
                                </select>
                            </div>
                         </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Investor:</label>
                                <select name="investor_id" class="form-control">
                                    <option value="" >Select Investor</option>
                                    @forelse($mobileusers as $key=>$user)
                                    @if($user->id == $deal->investor_id)
                                    <option value="{{ $user->id }}" selected>{{ $user->first_name.' '.$user->last_name }}</option>
                                    @else
                                    <option value="{{ $user->id }}">{{ $user->first_name.' '.$user->last_name }}</option>
                                    @endif
                                    @empty
                                    @endforelse
                                </select>
                            </div>
                        </div>
                          <div class="col-md-6">
                            <div class="form-group">
                                <label>Closer:</label>
                                <select name="closer_id" class="form-control">
                                    <option value="" >Select Closer</option>
                                    @forelse($mobileusers as $key=>$user)
                                    @if($user->id == $deal->closer_id)
                                    <option value="{{ $user->id }}" selected>{{ $user->first_name.' '.$user->last_name }}</option>
                                    @else
                                    <option value="{{ $user->id }}">{{ $user->first_name.' '.$user->last_name }}</option>
                                    @endif
                                    @empty
                                    @endforelse
                                </select>
                            </div>
                           </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Deal Type:</label>
                                    <select name="deal_type" class="form-control">
                                        <option value="" >Select Deal Type</option>
                                        @forelse($deal->dealType as $key=>$dealType)
                                        @if($key == $deal->deal_type)
                                            <option value="{{ $key }}" selected>{{ $dealType }}</option>
                                        @else
                                            <option value="{{ $key }}">{{ $dealType }}</option>
                                        @endif
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Purchase Finance:</label>
                                    <select name="purchase_finance" class="form-control">
                                        <option value="" >Select Deal Type</option>
                                        @forelse($deal->purchaseFinance as $key=>$purchase_finance)
                                        @if($key == $deal->purchase_finance)
                                            <option value="{{ $key }}" selected>{{ $purchase_finance }}</option>
                                        @else
                                            <option value="{{ $key }}">{{ $purchase_finance }}</option>
                                        @endif
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Ownership:</label>
                                    <select name="ownership" class="form-control">
                                        <option value="" >Select Deal Type</option>
                                        @forelse($deal->ownershipList as $key=>$ownership)
                                        @if($key == $deal->purchase_finance)
                                            <option value="{{ $key }}" selected>{{ $ownership }}</option>
                                        @else
                                            <option value="{{ $key }}">{{ $ownership }}</option>
                                        @endif
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Purchase Date:</label>
                                    <input type="text" class="form-control date-input" value="{{ dynamicDateFormat($deal->purchase_date,5) }}"  name="purchase_date">
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sell Date:</label>
                                    <input type="text" class="form-control date-input" value="{{ dynamicDateFormat($deal->sell_date,5) }}"   name="sell_date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Purchase Price:</label>
                                    <input type="number" placeholder="Enter Purchase Price" class="form-control" value="{{ $deal->purchase_price }}"   name="purchase_price">
                                </div>
                            </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                    <label>Purchase Closing Costs:</label>
                                    <input type="number" placeholder="Enter Purchase Closing Costs" class="form-control" value="{{ $deal->purchase_closing_costs }}"   name="purchase_closing_costs">
                                </div>
                            </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cash In At Purchase:</label>
                                    <input type="number" placeholder="Enter Cash In At Purchase" class="form-control" value="{{ $deal->cash_in_at_purchase }}"   name="cash_in_at_purchase">
                                </div>
                            </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                    <label>Rehab And Other Costs:</label>
                                    <input type="number" placeholder="Enter Rehab And Other Costs" class="form-control" value="{{ $deal->rehab_and_other_costs }}"   name="rehab_and_other_costs">
                                </div>
                            </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                    <label>Total Cash In:</label>
                                    <input type="number" placeholder="Enter Total Cash In" class="form-control" value="{{ $deal->total_cash_in }}"   name="total_cash_in">
                                </div>
                            </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                    <label>Investor Commission:</label>
                                    <input type="number" placeholder="Enter Investor Commission" class="form-control" value="{{ $deal->Investor_commission }}"   name="Investor_commission">
                                </div>
                            </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                    <label>Total Cost:</label>
                                    <input type="number" placeholder="Enter Total Cost" class="form-control" value="{{ $deal->total_cost }}"   name="total_cost">
                                </div>
                            </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sales Value:</label>
                                    <input type="number" placeholder="Enter Sales Value" class="form-control" value="{{ $deal->sales_value }}"   name="sales_value">
                                </div>
                            </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sales Cash Proceeds:</label>
                                    <input type="number" placeholder="Enter Sales Cash Proceeds" class="form-control" value="{{ $deal->sales_cash_proceeds }}"   name="sales_cash_proceeds">
                                </div>
                            </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                    <label>LH Profit after sharing:</label>
                                    <input type="number" placeholder="Enter LH Profit after sharing" class="form-control" value="{{ $deal->lh_profit_after_sharing }}"   name="lh_profit_after_sharing">
                                </div>
                            </div>
                            <div class="col-md-12">
                               <div class="form-group">
                                    <label>Note:</label>
                                    <textarea name="notes" placeholder="Enter Notes" class="form-control" rows="4">{{ $deal->notes }}</textarea>
                               </div>
                            </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <button type="button" class="btn btn-primary submit_url"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
          <div class="col-md-7">    
            <div class="col-md-10">
                <h1 class="cust-head">History</h1>
            </div>
            <input type="hidden" name="id" class="id" value=""/>
            <div class="table-wrapper-scroll-y">
                <table class="table table-striped jambo_table history" id="scroll" >
                    <thead>
                        <tr class="headings">
                            <th >S.NO</th>
                            <th>Status</th>
                            <th>Who</th>
                            <th>When</th>

                        </tr>
                    </thead>
                    <tbody class="">

                    </tbody>
                </table>
            </div> 
            <br>
            <div class="col-md-10">
                <h1 class="cust-head">Appointment</h1>
            </div>
            <div class="table-wrapper-scroll-y">
                <table class="table table-striped jambo_table" id="scroll" >
                    <thead>
                        <tr class="headings">
                            <th >S.NO</th>
                            <th>Query</th>
                            <th>Response</th>


                        </tr>
                    </thead>
                    <tbody class="appointment">

                    </tbody>
                </table>
            </div>
            <br>
            <div class="col-md-10">
                <h1 class="cust-head">Summary</h1>
            </div>
            <div class="table-wrapper-scroll-y">
                <table class="table table-striped jambo_table" id="scroll" >
                    <thead>
                        <tr class="headings">
                            <th >S.NO</th>
                            <th>Query</th>
                            <th>Response</th>


                        </tr>
                    </thead>
                    <tbody class="lead_summary">

                    </tbody>
                </table>
            </div>
    </div>
</div>
@include('tenant.include.footer')

<script>
    $('.submit_url').click(function (e) {
        e.preventDefault()

        $.ajax({
        url: '{{ route('tenant.deals.update',$deal->id) }}',
        type: 'post',
                data: $(".dealEditForm").serialize(),
                success: function (data) {
                    // if (data.success) {
                    //     window.location.href = "{{ route('tenant.deals.edit',$deal->id) }}";
                    // }
                    toastr.success('Deal Updated Successfully');
                }
        }
        );

        // $(this).parents().find('form').submit();
    });
</script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/df-number-format/2.1.6/jquery.number.js" integrity="sha512-am13TYrHJ6yOQ80pSlL4lA5vQrOmSbgLL2pCZXW+NOJrXUWciLP1WH3LCCFJwFkmYYFZw7sVdwwKOFxLLHRUPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#response').removeClass('text-center');
            let current_url = window.location.href;
            current_url = current_url.split('/');
            let id = {{ $deal->lead_id }};
            let url = '{{ URL::to(' / tenant / lead / history / export') }}';
            $('.export-history').attr('href', url + '/' + id);

            var app_columns = ['query', 'response'];

            loadGridWitoutAjax('GET', base_url + "/tenant/leads/" + id, {}, {}, app_columns, ' .appointment', 'query_appointment', false);

            var sum_columns = ['query', 'response'];

            loadGridWitoutAjax('GET', base_url + "/tenant/leads/" + id, {}, {}, sum_columns, ' .lead_summary', 'query_summary', false);


            $('.id').val(id);
            $('.submit_url').val("{{ URL::to('tenant/leads') }}" + "/" + id);


            var columns = ['status.title', 'assign.name', 'created_at'];


            loadGridWitoutAjax('GET', base_url + "/tenant/lead/history?lead_id=" + id, {}, {}, columns, '.history tbody', '', false);

            var columns = ['old_status', 'assignee', 'title', 'old_type', 'address', 'city', 'state', 'foreclosure_date', 'admin_notes', 'appointment_date', 'media', 'first_name', 'last_name', 'is_expired'];
            getEditRecord('GET', base_url + "/tenant/leads/" + id, {}, {}, columns);

            $('.delete').on('click', function () {
                var choice = confirm('Do you really want to delete this record?');
                if (choice === true) {

                    let deleteRecord = "{{ URL::to('tenant/lead/delete') }}" + "/" + id;

                    ajaxCall('POST', deleteRecord, {id}, {});
                    $(".delete").prop('disabled', true);
                    var redirect_url = $('.redirect_url').val();
                    redirect_url = typeof redirect_url == 'undefined' ? window.location.href : redirect_url;
                    setTimeout(function () {
                        window.location.href = redirect_url;
                    }, 1000)

                }
                return false;
            });
        })


    </script>  <script src="https://cdnjs.cloudflare.com/ajax/libs/df-number-format/2.1.6/jquery.number.js" integrity="sha512-am13TYrHJ6yOQ80pSlL4lA5vQrOmSbgLL2pCZXW+NOJrXUWciLP1WH3LCCFJwFkmYYFZw7sVdwwKOFxLLHRUPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#response').removeClass('text-center');
            let current_url = window.location.href;
            current_url = current_url.split('/');
            let id = "{{ $deal->lead_id }}";
            let url = '{{ URL::to(' / tenant / lead / history / export') }}';
            $('.export-history').attr('href', url + '/' + id);

            var app_columns = ['query', 'response'];

            loadGridWitoutAjax('GET', base_url + "/tenant/leads/" + id, {}, {}, app_columns, ' .appointment', 'query_appointment', false);

            var sum_columns = ['query', 'response'];

            loadGridWitoutAjax('GET', base_url + "/tenant/leads/" + id, {}, {}, sum_columns, ' .lead_summary', 'query_summary', false);


            $('.id').val(id);
            $('.submit_url').val("{{ URL::to('tenant/leads') }}" + "/" + id);


            var columns = ['status.title', 'assign.name', 'created_at'];


            loadGridWitoutAjax('GET', base_url + "/tenant/lead/history?lead_id=" + id, {}, {}, columns, '.history tbody', '', false);

            var columns = ['old_status', 'assignee', 'title', 'old_type', 'address', 'city', 'state', 'foreclosure_date', 'admin_notes', 'appointment_date', 'media', 'first_name', 'last_name', 'is_expired'];
            getEditRecord('GET', base_url + "/tenant/leads/" + id, {}, {}, columns);

            $('.delete').on('click', function () {
                var choice = confirm('Do you really want to delete this record?');
                if (choice === true) {

                    let deleteRecord = "{{ URL::to('tenant/lead/delete') }}" + "/" + id;

                    ajaxCall('POST', deleteRecord, {id}, {});
                    $(".delete").prop('disabled', true);
                    var redirect_url = $('.redirect_url').val();
                    redirect_url = typeof redirect_url == 'undefined' ? window.location.href : redirect_url;
                    setTimeout(function () {
                        window.location.href = redirect_url;
                    }, 1000)

                }
                return false;
            });
        })

        $('.date-input').datepicker({ dateFormat: 'mm/dd/yy' }).val();
    </script>