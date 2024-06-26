<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\LeadHistory as LeadHistoryGet;

class LeadHistory extends JsonResource
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
            'lead_history_id' => $this->lead_history_id,
            'title' => $this->title,
            'owner' => (empty($this->owner)) ? '' : $this->owner,
            'creator_id' => $this->creator_id,
            'address' => $this->address,
            'key_history' => $this->key_history ?? '',
            'value_history' => $this->value_history ?? '',
            'assign_id' => $this->assign_id,
            'assign' => new User((\App\Models\User::getById($this->assign_id))),
            'query' =>  [],
            'custom' =>  [],
            //'status' =>  new Status(\App\Models\Status::getById($this->status_id)), //new \App\Http\Resources\Status($this->leadStatus),
            'status' =>  new \App\Http\Resources\Status($this->leadStatus),
            'latest_status' =>  new \App\Http\Resources\Status($this->leadLatestStatus),
            'type' => new \App\Http\Resources\Type($this->leadType),
            'media' => Media::collection($this->leadMedia),
            //'created_at' => $this->created_at->diffForHumans()
            'created_at' => dynamicDateFormat(dateTimezoneChange($this->created_at),3),
        ];

        if(!empty($this->leadStatus) && !empty($this->lead_history_title)){
            if($response['assign']->user_group_id != 3){
                $this->leadStatus->title = $this->lead_history_title;
            }else{
                if(!empty($this->lead_history_title)){
                    $this->leadStatus->title = $this->lead_history_title;
                }else{
                    $leadHistory = LeadHistoryGet::find($this->lead_history_id);
                    $this->leadStatus->title = $leadHistory->title;
                }

            }
        }

        if(empty($this->leadStatus)){
            $response['status'] = [
                'id' => $this->id,
                'title' => $this->lead_history_title,
                'code' => $this->code,
                'lead_count' => 0,
                'lead_percentage' => 0,
                'color_code' => '#00FF00'
            ];

        }

        return $response;
    }


    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
     public function getDayLightTimeZone($sendDate)
     {
          $check_daylight = date('d-M-Y');
            $date = new \DateTime($check_daylight . 'America/Chicago');
            $check_daylight_result = $date->format('I');
            if ($check_daylight_result == 1) {
                $timestamp1 = date('Y-m-d H:i:s',strtotime($sendDate));
                $getDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $timestamp1);
                $getDate->setTimezone('CST');
                $timestamp = strtotime($getDate) + 60 * 60;
                $getDate = date('m-d-Y h:i A', $timestamp);
            } else {
                $timestamp1 = date('Y-m-d H:i:s',strtotime($sendDate));
                $getDate = Carbon::createFromFormat('m-d-Y h:i A', $timestamp1);
                $getDate->setTimezone('CST');
            }

         return $getDate;
     }
}
