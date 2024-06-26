<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as Auditables;

class UserLeadKnocks extends Model implements Auditable {

    use Auditables;

    protected $table = "user_lead_knocks";
    protected $guarded = array();

    public function lead() {
        return self::hasOne(Lead::class, 'id', 'lead_id')->withTrashed();
    }

    public function user() {
        return self::hasOne(User::class, 'id', 'user_id');
    }

    public function status() {
        return self::hasOne(Status::class, 'id', 'status_id');
    }

    public static function getById($id) {
        $query = self::select();
        return $query->where('id', $id)
                        ->first();
    }

    public static function getList($tenant_id) {
        $query = self::select();
        return $query->where('tenant_id', $tenant_id)
                        ->whereNull('deleted_at')
                        ->orderBy('title', 'asc')
                        ->get();
    }

    public static function insertLeadKnocks($params) {
        $params['lead_history_id'] = !empty($params['lead_history_id']) ? $params['lead_history_id'] : null;
        $params['created_at'] = NOW();
        $input['user_id'] = $params['user_id'];
        $input['lead_id'] = $params['lead_id'];
        $input['status_id'] = $params['status_id'];
        $input['lead_history_id'] = $params['lead_history_id'];

        if (isset($params['distance'])) {
            $params['distance'] = !empty($params['distance']) ? $params['distance'] : null;
            $input['distance'] = $params['distance'];
        }

        if (isset($params['is_verified'])) {
            $params['is_verified'] = !empty($params['is_verified']) ? $params['is_verified'] : 0;
            $input['is_verified'] = $params['is_verified'];
        }

        if (isset($params['lead_lat'])) {
            $params['lead_lat'] = !empty($params['lead_lat']) ? $params['lead_lat'] : null;
            $input['lead_lat'] = $params['lead_lat'];
        }
        if (isset($params['lead_long'])) {
            $params['lead_long'] = !empty($params['lead_long']) ? $params['lead_long'] : null;
            $input['lead_long'] = $params['lead_long'];
        }
        if (isset($params['application_lat'])) {
            $params['application_lat'] = !empty($params['application_lat']) ? $params['application_lat'] : null;
            $input['application_lat'] = $params['application_lat'];
        }
        if (isset($params['application_long'])) {
            $params['application_long'] = !empty($params['application_long']) ? $params['application_long'] : null;
            $input['application_long'] = $params['application_long'];
        }

        if (isset($params['lead_lat']) AND isset($params['lead_long']) AND isset($params['application_lat']) AND isset($params['application_long'])) {
            $params['backend_distance'] = calculateDistance($params['lead_lat'], $params['lead_long'], $params['application_lat'], $params['application_long']);
            $input['backend_distance'] = $params['backend_distance'];
        }

        if ($params['lead_history_id'] != null) {
            UserLeadKnocks::create($input);
        }
        return;
    }

}
