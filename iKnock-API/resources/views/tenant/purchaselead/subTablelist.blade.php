
@forelse($followUpLeadViewSetps as $key=>$followUpLeadViewSetp)
<td>
    @if($followUpLeadViewSetp->title_slug == 'no')
    {{ $linenumber }}

    @elseif($followUpLeadViewSetp->title_slug == 'all_delete')
    <input type="checkbox" class="followUpDeleteLead" id="checkbox{{ $followingLead['id'] }}" name="followup_ids" value="{{ $followingLead['id'] }}">
    
    @elseif($followUpLeadViewSetp->title_slug == 'is_lead_up')
        <button class="btn btn-primary isLeadup btn-sm" data-id="{{ $followingLead['id'] }}"><i class="fas fa-users"></i> Lead Mgmt</button>
    
    @elseif($followUpLeadViewSetp->title_slug == 'is_marketing')
        <button class="btn btn-dark isMarketing btn-sm" data-id="{{ $followingLead['id'] }}"><i class="fas fa-shopping-cart"></i> Marketing</button>
        
    @elseif($followUpLeadViewSetp->title_slug == 'is_followup')
        <button class="btn btn-primary isFollowUp btn-sm" data-id="{{ $followingLead['id'] }}"><i class="fas fa-shopping-cart"></i> Follow Up Lead Management</button>
        
    

    @elseif($followUpLeadViewSetp->title_slug == 'is_deal')
        <button class="btn btn-info isDeal btn-sm" data-id="{{ $followingLead['id'] }}"><i class="fas fa-handshake"></i> Deal</button>

    @elseif($followUpLeadViewSetp->title_slug == 'contract_date' || $followUpLeadViewSetp->title_slug == 'status_update')
    <a href="#" data-name="{{ $followUpLeadViewSetp->title_slug }}" class="detailupdate editable editable-click" data-type="date" data-value=""  data-pk="{{ $followingLead['id'] }}" data-original-title="Enter {{ $followUpLeadViewSetp->title }}:" title="">
        {{ dateFormatYMDtoMDY($followingLead[$followUpLeadViewSetp->title_slug])  }}
    </a>


    @elseif($followUpLeadViewSetp->title_slug == 'auction_date')
    <a href="#" data-name="auction" class="detailupdate editable editable-click" data-type="date" data-value=""  data-pk="{{ $followingLead['id'] }}" data-original-title="Enter {{ $followUpLeadViewSetp->title }}:" title="">
        {{ $followingLead['auction']  }}
    </a>

    @elseif($followUpLeadViewSetp->title_slug == 'is_retired')
    <input type="checkbox" class="followUpIsRetired" data-id="{{ $followingLead['id'] }}" name="followup_ids" value="{{ $followingLead['is_retired'] }}" {{ $followingLead['is_retired'] == 1 ? 'checked':'' }}>

    @elseif($followUpLeadViewSetp->title_slug == 'investor')
    <a href="#" data-name="investor_id" data-source='{{ $mobileusers }}' data-value="{{ $followingLead['investor_id'] }}" class="detailupdate editable editable-click" data-type="select" data-pk="{{ $followingLead['id'] }}" data-title="address" data-original-title="Select Investor:">
        {{ getUser($followingLead['investor_id'])->fullName }}
    </a>

    @elseif($followUpLeadViewSetp->title_slug == 'investor_notes')
    <a href="#" data-name="investor_notes" class="detailupdate editable editable-click" data-type="textarea" data-pk="{{ $followingLead['id'] }}" data-title="title" data-original-title="Enter Notes and Actions:" title="">{!! $followingLead['investor_notes'] !!}</a>
        
        
    

    @elseif($followUpLeadViewSetp->title_slug == 'lead')
    <a href="#" data-name="user_detail" data-source='{{ $users }}' data-value="{{ $followingLead['user_detail'] }}" class="detailupdate editable editable-click" data-type="select" data-pk="{{ $followingLead['id'] }}" data-original-title="Select Lead:">
        {{ getUser($followingLead['user_detail'])->fullName }}
    </a>	


    @elseif($followUpLeadViewSetp->title_slug == 'action' && !empty($followingLead['id']))
    <a href="{{ route('tenant.followup-lead.edit',$followingLead['id']) }}" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i></a>


    @elseif($followUpLeadViewSetp->title_slug == 'status')
    <a href="#" data-name="follow_status" data-source='{{ $statues }}' style="background: {{ getFollowStatus($followingLead['follow_status'])->color_code ?? '#e2e2e2' }}" data-value="{{ $followingLead['follow_status'] }}" class="detailupdate editable label-status editable-click" data-type="select" data-pk="{{ $followingLead['id'] }}" data-original-title="Select Status:">
        {{ getFollowStatus($followingLead['follow_status'])->title }} 
    </a>


    @elseif($followUpLeadViewSetp->title_slug == 'notes_and_actions')
    <a href="#" data-name="admin_notes" class="detailupdate editable editable-click" data-type="textarea" data-pk="{{ $followingLead['id'] }}" data-title="title" data-original-title="Enter Notes and Actions:" title="">{{$followingLead['admin_notes']}}</a>


    @elseif($followUpLeadViewSetp->title_slug == 'homeowner_address')
    <a href="#" data-name="formatted_address" class="detailupdate editable editable-click" data-type="text" data-pk="{{ $followingLead['id'] }}" data-title="title" data-original-title="Enter Homeowner Address:" title="">
        {{ $followingLead['homeowner_address']  }}
    </a>

    @elseif($followUpLeadViewSetp->title_slug == 'homeowner_city')
    <a href="#" data-name=" formatted_address" class="detailupdate editable editable-click" data-type="text" data-pk="{{ $followingLead['id'] }}" data-title="title" data-original-title="Enter Homeowner City:" title="">
        {{ $followingLead['homeowner_city']  }}
    </a>

    @elseif($followUpLeadViewSetp->title_slug == 'homeowner_state')
    <a href="#" data-name=" formatted_address" class="detailupdate editable editable-click" data-type="text" data-pk="{{ $followingLead['id'] }}" data-title="title" data-original-title="Enter Homeowner  State:" title="">
        {{ $followingLead['homeowner_state']  }}
    </a>

    @elseif($followUpLeadViewSetp->title_slug == 'homeowner_county')
    <a href="#" data-name=" formatted_address" class="detailupdate editable editable-click" data-type="text" data-pk="{{ $followingLead['id'] }}" data-title="title" data-original-title="Enter Homeowner County:" title="">
        {{ $followingLead['homeowner_county']  }}
    </a>

    @elseif($followUpLeadViewSetp->title_slug == 'homeowner_zip_code')
    <a href="#" data-name=" formatted_address" class="detailupdate editable editable-click" data-type="text" data-pk="{{ $followingLead['id'] }}" data-title="title" data-original-title="Enter Homeowner Zip Code:" title="">
        {{ $followingLead['homeowner_zip_code']  }}
    </a>

    @elseif($followUpLeadViewSetp->title_slug == 'homeowner_name')
    <a href="#" data-name="title" class="detailupdate editable editable-click" data-type="text" data-pk="{{ $followingLead['id'] }}" data-title="title" data-original-title="Enter Homeowner Name:" title="">
        {{ $followingLead['homeowner_name']  }}
    </a>

    @else
        @if($followUpLeadViewSetp->input_type == 1)
            <a href="#" data-name="{{ $followUpLeadViewSetp->view_type == 1 ?  getCustomFiledPurchase($followUpLeadViewSetp->title_slug,$followingLead['id'])->id :$followUpLeadViewSetp->title_slug }}" class="{{ $followUpLeadViewSetp->view_type == 1 ? 'detailCustomUpdate':'detailupdate' }}  editable editable-click" data-type="textarea" data-value="{{  $followingLead[$followUpLeadViewSetp->title_slug] }}" data-pk="{{ $followingLead['id'] }}" data-title="title" data-original-title="Enter {{ $followUpLeadViewSetp->title }}:" title="">{{$followingLead[$followUpLeadViewSetp->title_slug]}}</a>
        @elseif($followUpLeadViewSetp->input_type == 2)
              <a href="#" data-name="{{ $followUpLeadViewSetp->view_type == 1 ?  getCustomFiledPurchase($followUpLeadViewSetp->title_slug,$followingLead['id'])->id :$followUpLeadViewSetp->title_slug }}" class="{{ $followUpLeadViewSetp->view_type == 1 ? 'detailCustomUpdate':'detailupdate' }}  editable editable-click" data-type="date" data-pk="{{ $followingLead['id'] }}" data-value="{{  $followingLead[$followUpLeadViewSetp->title_slug] }}" data-title="title" data-original-title="Enter {{ $followUpLeadViewSetp->title }}:" title="">{{ $followingLead[$followUpLeadViewSetp->title_slug] }}
                
            </a>
        @elseif($followUpLeadViewSetp->input_type == 3)
             <a href="#" data-name="{{ $followUpLeadViewSetp->view_type == 1 ?  getCustomFiledPurchase($followUpLeadViewSetp->title_slug,$followingLead['id'])->id :$followUpLeadViewSetp->title_slug }}" class="{{ $followUpLeadViewSetp->view_type == 1 ? 'detailCustomUpdate':'detailupdate' }}  editable editable-click" data-type="number" data-pk="{{ $followingLead['id'] }}" data-value="{{  $followingLead[$followUpLeadViewSetp->title_slug] }}"  data-title="title" data-original-title="Enter {{ $followUpLeadViewSetp->title }}:" title="">{{ $followingLead[$followUpLeadViewSetp->title_slug] }}
                
            </a>
        @elseif($followUpLeadViewSetp->input_type == 4)
           <a href="#" data-name="{{ $followUpLeadViewSetp->view_type == 1 ?  getCustomFiledPurchase($followUpLeadViewSetp->title_slug,$followingLead['id'])->id :$followUpLeadViewSetp->title_slug }}" class="{{ $followUpLeadViewSetp->view_type == 1 ? 'detailCustomUpdate':'detailupdate' }}  editable editable-click" data-type="number" data-pk="{{ $followingLead['id'] }}" data-title="title" data-value="{{  $followingLead[$followUpLeadViewSetp->title_slug] }}"  data-original-title="Enter {{ $followUpLeadViewSetp->title }}:" title="">{{ $followingLead[$followUpLeadViewSetp->title_slug] }}
                
            </a>
        @elseif($followUpLeadViewSetp->input_type == 5)
            <a href="#" data-name="{{ $followUpLeadViewSetp->view_type == 1 ?  getCustomFiledPurchase($followUpLeadViewSetp->title_slug,$followingLead['id'])->id :$followUpLeadViewSetp->title_slug }}" class="{{ $followUpLeadViewSetp->view_type == 1 ? 'detailCustomUpdate':'detailupdate' }}  editable editable-click" data-type="select" data-source="{{ $followUpLeadViewSetp->pickListArray }}" data-value="{{ $followingLead[$followUpLeadViewSetp->title_slug] }}"  data-pk="{{ $followingLead['id'] }}" data-title="title" data-original-title="Enter {{ $followUpLeadViewSetp->title }}:" title="">{{ $followingLead[$followUpLeadViewSetp->title_slug] }}              
            </a>
        @endif
    @endif
</td>
@empty
@endforelse
<td colspan="">
    <a href="{{asset('tenant/purchase-lead/'.$followingLead['id'].'/edit')}}">
        Edit
    </a>   
</td>