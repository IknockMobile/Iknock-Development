    @include('tenant.include.header')
    @include('tenant.include.sidebar')
    <div class="right_col" role="main">
        <div class="row" id="content-heading">
            <!--content-heading here-->
            <div class="col-md-9">
                <h1 class="cust-head">View Marketing Lead</h1>
            </div>
            <div class="col-md-3">
            </div>
        </div>

        <hr class="border">

        <div class="row" id="pg-content">
            @include('tenant.error')

            <div class="col-md-5" style="padding-left:0px;">
                <div class="jumbotron">
                    
                    <form action="{{ route('tenant.marketing-lead.update',$marketing_data->id) }}" class="formEditFollwing" method="post">
                        @csrf	


                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Homeowner Name:</label>
                                    <input type="text" name="title" class="form-control" placeholder="Enter Home Owener Name" value="{{ $followup_lead->title ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Homeowner Address:</label>
                                    <input type="text" name="formatted_address"  class="form-control" placeholder="Enter Home Owener Address" value="{{ $followup_lead->address ?? ''}}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Follow Up Status:</label>
                                    <select name="follow_status" value="" class="form-control">
                                        <option>Select Follow Up Status</option>
                                        @forelse($statusList as $key=>$status)
                                        @if($status->id == $followup_lead->follow_status)
                                        <option value="{{ $status->id }}" selected>{{ $status->title }}</option>
                                        @else
                                        <option value="{{ $status->id }}">{{ $status->title }}</option>
                                        @endif
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Auction Date:</label>
                                    <input type="text" name="auction" class="form-control datepicker" placeholder="Enter Auction Date" value="{{ $followup_lead->auction ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Contract Date:</label>
                                    <input type="text" name="contract_date" class="form-control datepicker" placeholder="Enter contract date" value="{{ $followup_lead->contract_date ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Lead:</label>
                                    <select name="user_detail" class="form-control">
                                        <option value="" >Select User</option>
                                        @forelse($users as $key=>$user)
                                        @if($user->id == $followup_lead->user_detail)
                                        <option value="{{ $user->id }}" selected>{{ $user->fullName }}</option>
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
                                    <label>Investor:</label>
                                    <select name="investor_id" class="form-control">
                                        <option value="" >Select Investor</option>
                                        @forelse($mobileusers as $key=>$user)
                                        @if($user->id == $followup_lead->investor_id)
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
                                    <label>Date Status Updated:</label>
                                    <input type="text" name="date_status_updated" class="form-control datepicker" placeholder="Enter date status updated" value="{{ $followup_lead->date_status_updated ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Notes & Actions:</label>
                                    <input type="text" name="admin_notes" class="form-control" placeholder="Enter Admin Notes" value="{{ $followup_lead->admin_notes ?? ''}}">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Investor Notes:</label>
                                    <input type="text" name="investor_notes" class="form-control" placeholder="Enter investor notes" value="{{ $followup_lead->investor_notes ?? ''}}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <hr>
                            </div>
                        </div>
                        <div class="row">
                            @if(!empty($followup_lead->getFollowingLeadCustomFileds))
                            @foreach($followup_lead->getFollowingLeadCustomFileds as $key => $followingCustomField)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ $followingCustomField->followUpLeadViewSetp->title }}:</label>
                                    <input class="form-control" name="followingCustomField[{{ $followingCustomField->id }}][value]" value="{{ $followingCustomField->field_value }}" placeholder="Enter {{ $followingCustomField->followUpLeadViewSetp->title }}">
                                    <input type="hidden" name="followingCustomField[{{ $followingCustomField->id }}][id]" value="{{ $followingCustomField->id }}">
                                </div>
                            </div>
                            @endforeach
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <h4>
                                    Data from marketing screen
                                </h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Homeowner Name</label>
                                    <input type="text" name="m_title" class="form-control" placeholder="Enter Homeowner Name" value="{{ $marketing_data->title ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Homeowner Address</label>
                                    <input type="text" name="m_address" class="form-control" placeholder="Enter Homeowner Address" value="{{ $marketing_data->address ?? ''}}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Appt Email:</label>
                                    <input type="text" name="m_appt_email" class="form-control" placeholder="Enter Appt Email" value="{{ $marketing_data->appt_email ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Appt Phone:</label>
                                    <input type="text" name="m_appt_phone" class="form-control" placeholder="Enter Appt Phone" value="{{ $marketing_data->appt_phone ?? ''}}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Marketing Email</label>
                                    <input type="text" name="m_marketing_mail" class="form-control" placeholder="Enter Marketing Email" value="{{ $marketing_data->marketing_mail ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Marketing Address</label>
                                    <input type="text" name="m_marketing_address" class="form-control" placeholder="Enter Marketing Address" value="{{ $marketing_data->marketing_address ?? ''}}">
                                </div>
                            </div>
                        </div>                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Notes and Actions</label>
                                    <input type="text" name="m_admin_notes" class="form-control" placeholder="Enter Notes and Actions" value="{{ $marketing_data->admin_notes ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Investor Notes:</label>
                                    <input type="text" name="m_investore_note" class="form-control" placeholder="Enter Investor Notes" value="{{ $marketing_data->investore_note ?? ''}}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="button" class="btn btn-save submit_url"><i class="fas fa-save"></i> Save</button>
                            </div>
                        </div>
                        
                        
                    </form>



                </div>

            </div>

            <div class="col-md-7">
                <div class="">
                    <div class="col-md-10">
                        <h1 class="cust-head">History</h1>
                    </div>
                    <input type="hidden" name="id" class="id" value=""/>
                    <!--                <div class="col-md-2 text-right">
                                        <a href="" class="export-history btn btn-default b1">Export</a>
                                        <div></div>
                    
                                    </div>-->
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
                    <br>
                    <div class="col-md-10">
                        <h1 class="cust-head">
                            Lead Details
                        </h1>
                    </div>
                </div>
                <div class="jumbotron">
                <div class="row nomargin">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="exampleFormControlSelect1">Assigned To</label>
                                @if(count($data['agent']))

                                <select class="form-control selectbox selectpicker input" data-live-search="true" name="target_id" value="">
                                    <option value="">Not Assigned</option>
                                    @foreach ($data['agent'] as $agent)
                                    <option data-tokens="{{ $agent->first_name }}" value="{{ $agent->id }}">{{ $agent->first_name }}  {{ $agent->last_name }}</option>
                                    @endforeach
                                </select>
                                @else
                                <select class="form-control selectbox  input" disabled selected name="target_id" value="">

                                    <option data-tokens="" value="">No user Found </option>

                                </select>
                                @endif

                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{config('constants.LEAD_TITLE_DISPLAY')}}</label>
                                <input type="text" value="" name="title"  class="input house-name-input">
                            </div>
                        </div>
                    </div>                
                    <div class="form-group col-md-6">
                        <label for="exampleFormControlSelect1">Is Retired</label>
                        <select class="form-control modal_type selectbox selectpicker input" data-live-search="true"
                                name="is_expired" value="">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Address</label>
                        <input type="text" value="" name="address"  class="input address-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>City</label>
                        <input type="text" value="" name="city"  class="input city-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>State</label>
                        <input type="text" value="" name="state"  class="input state-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>County</label>
                        <input type="text" value="" name="county"  class="input county-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Zip Code</label>
                        <input type="text" value="" name="zip_code"  class="input zip-code-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Auction</label>
                        <input type="text" value="" name="auction"  class="input auction-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Lead Value</label>
                        <input type="text" value="" name="lead_value"  class="input lead_value-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Original Loan</label>
                        <input type="text" value="" name="original_loan"  class="input original_loan-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Loan Date</label>
                        <input type="text" value="" name="loan_date"  class="input loan_date-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Sq Ft</label>
                        <input type="text" value="" name="sq_ft"  class="input sq_ft-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Yr Blt</label>
                        <input type="text" value="" name="yr_blt"  class="input yr_blt-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Eq</label>
                        <input type="text" value="" name="eq"  class="input eq-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Mortgagee</label>
                        <input type="text" value="" name="mortgagee"  class="input mortgagee-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Loan Type</label>
                        <input type="text" value="" name="loan_type"  class="input loan_type-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Loan Mod</label>
                        <input type="text" value="" name="loan_mod"  class="input loan_mod-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Trustee</label>
                        <input type="text" value="" name="trustee"  class="input trustee-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Owner Address</label>
                        <input type="text" value="" name="owner_address"  class="input owner_address-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Source</label>
                        <input type="text" value="" name="source"  class="input source-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Admin Notes</label>
                        <input type="text" value="" name="admin_notes"  class="input admin_notes-input">
                    </div>
                    <div class="row nomargin">
                        @if(!empty($data['lead']['leadCustom']) && count($data['lead']['leadCustom']) != 0)

                        @foreach ($data['lead']['leadCustom'] as $key=>$custom)

                        @if(!empty($custom->key))
                        @if($custom->key != 'Lead Type' AND $custom->key != 'Lead Status')
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ $custom->key }}</label>
                                <input  type="text" value="{{ $custom->value }}" name="customData[{{ $key }}][value]" class="input custom-data">
                                <input type="hidden" value="{{ $custom->key }}" name="customData[{{ $key }}][name]">
                                <input type="hidden" value="{{ $custom->id }}" name="customData[{{ $key }}][id]">
                            </div>
                        </div>
                        @endif
                        @endif


                        @endforeach

                        @endif
                    </div>
                <!--content-table-end-->
            </div>
            </div>
        </div>

    </div>

    @include('tenant.include.footer')

    <script>
        $('.submit_url').click(function (e) {
            e.preventDefault()
            $.ajax({
            url: '{{ route('tenant.marketing-lead.update',$marketing_data->id) }}',
            type: 'post',
                    data: $(".formEditFollwing").serialize(),
                    success: function (data) {
                        if (data.success) {
                        
            window.location.href = "{{ route('tenant.marketing-lead.edit',$followup_lead->id) }}";
                        }
                        }
            }
            );

            // $(this).parents().find('form').submit();
        });

        $('body').on('click', '.add-more', function (event) {
            event.preventDefault();
            var obj = $(this);

            obj.html('<i class="fas fa-minus"></i>');
            obj.removeClass('btn-primary add-more');
            obj.addClass('btn-danger remove-field');

            var formhtml = $('.fieldcustombox-more').html();

            var nameFieldCount = parseInt($('.fieldbox').length) + parseInt(1);

            formhtml = formhtml.replaceAll("key", nameFieldCount);

            $('.fieldcustombox').append(formhtml);
        });

        $('body').on('click', '.remove-field', function (event) {
            event.preventDefault();
            $(this).parent().parent().parent().remove();
        });


    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/df-number-format/2.1.6/jquery.number.js" integrity="sha512-am13TYrHJ6yOQ80pSlL4lA5vQrOmSbgLL2pCZXW+NOJrXUWciLP1WH3LCCFJwFkmYYFZw7sVdwwKOFxLLHRUPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#response').removeClass('text-center');
            let current_url = window.location.href;
            current_url = current_url.split('/');
            let id = <?php echo $followup_lead->lead_id; ?>;
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


    </script>
