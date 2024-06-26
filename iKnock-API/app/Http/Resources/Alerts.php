<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Http\Resources\Json\JsonResource;

class Alerts extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        if ($this->type == 1) {
            $response = [
                'id' => $this->id,
                'type' => 'Email',
                'value' => $this->value
            ];
        } else {
            $response = [
                'id' => $this->id,
                'type' => 'Mobile',
                'value' => $this->value
            ];
        }


        return $response;
    }

}
