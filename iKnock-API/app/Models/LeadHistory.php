<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use DB;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as Auditables;

class LeadHistory extends Model implements Auditable {

    use Auditables,
        Cachable;

    protected $guarded = array();
    protected $connection = 'mysql2';
    protected $table = "lead_history";

    //public $timestamps = false;
    const UPDATED_AT = null;

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function lead() {
        return self::hasOne(Lead::class, 'id', 'lead_id');
    }

    public function latestnotes() {
        return self::hasOne(LeadQuery::class, 'lead_id', 'lead_id')
                        ->select('lead_query.*')
                        ->where('query_id', '=', 8)
                        ->where('response', '!=', '');
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function status() {
        return self::hasOne(Status::class, 'id', 'status_id');
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
        $obj->title = $history['title'];
        $obj->status_id = $history['status_id'];
        $obj->key_history = $history['key_history'] ?? null;
        $obj->value_history = $history['value_history'] ?? null;
        $obj->latest_status_id = $history['latest_status_id'];
        $obj->followup_status_id = $history['followup_status_id'] ?? 0;
        $obj->is_historical = $history['is_historical'] ?? 0;
        $obj->historical_id = $history['historical_id'] ?? null;

        if (!empty($history['created_at'])) {
            $obj->created_at = $history['created_at'];
        }

        $obj->save();

        return $obj->id;
    }

    public static function createNew($history) {
        if ($history['status_id'] > 0 && empty($history['title']))
            $history['title'] = (!isset($history['lead_status_title'])) ? 'Lead status updated' : $history['lead_status_title'];

        $obj = new static();
        $obj->lead_id = $history['lead_id'];
        $obj->assign_id = $history['assign_id'];
        $obj->title = $history['title'];
        $obj->key_history = $history['key_history'];
        $obj->value_history = $history['value_history'];
        $obj->status_id = $history['status_id'];
        $obj->latest_status_id = $history['latest_status_id'];


        $obj->save();

        return $obj->id;
    }

//    public static function getList($params)
//    {
//        //$query = DB::table('lead_history');
//        $query = self::select('lead.id', 'lead.title', 'lead.owner', 'lead.address', 'lead.zip_code', 'lead.city', 'lead.creator_id', 'lead_history.status_id', 'lead.type_id',
//            'lead_history.assign_id',DB::raw("concat(user.first_name,' ', user.last_name) as name")
//            ,DB::raw("(IF(lead_history.status_id = 0, lead_history.title, concat(status.title, ' status updated'))) as lead_history_title")
//            , 'lead_history.created_at');
//        $query->leftJoin('lead', 'lead.id', 'lead_history.lead_id');
//        $query->leftJoin('status', 'status.id', 'lead_history.status_id');
//        $query->leftJoin('user', 'user.id', 'lead_history.assign_id');
//
//        if(isset($params['lead_id']) && !empty($params['lead_id']))
//            $query->where('lead_id', $params['lead_id']);
//
//        if(isset($params['lead_ids']) && !empty($params['lead_ids']))
//            $query->whereIn('lead_id', $params['lead_ids']);
//
//        if(isset($params['search']) && !empty($params['search']))
//            $query->whereRaw("lead.title like '%{$params['search']}%'");
//
//        $query->with('leadStatus');
//        $query->with('leadType');
//        $query->with('leadMedia');
//
//        if (isset($params['is_lead_export']) AND $params['is_lead_export'] === 'true') {
//            $query->where('lead_history.status_id', '!=', 0);
//            $query->where('lead_history.assign_id', '!=', 0);
//            $query->whereNotNull('lead_history.lead_id');
//            $query->orderBy('lead.title');
//            return $query->get();
//        }
//        $query->orderBy('lead_history.created_at', 'desc');
//        return $query->paginate(Config::get('constants.PAGINATION_PAGE_SIZE'));
//    }

    public static function getList($params) {
        $query = self::select('status.title as status_title',
                        'lead_detail.id',
                        'lead_detail.title',
                        'lead_detail.owner',
                        'lead_detail.address',
                        'lead_detail.zip_code',
                        'lead_detail.city',
                        'lead_detail.state',
                        'lead_detail.county',
                        'lead_detail.creator_id',
                        'lead_history.status_id',
                        'lead_detail.is_verified',
                        'lead_detail.type_id',
                        'lead_detail.admin_notes',
                        'lead_detail.is_expired',
                        'lead_detail.assignee_id',
//                        'lead_detail.status_id',
                        'lead_detail.type_id',
                        'lead_detail.admin_notes',
                        'lead_detail.lead_value',
                        'lead_detail.mortgagee',
                        'lead_detail.original_loan',
                        'lead_detail.loan_date',
                        'lead_detail.loan_mod',
                        'lead_detail.trustee',
                        'lead_detail.sq_ft',
                        'lead_detail.yr_blt',
                        'lead_detail.eq',
                        'lead_detail.owner_address',
                        'lead_detail.source',
                        'lead_detail.created_by',
                        'lead_detail.updated_by',
                        'lead_history.created_at',
                        'lead_detail.updated_at',
                        'lead_status.title as lead_status_title',
                        'lead_user.first_name as lead_assignee_first_name',
                        'lead_user.last_name as lead_assignee_last_name',
                        'lead_user.email as lead_assignee_email',
                        'lead_history.assign_id',
                        DB::raw("concat(user.first_name,' ', user.last_name) as name")
                        , DB::raw("(IF(lead_history.status_id = 0, lead_history.title, concat(status.title, ' status updated'))) as lead_history_title")
                        , 'lead_history.created_at', 'lead_history.followup_status_id', 'lead_history.key_history', 'lead_history.value_history', 'lead_history.latest_status_id');
        $query->leftJoin('lead_detail', 'lead_detail.id', 'lead_history.lead_id');
        $query->leftJoin('status', 'status.id', 'lead_history.status_id');
        $query->leftJoin('user', 'user.id', 'lead_history.assign_id');

//        $query->leftJoin('status as lead_status', 'lead_detail.status_id', '=', 'lead_status.id');
        $query->leftJoin('type', 'lead_detail.type_id', '=', 'type.id');
        $query->leftJoin('user as lead_user', 'lead_detail.assignee_id', '=', 'lead_user.id');
        $query->leftJoin('status as lead_status', 'lead_detail.status_id', '=', 'lead_status.id');


        if (isset($params['lead_id']) && !empty($params['lead_id']))
            $query->where('lead_id', $params['lead_id']);

        if (isset($params['lead_ids']) && !empty($params['lead_ids']))
            $query->whereIn('lead_id', $params['lead_ids']);

        if (isset($params['search']) && !empty($params['search']))
            $query->whereRaw("lead_detail.title like '%{$params['search']}%'");

        $query->with('leadStatus');
        $query->with('leadType');
        $query->with('leadMedia');
        $query->with('followLeadStatus');


        if (isset($params['is_lead_export']) AND $params['is_lead_export'] === 'true') {
            $query->where('lead_history.assign_id', '!=', 0);
            $query->whereNotNull('lead_history.lead_id');
            $query->orderBy('lead_detail.title');
            return $query->get();
        }
        $query->orderBy('lead_history.lead_id', 'desc');
        return $query->get();
    }

    public function leadQuery() {
        return $this->hasOne(LeadQuery::class, 'lead_id', 'lead_id')->where('query_id', 8);
    }

    public static function getListHistory($params) {
       

        $query = self::select('status.title as status_title',
                        'lead_detail.id',
                        'lead_detail.title',
                        'lead_detail.owner',
                        'lead_detail.address',
                        'lead_detail.zip_code',
                        'lead_detail.city',
                        'lead_detail.state',
                        'lead_detail.county',
                        'lead_detail.creator_id',
                        'lead_history.status_id',
                        'lead_detail.is_verified',
                        'lead_detail.type_id',
                        'lead_detail.admin_notes',
                        'lead_detail.is_expired',
                        'lead_detail.assignee_id',
                        'lead_detail.status_id',
                        'lead_detail.type_id',
                        'lead_detail.admin_notes',
                        'lead_detail.lead_value',
                        'lead_detail.mortgagee',
                        'lead_detail.original_loan',
                        'lead_detail.loan_date',
                        'lead_detail.loan_mod',
                        'lead_detail.trustee',
                        'lead_detail.sq_ft',
                        'lead_detail.yr_blt',
                        'lead_detail.eq',
                        'lead_detail.owner_address',
                        'lead_detail.source',
                        'lead_detail.created_by',
                        'lead_detail.updated_by',
                        'lead_detail.created_at as lead_created_at',
                        'lead_detail.updated_at as lead_updated_at',
                        'lead_status.title as lead_status_title',
                        'lead_user.first_name as lead_assignee_first_name',
                        'lead_user.last_name as lead_assignee_last_name',
                        'user.first_name as assign_id_first_name',
                        'user.last_name as assign_id_last_name',
                        'user.email as assign_id_email',
                        'lead_query.response as new_lead_query_response',
                        'lead_history.assign_id'
                        , DB::raw("concat(user.first_name,' ', user.last_name) as name")
                        , DB::raw("(IF(lead_history.status_id = 0, lead_history.title, concat(status.title, ' status updated'))) as lead_history_title")
                        , 'lead_history.created_at', 'lead_history.followup_status_id', 'lead_history.key_history',
                        'lead_history.value_history', 'lead_history.latest_status_id');
        
        $query->with('latestNotes');
//      $query->with('leadQuery');
        $query->with('leadStatus');
        $query->with('leadType');
        $query->with('leadMedia');
        $query->leftJoin('lead_detail', 'lead_detail.id', 'lead_history.lead_id');
        $query->leftJoin('status', 'status.id', 'lead_history.status_id');
        
      $query->Join('lead_query', function ($join) {
          $join->on('lead_query.lead_id', '=', 'lead_history.lead_id')
                  ->where('lead_query.query_id', '=', 8);
      });

//      $query->leftJoin('lead_query', function ($join) {
//                  $join->on('lead_query.lead_id', '=', 'lead_history.lead_id');
//              })->where('lead_query.query_id', '=', 8);        
        
        $query->leftJoin('user', 'user.id', 'lead_history.assign_id');
        $query->leftJoin('type', 'lead_detail.type_id', '=', 'type.id');
        $query->leftJoin('user as lead_user', 'lead_detail.assignee_id', '=', 'lead_user.id');
        $query->leftJoin('status as lead_status', 'lead_detail.status_id', '=', 'lead_status.id');

        if (isset($params['lead_id']) && !empty($params['lead_id']))
            $query->where('lead_history.lead_id', $params['lead_id']);

        if (isset($params['lead_ids']) && !empty($params['lead_ids']))
            $query->whereIn('lead_history.lead_id', $params['lead_ids']);

        if (isset($params['search']) && !empty($params['search']))
            $query->whereRaw("lead_detail.title like '%{$params['search']}%'");

        if (isset($params['is_lead_export']) AND $params['is_lead_export'] === 'true') {
            $query->where('lead_history.assign_id', '!=', 0);
            $query->whereNotNull('lead_history.lead_id');
            $query->orderBy('lead_detail.title');
            return $query->get();
        }

        $query->orderBy('lead_history.lead_id', 'desc');

//        return $query->get();
         $sql = $query->toSql();

         dd($sql);
    }

    public static function getListWeb($params) {
        $query = self::select('status.title as status_title', 'lead_history.id as lead_history_id', 'lead_detail.id', 'lead_detail.title', 'lead_detail.owner', 'lead_detail.address', 'lead_detail.zip_code', 'lead_detail.city', 'lead_detail.creator_id', 'lead_history.status_id', 'lead_detail.type_id',
                        'lead_history.assign_id', DB::raw("concat(user.first_name,' ', user.last_name) as name")
                        , DB::raw("(IF(lead_history.status_id = 0, lead_history.title, concat(status.title, ' status updated'))) as lead_history_title")
                        , 'lead_history.created_at', 'lead_history.key_history', 'lead_history.value_history', 'lead_history.latest_status_id');
        $query->leftJoin('lead_detail', 'lead_detail.id', 'lead_history.lead_id');
        $query->leftJoin('status', 'status.id', 'lead_history.status_id');
        $query->leftJoin('user', 'user.id', 'lead_history.assign_id');

        if (isset($params['lead_id']) && !empty($params['lead_id']))
            $query->where('lead_id', $params['lead_id']);

        if (isset($params['lead_ids']) && !empty($params['lead_ids']))
            $query->whereIn('lead_id', $params['lead_ids']);

        if (isset($params['search']) && !empty($params['search']))
            $query->whereRaw("lead_detail.title like '%{$params['search']}%'");

        $query->with('leadStatus');
        $query->with('leadType');
        $query->with('leadMedia');

        if (isset($params['is_lead_export']) AND $params['is_lead_export'] === 'true') {
            // $query->where('lead_history.status_id', '!=', 0);
            $query->where('lead_history.assign_id', '!=', 0);
            $query->whereNotNull('lead_history.lead_id');
            $query->orderBy('lead_detail.title');
            return $query->get();
        }
        $query->orderBy('lead_history.created_at', 'desc');
//        return $query->get();
        return $query->paginate(Config::get('constants.PAGINATION_PAGE_SIZE'));
    }

    public static function getLastHistoryByLeadId($params) {
        //$query = DB::table('lead_history');
        $query = self::select('user.*');
        $query->join('user', 'user.id', 'lead_history.assign_id');
        $query->where('lead_id', $params['lead_id']);

        $query->orderBy('lead_history.created_at', 'desc');

        if (isset($params['search']) && !empty($params['search']))
            $query->whereRaw("lead_detail.title like '%{$params['search']}%'");

        return $query->first();
    }

    public function leadStatus() {
        return self::hasOne('App\Models\Status', 'id', 'status_id');
    }

    public function followLeadStatus() {
        return self::hasOne('App\Models\FollowStatus', 'id', 'followup_status_id');
    }

    public function leadLatestStatus() {
        return self::hasOne('App\Models\Status', 'id', 'latest_status_id');
    }

    public function leadType() {
        return self::hasOne('App\Models\Type', 'id', 'type_id');
    }

    public function leadMedia() {
        return self::hasMany('App\Models\Media', 'source_id')
                        ->where('source_type', 'lead');
    }

}
