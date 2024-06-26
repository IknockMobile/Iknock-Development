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

class LeadIndex extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request) {

        $query['query'] = [];
        $is_query = (is_numeric(\Request::segment(3))) ? true : false;
        // info($this->tenantQuery);
        // $query = ($is_query)? \App\Models\LeadQuery::getByLeadId($this->id, $this->tenantQuery) : [];

        $query_summary = (isset($query['summary'])) ? $query['summary'] : [];
        $query_appointment = (isset($query['appointment'])) ? $query['appointment'] : [];

        $address = (empty($this->zip_code)) ? $this->address : $this->address;
        //$address = (empty($this->formatted_address))? $address : $this->formatted_address;

        $media_map = [[
        'id' => 1,
        'media_type' => 'image',
        'path' => env('APP_URL') . Config::get('constants.MEDIA_IMAGE_PATH') . '5d3a973ece99b88a4f80d33539c08a67.jpeg',
        'thumb' => env('APP_URL') . Config::get('constants.MEDIA_IMAGE_PATH') . '5d3a973ece99b88a4f80d33539c08a67.jpeg',
        ]];

        $owner = (empty($this->owner)) ? '' : $this->owner;
        $owner = explode(' ', $owner);
        $first_name = $owner[0];
        unset($owner[0]);
        $last_name = implode(' ', $owner);

        // info($this->assignee_first.' '.$this->assignee_last);

        $response = [
            'id' => $this->id,
            'title' => $this->title,
            'is_verified' => $this->is_verified,
            'owner' => "$first_name $last_name",
            'first_name' => $first_name,
            'last_name' => $last_name,
            'creator_id' => $this->creator_id,
            // 'assignee' => (!empty($this->assignee_id))?new User(\App\Models\User::getById($this->assignee_id)) : (object)[],
            'assignee' => empty($this->assignee_id) ? '---' : $this->assignee_first . ' ' . $this->assignee_last,
            'address' => $address,
            'city' => $this->city,
            'county' => $this->county,
            'foreclosure_date' => $this->foreclosure_date,
            'admin_notes' => $this->admin_notes,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
            'mortgagee' => $this->mortgagee,
            'loan_type' => $this->loan_type,
            'loan_mod' => $this->loan_mod,
            'trustee' => $this->trustee,
            'owner_address' => $this->owner_address,
            'source' => $this->source,
            'auction' => $this->auction,
            'lead_value' => $this->lead_value,
            'original_loan' => $this->original_loan,
            'loan_date' => $this->loan_date,
            'sq_ft' => $this->sq_ft,
            'yr_blt' => $this->yr_blt,
            'eq' => $this->eq,
            'appointment_date' => (empty($this->appointment_date)) ? '' : date('m-d-Y', strtotime($this->appointment_date)),
            'appointment_end_date' => (empty($this->appointment_end_date)) ? '' : date('m-d-Y', strtotime($this->appointment_end_date)),
            'appointment_result' => (!empty($this->appointment_result)) ? $this->appointment_result : [],
            //'query' => LeadQuery::collection($this->tenantQuery),
            'query_summary' => $query_summary,
            'query_appointment' => $query_appointment,
            'coordinate' => ['latitude' => floatval($this->latitude),
                'longitude' => floatval($this->longitude)],
            'custom' => $this->leadcustom,
            'is_verfied' => (isset($this->is_verified)) ? ($this->is_verified == 1) ? true : false : false,
            'is_expired' => $this->is_expired,
            'is_follow_up' => $this->is_follow_up,
            // 'status' =>  new \App\Http\Resources\Status($this->leadStatus),
            // 'lead_status' => (isset($this->leadStatus->title))? $this->leadStatus->title : '',
            'lead_status' => (isset($this->status_title)) ? $this->leadStatus->title : '',
            'lead_color' => (isset($this->status_title)) ? $this->leadStatus->color_code : '',
            // 'type' => new \App\Http\Resources\Type($this->leadType),
            // 'media' => ($this->leadMedia->count())? Media::collection($this->leadMedia) : $media_map,
            //'media' => $media_map,
            'created_at' => dynamicDateFormat(dateTimezoneChange($this->created_at),3),
            'updated_at' => dynamicDateFormat(dateTimezoneChange($this->updated_at),3),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ];


        if (!empty($this->type_title)) {
            $response['lead_type'] = $this->type_title;
        }

        // $leadQueryData = LeadQuery::where('lead_id',$this->id)->get();
        // $lead_queary_data_notes = LeadQuery::where('lead_id','=', $this->id)
        //                 ->where('query_id','=',8)
        //                 ->first();
        
        
        if ($this->latestnotes->response == '') {
//            $lead_queary_data_notes = LeadQuery::where('lead_id', '=', $this->id)
//                    ->where('query_id', '=', 8)
//                    ->orderBy('id', 'desc')
//                    ->first();
            if (strlen($this->latestnotes->response) > 21)
                $lead_queary_data_notes->response = substr($this->latestnotes->response, 0, 20) . '...';
            $response['Notes_Add_to_Top_Include_Date_Your_Name_and_Notes'] = $this->latestnotes->response ?? '---';
        } else {
            if (strlen($this->latestnotes->response) > 21)
                $this->latestnotes->response = substr($this->latestnotes->response, 0, 20) . '...';
            $response['Notes_Add_to_Top_Include_Date_Your_Name_and_Notes'] = $this->latestnotes->response ?? '---';
        }

        $response['Notes_Add_to_Top_Include_Date_Your_Name_and_Notes'] = mb_convert_encoding($this->latestnotes->response, 'UTF-8', 'UTF-8');

        $response['tenant_query_data'] = $this->tenantquery;
        $response['tenant_query_detail'] = [];


        if (in_array($request['call_mode'], ['admin', 'web'])) {
            $response['custom'] = [];
            foreach ($this->leadcustom as $field) {
                $field['value'] = str_replace(["'"], ['&#039;'], $field['value']);
                $field['key'] = str_replace(["'"], ['&#039;'], $field['key']);
                $field['key'] = str_replace(Config::get('constants.SPECIAL_CHARACTERS.IGNORE'), Config::get('constants.SPECIAL_CHARACTERS.REPLACE'), $field['key']);
                $response[$field['key']] = $field['value'];
            }
        }

        $response['last_see_at'] = $this->last_see_at;
        $response['visit_knocks'] = UserLeadKnocks::where('lead_id',$this->id)->get()->count();

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
