<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\SoftDeletes;

class DealLeadViewSetp extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = array();

    public $inpuyTypes = [1 => 'TEXT', 2 => 'DATE', 3 => 'NUMBER', 4 => 'DOLLER', 5 => 'PICK LIST'];
    public $pickListTypes = [1 => 'User', 2 => 'Lead Status', 3 => 'Follow Status'];


      /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function getInputTypeNameAttribute() {
        if (empty($this->input_type)) {
            return '';
        }

        return $this->inpuyTypes[$this->input_type];
    }

     /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function getCustomPickListArrayAttribute() {
        if (empty($this->pick_list_content)) {
            return [];
        }

        return explode(',', $this->pick_list_content);
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function getPickListArrayAttribute() {
        $pickList = [];

        if (!empty($this->input_type) && $this->input_type == 5) {
            if ($this->pick_list_type == 1 && !empty($this->customPickListArray)) {

                foreach ($this->customPickListArray as $key => $value) {
                    $pickList[] = ['value' => $value, 'text' => $value];
                }
            } elseif ($this->pick_list_type == 2 && !empty($this->pick_list_content_model)) {
                switch ($this->pick_list_content_model) {
                    case 1:
                        $usersArray = User::latest()->whereIn('user_group_id', [2, 4])->get();

                        foreach ($usersArray as $key => $value) {
                            $pickList[] = ['value' => $value->fullname, 'text' => $value->fullname];
                        }

                        break;

                    case 2:

                        $statusArray = Status::latest()->get();

                        foreach ($statusArray as $key => $value) {
                            $pickList[] = ['value' => $value->title, 'text' => $value->title];
                        }

                        break;

                    case 3:
                        $statusArray = FollowStatus::latest()->get();

                        foreach ($statusArray as $key => $value) {
                            $pickList[] = ['value' => $value->title, 'text' => $value->title];
                        }

                        break;

                    default:
                        // code...
                        break;
                }
            }
        }

        return json_encode($pickList);
    }
}
