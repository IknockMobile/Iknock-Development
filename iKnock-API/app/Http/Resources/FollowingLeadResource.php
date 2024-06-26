<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\FollowStatus;
use App\Models\FollowUpLeadViewSetp;
use App\Models\User;

class FollowingLeadResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request) {

        $followUpLeadViewSetp = FollowUpLeadViewSetp::whereNotIn('title_slug', ['no', 'is_deal', 'is_marketing', 'lead_mgmt', 'action', 'is_lead_up', 'all_delete', 'notes_and_actions'])->orderBy('order_no', 'asc')->get();

        $input = [];
        if (!empty($followUpLeadViewSetp)) {
            foreach ($followUpLeadViewSetp as $key => $followUpLeadView) {
                if ($followUpLeadView->title_slug == 'homeowner_name') {
                    $input['homeowner_name'] = $this['homeowner_name'];
                } elseif ($followUpLeadView->title_slug == 'is_retired') {
                    $input['is_retired'] = $this['is_expired'] = 1 ? 'Yes' : 'No';
                } elseif ($followUpLeadView->title_slug == 'homeowner_address') {
                    $input['homeowner_address'] = $this['homeowner_address'];
                } elseif ($followUpLeadView->title_slug == 'investor') {
                    $user = User::find($this['investor_id']);
                    $input['investor'] = $user->fullname;
                } elseif ($followUpLeadView->title_slug == 'status') {
                    $followStatus = FollowStatus::find($this['follow_status']);
                    $input['status'] = $followStatus->title;
                } elseif ($followUpLeadView->title_slug == 'auction_date') {
                    $input['auction_date'] = $this['auction'];
                } elseif ($followUpLeadView->title_slug == 'homeowner_city') {
                    $input['homeowner_city'] = $this['homeowner_city'];
                } elseif ($followUpLeadView->title_slug == 'investor_notes') {
                    $input['investor_notes'] = $this['investor_notes'];
                } elseif ($followUpLeadView->title_slug == 'lead') {
                    $input['lead'] = getUser($this['user_detail'])->fullName;
                } else {
                    $input[$followUpLeadView->title_slug] = $this[$followUpLeadView->title_slug] ?? '';
                }
            }
        }

        $input['appointment_date'] = $this['appointment_date'] == '' ? '' : date('m/d/Y', strtotime($this['appointment_date']));
        $input['appointment_time'] = $this['appointment_date'] == '' ? '' : date('H:i:s', strtotime($this['appointment_date']));
        $input['appointment_result'] = $this['appointment_result'];
        $input['additional_note'] = $this['additional_note'];
        $input['additional_mobile'] = $this['additional_mobile'];
        $input['additional_email'] = $this['additional_email'];
        $input['additional_person'] = $this['additional_person'];

        return $input;
    }

}
