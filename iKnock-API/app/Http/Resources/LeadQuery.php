<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Http\Resources\Json\JsonResource;
class LeadQuery extends JsonResource
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
            'id' => empty($this->id) ? '' : $this->id,
            'type' => $this->type,
            'query' => $this->query,
            'response' => empty($this->response) ? '' : $this->response,
            'created_at' => dynamicDateFormat(dateTimezoneChange($this->created_at),3),
        ];

        return $response;
    }
}
