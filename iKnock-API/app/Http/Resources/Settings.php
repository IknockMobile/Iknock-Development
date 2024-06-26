<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Http\Resources\Json\JsonResource;
class Settings extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'setting_id' => $this->setting_id,
            //'user_id' => User::collection(\App\Models\User::getById($this->user_id)),
            'key' => $this->key,
            'label' => ucwords(str_replace('_', ' ', $this->key)),
            'value' => $this->value,
            'created_at' => date('m-d-Y', strtotime($this->created_at)),
        ];
    }
}
