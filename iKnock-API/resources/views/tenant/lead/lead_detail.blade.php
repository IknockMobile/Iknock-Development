@include('tenant.include.header')
<style>
    .cross{display: none;}
    .image-url{
        width: 80px !important;
        height:  80px !important;
        margin-right: 10px;
    }
    .appointment tbody td, .lead_summary tbody td{
        text-align: left !important;
    }
    .editable-buttons{
        display:block !important;
        margin:10px 0px;
    }
    .combodate .form-control{
        margin: 5px 0px !important; 
    }
</style>
@include('tenant.include.sidebar')
<div class="right_col" role="main">
    <div class="row" id="content-heading">
        <!--content-heading here-->
        <div class="col-md-9">
            <h1 class="cust-head"><i class="fas fa-home"></i> Lead Details</h1>
        </div>
        <div class="col-md-3">
            <ul class="nav navbar-nav navbar-right">
                <li>
                   <button class="btn btn-default b1 delete">Delete</button>
               </li>
           </ul>
       </div>
   </div>

   <hr class="border">
   <form>
    <div class="row" id="pg-content">
        @include('tenant.error')

        <div class="col-md-5" style="padding-left:0px;">
            <div class="jumbotron">
                {{ csrf_field() }}
                 <div class="row nomargin">
                    <div class="col-md-6 form-group">
                        <input type="hidden" name="target_id" class="target_id" value=""/>
                        <input type="hidden" name="status_id" class="status_id" value=""/>
                        <input type="hidden" name="type_id" class="type_id" value=""/>

                        <input type="hidden" class="submit_url" value="" />
                        <input type="hidden" class="redirect_url" value="{{ URL::to('tenant/lead/') }}">

                        <div class="form-group">
                            <label>Admin Edited Status</label>
                            @if(count($data['status']))
                                <select class="form-control selectbox selectpicker input" data-live-search="true" name="knocks_status_id" value="">
                                    <option value="">Select Lead Status</option>
                                    @foreach ($data['status'] as $status)
                                    
                                    @if ($status->id != 134 AND $status->id != 135 AND $status->id != 133) 
                                    <option data-tokens="{{ $status->title }}" value="{{ $status->id }}">{{ $status->title }} </option>
                                    @endif
                                    @endforeach
                                </select>
                            @endif

                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="exampleFormControlSelect1">Admin Edit Assigned to</label>
                            @if(count($data['agent']))
                                <select class="form-control selectbox selectpicker input" data-live-search="true" name="knocks_target_id" value="">
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
               </div>
                <div class="row nomargin">
                    <div class="col-md-6 form-group">
                        <input type="hidden" name="target_id" class="target_id" value=""/>
                        <input type="hidden" name="status_id" class="status_id" value=""/>
                        <input type="hidden" name="type_id" class="type_id" value=""/>

                        <input type="hidden" class="submit_url" value="" />
                        <input type="hidden" class="redirect_url" value="{{ URL::to('tenant/lead/') }}">

                        <div class="form-group">
                            <label>Lead Status</label>
                            @if(count($data['status']))

                            <select class="form-control selectbox selectpicker input" data-live-search="true" name="status_id" value="">
                                <option>Select Lead Status</option>
                                @foreach ($data['status'] as $status)
                                
                                @if ($status->id != 134 AND $status->id != 135 AND $status->id != 133) 
                                <option data-tokens="{{ $status->title }}" value="{{ $status->id }}">{{ $status->title }} </option>
                                @endif
                                @endforeach
                            </select>
                            @endif

                        </div>
                    </div>
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
               </div>
               <div class="row nomargin">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{config('constants.LEAD_TITLE_DISPLAY')}}</label>
                        <input type="text" value="" name="title"  class="input house-name-input">
                    </div>
                </div>

                            <!-- <div class="col-md-6">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" value="" name="first_name"  class="input">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" value="" name="last_name"  class="input">
                                </div>
                            </div> -->

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Lead Type</label>
                                    @if(count($data['type']))
                                    <select class="form-control selectbox selectpicker input" data-live-search="true" name="type_id" value="">
                                       <option>Select Lead Type</option>
                                        @foreach ($data['type'] as $type)
                                        <option data-tokens="{{ $type->title }}" value="{{ $type->id }}">{{ $type->title }} </option>
                                        @endforeach
                                    </select>
                                    @endif
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
                     {{-- sorting_filed_step_3 --}}
                    {{-- new filed --}}
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
                <?php 
                $lead_id = request()->segment(count(request()->segments()));
                
                
                $lead_queary_data_notes = App\Models\LeadQuery::where('lead_id','=', $lead_id)
                        ->where('query_id','=',8)
                        ->orderBy('id','desc')
                        ->first();
                ?>
                    @if(isset($lead_queary_data_notes->response))
                    <div class="col-md-6 form-group">
                        <label>Summary Notes (Add to Top, Include Date, Your Name and Notes)</label>
                        <input type="text" disabled="" value="{{$lead_queary_data_notes->response}}" name="application_notes"  class="input">
                    </div>
                    @endif

                    <div class="row nomargin">
                        <div class="col-md-12 margintop view_image" >
                        </div>
                    </div>
                

                    <div class="col-md-4" style="">
                      <button class="btn  ajax-button">Save</button>
                  </div>
              </div>

          </div>

          <div class="col-md-7">
            <div class="">
                <div class="col-md-10">
                    <h1 class="cust-head">History</h1>
                </div>
                <input type="hidden" name="id" class="id" value=""/>
                <div class="col-md-2 text-right">
                    <a href="" class="export-history btn btn-default b1">Export</a>
                    <div></div>
                    
                </div>
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
            <!--content-table-end-->
        </div>
    </div>
</form>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/df-number-format/2.1.6/jquery.number.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $('#response').removeClass('text-center');
        let current_url = window.location.href;
        current_url = current_url.split('/');
        let id  = current_url.slice(-1)[0];
        let url = '{{ URL::to('/tenant/lead/history/export') }}' ;
        $('.export-history').attr('href',url+'/'+id);

        var app_columns = [ 'query','response'];    

        loadGridWitoutAjax('GET',base_url + "/tenant/leads/"+id,{},{},app_columns,' .appointment','query_appointment',false);

        var sum_columns = [ 'query','response'];    

        loadGridWitoutAjax('GET',base_url + "/tenant/leads/"+id,{},{},sum_columns,' .lead_summary','query_summary',false);


        $('.id').val(id);
        $('.submit_url').val("{{ URL::to('tenant/leads') }}" + "/" + id);


        var columns = [ 'status.title','assign.name','created_at'];    


        loadGridWitoutAjax('GET',base_url + "/tenant/lead/history?lead_id="+id,{},{},columns,'.history tbody','',false);

        var columns = ['old_status','assignee','title','old_type','address','city','state','foreclosure_date','admin_notes','appointment_date','media','first_name','last_name','is_expired'];    
        getEditRecord('GET',base_url + "/tenant/leads/"+id,{},{},columns);

        $('.delete').on('click', function() {
            var choice = confirm('Do you really want to delete this record?');
            if(choice === true) {

              let deleteRecord =   "{{ URL::to('tenant/lead/delete') }}" + "/" + id;

              ajaxCall('POST',deleteRecord,{id},{});
              $(".delete").prop('disabled', true);
              var redirect_url = $('.redirect_url').val();
              redirect_url = typeof redirect_url == 'undefined' ? window.location.href : redirect_url;
              setTimeout(function(){
                 window.location.href = redirect_url;
             },1000)

          }
          return false;
      });
    })


</script>
@include('tenant.include.footer')
