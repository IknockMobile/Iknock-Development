<?php

namespace App\Http\Resources;

use App\Models\Status;
use App\Models\TenantQuery;
use App\Models\Type;
use App\Models\LeadQuery;
use App\Models\UserLeadKnocks;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Media as MediaModel;

class LeadList extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        $address = (empty($this->zip_code)) ? $this->address : $this->address;                
        $response = [
            'id' => $this->id,
            'title' => $this->title,
            'address' => $address,
            'created_at' => dynamicDateFormat(dateTimezoneChange($this->created_at), 3),            
            'status' => new \App\Http\Resources\Status($this->leadStatus),            
            'type' => new \App\Http\Resources\Type($this->leadType),
            'city' => $this->city,
            'last_see_at' => $this->lastLeadKnock->created_at ?? null,
            
            'zip_code' => $this->zip_code,
            'coordinate' => ['latitude' => floatval($this->latitude),
                'longitude' => floatval($this->longitude)],
        ];        
        $response['visit_knocks'] = (int) $this->visit_knocks;        
        return $response;
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function getDayLightTimeZone($sendDate) {
        $check_daylight = date('d-M-Y');
        $date = new \DateTime($check_daylight . 'America/Chicago');
        $check_daylight_result = $date->format('I');
        if ($check_daylight_result == 1) {
            $timestamp1 = date('Y-m-d H:i:s', strtotime($sendDate));
            $getDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $timestamp1);
            $getDate->setTimezone('CST');
            $timestamp = strtotime($getDate) + 60 * 60;
            $getDate = date('m-d-Y H:i A', $timestamp);
        } else {
            $timestamp1 = date('Y-m-d H:i:s', strtotime($sendDate));
            $getDate = Carbon::createFromFormat('m-d-Y H:i A', $timestamp1);
            $getDate->setTimezone('CST');
        }

        return $getDate;
    }

}
