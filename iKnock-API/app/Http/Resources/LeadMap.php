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

class LeadMap extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        $response = [
            'id' => $this->id,
            'title' => $this->title,
            'address' => $this->address,
            'city' => $this->city,
            'zip_code' => $this->zip_code,
            'state' => $this->state,
            'status' => new \App\Http\Resources\Status($this->leadStatus),
            'type' => new \App\Http\Resources\Type($this->leadType),
            'coordinate' => ['latitude' => floatval($this->latitude),
                'longitude' => floatval($this->longitude)],
        ];

        return $response;
    }

}
