<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Http\Resources\Json\JsonResource;
class UserLeadAppointment extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $query['query'] = [];
        $is_query = (is_numeric(\Request::segment(3))) ? true : false;
        $query = ($is_query)? \App\Models\LeadQuery::getByLeadId($this->id, $this->tenantQuery) : [];
        $query_summary = (isset($query['summary'])) ? $query['summary'] : [];
        $query_appointment = (isset($query['appointment'])) ? $query['appointment'] : [];

        if($this->is_out_bound == ''){
            $this->is_out_bound = 0;
        }else{
            if($this->is_out_bound == 0){
                $this->is_out_bound = 0;
            }else{
                $this->is_out_bound = 1;
            }
        }
      
      
        $response = [
            'id' => (empty($this->id)) ? '' : $this->id,
            'appointment_id' => $this->appointment_id,
            'title' => $this->title,
            'owner' => (empty($this->owner)) ? '' : $this->owner,
            'creator_id' => $this->creator_id,
            'address' => $this->address,
//            'is_out_bound' => (empty($this->is_out_bound)? ($this->is_out_bound === 0)? 0 : 1 : 1),
            'is_out_bound' => $this->is_out_bound,
            'appointment_date' => (empty($this->appointment_date))? '' : date('m-d-Y G:i', strtotime($this->appointment_date)),
            'appointment_end_date' => (empty($this->appointment_end_date))? '' : date('m-d-Y G:i', strtotime($this->appointment_end_date)),
            'appointment_result' => (empty($this->appointment_result))? '' : $this->appointment_result,
            //'query' => LeadQuery::collection($this->tenantQuery),
            'query_summary' => $query_summary,
            'query_appointment' => $query_appointment,
            'custom' =>  $this->leadCustom,
            'person_meeting' =>  $this->person_meeting,
            'result' =>  $this->result,
            'phone' =>  $this->phone,
            'email' =>  $this->email,
            'additional_notes' =>  $this->additional_notes,
            'status' =>  new \App\Http\Resources\Status($this->leadStatus),
            'type' => new \App\Http\Resources\Type($this->leadType),
            'media' => Media::collection($this->leadMedia),
            'created_at' => date('m-d-Y', strtotime($this->created_at))
        ];

        return $response;
    }
}
