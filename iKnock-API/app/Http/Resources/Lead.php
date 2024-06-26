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

class Lead extends JsonResource
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
        // info($this->tenantQuery);
        $query = ($is_query)? \App\Models\LeadQuery::getByLeadId($this->id, $this->tenantQuery) : [];
        $query_summary = (isset($query['summary'])) ? $query['summary'] : [];
        $query_appointment = (isset($query['appointment'])) ? $query['appointment'] : [];

        $address = (empty($this->zip_code))? $this->address : $this->address;
        //$address = (empty($this->formatted_address))? $address : $this->formatted_address;

        // info(env('BASE_URL').Config::get('constants.MEDIA_IMAGE_PATH').'5d3a973ece99b88a4f80d33539c08a67.jpeg');

//        $media_map = [[
//            'id' => 1,
//            'media_type' => 'image',
//            'path' => env('APP_URL').Config::get('constants.MEDIA_IMAGE_PATH').'5d3a973ece99b88a4f80d33539c08a67.jpeg',
//            'thumb' => env('APP_URL').Config::get('constants.MEDIA_IMAGE_PATH').'5d3a973ece99b88a4f80d33539c08a67.jpeg',
//        ]];
        $media_map = [];
        $owner = (empty($this->owner)) ? '' : $this->owner;
        $owner = explode(' ', $owner);
        $first_name = $owner[0];
        unset($owner[0]);
        $last_name = implode(' ', $owner);

        $media = MediaModel::latest()->where('source_id',$this->id)->get();
        
        $response = [
            'id' => $this->id,
            'title' => $this->title,
            'is_verified' => $this->is_verified,
            'owner' => "$first_name $last_name",
            'first_name' => $first_name,
            'last_name' => $last_name,
            'creator_id' => $this->creator_id,
            'assignee' => (!empty($this->assignee_id))?new User(\App\Models\User::getById($this->assignee_id)) : (object)[],
            'address' => $address,
            'city' => $this->city,
            'county' => $this->county,
            'foreclosure_date' => $this->foreclosure_date,
            'admin_notes' => $this->admin_notes,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
            'appointment_date' => (empty($this->appointment_date))? '' : date('m-d-Y', strtotime($this->appointment_date)),
            'appointment_end_date' => (empty($this->appointment_end_date))? '' : date('m-d-Y', strtotime($this->appointment_end_date)),
            'appointment_result' => (!empty($this->appointment_result)) ? $this->appointment_result : [],
            //'query' => LeadQuery::collection($this->tenantQuery),
            'query_summary' => $query_summary,
            'query_appointment' => $query_appointment,
            'auction' => $this->auction,
            'lead_value' => $this->lead_value,
            'original_loan' => $this->original_loan,
            'mortgagee' => $this->mortgagee,
            'loan_type' => $this->loan_type,
            'loan_mod' => $this->loan_mod,
            'trustee' => $this->trustee,
            'owner_address' => $this->owner_address,
            'source' => $this->source,
            'loan_date' => $this->loan_date,
            'sq_ft' => $this->sq_ft,
            'yr_blt' => $this->yr_blt,
            'eq' => $this->eq,
            'coordinate' => ['latitude' => floatval($this->latitude),
                        'longitude' => floatval($this->longitude)],
            'custom' =>  $this->leadCustom,
            'is_verfied' =>  (isset($this->is_verified))? ($this->is_verified == 1)? true : false : false,
            'is_expired' =>  $this->is_expired,
            'status' =>  new \App\Http\Resources\Status($this->leadStatus),
            'lead_status' => (isset($this->leadStatus->title))? $this->leadStatus->title : '',
            'type' => new \App\Http\Resources\Type($this->leadType),
            'media' => !empty($media) && count($media) !== 0 ? Media::collection($media) : $media_map,
            //'media' => $media_map,
            'created_at' => dynamicDateFormat(dateTimezoneChange($this->created_at),3),
            'updated_at' => dynamicDateFormat(dateTimezoneChange($this->updated_at),3),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ];

        if(isset($this->lead_type)){
            $response['lead_type'] = $this->lead_type;
        }

        // $leadQueryData = LeadQuery::where('lead_id',$this->id)->get();

        // $lead_queary_data_notes = LeadQuery::where('lead_id','=', $this->id)
        //                 ->where('query_id','=',8)
        //                 ->first();

        $response['Notes_Add_to_Top_Include_Date_Your_Name_and_Notes'] = $this->Notes_Add_to_Top_Include_Date_Your_Name_and_Notes ?? '---';
        $response['tenant_query_data'] = $this->tenantQuery;
        $response['tenant_query_detail'] = [];


        if(in_array($request['call_mode'],['admin', 'web'])){
            //$response['updated_by'] = (!empty($this->assignee_id))?new User(\App\Models\LeadHistory::getLastHistoryByLeadId($this->id)) : (object)[];
            $response['updated_by'] = new User(\App\Models\LeadHistory::getLastHistoryByLeadId(['lead_id' => $this->id]));

            $response['custom'] = [];
            
            if(!empty($this->leadCustom)){
                foreach ($this->leadCustom as $field) {
                    $field['value'] = str_replace(["'"],['&#039;'], $field['value']);
                    $field['key'] = str_replace(["'"],['&#039;'], $field['key']);
                    $field['key'] = str_replace(Config::get('constants.SPECIAL_CHARACTERS.IGNORE'), Config::get('constants.SPECIAL_CHARACTERS.REPLACE'), $field['key']);
                    $response[$field['key']] = $field['value'];
                    //$response['custom'][] = json_decode(json_encode($field));
                }
            }


        }

        $userLeadKnocks = UserLeadKnocks::where('lead_id',$this->id)->latest()->first();

        $response['last_see_at'] = $userLeadKnocks->created_at ?? Null;
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
                $getDate = date('m-d-Y H:i A', $timestamp);
            } else {
                $timestamp1 = date('Y-m-d H:i:s',strtotime($sendDate));
                $getDate = Carbon::createFromFormat('m-d-Y H:i A', $timestamp1);
                $getDate->setTimezone('CST');
            }

         return $getDate;
     }
}
