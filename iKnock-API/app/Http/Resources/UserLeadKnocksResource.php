<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class UserLeadKnocksResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request) {
        if (env('APP_ENV') == 'local') {
            $last_is_verified_data = 11777;
        } elseif (env('APP_ENV') == 'staging') {
            $last_is_verified_data = 9459;
        } elseif (env('APP_ENV') == 'production') {
            $last_is_verified_data = 12060;
        } else {
            $last_is_verified_data = 0;
        }

        $data['user_name'] = $this->user->first_name . ' ' . $this->user->last_name;
        $data['homeowner_name'] = $this->lead->title ?? '-';
        $data['homeowner_address'] = $this->lead->formatted_address ?? '-';


        if ($last_is_verified_data != 0 AND $last_is_verified_data <= $this->id) {
            if ($this->is_verified == 1) {
                $data['Is Verified'] = 'YES';
            } else {
                $data['Is Verified'] = 'NO';
            }
        } else {
            if ($this->lead->is_verified == 1) {
                $data['Is Verified'] = 'YES';
            } else {
                $data['Is Verified'] = 'NO';
            }
        }

        $data['status'] = $this->status->title;
        $data['distance'] = round($this->distance);
        $data['created_on'] = date('m/d/Y',strtotime(dateTimezoneChangeFullDateTimeReturn($this->created_at)));
        $data['created_time'] = date('H:i:s',strtotime(dateTimezoneChangeFullDateTimeReturn($this->created_at)));
        
        $data['lead_lat'] = $this->lead_lat;
        $data['lead_long'] = $this->lead_long;
        $data['application_lat'] = $this->application_lat;
        $data['application_long'] = $this->application_long;
        $data['backend_distance'] = $this->backend_distance;        
        
        return $data;
    }

}
