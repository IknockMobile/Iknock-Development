@forelse($followingLeads as $key=>$followingLead)
<tr>
	@include('tenant.purchaselead.subTablelist',['followingLead'=>$followingLead,'followUpLeadViewSetps'=>$followUpLeadViewSetps,'linenumber'=>++$key,'users'=>$users,'mobileusers'=>$mobileusers,'statues'=>$statues])
<tr>
@empty
<tr class="text-center">
	<td colspan="{{ $countSlug }}">Data No Found</td>
</tr>
@endforelse
