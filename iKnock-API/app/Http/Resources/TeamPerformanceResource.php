<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeamPerformanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {   
        $data['user_name'] = $this['agent_name'];
        $data['knocks_count'] = $this['lead_count'];
        $data['appointment_count'] = $this['appointment_count'];
        $data['profit'] = $this['commission_profit_count'];
        $data['contract'] = ''.count($this['commission_contract_count']).'';
        return $data;
    }
}
