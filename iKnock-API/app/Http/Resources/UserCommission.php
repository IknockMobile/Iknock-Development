<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Http\Resources\Json\JsonResource;
class UserCommission extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $response = [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'user_id' => $this->user_id,
            'user_name' => $this->first_name .' '. $this->last_name,
            'lead_id' => $this->lead_id,
            'lead_title' => $this->title,
            'target_month' => date('Y-m-d', strtotime($this->target_month)),
            'month' => dynamicDateFormat(dateTimezoneChange($this->created_at),5),
            'commission' => $this->commission,
            'commission_event' => $this->commission_event,
            'comments' => $this->comments,
            'created_at' => date('m-d-Y', strtotime($this->created_at)),
        ];
        return $response;
    }
}
