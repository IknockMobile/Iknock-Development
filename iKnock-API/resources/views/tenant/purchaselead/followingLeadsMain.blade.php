<table class="table lead-table table-bordered">
    <thead>
        @forelse($followUpLeadViewSetps as $key=> $followUpLeadViewSetp)
    <th class="sort-active text-center show-th  {{$followUpLeadViewSetp->title == 'Investor Notes' ? 'note-width':''}} {{ $followUpLeadViewSetp->title_slug == 'notes_and_actions' ? 'note-width':''}}" id="table-th-{{ $key }}">
        @if($followUpLeadViewSetp->view_type == 0)
        @if($followUpLeadViewSetp->title_slug == 'all_delete')
        <input type="checkbox" id="checkfollowUpLeadAll" name="checkfollowUpLeadAll" class="selectfollowUpLeadAll">        
        @else

        @endif
        @else

        @if($followUpLeadViewSetp->title_slug == 'is_lead_up')
        Lead Mgmt
        @else

        @endif
        @endif
        @if($followUpLeadViewSetp->title_slug == 'is_retired' OR $followUpLeadViewSetp->title_slug == 'all_delete' OR $followUpLeadViewSetp->title_slug == 'no' OR $followUpLeadViewSetp->title_slug == 'is_purchase' OR $followUpLeadViewSetp->title_slug == 'is_marketing' OR $followUpLeadViewSetp->title_slug == 'is_followup')
        @if($followUpLeadViewSetp->title_slug != 'all_delete')
        {{ $followUpLeadViewSetp->title }}
        @endif
        @else
        <div class="sort-box">
            @if($followUpLeadViewSetp->title_slug  == $input['sort_column'] || request()->get('sort_column') == $followUpLeadViewSetp->title_slug)

            @if($input['sort_type'] == 'asc' || request()->get('sort_type') == 'asc')
            <span class="sort-columns" data-type="desc" data-toggle="tooltip" data-placement="top" title="Desc" data-id="{{ $followUpLeadViewSetp->id }}" data-column="{{ $followUpLeadViewSetp->title_slug }}">
                <span>{{ $followUpLeadViewSetp->title }}</span>
                <i class="fas fa-sort-down"></i>
            </span>
            @else
            <span class="sort-columns" data-type="asc" data-toggle="tooltip" data-placement="top" title="Asc" data-id="{{ $followUpLeadViewSetp->id }}" data-column="{{ $followUpLeadViewSetp->title_slug }}">
                <span>{{ $followUpLeadViewSetp->title }}</span>
                <i class="fas fa-sort-up"></i>
            </span>
            @endif

            @else
            
            <span class="sort-columns" data-type="asc" data-toggle="tooltip" data-placement="top" title="Asc" data-id="{{ $followUpLeadViewSetp->id }}" data-column="{{ $followUpLeadViewSetp->title_slug }}">
                {{ $followUpLeadViewSetp->title }}
                <i class="fas fa-sort"></i>
            </span>
            
            @endif
        </div>
        @endif
    </th>
    @empty
    @endforelse
    <th class="">
        Action
    </th>
</thead>
<tbody class="body-table">
    {!! $followingLeadsData  !!}
</tbody>
</table>
{{ $followingLeads->appends($input ?? request()->query())->links() }}