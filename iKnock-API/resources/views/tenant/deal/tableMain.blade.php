<table class="table table-bordered deal-table table-striped">
    <thead>
        <tr>
            @forelse($dealLeadViewSetp as $key=>$value)
            <th class="sort-active text-center show-th  {{ $followUpLeadViewSetp->title_slug == 'notes' ? 'note-width':''}}" id="deal-list-{{ $key }}">
                @if($value->title_slug == 'all_delete')
                <input type="checkbox" class="all_delete_deal" >
                @else
                @if(!empty($input) && $value->title_slug  == $input['sort_column'] || request()->get('sort_column') == $value->title_slug)
                @if($input['sort_type'] == 'asc' || request()->get('sort_type') == 'asc')
                <span class="sort-columns" data-type="desc" data-toggle="tooltip" data-placement="top" title="Desc" data-id="{{ $value->id }}" data-column="{{ $value->title_slug }}">
                    <span>{{ $value->title }}</span>
                    <i class="fas fa-sort-down"></i>
                </span>
                @else
                <span class="sort-columns" data-type="asc" data-toggle="tooltip" data-placement="top" title="Asc" data-id="{{ $value->id }}" data-column="{{ $value->title_slug }}">
                    <span>{{ $value->title }}</span>
                    <i class="fas fa-sort-up"></i>
                </span>
                @endif
                @else
                @if($value->title_slug != 'no' && $value->title_slug != 'action')
                <span class="sort-columns" data-type="asc" data-toggle="tooltip" data-placement="top" title="Asc" data-id="{{ $value->id }}" data-column="{{ $value->title_slug }}">
                    {{ $value->title }}
                    <i class="fas fa-sort"></i>
                </span>
                @else
                {{ $value->title }} 

                @endif
                @endif
                @endif
            </th>
            @empty
            @endforelse
        </tr>
    </thead>
    <tbody class="body-table">
        @include('tenant.deal.tableBody',['dealLeads'=>$dealLeads,'dealLeadViewSetp'=>$dealLeadViewSetp,'mobileusers'=>$mobileusers,'dealTypes'=>$dealTypes,'dealStatus'=>$dealStatus,'purchaseFinance'=>$purchaseFinance,'ownershipList'=>$ownershipList])
    </tbody>
</table>
{!! $dealLeads->appends($input ?? request()->query())->links() !!}