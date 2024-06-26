@include('tenant.include.header')
@include('tenant.include.sidebar')
<div class="right_col" role="main">
    <div class="container body">
        <div class="row">
            <div class="col-md-12">
                <div class="cust-head"><i class="fas fa-clipboard-list"></i> Audit view</div>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Audit View	 	
                    </div>
                    <div class="panel-body">
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">Id</th>
                                <td>{{ $audit->id }}</td>
                            </tr>
                            <tr>
                                <th width="200">Model</th>
                                <td>{{ $audit->auditable_type }}</td>
                            </tr>
                            <tr>
                                <th width="200">Event</th>
                                <td>{{ $audit->event }}</td>
                            </tr>
                            <tr>
                                <th width="200">Auditable Id</th>
                                <td>{{ $audit->auditable_id }}</td>
                            </tr>
                            <tr>
                                <th>Requst Post URL</th>
                                <td>
                                    {{ $audit->url }}
                                </td>
                            </tr>
                            <tr>
                                <th>IP Address</th>
                                <td>
                                    {{ $audit->ip_address }}
                                </td>
                            </tr>
                            <tr>
                                <th>User Agent</th>
                                <td>
                                    {{ $audit->user_agent }}
                                </td>
                            </tr>
                            <tr>
                                <th width="200">User</th>
                                <td>
                                    <pre  class="text-left">
										{{ print_r(json_decode($audit->user,true)) }}
                                    </pre>
                                </td>
                            </tr>
                            <tr>
                                <th width="200">Old Values</th>
                                <td>
                                    <pre  class="text-left">
										{{ print_r(json_decode($audit->old_values,true)) }}
                                    </pre>
                                </td>
                            </tr>
                            <tr>
                                <th width="200">New Values</th>
                                <td>
                                    <pre  class="text-left">
										{{ print_r(json_decode($audit->new_values,true)) }}
                                    </pre>
                                </td>
                            </tr>

                            <tr>
                                <th width="200">Date</th>
                                <td>                                    
                                    {{ date('Y-m-d',strtotime(dateTimezoneChange($audit->created_at))) }}                                    
                                </td>
                            </tr>

                            <tr>
                                <th width="200">Lead Information</th>
                                <td>                                    
                                    <?php
                                    if ($audit->auditable_type == 'App\Models\FollowingCustomFields') {
                                        $FollowingCustomFields = App\Models\FollowingCustomFields::where('id', '=', $audit->auditable_id)->first();
                                        if (isset($FollowingCustomFields->id)) {
                                            $FollowingLead = App\Models\FollowingLead::where('id', '=', $FollowingCustomFields->followup_lead_id)->first();
                                            if (isset($FollowingLead->id)) {
                                                $Lead = App\Models\Lead::where('id', '=', $FollowingLead->lead_id)->first();
                                                if (isset($Lead->id)) {
                                                    echo $Lead->title . ' >  ' . $Lead->address . ' >  ' . $Lead->formatted_address;
                                                }
                                            }
                                        }
                                    }
                                    ?>   
                                    <?php
                                    if ($audit->auditable_type == 'App\Models\PurchaseCustomFields') {
                                        $FollowingCustomFields = App\Models\PurchaseCustomFields::where('id', '=', $audit->auditable_id)->first();
                                        if (isset($FollowingCustomFields->id)) {
                                            $FollowingLead = App\Models\PurchaseLead::where('id', '=', $FollowingCustomFields->followup_lead_id)->first();
                                            if (isset($FollowingLead->id)) {
                                                $Lead = App\Models\Lead::where('id', '=', $FollowingLead->lead_id)->first();
                                                if (isset($Lead->id)) {
                                                    echo $Lead->title . ' >  ' . $Lead->address . ' >  ' . $Lead->formatted_address;
                                                }
                                            }
                                        }
                                    }
                                    ?>   
                                </td>
                            </tr>


                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('tenant.include.footer2')
