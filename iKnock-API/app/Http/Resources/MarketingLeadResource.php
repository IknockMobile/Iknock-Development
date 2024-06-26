<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\FollowStatus;
use App\Models\FollowUpLeadViewSetp;
use App\Models\User;
use App\Models\CampaignTag;
class MarketingLeadResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request) {
        $input['homeowner_name'] = $this['homeowner_name'];
        $input['homeowner_address'] = $this['homeowner_address'];
        $input['lead'] = $this['lead'];
        $input['investor'] = $this['investor'];
        $input['admin_notes'] = $this['admin_notes'];
        $input['admin_notes'] = $this['admin_notes'];
        $input['investore_note'] = $this['investore_note'];
        $input['appt_email'] = $this['appt_email'];
        $input['appt_phone'] = $this['appt_phone'];
        $input['marketing_mail'] = $this['marketing_mail'];
        $input['marketing_address'] = $this['marketing_address'];
        $tags = CampaignTag::where('is_show_marketing', '=', 1)->get();
        foreach ($tags as $tag) {
            $input[$tag->tag_name] = $this[$tag->tag_name];
        }
        return $input;
    }

}
