<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LeadknocksResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $leadknocks['user_name'] = $this->user->first_name.' '.$this->user->last_name;
        $leadknocks['homeowner_name'] = $this->lead->title;
        $leadknocks['homeowner_address'] = $this->lead->formatted_address;
        $leadknocks['status'] = $this->status->title;
        $leadknocks['created_on'] = Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->format('m/d/Y h:i A');

        return $leadknocks;
    }
}
