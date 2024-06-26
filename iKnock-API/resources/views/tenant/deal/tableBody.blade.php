@forelse($dealLeads as $keylead=>$dealLead)
<?php

$custum_fields = json_decode($dealLead->custum_fields);

$custum_fieldsArray = (array) $custum_fields;
$dealLeadArray = (array) $dealLead;
?>

<tr>
    @forelse($dealLeadViewSetp as $key=>$dealLeadView)
    <td>
        @if($dealLeadView->title_slug == 'no')
        {{ ++$keylead }}
        @elseif($dealLeadView->title_slug == 'all_delete')
        <input type="checkbox" class="deal_delete" data-id="{{ $dealLead->id }}" value="{{ $dealLead->id }}">
        @elseif($dealLeadView->title_slug == 'homeowner_name')
        <a href="#" data-name="title" class="detailupdateDeal editable editable-click" data-type="text" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter Homeowner Name:" title="">{{ $dealLead->title }}</a>
        @elseif($dealLeadView->title_slug == 'street_address')
        <a href="#" data-name="address" class="detailupdateDeal editable editable-click" data-type="text" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter Address:" title="">
            {{ $dealLead->address }}
        </a>
        @elseif($dealLeadView->title_slug == 'city')
        <a href="#" data-name="city" class="detailupdateDeal editable editable-click" data-type="text" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter City:" title="">
            {{ $dealLead->city }}
        </a>	
        @elseif($dealLeadView->title_slug == 'county')
        <a href="#" data-name="county" class="detailupdateDeal editable editable-click" data-type="text" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter County:" title="">
            {{ $dealLead->county }}
        </a>
        @elseif($dealLeadView->title_slug == 'state')
        <a href="#" data-name="state" class="detailupdateDeal editable editable-click" data-type="text" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter State:" title="">
            {{ $dealLead->state }}
        </a>
        @elseif($dealLeadView->title_slug == 'zip')
        <a href="#" data-name="zip_code" class="detailupdateDeal editable editable-click" data-type="text" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter Zip Code:" title="">
            {{ $dealLead->zip_code }}
        </a>
        @elseif($dealLeadView->title_slug == 'sq_ft')
        <a href="#" data-name="sq_ft" class="detailupdateDeal editable editable-click" data-type="number" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter SQ FT:" title="">
            {{ $dealLead->sq_ft }}
        </a>
        @elseif($dealLeadView->title_slug == 'year_built')
        <a href="#" data-name="yr_blt" class="detailupdateDeal editable editable-click" data-type="number" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter Yr Blt:" title="">
            {{ $dealLead->yr_blt }}
        </a>
        @elseif($dealLeadView->title_slug == 'investor')
        <a href="#" data-name="investor_id" data-source='{{ $mobileusers }}' data-value="{{ $dealLead->investor_id }}" class="detailupdateDeal editable editable-click" data-type="select" data-pk="{{ $dealLead->id }}" data-title="SELECT INVESTOR" data-original-title="Select Investor:">
            {{ getUser($dealLead->investor_id)->fullName ?? '' }}
        </a>
        @elseif($dealLeadView->title_slug == 'closer')
        <a href="#" data-name="closer_id" data-source='{{ $mobileusers }}' data-value="{{ $dealLead->closer_id }}" class="detailupdateDeal editable editable-click" data-type="select" data-pk="{{ $dealLead->id }}" data-title="SELECT CLOSER" data-original-title="SELECT CLOSER:">
            {{ getUser($dealLead->closer_id)->fullName ?? '' }}
        </a>
        @elseif($dealLeadView->title_slug == 'deal_status')
        <a href="#" data-name="deal_status" data-source='{{ $dealStatus }}' data-value="{{ $dealLead->deal_status }}" class="detailupdateDeal editable editable-click" data-type="select" data-pk="{{ $dealLead->id }}" data-title="SELECT Deal Status" data-original-title="Select Deal Status:">
            {{ $dealLead->dealStatusLabel }}
        </a>
        @elseif($dealLeadView->title_slug == 'deal_no')
        {{ $dealLead->deal_no }}
        {{-- @elseif($dealLeadView->title_slug == 'deal_type')
        <a href="#" data-name="deal_type" data-source='{{ $dealTypes }}' data-value="{{ $dealLead->deal_type }}" class="detailupdateDeal editable editable-click" data-type="select" data-pk="{{ $dealLead->id }}" data-title="SELECT Deal Type" data-original-title="Select Deal Type:">
            {{ $dealLead->dealTypeLabel }}
        </a> --}}
     {{--    @elseif($dealLeadView->title_slug == 'purchase_finance')
        <a href="#" data-name="purchase_finance" data-source='{{ $purchaseFinance }}' data-value="{{ $dealLead->purchase_finance }}" class="detailupdateDeal editable editable-click" data-type="select" data-pk="{{ $dealLead->id }}" data-title="SELECT Purchase Finance" data-original-title="Select Purchase Finance:">
            {{ $dealLead->purchaseFinanceLabel}}
        </a> --}}
        @elseif($dealLeadView->title_slug == 'ownership')
        <a href="#" data-name="ownership" data-source='{{ $ownershipList }}' data-value="{{ $dealLead->ownership }}" class="detailupdateDeal editable editable-click" data-type="select" data-pk="{{ $dealLead->id }}" data-title="SELECT Purchase Finance" data-original-title="Select Purchase Finance:">
            {{ $dealLead->ownershipLabel}}
        </a>
        @elseif($dealLeadView->title_slug == 'purchase_date')
        <a href="#" data-name="purchase_date" class="detailupdateDeal editable editable-click" data-type="date" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter Purchase Date:" title="">
            {{ dynamicDateFormat($dealLead->purchase_date,5) }}
        </a>	
        @elseif($dealLeadView->title_slug == 'sell_date')
        <a href="#" data-name="sell_date" class="detailupdateDeal editable editable-click" data-type="date" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter Sell Date:" title="">
            {{ dynamicDateFormat($dealLead->sell_date,5) }}
        </a>	
       {{--  @elseif($dealLeadView->title_slug == 'purchase_price')
        {{ empty($dealLead->purchase_price)  ?   '':'$'  }}
        <a href="#" data-name="purchase_price" class="detailupdateDeal editable editable-click" data-type="number" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter Purchase Price:" title="">
            {{ $dealLead->purchase_price }}
        </a> --}}
        {{-- @elseif($dealLeadView->title_slug == 'purchase_closing_costs')
        {{ empty($dealLead->purchase_closing_costs)  ?   '':'$'  }}
        <a href="#" data-name="purchase_closing_costs" class="detailupdateDeal editable editable-click" data-type="number" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter Purchase Closing Costs:" title="">
            {{ $dealLead->purchase_closing_costs }}
        </a> --}}
        @elseif($dealLeadView->title_slug == 'cash_in_at_purchase')
        {{ empty($dealLead->cash_in_at_purchase)  ?   '':'$'  }}
        <a href="#" data-name="cash_in_at_purchase" class="detailupdateDeal editable editable-click" data-type="number" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter Cash In At Purchase:" title="">
            {{ $dealLead->cash_in_at_purchase }}
        </a>
        @elseif($dealLeadView->title_slug == 'rehab_and_other_costs')
        {{ empty($dealLead->rehab_and_other_costs)  ?   '':'$'  }}
        <a href="#" data-name="rehab_and_other_costs" class="detailupdateDeal editable editable-click" data-type="number" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter Rehab And Other Costs:" title="">
            {{ $dealLead->rehab_and_other_costs }}
        </a>
        @elseif($dealLeadView->title_slug == 'total_cash_in')
        {{ empty($dealLead->total_cash_in)  ?   '':'$'  }}
        <a href="#" data-name="total_cash_in" class="detailupdateDeal editable editable-click" data-type="number" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter Total Cash In:" title="">
            {{ $dealLead->total_cash_in }}
        </a>
        @elseif($dealLeadView->title_slug == 'investor_commission')
        {{ empty($dealLead->Investor_commission)  ?   '':'$'  }}
        <a href="#" data-name="investor_commission" class="detailupdateDeal editable editable-click" data-type="number" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter Investor Commission:" title="">
            {{ $dealLead->Investor_commission }}
        </a>
        @elseif($dealLeadView->title_slug == 'total_cost')
        {{ empty($dealLead->total_cost)  ?   '':'$'  }}
        <a href="#" data-name="total_cost" class="detailupdateDeal editable editable-click" data-type="number" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter Total Cost:" title="">
            {{ $dealLead->total_cost }}
        </a>
        @elseif($dealLeadView->title_slug == 'sales_value')
        {{ empty($dealLead->sales_value)  ?   '':'$'  }}
        <a href="#" data-name="sales_value" class="detailupdateDeal editable editable-click" data-type="number" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter Sales Value:" title="">
            {{ $dealLead->sales_value }}
        </a>
        @elseif($dealLeadView->title_slug == 'sales_cash_proceeds')
        {{ empty($dealLead->sales_cash_proceeds)  ?   '':'$'  }}
        <a href="#" data-name="sales_cash_proceeds" class="detailupdateDeal editable editable-click" data-type="number" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter Sales Cash Proceeds:" title="">
            {{ $dealLead->sales_cash_proceeds }}
        </a>
        @elseif($dealLeadView->title_slug == 'lh_profit_after_sharing')
        {{ empty($dealLead->lh_profit_after_sharing)  ?   '':'$'  }}
        <a href="#" data-name="lh_profit_after_sharing" class="detailupdateDeal editable editable-click" data-type="number" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter LH profit After Sharing:" title="">
            {{ $dealLead->lh_profit_after_sharing }}
        </a>
        @elseif($dealLeadView->title_slug == 'notes')
        <a href="#" data-name="notes" class="detailupdateDeal editable editable-click" data-type="text" data-pk="{{ $dealLead->id }}" data-title="title" data-original-title="Enter Notes:" title="">
            {{ $dealLead->notes }}
        </a>	
        @elseif($dealLeadView->title_slug == 'action')
        <a href="{{ route('tenant.deals.edit',$dealLead->id) }}" class="btn btn-info btn-sm">Edit</a>
        @else
        @if(isset($dealLeadArray[$dealLeadView->title_slug]))
        {{$dealLeadArray[$dealLeadView->title_slug]}}
       {{--  @elseif(isset($custum_fieldsArray[$dealLeadView->title_slug]))
        {{$custum_fieldsArray[$dealLeadView->title_slug]}} --}}
        @else
            @if($dealLeadView->input_type == 1)
                <a href="#" data-name="{{ $dealLeadView->view_type == 1 ?  getCustomFiledDeal($dealLeadView->title_slug,$dealLead['id'])->id :$dealLeadView->title_slug }}" class="{{ $dealLeadView->view_type == 1 ? 'detailCustomUpdate':'detailupdateDeal' }}  editable editable-click" data-type="textarea" data-value="{{  $dealLead[$dealLeadView->title_slug] }}" data-pk="{{ $dealLead['id'] }}" data-title="title" data-original-title="Enter {{ $dealLeadView->title }}:" title="">{{getCustomFiledDeal($dealLeadView->title_slug,$dealLead['id'])->field_value}}</a>
            @elseif($dealLeadView->input_type == 2)
                  <a href="#" data-name="{{ $dealLeadView->view_type == 1 ?  getCustomFiledDeal($dealLeadView->title_slug,$dealLead['id'])->id :$dealLeadView->title_slug }}" class="{{ $dealLeadView->view_type == 1 ? 'detailCustomUpdate':'detailupdateDeal' }}  editable editable-click" data-type="date" data-pk="{{ $dealLead['id'] }}" data-value="{{  getCustomFiledDeal($dealLeadView->title_slug,$dealLead['id'])->field_value }}" data-title="title" data-original-title="Enter {{ $dealLeadView->title }}:" title="">{{ getCustomFiledDeal($dealLeadView->title_slug,$dealLead['id'])->field_value }}
                </a>
            @elseif($dealLeadView->input_type == 3)
                 <a href="#" data-name="{{ $dealLeadView->view_type == 1 ?  getCustomFiledDeal($dealLeadView->title_slug,$dealLead['id'])->id :$dealLeadView->title_slug }}" class="{{ $dealLeadView->view_type == 1 ? 'detailCustomUpdate':'detailupdateDeal' }}  editable editable-click" data-type="number" data-pk="{{ $dealLead['id'] }}" data-value="{{  getCustomFiledDeal($dealLeadView->title_slug,$dealLead['id'])->field_value }}"  data-title="title" data-original-title="Enter {{ $dealLeadView->title }}:" title="">{{ getCustomFiledDeal($dealLeadView->title_slug,$dealLead['id'])->field_value }}
                    
                </a>
            @elseif($dealLeadView->input_type == 4)
              $ <a href="#" data-name="{{ $dealLeadView->view_type == 1 ?  getCustomFiledDeal($dealLeadView->title_slug,$dealLead['id'])->id :$dealLeadView->title_slug }}" class="{{ $dealLeadView->view_type == 1 ? 'detailCustomUpdate':'detailupdateDeal' }}  editable editable-click" data-type="number" data-pk="{{ $dealLead['id'] }}" data-title="title" data-value="{{   getCustomFiledDeal($dealLeadView->title_slug,$dealLead['id'])->field_value }}"  data-original-title="Enter {{ $dealLeadView->title }}:" title="">
                @if(is_numeric(getCustomFiledDeal($dealLeadView->title_slug,$dealLead['id'])->field_value))
                    {{  number_format(getCustomFiledDeal($dealLeadView->title_slug,$dealLead['id'])->field_value,2) }}
                @else
                    {{ getCustomFiledDeal($dealLeadView->title_slug,$dealLead['id'])->field_value }}
                @endif
                </a>
            @elseif($dealLeadView->input_type == 5)

                <a href="#" data-name="{{ $dealLeadView->view_type == 1 ?  getCustomFiledDeal($dealLeadView->title_slug,$dealLead['id'])->id :$dealLeadView->title_slug }}" class="{{ $dealLeadView->view_type == 1 ? 'detailCustomUpdate':'detailupdateDeal' }}  editable editable-click" data-type="select" data-source="{{ $dealLeadView->pickListArray }}" data-value="{{ getCustomFiledDeal($dealLeadView->title_slug,$dealLead['id'])->field_value }}"  data-pk="{{ $dealLead['id'] }}" data-title="title" data-original-title="Enter {{ $dealLeadView->title }}:" title="">{{ getCustomFiledDeal($dealLeadView->title_slug,$dealLead['id'])->field_value }}              
                </a>
            @endif
         
        @endif
        @endif
    </td>
    @empty

    @endforelse
</tr>
@empty
<tr><td colspan="{{ count($dealLeadViewSetp) }}" class="text-center">No Data Found!</td></tr>
@endforelse