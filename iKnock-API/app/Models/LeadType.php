<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use DB;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class LeadType extends Model {

    // use Cachable;

    protected $connection = 'mysql2';
    protected $table = "lead_type_history";

    const UPDATED_AT = null;

    protected $guarded = array();

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function lead() {
        return self::hasOne(Lead::class, 'id', 'lead_id');
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function user() {
        return self::hasOne(User::class, 'id', 'assign_id');
    }

    public static function create($history) {

        if ($history['status_id'] > 0 && empty($history['title']))
            $history['title'] = (!isset($history['lead_status_title'])) ? 'Lead status updated' : $history['lead_status_title'];

        $obj = new static();
        $obj->lead_id = $history['lead_id'];
        $obj->assign_id = $history['assign_id'];
        $obj->type_id = $history['type_id'];
        $obj->title = $history['title'];
        $obj->save();

        return $obj->id;
    }

    public function leadType() {
        return self::hasOne('App\Models\Type', 'id', 'type_id');
    }

}
