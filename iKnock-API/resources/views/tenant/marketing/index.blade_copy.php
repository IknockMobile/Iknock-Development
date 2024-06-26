@include('tenant.include.header')
@include('tenant.include.sidebar')
<style type="text/css">
    table{
        width: auto;
        overflow-x: scroll;
        display: inline-block;
        white-space: nowrap;
    }
</style>
<div class="right_col" role="main">
    <div class="container body">
        <div class="col-md-5">
            <div class="cust-head"><i class="fas fa-store"></i> Marketing Lead Management</div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h5>Marketing Lead List</h5> 
                </div>
                <div class="panel-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Homeowner Name</th>
                                <th>Homeowner Address</th>
                                <th>Lead</th>
                                <th>Investor</th>
                                <th>Notes and Actions</th>
                                <th>Investor Notes</th>
                                <th>Appt Email</th>
                                <th>Appt Phone</th>

                                <th>Marketing Email</th>
                                <th>Marketing Address</th>
                                <?php $campaign_id_arr = []; ?>
                                @forelse($campaigns as $key=>$campaign)
                                <th title="{{ $campaign->campaign_id }}">{{ $campaign->title }}</th>
                                <?php $campaign_id_arr[] = $campaign->campaign_id; ?>
                                @empty
                                @endif
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($marketings as $key=>$marketing)
                            <tr>
                                <td>{{ $marketing->title }}</td>
                                <td>{{ $marketing->address }}</td>
                                <td>{{$marketing->lead_first_name}} {{$marketing->lead_last_name}}</td>
                                <td>{{$marketing->in_first_name}} {{$marketing->in_last_name}}</td>
                                <td>
                                    <?php 
                                        if($marketing->admin_notes != ''){
                                            echo substr($marketing->admin_notes,0,30);
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                        if($marketing->investore_note != ''){
                                            echo substr($marketing->investore_note,0,30);
                                        }
                                    ?>
                                </td>
                                <td>{{ $marketing->appt_email }}</td>
                                <td>{{ $marketing->appt_phone }}</td>
                                <td><a href="#" data-name="marketing_mail" class="detailEmailupdate editable editable-click" data-type="email" data-value="{{ $marketing->marketing_mail }}" data-pk="{{ $marketing->id }}" data-original-title="Enter Email:" title="">{{ $marketing->marketing_mail }}</a></td>

                                <td><a href="#" data-name="marketing_address" class="detailEmailupdate editable editable-click" data-type="text" data-mail="{{ $marketing->marketing_mail }}"  data-value="{{ $marketing->marketing_address }}" data-pk="{{ $marketing->id }}" data-original-title="Enter Address:" title="">{{ $marketing->marketing_address }}</a></td>
                                @forelse($marketing->marketingCampaign as $key=> $marketingCampaign)									                                
                                <?php if(in_array($marketingCampaign->campaign->campaign_id, $campaign_id_arr)){ ?>
                                <td>                                    
                                    <input class="campaignedit" type="checkbox" {{ $marketingCampaign->status == 1 ? 'checked':'' }}  data-toggle="toggle" data-id="{{ $marketing->id }}" data-champid="{{$marketingCampaign->campaign->campaign_id}}" data-offstyle="danger" data-size="md">{{ $marketingCampaign->title }}</td>
                                <?php } ?>
                                @empty
                                @endforelse
                                <td>  
                                    <?php 
                                    $marketing_details = \App\Models\FollowingLead::where('lead_id','=',$marketing->lead_id)->first();
                                    ?>
                                    <a href="{{ url('tenant/marketing-lead/'.$marketing_details->id.'/edit')}}">
                                        Edit
                                    </a>                                    
                                </td>
                            </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                    {{ $marketings->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@include('tenant.include.footer')
<script type="text/javascript">
    $('body').on('change', '.campaignedit', function (event) {
        event.preventDefault();
        var obj = $(this);
        var id = obj.attr('data-id');
        var champid = obj.attr('data-champid');

        if (obj.prop('checked') == true) {
            var value = 1;
        } else {
            var value = 0;
        }

        $.ajax({
            url: '{{ route('tenant.marketing.campaign.status.update') }}',
            type: 'post',
            dataType: 'json',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: id,
                champid: champid,
                value: value,
            },
        })
                .done(function () {
                    toastr.success('Campaign status updated successfully');
                });

    });
    $('.detailEmailupdate').editable({
        send: 'always',
        url: "/tenant/marketing/email/editable",
        success: function (response) {
            toastr.success('Marketing mail updated successfully.');
        }
    });
</script>
