<?php
namespace App\Http\Resources;

use App\Models\UserGenre;
use App\Models\UserProperty;
use App\Models\UserWishlist;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $image_url = ($this->gender == 'female')? asset('/image/').'/female.png': asset('/image/').'/male.png';
        $user_exist_image = ($this->user_group_id == 3)? env('APP_URL').'/'.$this->image_url : env('APP_URL').Config::get('constants.USER_IMAGE_PATH').$this->image_url;
        $user_exist_image = (filter_var($this->image_url, FILTER_VALIDATE_URL))? $this->image_url : $user_exist_image;

        if($this->user_group_id == 1){
            $typeGroup = 'tenant';
        }elseif($this->user_group_id == 2){
            $typeGroup = 'agent';
        }else{
            $typeGroup = 'sub admin';
        }

        $response = [
            'id' => $this->id,
            'code' => 'IN' .'-' . str_pad($this->company_id, 3, '0', STR_PAD_LEFT) .'-' .str_pad($this->id, 4, '0', STR_PAD_LEFT),
            'name' => ucfirst($this->first_name) . ' ' . $this->last_name,
            'email' => $this->email,
            'mobile_no' => (empty($this->mobile_no))? '' : $this->mobile_no,
            'date_of_join' => (empty($this->date_of_join))? '' : date('Y-m-d', strtotime($this->date_of_join)),
            'image_url' => (empty($this->image_url)) ? $image_url : asset('uploads/user/'.$this->image_url),
            //'image_url' => (empty($this->image_url)) ? '' : env('APP_URL').Config::get('constants.USER_IMAGE_PATH').$this->image_url,
            'token' => $this->token,
            //'token_expiry_at' => date('m-d-Y', strtotime($this->token_expiry_at)),


            'user_group_id' => $this->user_group_id,
            'user_type' => $typeGroup,

            'age' => $this->age,
            //'gender' => $this->gender,

            //'is_subscribed' => \App\Models\User::verifySubscription($this->id, $this->user_group_id, $this->subscription_expiry_date),

            //'city' => $this->city,
            //'state' => $this->state,
            //'address' => $this->address,

            //'latitude' => $this->latitude,
            //'longitude' => $this->longitude,

            //'website' => $this->website,
            //'about_me' => $this->about_me,
            //'website' => $this->website,
            'user_status_id' => $this->status_id,
            'user_status' => ($this->status_id == 1) ? 'Active' : 'In Active',
            'device_type' => $this->device_type,
            'device_token' => $this->device_token,
            'device' => $this->device,
            'created_at' => dynamicDateFormat(dateTimezoneChange($this->created_at),3),
            'startup_paid' => $this->startup_paid,
            'startup_reimbursed' => $this->startup_reimbursed,
            'last_app_activity' => dateTimezoneChange($this->last_app_activity),
        ];

        if(isset($this->printer_email_address)){
            $response['printer_email_address'] = $this->printer_email_address;
        }

        return $response;
    }
}
