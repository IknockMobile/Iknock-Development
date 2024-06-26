<?php

namespace App\Models;

use App\Libraries\Helper;
use App\Libraries\History;
use function GuzzleHttp\Psr7\str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Carbon\Carbon;
use App\Models\FollowingLead;
use Carbon\CarbonPeriod;
use App\Models\UserLeadKnocks;
use DB;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as Auditables;
use App\Models\PurchaseLead;
use DateTime;

class Lead extends Model implements Auditable {

    protected $table = "lead_detail";

    use SoftDeletes,
        Auditables,
        Cachable;

    protected $guarded = array();

    // protected $casts = [
    //     'original_loan' => 'integer',
    // ];



    public static function getById($id) {

        $query = self::select();
        $query->with('tenantQuery');
        $query->with('leadCustom');
        $query->with('leadStatus');
        $query->with('leadType');
        $query->with('leadMedia');
        return $query->where('id', $id)
                        ->first();
    }

    public static function saveTempFile($params) {

        \DB::statement("INSERT INTO tenant_tmp_file (tenant_id, media_url, created_at) VALUES 
                    ({$params['tenant_id']}, '{$params['media_url']}', NOW())
                    ON DUPLICATE KEY UPDATE media_url = '{$params['media_url']}'");

        return true;
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    protected static function boot() {

        parent::boot();

        static::updating(function ($input) {
            if ($input->isDirty('is_expired') == 1) {
                createLeadHistory($input->id, $input->is_expired, session()->get('user'));
            }
        });
    }

    public function getAdNoteAttribute() {
        return $this->admin_notes;
    }

    public static function bulkUpdate($params) {
        $lead_ids = [];
        $lead_status_count = [];
        $lead_history = [];
        $lead_result = \DB::select("SELECT * FROM lead_detail WHERE company_id = {$params['company_id']} AND id IN ({$params['lead_ids']})");

        $user_data = User::where('id', $params['user_id'])
                ->first();

        if (isset($user_data->first_name) AND $user_data->first_name != '' AND is_array($params['lead_ids'])) {
            $updated_data = [];
            $updated_data['updated_by'] = $user_data->first_name . ' ' . $user_data->last_name;
            Lead::whereIn('id', $params['lead_ids'])->update($updated_data);
        }


        $obj_history = new History();
        $obj_history->history_trigger_prefx = 'lead';
        $obj_history->history_trigger_map = ['bulkAssignee', 'bulkExpired', 'bulkStatus'];

        foreach ($lead_result as $lead_row) {
            $lead_ids[] = $lead_row->id;
            if (!empty($params['status_id']) && $params['status_id'] != $lead_row->status_id) {
                if (!isset($lead_status_count[$lead_row->status_id]))
                    $lead_status_count[$lead_row->status_id] = 0;
                $lead_status_count[$lead_row->status_id]++;
            }
            $lead_old_data = (array) $lead_row;
            $params['id'] = $lead_row->id;
            $params['target_id'] = (($params['assign_id'] == '')) ? $lead_row->assignee_id : $params['assign_id'];

            if (empty($params['status_id']))
                $params['status_id'] = $lead_row->status_id;

            if ($params['is_expired'] == '')
                $params['is_expired'] = $lead_row->is_expired;



            $obj_history->initiate($lead_old_data, $params);
        }

        $params['lead_ids'] = implode(',', $lead_ids);


        $update_value = [];
        if (!empty($params['assign_id']))
            $update_value[] = 'assignee_id = ' . $params['assign_id'];

        if (!empty($params['status_id'])) {
            $update_value[] = 'status_id = ' . $params['status_id'];

            if (count($lead_history))
                \DB::statement("INSERT INTO lead_history (lead_id, assign_id, status_id, created_at) VALUES " . implode(',', $lead_history));

            $update_status_count = 0;
            foreach ($lead_status_count as $status_id => $status_count) {
                \DB::statement("Update `status` SET lead_count = (lead_count - $status_count) WHERE id = $status_id");
                $update_status_count += $status_count;
            }

            if ($update_status_count)
                \DB::statement("Update `status` SET lead_count = (lead_count + $update_status_count) WHERE id = {$params['status_id']}");
        }

        if (!empty($params['type_id']))
            $update_value[] = 'type_id = ' . $params['type_id'];

        if (($params['is_expired'] == 0 || $params['is_expired'] == 1) && $params['is_expired'] != '')
            $update_value[] = 'is_expired = ' . $params['is_expired'];

        if ($params['action'] == 'delete') {
            $update_value = [];
            $update_value[] = 'deleted_at = NOW()';
            UserLeadAppointment::destroyByLeadId($params['lead_ids']);
        }
        \DB::statement("Update lead_detail SET " . implode(',', $update_value) . " WHERE id IN ({$params['lead_ids']})");
        $obj_history->bulkExecute();
        return true;
    }

    public static function saveTemplate($params) {

        \DB::statement("INSERT INTO tenant_template (tenant_id, title, description, created_at) VALUES 
                    ({$params['tenant_id']}, '{$params['title']}', '{$params['description']}', NOW())");
        return \DB::getPdo()->lastInsertId();
    }

    public static function saveLead($obj) {
        $pdo_parmas = [];
        $pdo_parmas['assignee_id'] = $obj->assignee_id;
        $pdo_parmas['creator_id'] = $obj->creator_id;
        $pdo_parmas['company_id'] = $obj->company_id;
        $pdo_parmas['title'] = $obj->title;
        $pdo_parmas['owner'] = $obj->owner;
        $pdo_parmas['county'] = $obj->county;
        $pdo_parmas['state'] = $obj->state;
        $pdo_parmas['address'] = $obj->address;
        $pdo_parmas['foreclosure_date'] = $obj->foreclosure_date;
        $pdo_parmas['admin_notes'] = $obj->admin_notes;
        $pdo_parmas['formatted_address'] = $obj->formatted_address;
        $pdo_parmas['city'] = $obj->city;
        $pdo_parmas['zip_code'] = $obj->zip_code;
        $pdo_parmas['auction'] = $obj->auction;
        $pdo_parmas['lead_value'] = $obj->lead_value;
        $pdo_parmas['loan_date'] = $obj->loan_date;
        $pdo_parmas['yr_blt'] = $obj->yr_blt;
        $pdo_parmas['sq_ft'] = $obj->sq_ft;
        $pdo_parmas['eq'] = $obj->eq;
        $pdo_parmas['latitude'] = $obj->latitude;
        $pdo_parmas['longitude'] = $obj->longitude;
        $pdo_parmas['type_id'] = $obj->type_id;
        $pdo_parmas['original_loan'] = $obj->original_loan;
        $pdo_parmas['owner_address'] = $obj->owner_address;

        $lead_exits = Lead::where('address', '=', $obj->address)->where('is_follow_up', 0)->first();

        $following_lead_exits = FollowingLead::where('address', '=', $obj->address)
                        ->where('is_lead_up', '=', 0)->where('is_purchase', '=', 0)->first();

        $purchase_lead_exits = PurchaseLead::where('address', '=', $obj->address)
                        ->where('is_followup', '=', 0)->first();

        if (isset($following_lead_exits->id)) {
            $pdo_parmas['is_retired'] = 0;
            $pdo_parmas['is_expired'] = 0;
            FollowingLead::where('id', '=', $following_lead_exits->id)->update($pdo_parmas);

            return $following_lead_exits->lead_id;
        } elseif (isset($purchase_lead_exits->id)) {
            $pdo_parmas['is_retired'] = 0;
            $pdo_parmas['is_expired'] = 0;
            PurchaseLead::where('id', '=', $purchase_lead_exits->id)->update($pdo_parmas);

            return $purchase_lead_exits->lead_id;
        } else {
            if ($lead_exits->is_follow_up == 0 && isset($lead_exits->id) AND $obj->address != '') {
                $pdo_parmas['assignee_id'] = $lead_exits->assignee_id;
                $pdo_parmas['is_expired'] = 0;
                $pdo_parmas['is_follow_up'] = 0;

                if ($lead_exits->is_expired == 1) {
                    $pdo_parmas['status_id'] = 77;
                }

                $updated_field = '';
                $updated_field_ar = [];
                foreach ($pdo_parmas as $key => $new_params) {
                    if ($lead_exits->$key != $pdo_parmas[$key]) {
                        if (!in_array($key, $updated_field_ar)) {
                            $updated_field_ar[] = $key;
                            $updated_field .= $key . ', ';
                        }
                    }
                }


                if ($updated_field != '') {
                    $obj_lead_history = LeadHistory::create([
                                'lead_id' => $lead_exits->id,
                                'title' => $updated_field . ' this listed fields updated in exiting lead.',
                                'assign_id' => $pdo_parmas['creator_id'],
                                'status_id' => 0
                    ]);
                }

                Lead::where('id', '=', $lead_exits->id)->update($pdo_parmas);
                $lead = Lead::where('id', '=', $lead_exits->id)->first();
                return $lead->id;
            } else {
                $pdo_parmas['status_id'] = $obj->status_id;
                $lead = Lead::create($pdo_parmas);
                return $lead->id;
            }
        }
    }

    public static function getTemplateById($template_id, $join = 'LEFT', $field_id = 0) {
        $field_where_clause = '';
        if (!empty($field_id))
            $field_where_clause = " AND tenant_custom_field.id = $field_id";

        $result = \DB::select("Select IFNULL(tenant_custom_field.id, template_fields.field) as id, IFNULL(tenant_custom_field.`key`,
                    template_fields.field) as `key`, template_fields.* 
                    FROM template_fields $join JOIN tenant_custom_field ON tenant_custom_field.id = 
                    template_fields.field WHERE template_fields.template_id = $template_id $field_where_clause AND tenant_custom_field.is_active = 1 AND tenant_custom_field.key != 'updated_by' AND tenant_custom_field.key != 'created_at' AND tenant_custom_field.key != 'created_by' AND tenant_custom_field.key != 'updated_at'  ORDER BY template_fields.order_by ASC");

        return $result;
    }

    public static function getByTemplateId($template_id, $id = '', $join = 'LEFT') {
        $id = !empty($id) ? " HAVING id= '{$id}'" : '';
        $result = \DB::select("Select IFNULL(tenant_custom_field.id, template_fields.field) as id, IFNULL(tenant_custom_field.`key`,template_fields.field) as `key`, template_fields.* FROM template_fields $join JOIN tenant_custom_field ON tenant_custom_field.id = 
                  template_fields.field WHERE template_fields.template_id = $template_id  $id ORDER BY template_fields.order_by ASC");
        return $result;
    }

    public static function getByTenantTemplateFieldDetail($id, $join = 'LEFT') {
        $result = \DB::select("Select tenant_custom_field.id, tenant_custom_field.`key`, template_fields.*
        FROM template_fields $join JOIN tenant_custom_field ON tenant_custom_field.id = template_fields.field 
        WHERE tenant_custom_field.id = $id ORDER BY tenant_custom_field.order_by ASC");
        return $result;
    }

    public static function getFieldsTemplateById($template_id, $params) {
        $join_type = 'INNER';
        if (!empty($params['is_all']))
            $join_type = 'INNER';
        $join_type = 'LEFT';
        if ($params['is_all'] == 2)
            $join_type = 'LEFT';
        if ($template_id == 'max') {
            $max_template = Type::getByMax($params['company_id']);
            $template_id = $max_template->id;
        }

        $result = \DB::select("Select template_fields.*, field as id, tenant_custom_field.key from template_fields
                                  LEFT JOIN tenant_custom_field ON tenant_custom_field.id = template_fields.field 
                                  WHERE template_fields.template_id = $template_id  ORDER BY template_fields.order_by ASC;");

        $columns = Config::get('constants.LEAD_DEFAULT_COLUMNS');
        if ($params['is_all'] == 2)
            $columns = [];

        $defual_set = [];
        return $result;
    }

    public static function getFieldsDefault($params) {

        $result = \DB::select("Select * FROM tenant_custom_field
                          WHERE tenant_id = {$params['tenant_id']}  ORDER BY tenant_custom_field.order_by ASC");

        $columns = ['title', 'owner', 'lead_type', 'address', 'foreclosure_date', 'city', 'county', 'state', 'is_expired'];
        if ($params['is_all'] == 2)
            $columns = [];

        $defual_set = [];
        if (!empty($params['is_all'])) {
            foreach ($columns as $column) {
                $tmp['template_id'] = 0;
                $tmp['field'] = $column;
                $tmp['index'] = 0;
                $tmp['key'] = $column;

                $defual_set[] = $tmp;
            }
            $result = array_merge($defual_set, $result);
        }

        return $result;
    }

    public static function saveTemplateFields($template_id, $data, $index_map = [], $order_by = 1) {
        $statements = [];
        $index_map_col = '';
        foreach ($data as $key => $value) {
            if (isset($index_map[$key])) {
                $index_value = $index_map[$key];
                $index_map_col = ', index_map';
                $statements[] = "($template_id, '{$key}', '$value', $order_by, '$index_value')";
            } else {
                $statements[] = "($template_id, '{$key}', '$value', $order_by)";
            }
        }

        \DB::statement("INSERT INTO template_fields (template_id, field, `index`, order_by $index_map_col) VALUES " .
                implode(',', $statements) . "ON DUPLICATE KEY UPDATE `index` = VALUES(`index`)");
        return true;
    }

    public static function saveTemplateField($template_id, $data) {
        $is_order_by = '';
        foreach ($data as $row) {
            if (isset($row['order_by'])) {
                $is_order_by = ' , order_by';
                $statements[] = "($template_id, '{$row['field']}', '{$row['index']}', '{$row['index_map']}', {$row['order_by']})";
            } else
                $statements[] = "($template_id, '{$row['field']}', '{$row['index']}', '{$row['index_map']}')";
        }


        \DB::statement("INSERT INTO template_fields (template_id, field, `index`, index_map $is_order_by) VALUES " .
                implode(',', $statements) . "ON DUPLICATE KEY UPDATE `index` = VALUES(`index`), `index_map` = VALUES(`index_map`)");
        return true;
    }

    public static function saveTemplateFieldCustom($template_id, $data) {
        $is_order_by = '';
        if (!empty($data)) {
            foreach ($data as $row) {
                if (isset($row['order_by'])) {
                    $is_order_by = ' , order_by';
                    $statements[] = "($template_id, '{$row['field']}', '{$row['index']}', '{$row['index_map']}', {$row['order_by']})";
                } else
                    $statements[] = "($template_id, '{$row['field']}', '{$row['index']}', '{$row['index_map']}')";
            }
        }

        if (!empty($statements)) {
            \DB::statement("INSERT INTO template_fields (template_id, field, `index`, index_map $is_order_by) VALUES " . implode(',', $statements) . "ON DUPLICATE KEY UPDATE `index` = VALUES(`index`), `index_map` = VALUES(`index_map`)");
        }

        return true;
    }

    public static function getTemplateFields($template_id) {
        return \DB::select("SELECT * FROM template_fields WHERE  template_id = $template_id");
    }

    public static function deleteTemplateFields($template_id, $field_id) {
        \DB::statement("DELETE FROM template_fields WHERE  template_id = $template_id AND  field = '$field_id'");
        return true;
    }

    public static function deleteDefaultLeadFields($field_id) {
        \DB::statement("DELETE FROM tenant_custom_field WHERE  id = $field_id");
        return true;
    }

    public static function getTemplate($tenant_id) {
        return \DB::select("SELECT id, title FROM tenant_template WHERE tenant_id = $tenant_id AND deleted_at IS  NULL ORDER BY 1 ASC");
    }

    public static function deleteTemplate($params) {
        \DB::statement("Update tenant_template SET deleted_at = NOW() WHERE id = {$params['template_id']} AND tenant_id = {$params['tenant_id']})");
    }

    public static function getTemplateDetailById($tenant_id, $template_id) {
        return \DB::select("SELECT id, title FROM tenant_template WHERE id = $template_id AND tenant_id = $tenant_id ORDER BY 1 DESC");
    }

    public static function getTempfile($tenant_id) {
        $result = \DB::select("SELECT * FROM tenant_tmp_file WHERE tenant_id = $tenant_id ORDER BY 1 DESC LIMIT 1");
        return (isset($result[0])) ? $result[0] : [];
    }

    public static function getList($params) {
        if (!empty($params['latitude']) && !empty($params['longitude'])) {
            $lat = $params['latitude'];
            $lng = $params['longitude'];
            $radius = $params['radius'];

            $haversine = "(3959 * acos (
                    cos ( radians($lat) )
                    * cos( radians(`latitude`) )
                    * cos( radians(`longitude`) - radians($lng) )
                    + sin ( radians($lat) )
                    * sin( radians(`latitude`) )
                ))";
        }

        $query = self::select('lead_detail.*', \DB::raw('type.title as lead_type'));
        $query->leftJoin('type', 'type.id', 'lead_detail.type_id');
        $query->where('lead_detail.company_id', $params['company_id']);
        $query->where('lead_detail.is_disabled', 0);
        $query->whereNull('lead_detail.deleted_at');

        if (isset($params['user_ids']) && !empty($params['user_ids']))
            $query->whereRaw('lead_detail.assignee_id  IN (' . $params['user_ids'] . ')');

        if (!isset($params['is_web']) || $params['is_web'] == 0) {
            $query->where('lead_detail.is_expired', 0);
        }

        if (isset($params['start_date']) && isset($params['end_date']) && !empty($params['start_date']) && !empty($params['end_date'])) {

            $params['start_date'] = dateTimezoneChangeNew($params['start_date'] . ' 00:00:00');
            $params['end_date'] = dateTimezoneChangeNew($params['end_date'] . ' 23:59:59');

            $params['start_date'] = date('Y-m-d H:i:s', strtotime($params['start_date']));
            $params['end_date'] = date('Y-m-d H:i:s', strtotime($params['end_date']));

            $query->whereRaw("lead_detail.created_at >= '{$params['start_date']}' && lead_detail.created_at <= '{$params['end_date']}'");
        }

        if (!empty($params['lead_type_id'])) {
            $query->whereRaw('lead_detail.type_id  IN (' . $params['lead_type_id'] . ')');
        }
        if (!isset($params['order_by'])) {
            $params['order_by'] = 'id';
            $params['order_type'] = 'desc';
        }
        $order_by = 'lead_detail.' . $params['order_by'];
        if (strtolower($params['order_by']) == 'lead_type') {
            $order_by = 'type.title';
        }

        if (strtolower($params['order_by']) == 'first_name' || strtolower($params['order_by']) == 'last_name')
            $order_by = 'lead_detail.owner'; {
            
        }

        $query->orderBy($order_by, $params['order_type']);

        if (isset($params['search']) && !empty($params['search'])) {

            $query->where(function($querysub) use($params) {
                $querysub->orwhere('zip_code', 'like', '%' . $params['search'] . '%')
                        ->orwhere('formatted_address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('county', 'like', '%' . $params['search'] . '%')
                        ->orwhere('state', 'like', '%' . $params['search'] . '%')
                        ->orwhere('city', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.title', 'like', '%' . trim($params['search']) . '%');
            });
        }

        $time_clause = '';
        $group_by__clause = '';
        if (!empty($params['time_slot'])) {
            $time_clauses['today'] = " DATE(lead_detail.created_at) = DATE(NOW())";
            $time_clauses['yesterday'] = " DATE(lead_detail.created_at) = DATE(NOW() - INTERVAL 1 DAY)";
            $time_clauses['week'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 7 DAY";
            $time_clauses['last_week'] = " YEARWEEK(lead_detail.created_at) = YEARWEEK(NOW() - INTERVAL 1 WEEK)";
            $time_clauses['month'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 1 MONTH";
            $time_clauses['last_month'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 MONTH)  AND month(lead_detail.created_at) = Month(NOW() - INTERVAL 1 MONTH) ";
            $time_clauses['year'] = " lead_detail.created_at > DATE_SUB(NOW(),INTERVAL 1 YEAR)";
            $time_clauses['last_year'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 YEAR)";

            $group_by_clauses['today'] = " hour, day, month, year";
            $group_by_clauses['yesterday'] = " hour, day, month, year";
            $group_by_clauses['week'] = " day, month, year";
            $group_by_clauses['last_week'] = " day, month, year";
            $group_by_clauses['month'] = " day, month, year";
            $group_by_clauses['last_month'] = " day, month, year";
            $group_by_clauses['year'] = " month, year";
            $group_by_clauses['last_year'] = " month, year";

            $slot_types['today'] = " hour";
            $slot_types['yesterday'] = " hour";
            $slot_types['week'] = " day";
            $slot_types['last_week'] = " day";
            $slot_types['month'] = " day";
            $slot_types['last_month'] = " day";
            $slot_types['year'] = " month";
            $slot_types['last_year'] = " month";

            $time_clause = $time_clauses[$params['slot']];
            $group_by__clause = $group_by_clauses[$params['slot']];

            $query->whereRaw("$time_clause");
        }

        if ((isset($params['is_status_group_by']) && $params['is_status_group_by'])) {
            $query->select('lead_detail.*', \DB::raw('count(lead_detail.id) as lead_count'));
            $query->groupBy('lead_detail.status_id');
            return $query->get();
        }

        if ((isset($params['is_paginate']) && !$params['is_paginate']) || array_key_exists("export", $params) && $params['export'] === 'true') {
            return $query->get();
        }



        return $query->paginate(Config::get('constants.PAGINATION_PAGE_SIZE'));
    }

    public static function getListAll($params) {
        if (!empty($params['latitude']) && !empty($params['longitude'])) {
            $lat = $params['latitude'];
            $lng = $params['longitude'];
            $radius = $params['radius'];

            $haversine = "(3959 * acos (
                    cos ( radians($lat) )
                    * cos( radians(`latitude`) )
                    * cos( radians(`longitude`) - radians($lng) )
                    + sin ( radians($lat) )
                    * sin( radians(`latitude`) )
                ))";
        }

        $query = self::select('lead_detail.*', \DB::raw('type.title as lead_type'));
        $query->leftJoin('type', 'type.id', 'lead_detail.type_id');
        $query->where('lead_detail.company_id', $params['company_id']);
        $query->where('lead_detail.is_disabled', 0);
        $query->whereNull('lead_detail.deleted_at');

        if (isset($params['user_ids']) && !empty($params['user_ids']))
            $query->whereRaw('lead_detail.assignee_id  IN (' . $params['user_ids'] . ')');

        if (!isset($params['is_web']) || $params['is_web'] == 0) {
//            $query->where('lead_detail.is_expired', 0);
        }

        if (isset($params['start_date']) && isset($params['end_date']) && !empty($params['start_date']) && !empty($params['end_date'])) {

            $params['start_date'] = dateTimezoneChangeNew($params['start_date'] . ' 00:00:00');
            $params['end_date'] = dateTimezoneChangeNew($params['end_date'] . ' 23:59:59');

            $params['start_date'] = date('Y-m-d H:i:s', strtotime($params['start_date']));
            $params['end_date'] = date('Y-m-d H:i:s', strtotime($params['end_date']));

            $query->whereRaw("lead_detail.created_at >= '{$params['start_date']}' && lead_detail.created_at <= '{$params['end_date']}'");
        }

        if (!empty($params['lead_type_id'])) {
            $query->whereRaw('lead_detail.type_id  IN (' . $params['lead_type_id'] . ')');
        }
        if (!isset($params['order_by'])) {
            $params['order_by'] = 'id';
            $params['order_type'] = 'desc';
        }
        $order_by = 'lead_detail.' . $params['order_by'];
        if (strtolower($params['order_by']) == 'lead_type') {
            $order_by = 'type.title';
        }

        if (strtolower($params['order_by']) == 'first_name' || strtolower($params['order_by']) == 'last_name')
            $order_by = 'lead_detail.owner'; {
            
        }

        $query->orderBy($order_by, $params['order_type']);
        if (isset($params['search']) && !empty($params['search'])) {
            $query->where(function($querysub) use($params) {
                $querysub->orwhere('zip_code', 'like', '%' . $params['search'] . '%')
                        ->orwhere('formatted_address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('county', 'like', '%' . $params['search'] . '%')
                        ->orwhere('state', 'like', '%' . $params['search'] . '%')
                        ->orwhere('city', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.title', 'like', '%' . trim($params['search']) . '%');
            });
        }

        $time_clause = '';
        $group_by__clause = '';
        if (!empty($params['time_slot'])) {
            $time_clauses['today'] = " DATE(lead_detail.created_at) = DATE(NOW())";
            $time_clauses['yesterday'] = " DATE(lead_detail.created_at) = DATE(NOW() - INTERVAL 1 DAY)";
            $time_clauses['week'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 7 DAY";
            $time_clauses['last_week'] = " YEARWEEK(lead_detail.created_at) = YEARWEEK(NOW() - INTERVAL 1 WEEK)";
            $time_clauses['month'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 1 MONTH";
            $time_clauses['last_month'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 MONTH)  AND month(lead_detail.created_at) = Month(NOW() - INTERVAL 1 MONTH) ";
            $time_clauses['year'] = " lead_detail.created_at > DATE_SUB(NOW(),INTERVAL 1 YEAR)";
            $time_clauses['last_year'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 YEAR)";

            $group_by_clauses['today'] = " hour, day, month, year";
            $group_by_clauses['yesterday'] = " hour, day, month, year";
            $group_by_clauses['week'] = " day, month, year";
            $group_by_clauses['last_week'] = " day, month, year";
            $group_by_clauses['month'] = " day, month, year";
            $group_by_clauses['last_month'] = " day, month, year";
            $group_by_clauses['year'] = " month, year";
            $group_by_clauses['last_year'] = " month, year";

            $slot_types['today'] = " hour";
            $slot_types['yesterday'] = " hour";
            $slot_types['week'] = " day";
            $slot_types['last_week'] = " day";
            $slot_types['month'] = " day";
            $slot_types['last_month'] = " day";
            $slot_types['year'] = " month";
            $slot_types['last_year'] = " month";

            $time_clause = $time_clauses[$params['slot']];
            $group_by__clause = $group_by_clauses[$params['slot']];

            $query->whereRaw("$time_clause");
        }

        if ((isset($params['is_status_group_by']) && $params['is_status_group_by'])) {
            $query->select('lead_detail.*', \DB::raw('count(lead_detail.id) as lead_count'));
            $query->groupBy('lead_detail.status_id');
            return $query->get();
        }

        if ((isset($params['is_paginate']) && !$params['is_paginate']) || array_key_exists("export", $params) && $params['export'] === 'true') {
            return $query->get();
        }



        return $query->paginate(Config::get('constants.PAGINATION_PAGE_SIZE'));
    }

    public static function getListIndex($params) {

        if (!empty($params['latitude']) && !empty($params['longitude'])) {
            $lat = $params['latitude'];
            $lng = $params['longitude'];
            $radius = $params['radius'];

            $haversine = "(3959 * acos (
                    cos ( radians($lat) )
                    * cos( radians(`latitude`) )
                    * cos( radians(`longitude`) - radians($lng) )
                    + sin ( radians($lat) )
                    * sin( radians(`latitude`) )
                ))";
        }
        if (!empty($params['auction_start_date']) && !empty($params['auction_end_date'])) {
            $query = self::select('lead_detail.*', 'type.title as type_title', 'status.title as status_title', 'lead_query.response as Notes_Add_to_Top_Include_Date_Your_Name_and_Notes', 'user.first_name as assignee_first', 'user.last_name as assignee_last', 'lead_custom_field.value as auction_date_value');
            $query->leftJoin('lead_custom_field', 'lead_detail.id', '=', 'lead_custom_field.lead_id');
        } else {
            $query = self::select('lead_detail.*', 'type.title as type_title', 'status.title as status_title', 'lead_query.response as Notes_Add_to_Top_Include_Date_Your_Name_and_Notes', 'user.first_name as assignee_first', 'user.last_name as assignee_last');
        }

        $query->with('leadCustom', 'tenantquery', 'latestnotes');
        $query->leftJoin('lead_query', 'lead_detail.id', '=', 'lead_query.lead_id');
        $query->leftJoin('status', 'lead_detail.status_id', '=', 'status.id');
        $query->leftJoin('type', 'lead_detail.type_id', '=', 'type.id');
        $query->leftJoin('user', 'lead_detail.assignee_id', '=', 'user.id');
        $query->where('lead_query.query_id', 8);
        $query->where('lead_detail.is_follow_up', 0);
        $query->where('lead_detail.company_id', $params['company_id']);
        $query->whereNull('lead_detail.deleted_at');

        if (isset($params['user_ids']) && !empty($params['user_ids']))
            $query->whereRaw('lead_detail.assignee_id  IN (' . $params['user_ids'] . ')');

        if (isset($params['status_ids']) && !empty($params['status_ids']))
            $query->whereRaw('lead_detail.status_id  IN (' . $params['status_ids'] . ')');

        if (!isset($params['is_web']) || $params['is_web'] == 0) {
            $query->where('lead_detail.is_expired', 0);
        }

        updateLeadAuctionDate();
        storeDateAuctionDateFormat();
        if (isset($params['auction_start_date']) && isset($params['auction_end_date']) && !empty($params['auction_start_date']) && !empty($params['auction_end_date'])) {
            $startDate = dateChangeDbFromate($params['auction_start_date']);
            $endDate = dateChangeDbFromate($params['auction_end_date']);
            $query->whereBetween('lead_detail.auction_date', [$startDate, $endDate]);
            $query->orwhere('lead_custom_field.tenant_custom_field_id', 157);
        }
        if (!empty($params['start_date']) && !empty($params['end_date'])) {
            $params['start_date'] = dateTimezoneChangeNew($params['start_date'] . ' 00:00:00');
            $params['end_date'] = dateTimezoneChangeNew($params['end_date'] . ' 23:59:59');

            $params['start_date'] = date('Y-m-d H:i:s', strtotime($params['start_date']));
            $params['end_date'] = date('Y-m-d H:i:s', strtotime($params['end_date']));
            $query->whereRaw("lead_detail.created_at >= '{$params['start_date']}' && lead_detail.created_at <= '{$params['end_date']}'");
        }


        if (!is_null($params['is_retired']) && $params['is_retired'] == 1) {
            $query->where('lead_detail.is_expired', $params['is_retired']);
        } elseif (!is_null($params['is_retired']) && $params['is_retired'] == 2) {
            $query->where('lead_detail.is_expired', 0);
        }

        if (!empty($params['lead_type_id'])) {
            $query->whereRaw('lead_detail.type_id  IN (' . $params['lead_type_id'] . ')');
        }
        if (!isset($params['order_by'])) {
            $params['order_by'] = 'id';
            $params['order_type'] = 'desc';
        }

        $order_by = 'lead_detail.' . $params['order_by'];

        if ($params['order_by'] == 'Lead Type') {
            $order_by = 'type.title';
        }

        if ($params['order_by'] == 'updated_by') {
            $order_by = 'lead_detail.updated_by';
        }
        if ($params['order_by'] == 'created_by') {
            $order_by = 'lead_detail.created_by';
        }
        if ($params['order_by'] == 'Address') {
            $order_by = 'lead_detail.address';
        }
        if ($params['order_by'] == 'City') {
            $order_by = 'lead_detail.city';
        }
        if ($params['order_by'] == 'County') {
            $order_by = 'lead_detail.county';
        }

        if (strtolower($params['order_by']) == 'first_name' || strtolower($params['order_by']) == 'last_name') {
            $order_by = 'lead_detail.owner';
        }

        if (strtolower($params['order_by']) == 'first_name' || strtolower($params['order_by']) == 'last_name') {
            $order_by = 'lead_detail.owner';
        }

        if (strtolower($params['order_by']) == 'lead_name' || strtolower($params['order_by']) == 'homeowner name') {
            $order_by = 'lead_detail.title';
        }

        if (strtolower($params['order_by']) == 'admin notes' OR $params['order_by'] == 'Admin Notes') {
            $order_by = 'lead_detail.admin_notes';
        }

        if (strtolower($params['order_by']) == 'auction') {
            $order_by = 'lead_detail.auction_date';
        }

        if (strtolower($params['order_by']) == 'lead_value' OR $params['order_by'] == 'Lead Value') {
            $order_by = 'lead_detail.lead_value';
        }

        if (strtolower($params['order_by']) == 'original_loan' OR $params['order_by'] == 'Original Loan') {
            $order_by = 'lead_detail.original_loan_2';
        }

        if (strtolower($params['order_by']) == 'loan date') {
            $order_by = 'lead_detail.loan_date';
        }

        if (strtolower($params['order_by']) == 'sq_ft' OR $params['order_by'] == 'Sq Ft') {
            $order_by = 'lead_detail.sq_ft_2';
        }

        if ($params['order_by'] == 'Zip') {
            $order_by = 'lead_detail.zip_code';
        }

        if (strtolower($params['order_by']) == 'yr_blt' OR $params['order_by'] == 'Yr Blt') {
            $order_by = 'lead_detail.yr_blt';
        }

        if (strtolower($params['order_by']) == 'yr_blt' OR $params['order_by'] == 'Yr Blt') {
            $order_by = 'lead_detail.yr_blt';
        }

        if (strtolower($params['order_by']) == 'mortgagee' OR $params['order_by'] == 'Mortgagee') {
            $order_by = 'lead_detail.mortgagee';
        }

        if (strtolower($params['order_by']) == 'source' OR $params['order_by'] == 'Source') {
            $order_by = 'lead_detail.source';
        }

        if (strtolower($params['order_by']) == 'trustee' OR $params['order_by'] == 'Trustee') {
            $order_by = 'lead_detail.trustee';
        }

        if (strtolower($params['order_by']) == 'Loan Type' OR $params['order_by'] == 'loan_type' OR $params['order_by'] == 'Loan Type') {
            $order_by = 'lead_detail.loan_type';
        }

        if (strtolower($params['order_by']) == 'loan_mod' OR $params['order_by'] == 'Loan Mod') {
            $order_by = 'lead_detail.loan_mod';
        }

        if (strtolower($params['order_by']) == 'Owner Address - If Not Owner Occupied' OR $params['order_by'] == 'Owner Address' OR $params['order_by'] == 'owner_address' OR strtolower($params['order_by']) == 'owner address - if not owner occupied') {
            $order_by = 'lead_detail.owner_address';
        }

        if (strtolower($params['order_by']) == 'assigned_to') {
            $order_by = 'user.first_name';
        }

        if (strtolower($params['order_by']) == 'lead_status' OR $params['order_by'] == 'Lead Status') {
            $order_by = 'status.title';
        }

        if (strtolower($params['order_by']) == 'Notes' OR $params['order_by'] == 'Notes') {
            $order_by = 'lead_query.response';
        }

        if (strtolower($params['order_by']) == 'Notes' OR $params['order_by'] == 'Notes') {
            $order_by = 'lead_query.response';
        }

        if (strtolower($params['order_by']) == 'assigned to' OR $params['order_by'] == 'Assigned To') {
            $order_by = 'user.first_name';
        }



        $query->orderBy($order_by, $params['order_type']);

        if (isset($params['search']) && !empty($params['search'])) {
            $query->where(function($querysub) use($params) {
                $querysub->orwhere('lead_detail.zip_code', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.formatted_address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.county', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.state', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.city', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.title', 'like', '%' . trim($params['search']) . '%');
            });
        }


        $time_clause = '';
        $group_by__clause = '';
        if (!empty($params['time_slot'])) {
            $time_clauses['today'] = " DATE(lead_detail.created_at) = DATE(NOW())";
            $time_clauses['yesterday'] = " DATE(lead_detail.created_at) = DATE(NOW() - INTERVAL 1 DAY)";
            $time_clauses['week'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 7 DAY";
            $time_clauses['last_week'] = " YEARWEEK(lead_detail.created_at) = YEARWEEK(NOW() - INTERVAL 1 WEEK)";
            $time_clauses['month'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 1 MONTH";
            $time_clauses['last_month'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 MONTH)  AND month(lead_detail.created_at) = Month(NOW() - INTERVAL 1 MONTH) ";
            $time_clauses['year'] = " lead_detail.created_at > DATE_SUB(NOW(),INTERVAL 1 YEAR)";
            $time_clauses['last_year'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 YEAR)";

            $group_by_clauses['today'] = " hour, day, month, year";
            $group_by_clauses['yesterday'] = " hour, day, month, year";
            $group_by_clauses['week'] = " day, month, year";
            $group_by_clauses['last_week'] = " day, month, year";
            $group_by_clauses['month'] = " day, month, year";
            $group_by_clauses['last_month'] = " day, month, year";
            $group_by_clauses['year'] = " month, year";
            $group_by_clauses['last_year'] = " month, year";

            $slot_types['today'] = " hour";
            $slot_types['yesterday'] = " hour";
            $slot_types['week'] = " day";
            $slot_types['last_week'] = " day";
            $slot_types['month'] = " day";
            $slot_types['last_month'] = " day";
            $slot_types['year'] = " month";
            $slot_types['last_year'] = " month";

            $time_clause = $time_clauses[$params['slot']];
            $group_by__clause = $group_by_clauses[$params['slot']];

            $query->whereRaw("$time_clause");
        }

        if ((isset($params['is_status_group_by']) && $params['is_status_group_by'])) {
            $query->select('lead_detail.*', \DB::raw('count(lead_detail.id) as lead_count'));
            $query->groupBy('lead_detail.status_id');

            return $query->get();
        }

        if ((isset($params['is_web']) && $params['is_web'] == 0) || array_key_exists("export", $params) && $params['export'] === 'true') {
            return $query->get();
        }

        $DisplayedPerScreen = settingForRecordsDisplayedPerScreen();
        if (isset($DisplayedPerScreen->lead_management)) {
            $result = $query->groupBy('lead_detail.id')->paginate($DisplayedPerScreen->lead_management);
        } else {
            $result = $query->groupBy('lead_detail.id')->paginate(Config::get('constants.PAGINATION_PAGE_SIZE'));
        }


        return $result;
    }

    public static function getListIndexNew($params) {
        if (!empty($params['auction_start_date']) && !empty($params['auction_end_date'])) {
            $query = self::select('lead_detail.*', 'type.title as type_title', 'status.title as status_title', 'lead_custom_field.value as auction_date_value', DB::raw('(SELECT COUNT(*) FROM user_lead_knocks WHERE lead_id = lead_detail.id) as visit_knocks'));
            $query->leftJoin('lead_custom_field', 'lead_detail.id', '=', 'lead_custom_field.lead_id');
        } else {
            $query = self::select(
                            'lead_detail.id as id',
                            'lead_detail.title as title',
                            'lead_detail.address as address',
                            'lead_detail.created_at as created_at',
                            'lead_detail.city as city',
                            'lead_detail.status_id as status_id',
                            'lead_detail.type_id as type_id',
                            'lead_detail.zip_code as zip_code',
                            'lead_detail.latitude as latitude',
                            'lead_detail.longitude as longitude',
                            'type.title as type_title',
                            'status.title as status_title',
                            DB::raw('(SELECT COUNT(*) FROM user_lead_knocks WHERE lead_id = lead_detail.id) as visit_knocks')
            );
        }
        $query->leftJoin('status', 'lead_detail.status_id', '=', 'status.id');
        $query->leftJoin('type', 'lead_detail.type_id', '=', 'type.id');
        $query->where('lead_detail.is_follow_up', 0);
        $query->where('lead_detail.company_id', $params['company_id']);
        $query->whereNull('lead_detail.deleted_at');

        if (isset($params['user_ids']) && !empty($params['user_ids']))
            $query->whereRaw('lead_detail.assignee_id  IN (' . $params['user_ids'] . ')');

        if (isset($params['status_ids']) && !empty($params['status_ids']))
            $query->whereRaw('lead_detail.status_id  IN (' . $params['status_ids'] . ')');

        if (!isset($params['is_web']) || $params['is_web'] == 0) {
            $query->where('lead_detail.is_expired', 0);
        }

        if (isset($params['auction_start_date']) && isset($params['auction_end_date']) && !empty($params['auction_start_date']) && !empty($params['auction_end_date'])) {
            $startDate = dateChangeDbFromate($params['auction_start_date']);
            $endDate = dateChangeDbFromate($params['auction_end_date']);
            $query->whereBetween('lead_detail.auction_date', [$startDate, $endDate]);
            $query->orwhere('lead_custom_field.tenant_custom_field_id', 157);
        }
        if (!empty($params['start_date']) && !empty($params['end_date'])) {
            $params['start_date'] = dateTimezoneChangeNew($params['start_date'] . ' 00:00:00');
            $params['end_date'] = dateTimezoneChangeNew($params['end_date'] . ' 23:59:59');

            $params['start_date'] = date('Y-m-d H:i:s', strtotime($params['start_date']));
            $params['end_date'] = date('Y-m-d H:i:s', strtotime($params['end_date']));
            $query->whereRaw("lead_detail.created_at >= '{$params['start_date']}' && lead_detail.created_at <= '{$params['end_date']}'");
        }


        if (!is_null($params['is_retired']) && $params['is_retired'] == 1) {
            $query->where('lead_detail.is_expired', $params['is_retired']);
        } elseif (!is_null($params['is_retired']) && $params['is_retired'] == 2) {
            $query->where('lead_detail.is_expired', 0);
        }

        if (!empty($params['lead_type_id'])) {
            $query->whereRaw('lead_detail.type_id  IN (' . $params['lead_type_id'] . ')');
        }
        if (!isset($params['order_by'])) {
            $params['order_by'] = 'id';
            $params['order_type'] = 'desc';
        }

        $order_by = 'lead_detail.' . $params['order_by'];

        $query->orderBy($order_by, $params['order_type']);

        if (isset($params['search']) && !empty($params['search'])) {
            $query->where(function($querysub) use($params) {
                $querysub->orwhere('lead_detail.zip_code', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.formatted_address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.county', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.state', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.city', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.title', 'like', '%' . trim($params['search']) . '%');
            });
        }


        $time_clause = '';
        $group_by__clause = '';
        if (!empty($params['time_slot'])) {
            $time_clauses['today'] = " DATE(lead_detail.created_at) = DATE(NOW())";
            $time_clauses['yesterday'] = " DATE(lead_detail.created_at) = DATE(NOW() - INTERVAL 1 DAY)";
            $time_clauses['week'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 7 DAY";
            $time_clauses['last_week'] = " YEARWEEK(lead_detail.created_at) = YEARWEEK(NOW() - INTERVAL 1 WEEK)";
            $time_clauses['month'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 1 MONTH";
            $time_clauses['last_month'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 MONTH)  AND month(lead_detail.created_at) = Month(NOW() - INTERVAL 1 MONTH) ";
            $time_clauses['year'] = " lead_detail.created_at > DATE_SUB(NOW(),INTERVAL 1 YEAR)";
            $time_clauses['last_year'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 YEAR)";

            $group_by_clauses['today'] = " hour, day, month, year";
            $group_by_clauses['yesterday'] = " hour, day, month, year";
            $group_by_clauses['week'] = " day, month, year";
            $group_by_clauses['last_week'] = " day, month, year";
            $group_by_clauses['month'] = " day, month, year";
            $group_by_clauses['last_month'] = " day, month, year";
            $group_by_clauses['year'] = " month, year";
            $group_by_clauses['last_year'] = " month, year";

            $slot_types['today'] = " hour";
            $slot_types['yesterday'] = " hour";
            $slot_types['week'] = " day";
            $slot_types['last_week'] = " day";
            $slot_types['month'] = " day";
            $slot_types['last_month'] = " day";
            $slot_types['year'] = " month";
            $slot_types['last_year'] = " month";

            $time_clause = $time_clauses[$params['slot']];
            $group_by__clause = $group_by_clauses[$params['slot']];

            $query->whereRaw("$time_clause");
        }

        if ((isset($params['is_status_group_by']) && $params['is_status_group_by'])) {
            $query->select('lead_detail.*', \DB::raw('count(lead_detail.id) as lead_count'));
            $query->groupBy('lead_detail.status_id');

            return $query->get();
        }

        if ((isset($params['is_web']) && $params['is_web'] == 0) || array_key_exists("export", $params) && $params['export'] === 'true') {
            return $query->get();
        }

        $DisplayedPerScreen = settingForRecordsDisplayedPerScreen();
        if (isset($DisplayedPerScreen->lead_management)) {
            $result = $query->groupBy('lead_detail.id')->paginate($DisplayedPerScreen->lead_management);
        } else {
            $result = $query->groupBy('lead_detail.id')->paginate(Config::get('constants.PAGINATION_PAGE_SIZE'));
        }


        return $result;
    }

    public static function getListIndexV2($params) {
        if (!empty($params['auction_start_date']) && !empty($params['auction_end_date'])) {
            $query = self::select('lead_detail.*', 'type.title as type_title', 'status.title as status_title', 'user.first_name as assignee_first', 'user.last_name as assignee_last', 'lead_custom_field.value as auction_date_value');
            $query->leftJoin('lead_custom_field', 'lead_detail.id', '=', 'lead_custom_field.lead_id');
        } else {
            $query = self::select('lead_detail.*', 'type.title as type_title', 'status.title as status_title', 'user.first_name as assignee_first', 'user.last_name as assignee_last');
        }
        $query->leftJoin('status', 'lead_detail.status_id', '=', 'status.id');
        $query->leftJoin('type', 'lead_detail.type_id', '=', 'type.id');
        $query->leftJoin('user', 'lead_detail.assignee_id', '=', 'user.id');
        $query->where('lead_detail.is_follow_up', 0);
        $query->where('lead_detail.company_id', $params['company_id']);
        $query->whereNull('lead_detail.deleted_at');

        if (isset($params['user_ids']) && !empty($params['user_ids']))
            $query->whereRaw('lead_detail.assignee_id  IN (' . $params['user_ids'] . ')');

        if (isset($params['status_ids']) && !empty($params['status_ids']))
            $query->whereRaw('lead_detail.status_id  IN (' . $params['status_ids'] . ')');

        if (!isset($params['is_web']) || $params['is_web'] == 0) {
            $query->where('lead_detail.is_expired', 0);
        }

        if (isset($params['auction_start_date']) && isset($params['auction_end_date']) && !empty($params['auction_start_date']) && !empty($params['auction_end_date'])) {
            $startDate = dateChangeDbFromate($params['auction_start_date']);
            $endDate = dateChangeDbFromate($params['auction_end_date']);
            $query->whereBetween('lead_detail.auction_date', [$startDate, $endDate]);
            $query->orwhere('lead_custom_field.tenant_custom_field_id', 157);
        }
        if (!empty($params['start_date']) && !empty($params['end_date'])) {
            $params['start_date'] = dateTimezoneChangeNew($params['start_date'] . ' 00:00:00');
            $params['end_date'] = dateTimezoneChangeNew($params['end_date'] . ' 23:59:59');

            $params['start_date'] = date('Y-m-d H:i:s', strtotime($params['start_date']));
            $params['end_date'] = date('Y-m-d H:i:s', strtotime($params['end_date']));
            $query->whereRaw("lead_detail.created_at >= '{$params['start_date']}' && lead_detail.created_at <= '{$params['end_date']}'");
        }


        if (!is_null($params['is_retired']) && $params['is_retired'] == 1) {
            $query->where('lead_detail.is_expired', $params['is_retired']);
        } elseif (!is_null($params['is_retired']) && $params['is_retired'] == 2) {
            $query->where('lead_detail.is_expired', 0);
        }

        if (!empty($params['lead_type_id'])) {
            $query->whereRaw('lead_detail.type_id  IN (' . $params['lead_type_id'] . ')');
        }
        if (!isset($params['order_by'])) {
            $params['order_by'] = 'id';
            $params['order_type'] = 'desc';
        }

        $order_by = 'lead_detail.' . $params['order_by'];

        $query->orderBy($order_by, $params['order_type']);

        if (isset($params['search']) && !empty($params['search'])) {
            $query->where(function($querysub) use($params) {
                $querysub->orwhere('lead_detail.zip_code', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.formatted_address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.county', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.state', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.city', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.title', 'like', '%' . trim($params['search']) . '%');
            });
        }


        $time_clause = '';
        $group_by__clause = '';
        if (!empty($params['time_slot'])) {
            $time_clauses['today'] = " DATE(lead_detail.created_at) = DATE(NOW())";
            $time_clauses['yesterday'] = " DATE(lead_detail.created_at) = DATE(NOW() - INTERVAL 1 DAY)";
            $time_clauses['week'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 7 DAY";
            $time_clauses['last_week'] = " YEARWEEK(lead_detail.created_at) = YEARWEEK(NOW() - INTERVAL 1 WEEK)";
            $time_clauses['month'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 1 MONTH";
            $time_clauses['last_month'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 MONTH)  AND month(lead_detail.created_at) = Month(NOW() - INTERVAL 1 MONTH) ";
            $time_clauses['year'] = " lead_detail.created_at > DATE_SUB(NOW(),INTERVAL 1 YEAR)";
            $time_clauses['last_year'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 YEAR)";

            $group_by_clauses['today'] = " hour, day, month, year";
            $group_by_clauses['yesterday'] = " hour, day, month, year";
            $group_by_clauses['week'] = " day, month, year";
            $group_by_clauses['last_week'] = " day, month, year";
            $group_by_clauses['month'] = " day, month, year";
            $group_by_clauses['last_month'] = " day, month, year";
            $group_by_clauses['year'] = " month, year";
            $group_by_clauses['last_year'] = " month, year";

            $slot_types['today'] = " hour";
            $slot_types['yesterday'] = " hour";
            $slot_types['week'] = " day";
            $slot_types['last_week'] = " day";
            $slot_types['month'] = " day";
            $slot_types['last_month'] = " day";
            $slot_types['year'] = " month";
            $slot_types['last_year'] = " month";

            $time_clause = $time_clauses[$params['slot']];
            $group_by__clause = $group_by_clauses[$params['slot']];

            $query->whereRaw("$time_clause");
        }

        if ((isset($params['is_status_group_by']) && $params['is_status_group_by'])) {
            $query->select('lead_detail.*', \DB::raw('count(lead_detail.id) as lead_count'));
            $query->groupBy('lead_detail.status_id');

            return $query->get();
        }

        if ((isset($params['is_web']) && $params['is_web'] == 0) || array_key_exists("export", $params) && $params['export'] === 'true') {
            return $query->get();
        }

        $DisplayedPerScreen = settingForRecordsDisplayedPerScreen();
        if (isset($DisplayedPerScreen->lead_management)) {
            $result = $query->groupBy('lead_detail.id')->paginate($DisplayedPerScreen->lead_management);
        } else {
            $result = $query->groupBy('lead_detail.id')->paginate(Config::get('constants.PAGINATION_PAGE_SIZE'));
        }


        return $result;
    }

    public static function getListApiIndex($params) {
        if (!empty($params['latitude']) && !empty($params['longitude'])) {
            $lat = $params['latitude'];
            $lng = $params['longitude'];
            $radius = $params['radius'];

            $haversine = "(3959 * acos (
                    cos ( radians($lat) )
                    * cos( radians(`latitude`) )
                    * cos( radians(`longitude`) - radians($lng) )
                    + sin ( radians($lat) )
                    * sin( radians(`latitude`) )
                ))";
        }
        if (!empty($params['auction_start_date']) && !empty($params['auction_end_date'])) {
            $query = self::select('lead_detail.*', 'type.title as type_title', 'status.title as status_title', 'lead_query.response as Notes_Add_to_Top_Include_Date_Your_Name_and_Notes', 'user.first_name as assignee_first', 'user.last_name as assignee_last', 'lead_custom_field.value as auction_date_value');
            $query->leftJoin('lead_custom_field', 'lead_detail.id', '=', 'lead_custom_field.lead_id');
        } else {
            $query = self::select('lead_detail.*', 'type.title as type_title', 'status.title as status_title', 'lead_query.response as Notes_Add_to_Top_Include_Date_Your_Name_and_Notes', 'user.first_name as assignee_first', 'user.last_name as assignee_last');
        }
        $query->with('leadCustom', 'tenantquery');
        $query->leftJoin('lead_query', 'lead_detail.id', '=', 'lead_query.lead_id');
        $query->leftJoin('status', 'lead_detail.status_id', '=', 'status.id');
        $query->leftJoin('type', 'lead_detail.type_id', '=', 'type.id');
        $query->leftJoin('user', 'lead_detail.assignee_id', '=', 'user.id');
        $query->where('lead_query.query_id', 8);
        $query->where('lead_detail.is_follow_up', 0);
        $query->where('lead_detail.company_id', $params['company_id']);
        $query->whereNull('lead_detail.deleted_at');
        if (isset($params['user_ids']) && !empty($params['user_ids']))
            $query->whereRaw('lead_detail.assignee_id  IN (' . $params['user_ids'] . ')');

        if (isset($params['status_ids']) && !empty($params['status_ids']))
            $query->whereRaw('lead_detail.status_id  IN (' . $params['status_ids'] . ')');

        if (!isset($params['is_web']) || $params['is_web'] == 0) {
            $query->where('lead_detail.is_expired', 0);
        }

        updateLeadAuctionDate();
        storeDateAuctionDateFormat();
        if (isset($params['auction_start_date']) && isset($params['auction_end_date']) && !empty($params['auction_start_date']) && !empty($params['auction_end_date'])) {
            $startDate = dateChangeDbFromate($params['auction_start_date']);
            $endDate = dateChangeDbFromate($params['auction_end_date']);
            $query->whereBetween('lead_detail.auction_date', [$startDate, $endDate]);
            $query->orwhere('lead_custom_field.tenant_custom_field_id', 157);
        }
        if (!empty($params['start_date']) && !empty($params['end_date'])) {

            $params['start_date'] = dateTimezoneChangeNew($params['start_date'] . ' 00:00:00');
            $params['end_date'] = dateTimezoneChangeNew($params['end_date'] . ' 23:59:59');

            $params['start_date'] = date('Y-m-d H:i:s', strtotime($params['start_date']));
            $params['end_date'] = date('Y-m-d H:i:s', strtotime($params['end_date']));

            $query->whereRaw("lead_detail.created_at >= '{$params['start_date']}' && lead_detail.created_at <= '{$params['end_date']}'");
        }
        if (!is_null($params['is_retired']) && $params['is_retired'] == 1) {
            $query->where('lead_detail.is_expired', $params['is_retired']);
        } elseif (!is_null($params['is_retired']) && $params['is_retired'] == 2) {
            $query->where('lead_detail.is_expired', 0);
        }
        if (!empty($params['lead_type_id'])) {
            $query->whereRaw('lead_detail.type_id  IN (' . $params['lead_type_id'] . ')');
        }
        if (!isset($params['order_by'])) {
            $params['order_by'] = 'id';
            $params['order_type'] = 'desc';
        }
        $order_by = 'lead_detail.' . $params['order_by'];
        if ($params['order_by'] == 'Lead Type') {
            $order_by = 'type.title';
        }
        if ($params['order_by'] == 'updated_by') {
            $order_by = 'lead_detail.updated_by';
        }
        if ($params['order_by'] == 'created_by') {
            $order_by = 'lead_detail.created_by';
        }
        if ($params['order_by'] == 'Address') {
            $order_by = 'lead_detail.address';
        }
        if ($params['order_by'] == 'City') {
            $order_by = 'lead_detail.city';
        }
        if ($params['order_by'] == 'County') {
            $order_by = 'lead_detail.county';
        }
        if (strtolower($params['order_by']) == 'first_name' || strtolower($params['order_by']) == 'last_name') {
            $order_by = 'lead_detail.owner';
        }
        if (strtolower($params['order_by']) == 'first_name' || strtolower($params['order_by']) == 'last_name') {
            $order_by = 'lead_detail.owner';
        }
        if (strtolower($params['order_by']) == 'lead_name' || strtolower($params['order_by']) == 'homeowner name') {
            $order_by = 'lead_detail.title';
        }
        if (strtolower($params['order_by']) == 'admin notes' OR $params['order_by'] == 'Admin Notes') {
            $order_by = 'lead_detail.admin_notes';
        }
        if (strtolower($params['order_by']) == 'auction') {
            $order_by = 'lead_detail.auction_date';
        }
        if (strtolower($params['order_by']) == 'lead_value' OR $params['order_by'] == 'Lead Value') {
            $order_by = 'lead_detail.lead_value';
        }
        if (strtolower($params['order_by']) == 'original_loan' OR $params['order_by'] == 'Original Loan') {
            $order_by = 'lead_detail.original_loan_2';
        }
        if (strtolower($params['order_by']) == 'loan date') {
            $order_by = 'lead_detail.loan_date';
        }
        if (strtolower($params['order_by']) == 'sq_ft' OR $params['order_by'] == 'Sq Ft') {
            $order_by = 'lead_detail.sq_ft_2';
        }
        if ($params['order_by'] == 'Zip') {
            $order_by = 'lead_detail.zip_code';
        }
        if (strtolower($params['order_by']) == 'yr_blt' OR $params['order_by'] == 'Yr Blt') {
            $order_by = 'lead_detail.yr_blt';
        }
        if (strtolower($params['order_by']) == 'yr_blt' OR $params['order_by'] == 'Yr Blt') {
            $order_by = 'lead_detail.yr_blt';
        }
        if (strtolower($params['order_by']) == 'mortgagee' OR $params['order_by'] == 'Mortgagee') {
            $order_by = 'lead_detail.mortgagee';
        }
        if (strtolower($params['order_by']) == 'source' OR $params['order_by'] == 'Source') {
            $order_by = 'lead_detail.source';
        }
        if (strtolower($params['order_by']) == 'trustee' OR $params['order_by'] == 'Trustee') {
            $order_by = 'lead_detail.trustee';
        }
        if (strtolower($params['order_by']) == 'Loan Type' OR $params['order_by'] == 'loan_type' OR $params['order_by'] == 'Loan Type') {
            $order_by = 'lead_detail.loan_type';
        }
        if (strtolower($params['order_by']) == 'loan_mod' OR $params['order_by'] == 'Loan Mod') {
            $order_by = 'lead_detail.loan_mod';
        }
        if (strtolower($params['order_by']) == 'Owner Address - If Not Owner Occupied' OR $params['order_by'] == 'Owner Address' OR $params['order_by'] == 'owner_address' OR strtolower($params['order_by']) == 'owner address - if not owner occupied') {
            $order_by = 'lead_detail.owner_address';
        }
        if (strtolower($params['order_by']) == 'assigned_to') {
            $order_by = 'user.first_name';
        }
        if (strtolower($params['order_by']) == 'lead_status' OR $params['order_by'] == 'Lead Status') {
            $order_by = 'status.title';
        }
        if (strtolower($params['order_by']) == 'Notes' OR $params['order_by'] == 'Notes') {
            $order_by = 'lead_query.response';
        }
        if (strtolower($params['order_by']) == 'Notes' OR $params['order_by'] == 'Notes') {
            $order_by = 'lead_query.response';
        }
        if (strtolower($params['order_by']) == 'assigned to' OR $params['order_by'] == 'Assigned To') {
            $order_by = 'user.first_name';
        }
        $query->orderBy($order_by, $params['order_type']);
        if (isset($params['search']) && !empty($params['search'])) {
            $query->where(function($querysub) use($params) {
                $querysub->orwhere('lead_detail.zip_code', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.formatted_address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.county', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.state', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.city', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.title', 'like', '%' . trim($params['search']) . '%');
            });
        }
        $time_clause = '';
        $group_by__clause = '';
        if (!empty($params['time_slot'])) {
            $time_clauses['today'] = " DATE(lead_detail.created_at) = DATE(NOW())";
            $time_clauses['yesterday'] = " DATE(lead_detail.created_at) = DATE(NOW() - INTERVAL 1 DAY)";
            $time_clauses['week'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 7 DAY";
            $time_clauses['last_week'] = " YEARWEEK(lead_detail.created_at) = YEARWEEK(NOW() - INTERVAL 1 WEEK)";
            $time_clauses['month'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 1 MONTH";
            $time_clauses['last_month'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 MONTH)  AND month(lead_detail.created_at) = Month(NOW() - INTERVAL 1 MONTH) ";
            $time_clauses['year'] = " lead_detail.created_at > DATE_SUB(NOW(),INTERVAL 1 YEAR)";
            $time_clauses['last_year'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 YEAR)";
            $group_by_clauses['today'] = " hour, day, month, year";
            $group_by_clauses['yesterday'] = " hour, day, month, year";
            $group_by_clauses['week'] = " day, month, year";
            $group_by_clauses['last_week'] = " day, month, year";
            $group_by_clauses['month'] = " day, month, year";
            $group_by_clauses['last_month'] = " day, month, year";
            $group_by_clauses['year'] = " month, year";
            $group_by_clauses['last_year'] = " month, year";
            $slot_types['today'] = " hour";
            $slot_types['yesterday'] = " hour";
            $slot_types['week'] = " day";
            $slot_types['last_week'] = " day";
            $slot_types['month'] = " day";
            $slot_types['last_month'] = " day";
            $slot_types['year'] = " month";
            $slot_types['last_year'] = " month";
            $time_clause = $time_clauses[$params['slot']];
            $group_by__clause = $group_by_clauses[$params['slot']];

            $query->whereRaw("$time_clause");
        }
        if ((isset($params['is_status_group_by']) && $params['is_status_group_by'])) {
            $query->select('lead_detail.*', \DB::raw('count(lead_detail.id) as lead_count'));
            $query->groupBy('lead_detail.status_id');
            return $query->get();
        }
        if ((isset($params['is_web']) && $params['is_web'] == 0) || array_key_exists("export", $params) && $params['export'] === 'true') {
            return $query->get();
        }
        $result = $query->groupBy('lead_detail.id')->paginate(Config::get('constants.EXPORT_PAGE_SIZE'));
        return $result;
    }

    public static function getListApiIndexNew($params) {
        updateLeadAuctionDateNew();
        $query = self::select('lead_detail.state as state', 'lead_detail.city as city', 'lead_detail.zip_code as zip_code', 'lead_detail.title as title', 'lead_detail.address as address', 'lead_detail.id as id', 'lead_detail.status_id as status_id', 'lead_detail.type_id as type_id', 'lead_detail.latitude as latitude', 'lead_detail.longitude as longitude', 'type.title as type_title', 'status.title as status_title');
        $query->leftJoin('status', 'lead_detail.status_id', '=', 'status.id');
        $query->leftJoin('type', 'lead_detail.type_id', '=', 'type.id');
        $query->where('lead_detail.is_follow_up', 0);
        $query->where('lead_detail.company_id', $params['company_id']);
        $query->whereNull('lead_detail.deleted_at');
        if (!isset($params['is_web']) || $params['is_web'] == 0) {
            $query->where('lead_detail.is_expired', 0);
        }
        $query->where('lead_detail.is_expired', 0);
        $query->orderBy('lead_detail.id', 'desc');
        $result = $query->groupBy('lead_detail.id')->paginate(Config::get('constants.EXPORT_PAGE_SIZE'));
        return $result;
    }

    public static function getListMapIndexV2($params) {
        updateLeadAuctionDateNew();
        $query = self::select('lead_detail.*', 'type.title as type_title', 'status.title as status_title', 'user.first_name as assignee_first', 'user.last_name as assignee_last');
        $query->leftJoin('status', 'lead_detail.status_id', '=', 'status.id');
        $query->leftJoin('type', 'lead_detail.type_id', '=', 'type.id');
        $query->leftJoin('user', 'lead_detail.assignee_id', '=', 'user.id');
        $query->where('lead_detail.is_follow_up', 0);
        $query->where('lead_detail.company_id', $params['company_id']);
        $query->whereNull('lead_detail.deleted_at');
        if (!isset($params['is_web']) || $params['is_web'] == 0) {
            $query->where('lead_detail.is_expired', 0);
        }
        $query->where('lead_detail.is_expired', 0);
        $query->orderBy('lead_detail.id', 'desc');
        $result = $query->groupBy('lead_detail.id')->paginate(Config::get('constants.EXPORT_PAGE_SIZE'));
        return $result;
    }

    public static function getListIndexExport($params) {
        if (!empty($params['latitude']) && !empty($params['longitude'])) {
            $lat = $params['latitude'];
            $lng = $params['longitude'];
            $radius = $params['radius'];
            $haversine = "(3959 * acos (
                    cos ( radians($lat) )
                    * cos( radians(`latitude`) )
                    * cos( radians(`longitude`) - radians($lng) )
                    + sin ( radians($lat) )
                    * sin( radians(`latitude`) )
                ))";
        }

        if (!empty($params['auction_start_date']) && !empty($params['auction_end_date'])) {
            $query = self::select('lead_detail.*', 'type.title as type_title', 'status.title as status_title', 'lead_query.response as Notes_Add_to_Top_Include_Date_Your_Name_and_Notes', 'user.first_name as assignee_first', 'user.last_name as assignee_last', 'lead_custom_field.value as auction_date_value');
            $query->leftJoin('lead_custom_field', 'lead_detail.id', '=', 'lead_custom_field.lead_id');
        } else {
            $query = self::select('lead_detail.*', 'type.title as type_title', 'status.title as status_title', 'lead_query.response as Notes_Add_to_Top_Include_Date_Your_Name_and_Notes', 'user.first_name as assignee_first', 'user.last_name as assignee_last');
        }

        $query->with('leadCustom', 'tenantquery');
        $query->leftJoin('lead_query', 'lead_detail.id', '=', 'lead_query.lead_id');
        $query->leftJoin('status', 'lead_detail.status_id', '=', 'status.id');
        $query->leftJoin('type', 'lead_detail.type_id', '=', 'type.id');
        $query->leftJoin('user', 'lead_detail.assignee_id', '=', 'user.id');
        $query->where('lead_query.query_id', 8);
        $query->where('lead_detail.is_follow_up', 0);
        $query->where('lead_detail.company_id', $params['company_id']);
        $query->whereNull('lead_detail.deleted_at');
        if (isset($params['user_ids']) && !empty($params['user_ids']))
            $query->whereRaw('lead_detail.assignee_id  IN (' . $params['user_ids'] . ')');

        if (isset($params['status_ids']) && !empty($params['status_ids']))
            $query->whereRaw('lead_detail.status_id  IN (' . $params['status_ids'] . ')');

        if (!isset($params['is_web']) || $params['is_web'] == 0) {
            
        }
        updateLeadAuctionDate();
        storeDateAuctionDateFormat();
        if (isset($params['auction_start_date']) && isset($params['auction_end_date']) && !empty($params['auction_start_date']) && !empty($params['auction_end_date'])) {
            $startDate = dateChangeDbFromate($params['auction_start_date']);
            $endDate = dateChangeDbFromate($params['auction_end_date']);
            $query->whereBetween('lead_detail.auction_date', [$startDate, $endDate]);
            $query->orwhere('lead_custom_field.tenant_custom_field_id', 157);
        }
        if (!empty($params['start_date']) && !empty($params['end_date'])) {
            $params['start_date'] = dateTimezoneChangeNew($params['start_date'] . ' 00:00:00');
            $params['end_date'] = dateTimezoneChangeNew($params['end_date'] . ' 23:59:59');

            $params['start_date'] = date('Y-m-d H:i:s', strtotime($params['start_date']));
            $params['end_date'] = date('Y-m-d H:i:s', strtotime($params['end_date']));
            $query->whereRaw("lead_detail.created_at >= '{$params['start_date']}' && lead_detail.created_at <= '{$params['end_date']}'");
        }
        if (!is_null($params['is_retired']) && $params['is_retired'] == 1) {
            $query->where('lead_detail.is_expired', $params['is_retired']);
        } elseif (!is_null($params['is_retired']) && $params['is_retired'] == 2) {
            $query->where('lead_detail.is_expired', 0);
        }
        if (!empty($params['lead_type_id'])) {
            $query->whereRaw('lead_detail.type_id  IN (' . $params['lead_type_id'] . ')');
        }
        if (!isset($params['order_by'])) {
            $params['order_by'] = 'id';
            $params['order_type'] = 'desc';
        }
        $order_by = 'lead_detail.' . $params['order_by'];
        if ($params['order_by'] == 'Lead Type') {
            $order_by = 'type.title';
        }
        if ($params['order_by'] == 'updated_by') {
            $order_by = 'lead_detail.updated_by';
        }
        if ($params['order_by'] == 'created_by') {
            $order_by = 'lead_detail.created_by';
        }
        if ($params['order_by'] == 'Address') {
            $order_by = 'lead_detail.address';
        }
        if ($params['order_by'] == 'City') {
            $order_by = 'lead_detail.city';
        }
        if ($params['order_by'] == 'County') {
            $order_by = 'lead_detail.county';
        }
        if (strtolower($params['order_by']) == 'first_name' || strtolower($params['order_by']) == 'last_name') {
            $order_by = 'lead_detail.owner';
        }
        if (strtolower($params['order_by']) == 'first_name' || strtolower($params['order_by']) == 'last_name') {
            $order_by = 'lead_detail.owner';
        }
        if (strtolower($params['order_by']) == 'lead_name' || strtolower($params['order_by']) == 'homeowner name') {
            $order_by = 'lead_detail.title';
        }
        if (strtolower($params['order_by']) == 'admin notes' OR $params['order_by'] == 'Admin Notes') {
            $order_by = 'lead_detail.admin_notes';
        }
        if (strtolower($params['order_by']) == 'auction') {
            $order_by = 'lead_detail.auction_date';
        }
        if (strtolower($params['order_by']) == 'lead_value' OR $params['order_by'] == 'Lead Value') {
            $order_by = 'lead_detail.lead_value';
        }
        if (strtolower($params['order_by']) == 'original_loan' OR $params['order_by'] == 'Original Loan') {
            $order_by = 'lead_detail.original_loan_2';
        }
        if (strtolower($params['order_by']) == 'loan date') {
            $order_by = 'lead_detail.loan_date';
        }
        if (strtolower($params['order_by']) == 'sq_ft' OR $params['order_by'] == 'Sq Ft') {
            $order_by = 'lead_detail.sq_ft_2';
        }
        if ($params['order_by'] == 'Zip') {
            $order_by = 'lead_detail.zip_code';
        }
        if (strtolower($params['order_by']) == 'yr_blt' OR $params['order_by'] == 'Yr Blt') {
            $order_by = 'lead_detail.yr_blt';
        }
        if (strtolower($params['order_by']) == 'yr_blt' OR $params['order_by'] == 'Yr Blt') {
            $order_by = 'lead_detail.yr_blt';
        }
        if (strtolower($params['order_by']) == 'mortgagee' OR $params['order_by'] == 'Mortgagee') {
            $order_by = 'lead_detail.mortgagee';
        }
        if (strtolower($params['order_by']) == 'source' OR $params['order_by'] == 'Source') {
            $order_by = 'lead_detail.source';
        }
        if (strtolower($params['order_by']) == 'trustee' OR $params['order_by'] == 'Trustee') {
            $order_by = 'lead_detail.trustee';
        }
        if (strtolower($params['order_by']) == 'Loan Type' OR $params['order_by'] == 'loan_type' OR $params['order_by'] == 'Loan Type') {
            $order_by = 'lead_detail.loan_type';
        }
        if (strtolower($params['order_by']) == 'loan_mod' OR $params['order_by'] == 'Loan Mod') {
            $order_by = 'lead_detail.loan_mod';
        }
        if (strtolower($params['order_by']) == 'Owner Address - If Not Owner Occupied' OR $params['order_by'] == 'Owner Address' OR $params['order_by'] == 'owner_address' OR strtolower($params['order_by']) == 'owner address - if not owner occupied') {
            $order_by = 'lead_detail.owner_address';
        }
        if (strtolower($params['order_by']) == 'assigned_to') {
            $order_by = 'user.first_name';
        }
        if (strtolower($params['order_by']) == 'lead_status' OR $params['order_by'] == 'Lead Status') {
            $order_by = 'status.title';
        }
        if (strtolower($params['order_by']) == 'Notes' OR $params['order_by'] == 'Notes') {
            $order_by = 'lead_query.response';
        }
        if (strtolower($params['order_by']) == 'Notes' OR $params['order_by'] == 'Notes') {
            $order_by = 'lead_query.response';
        }
        if (strtolower($params['order_by']) == 'assigned to' OR $params['order_by'] == 'Assigned To') {
            $order_by = 'user.first_name';
        }
        $query->orderBy($order_by, $params['order_type']);
        if (isset($params['search']) && !empty($params['search'])) {
            $query->where(function($querysub) use($params) {
                $querysub->orwhere('lead_detail.zip_code', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.formatted_address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.county', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.state', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.city', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.title', 'like', '%' . trim($params['search']) . '%');
            });
        }
        $time_clause = '';
        $group_by__clause = '';
        if (!empty($params['time_slot'])) {
            $time_clauses['today'] = " DATE(lead_detail.created_at) = DATE(NOW())";
            $time_clauses['yesterday'] = " DATE(lead_detail.created_at) = DATE(NOW() - INTERVAL 1 DAY)";
            $time_clauses['week'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 7 DAY";
            $time_clauses['last_week'] = " YEARWEEK(lead_detail.created_at) = YEARWEEK(NOW() - INTERVAL 1 WEEK)";
            $time_clauses['month'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 1 MONTH";
            $time_clauses['last_month'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 MONTH)  AND month(lead_detail.created_at) = Month(NOW() - INTERVAL 1 MONTH) ";
            $time_clauses['year'] = " lead_detail.created_at > DATE_SUB(NOW(),INTERVAL 1 YEAR)";
            $time_clauses['last_year'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 YEAR)";
            $group_by_clauses['today'] = " hour, day, month, year";
            $group_by_clauses['yesterday'] = " hour, day, month, year";
            $group_by_clauses['week'] = " day, month, year";
            $group_by_clauses['last_week'] = " day, month, year";
            $group_by_clauses['month'] = " day, month, year";
            $group_by_clauses['last_month'] = " day, month, year";
            $group_by_clauses['year'] = " month, year";
            $group_by_clauses['last_year'] = " month, year";
            $slot_types['today'] = " hour";
            $slot_types['yesterday'] = " hour";
            $slot_types['week'] = " day";
            $slot_types['last_week'] = " day";
            $slot_types['month'] = " day";
            $slot_types['last_month'] = " day";
            $slot_types['year'] = " month";
            $slot_types['last_year'] = " month";
            $time_clause = $time_clauses[$params['slot']];
            $group_by__clause = $group_by_clauses[$params['slot']];
            $query->whereRaw("$time_clause");
        }
        if ((isset($params['is_status_group_by']) && $params['is_status_group_by'])) {
            $query->select('lead_detail.*', \DB::raw('count(lead_detail.id) as lead_count'));
            $query->groupBy('lead_detail.status_id');
            return $query->get();
        }
        return $query->get();
    }

    public static function getListIndexExportIds($params) {
        if (!empty($params['auction_start_date']) && !empty($params['auction_end_date'])) {
            $query = self::select('lead_detail.*', 'lead_custom_field.value as auction_date_value');
            $query->leftJoin('lead_custom_field', 'lead_detail.id', '=', 'lead_custom_field.lead_id');
        } else {
            $query = self::select('lead_detail.*',);
        }
        $query->where('lead_detail.is_follow_up', 0);
        $query->where('lead_detail.company_id', $params['company_id']);
        $query->whereNull('lead_detail.deleted_at');
        if (isset($params['user_ids']) && !empty($params['user_ids']))
            $query->whereRaw('lead_detail.assignee_id  IN (' . $params['user_ids'] . ')');

        if (isset($params['status_ids']) && !empty($params['status_ids']))
            $query->whereRaw('lead_detail.status_id  IN (' . $params['status_ids'] . ')');

        if (!isset($params['is_web']) || $params['is_web'] == 0) {
            
        }

        if (isset($params['auction_start_date']) && isset($params['auction_end_date']) && !empty($params['auction_start_date']) && !empty($params['auction_end_date'])) {
            $startDate = dateChangeDbFromate($params['auction_start_date']);
            $endDate = dateChangeDbFromate($params['auction_end_date']);
            $query->whereBetween('lead_detail.auction_date', [$startDate, $endDate]);
            $query->orwhere('lead_custom_field.tenant_custom_field_id', 157);
        }
        if (!empty($params['start_date']) && !empty($params['end_date'])) {
            $params['start_date'] = dateTimezoneChangeNew($params['start_date'] . ' 00:00:00');
            $params['end_date'] = dateTimezoneChangeNew($params['end_date'] . ' 23:59:59');

            $params['start_date'] = date('Y-m-d H:i:s', strtotime($params['start_date']));
            $params['end_date'] = date('Y-m-d H:i:s', strtotime($params['end_date']));
            $query->whereRaw("lead_detail.created_at >= '{$params['start_date']}' && lead_detail.created_at <= '{$params['end_date']}'");
        }
        if (!is_null($params['is_retired']) && $params['is_retired'] == 1) {
            $query->where('lead_detail.is_expired', $params['is_retired']);
        } elseif (!is_null($params['is_retired']) && $params['is_retired'] == 2) {
            $query->where('lead_detail.is_expired', 0);
        }
        if (!empty($params['lead_type_id'])) {
            $query->whereRaw('lead_detail.type_id  IN (' . $params['lead_type_id'] . ')');
        }

        $query->orderBy('id', 'desc');
        if (isset($params['search']) && !empty($params['search'])) {
            $query->where(function($querysub) use($params) {
                $querysub->orwhere('lead_detail.zip_code', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.formatted_address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.county', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.state', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.city', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.title', 'like', '%' . trim($params['search']) . '%');
            });
        }
        $time_clause = '';
        $group_by__clause = '';
        if (!empty($params['time_slot'])) {
            $time_clauses['today'] = " DATE(lead_detail.created_at) = DATE(NOW())";
            $time_clauses['yesterday'] = " DATE(lead_detail.created_at) = DATE(NOW() - INTERVAL 1 DAY)";
            $time_clauses['week'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 7 DAY";
            $time_clauses['last_week'] = " YEARWEEK(lead_detail.created_at) = YEARWEEK(NOW() - INTERVAL 1 WEEK)";
            $time_clauses['month'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 1 MONTH";
            $time_clauses['last_month'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 MONTH)  AND month(lead_detail.created_at) = Month(NOW() - INTERVAL 1 MONTH) ";
            $time_clauses['year'] = " lead_detail.created_at > DATE_SUB(NOW(),INTERVAL 1 YEAR)";
            $time_clauses['last_year'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 YEAR)";
            $group_by_clauses['today'] = " hour, day, month, year";
            $group_by_clauses['yesterday'] = " hour, day, month, year";
            $group_by_clauses['week'] = " day, month, year";
            $group_by_clauses['last_week'] = " day, month, year";
            $group_by_clauses['month'] = " day, month, year";
            $group_by_clauses['last_month'] = " day, month, year";
            $group_by_clauses['year'] = " month, year";
            $group_by_clauses['last_year'] = " month, year";
            $slot_types['today'] = " hour";
            $slot_types['yesterday'] = " hour";
            $slot_types['week'] = " day";
            $slot_types['last_week'] = " day";
            $slot_types['month'] = " day";
            $slot_types['last_month'] = " day";
            $slot_types['year'] = " month";
            $slot_types['last_year'] = " month";
            $time_clause = $time_clauses[$params['slot']];
            $group_by__clause = $group_by_clauses[$params['slot']];
            $query->whereRaw("$time_clause");
        }
        if ((isset($params['is_status_group_by']) && $params['is_status_group_by'])) {
            $query->select('lead_detail.*', \DB::raw('count(lead_detail.id) as lead_count'));
            $query->groupBy('lead_detail.status_id');
            return $query->get();
        }
        return $query->get();
    }

    public static function getListIndexExportFollowUp($params) {
        $followingLeads = new FollowingLead();


        if (!empty($input['search_text'])) {
            $followingLeads = $followingLeads->where(function($querysub) use($input) {
                $querysub->orwhere('title', 'like', '%' . $input['search_text'] . '%')
                        ->orwhere('formatted_address', 'like', '%' . $input['search_text'] . '%')
                        ->orwhere('investor_notes', 'like', '%' . $input['search_text'] . '%')
                        ->orwhere('admin_notes', 'like', '%' . $input['search_text'] . '%');
            });
        }

        if (!empty($input['user_id_search'])) {
            $followingLeads = $followingLeads->orwhereIn('investor_id', $input['user_id_search'])
                    ->orwhereIn('user_detail', $input['user_id_search']);
        }

        if (!empty($input['lead_status_id'])) {
            $followingLeads = $followingLeads->whereIn('follow_status', $input['lead_status_id']);
        }

        $auctionDate = json_decode($input['auction_date']);

        if (!empty($auctionDate)) {

            $start = dateFormatYMDtoMDY($auctionDate->start);
            $end = dateFormatYMDtoMDY($auctionDate->end);

            $followingLeads = $followingLeads->whereBetween('auction', [$start, $end]);
        }

        if (!empty($input['sort_column']) && !empty($input['sort_type'])) {
            switch ($input['sort_column']) {
                case 'homeowner_name':
                    $followingLeads = $followingLeads->orderby('title', $input['sort_type']);
                    break;

                case 'homeowner_address':
                    $followingLeads = $followingLeads->orderby('formatted_address', $input['sort_type']);
                    break;

                case 'notes_and_actions':
                    $followingLeads = $followingLeads->orderby('admin_notes', $input['sort_type']);

                    break;

                case 'status_update':
                    $followingLeads = $followingLeads->orderby('date_status_updated', $input['sort_type']);

                    break;

                case 'auction_date':
                    $followingLeads = $followingLeads->orderby('auction', $input['sort_type']);

                    break;

                case 'contract_date':
                    $followingLeads = $followingLeads->orderby('contract_date', $input['sort_type']);

                    break;

                case 'status':
                    $followingLeads = $followingLeads->orderby('follow_status', $input['sort_type']);

                    break;

                case 'lead':
                    $followingLeads = $followingLeads->orderby('user_detail', $input['sort_type']);

                case 'investor':
                    $followingLeads = $followingLeads->orderby('investor_id', $input['sort_type']);

                case 'investor_notes':
                    $followingLeads = $followingLeads->orderby('investor_notes', $input['sort_type']);

                    break;

                case 'is_retired':
                    $followingLeads = $followingLeads->orderby('is_retired', $input['sort_type']);

                    break;

                default:
                    // code...
                    break;
            }
        } else {
            $followingLeads = $followingLeads->latest();
        }

        $statusDateRange = json_decode($input['status_date_range']);

        if (!empty($statusDateRange)) {
            $followingLeads = $followingLeads->whereDate('date_status_updated', '>=', $statusDateRange->start)
                    ->whereDate('date_status_updated', '<=', $statusDateRange->end);
        }
        $followingLeads->where('is_lead_up', '=', 0);
        return $followingLeads = $followingLeads->get();
    }

    public static function getListIndexDummy($params) {

        if (!empty($params['latitude']) && !empty($params['longitude'])) {
            $lat = $params['latitude'];
            $lng = $params['longitude'];
            $radius = $params['radius'];

            $haversine = "(3959 * acos (
                    cos ( radians($lat) )
                    * cos( radians(`latitude`) )
                    * cos( radians(`longitude`) - radians($lng) )
                    + sin ( radians($lat) )
                    * sin( radians(`latitude`) )
                ))";
        }


        if (!empty($params['auction_start_date']) && !empty($params['auction_end_date'])) {
            $query = self::select('lead_detail.*', 'type.title as type_title', 'status.title as status_title', 'lead_query.response as Notes_Add_to_Top_Include_Date_Your_Name_and_Notes', 'user.first_name as assignee_first', 'user.last_name as assignee_last', 'lead_custom_field.value as auction_date_value');
            $query->leftJoin('lead_custom_field', 'lead_detail.id', '=', 'lead_custom_field.lead_id');
            $query->where('lead_custom_field.tenant_custom_field_id', 157);
        } else {
            $query = self::select('lead_detail.*', 'type.title as type_title', 'status.title as status_title', 'lead_query.response as Notes_Add_to_Top_Include_Date_Your_Name_and_Notes', 'user.first_name as assignee_first', 'user.last_name as assignee_last');
        }

        $query->with('leadCustom', 'tenantquery');
        $query->leftJoin('lead_query', 'lead_detail.id', '=', 'lead_query.lead_id');
        $query->leftJoin('status', 'lead_detail.status_id', '=', 'status.id');
        $query->leftJoin('type', 'lead_detail.type_id', '=', 'type.id');
        $query->leftJoin('user', 'lead_detail.assignee_id', '=', 'user.id');
        $query->where('lead_query.query_id', 8);
        $query->where('lead_detail.company_id', $params['company_id']);
        $query->whereNull('lead_detail.deleted_at');

        return $query->get();

        if (isset($params['user_ids']) && !empty($params['user_ids']))
            $query->whereRaw('lead_detail.assignee_id  IN (' . $params['user_ids'] . ')');

        if (isset($params['status_ids']) && !empty($params['status_ids']))
            $query->whereRaw('lead_detail.status_id  IN (' . $params['status_ids'] . ')');

        if (!isset($params['is_web']) || $params['is_web'] == 0) {
            $query->where('lead_detail.is_expired', 0);
        }

        if (isset($params['auction_start_date']) && isset($params['auction_end_date']) && !empty($params['auction_start_date']) && !empty($params['auction_end_date'])) {

            $params['auction_start_date'] = date('n/j/Y', strtotime($params['auction_start_date']));
            $params['auction_end_date'] = date('n/j/Y', strtotime($params['auction_end_date']));

            $query->whereRaw("lead_custom_field.value >= '{$params['auction_start_date']}' && lead_custom_field.value <= '{$params['auction_end_date']}'");
        }

        if (!empty($params['start_date']) && !empty($params['end_date'])) {

            $params['start_date'] = dateTimezoneChangeNew($params['start_date'] . ' 00:00:00');
            $params['end_date'] = dateTimezoneChangeNew($params['end_date'] . ' 23:59:59');

            $params['start_date'] = date('Y-m-d H:i:s', strtotime($params['start_date']));
            $params['end_date'] = date('Y-m-d H:i:s', strtotime($params['end_date']));
            $query->whereRaw("lead_detail.created_at >= '{$params['start_date']}' && lead_detail.created_at <= '{$params['end_date']}'");
        }


        if (!is_null($params['is_retired']) && $params['is_retired'] == 1) {
            $query->where('lead_detail.is_expired', $params['is_retired']);
        } elseif (!is_null($params['is_retired']) && $params['is_retired'] == 2) {
            $query->where('lead_detail.is_expired', 0);
        }

        if (!empty($params['lead_type_id'])) {
            $query->whereRaw('lead_detail.type_id  IN (' . $params['lead_type_id'] . ')');
        }
        if (!isset($params['order_by'])) {
            $params['order_by'] = 'id';
            $params['order_type'] = 'desc';
        }
        $order_by = 'lead_detail.' . $params['order_by'];
        if (strtolower($params['order_by']) == 'lead_type') {
            $order_by = 'type.title';
        }

        if (strtolower($params['order_by']) == 'first_name' || strtolower($params['order_by']) == 'last_name') {
            $order_by = 'lead_detail.owner';
        }

        if (strtolower($params['order_by']) == 'lead_name' || strtolower($params['order_by']) == 'homeowner name') {
            $order_by = 'lead_detail.title';
        }

        if (strtolower($params['order_by']) == 'admin notes') {
            $order_by = 'lead_detail.admin_notes';
        }

        $query->orderBy($order_by, $params['order_type']);

        if (isset($params['search']) && !empty($params['search'])) {

            $query->where(function($querysub) use($params) {
                $querysub->orwhere('lead_detail.zip_code', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.formatted_address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.county', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.state', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.city', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.title', 'like', '%' . trim($params['search']) . '%');
            });
        }

        $time_clause = '';
        $group_by__clause = '';
        if (!empty($params['time_slot'])) {
            $time_clauses['today'] = " DATE(lead_detail.created_at) = DATE(NOW())";
            $time_clauses['yesterday'] = " DATE(lead_detail.created_at) = DATE(NOW() - INTERVAL 1 DAY)";
            $time_clauses['week'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 7 DAY";
            $time_clauses['last_week'] = " YEARWEEK(lead_detail.created_at) = YEARWEEK(NOW() - INTERVAL 1 WEEK)";
            $time_clauses['month'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 1 MONTH";
            $time_clauses['last_month'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 MONTH)  AND month(lead_detail.created_at) = Month(NOW() - INTERVAL 1 MONTH) ";
            $time_clauses['year'] = " lead_detail.created_at > DATE_SUB(NOW(),INTERVAL 1 YEAR)";
            $time_clauses['last_year'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 YEAR)";

            $group_by_clauses['today'] = " hour, day, month, year";
            $group_by_clauses['yesterday'] = " hour, day, month, year";
            $group_by_clauses['week'] = " day, month, year";
            $group_by_clauses['last_week'] = " day, month, year";
            $group_by_clauses['month'] = " day, month, year";
            $group_by_clauses['last_month'] = " day, month, year";
            $group_by_clauses['year'] = " month, year";
            $group_by_clauses['last_year'] = " month, year";

            $slot_types['today'] = " hour";
            $slot_types['yesterday'] = " hour";
            $slot_types['week'] = " day";
            $slot_types['last_week'] = " day";
            $slot_types['month'] = " day";
            $slot_types['last_month'] = " day";
            $slot_types['year'] = " month";
            $slot_types['last_year'] = " month";

            $time_clause = $time_clauses[$params['slot']];
            $group_by__clause = $group_by_clauses[$params['slot']];

            $query->whereRaw("$time_clause");
        }

        if ((isset($params['is_status_group_by']) && $params['is_status_group_by'])) {
            $query->select('lead_detail.*', \DB::raw('count(lead_detail.id) as lead_count'));
            $query->groupBy('lead_detail.status_id');

            return $query->get();
        }

        if ((isset($params['is_paginate']) && !$params['is_paginate']) || array_key_exists("export", $params) && $params['export'] === 'true') {
            return $query->get();
        }

        return $query->get();
    }

    public static function getListIndexView($params) {

        if (!empty($params['latitude']) && !empty($params['longitude'])) {
            $lat = $params['latitude'];
            $lng = $params['longitude'];
            $radius = $params['radius'];

            $haversine = "(3959 * acos (
                    cos ( radians($lat) )
                    * cos( radians(`latitude`) )
                    * cos( radians(`longitude`) - radians($lng) )
                    + sin ( radians($lat) )
                    * sin( radians(`latitude`) )
                ))";
        }


        if (!empty($params['auction_start_date']) && !empty($params['auction_end_date'])) {
            $query = LeadView::select('*');
            $query->leftJoin('lead_custom_field', 'leads_view.id', '=', 'lead_custom_field.lead_id');
            $query->where('lead_custom_field.tenant_custom_field_id', 157);
        } else {
            $query = LeadView::select('*');
        }

        $query->groupBy('leads_view.id');
        $query->where('leads_view.company_id', $params['company_id']);

        if (isset($params['user_ids']) && !empty($params['user_ids']))
            $query->whereRaw('leads_view.assignee_id  IN (' . $params['user_ids'] . ')');

        if (isset($params['status_ids']) && !empty($params['status_ids']))
            $query->whereRaw('leads_view.status_id  IN (' . $params['status_ids'] . ')');

        if (!isset($params['is_web']) || $params['is_web'] == 0) {
            $query->where('leads_view.is_expired', 0);
        }

        if (isset($params['auction_start_date']) && isset($params['auction_end_date']) && !empty($params['auction_start_date']) && !empty($params['auction_end_date'])) {

            $params['auction_start_date'] = date('n/j/Y', strtotime($params['auction_start_date']));
            $params['auction_end_date'] = date('n/j/Y', strtotime($params['auction_end_date']));

            $query->whereRaw("lead_custom_field.value >= '{$params['auction_start_date']}' && lead_custom_field.value <= '{$params['auction_end_date']}'");
        }

        if (!empty($params['start_date']) && !empty($params['end_date'])) {

            $params['start_date'] = dateTimezoneChangeNew($params['start_date'] . ' 00:00:00');
            $params['end_date'] = dateTimezoneChangeNew($params['end_date'] . ' 23:59:59');

            $params['start_date'] = date('Y-m-d H:i:s', strtotime($params['start_date']));
            $params['end_date'] = date('Y-m-d H:i:s', strtotime($params['end_date']));
            $query->whereRaw("leads_view.created_at >= '{$params['start_date']}' && leads_view.created_at <= '{$params['end_date']}'");
        }

        if (!is_null($params['is_retired']) && $params['is_retired'] == 1) {
            $query->where('leads_view.is_expired', $params['is_retired']);
        } elseif (!is_null($params['is_retired']) && $params['is_retired'] == 2) {
            $query->where('leads_view.is_expired', 0);
        }

        if (!empty($params['lead_type_id'])) {
            $query->whereRaw('leads_view.type_id  IN (' . $params['lead_type_id'] . ')');
        }
        if (!isset($params['order_by'])) {
            $params['order_by'] = 'id';
            $params['order_type'] = 'desc';
        }
        $order_by = 'leads_view.' . $params['order_by'];
        if (strtolower($params['order_by']) == 'lead_type') {
            $order_by = 'type.title';
        }

        if (strtolower($params['order_by']) == 'first_name' || strtolower($params['order_by']) == 'last_name') {
            $order_by = 'leads_view.owner';
        }

        if (strtolower($params['order_by']) == 'Zip' || strtolower($params['order_by']) == 'zip') {
            $order_by = 'leads_view.zip_code';
        }

        if (strtolower($params['order_by']) == 'lead_name' || strtolower($params['order_by']) == 'homeowner name') {
            $order_by = 'leads_view.title';
        }

        if (strtolower($params['order_by']) == 'admin notes') {
            $order_by = 'leads_view.admin_notes';
        }

        $query->orderBy($order_by, str_replace(' ', '_', strtolower($params['order_type'])));

        if (isset($params['search']) && !empty($params['search'])) {

            $query->where(function($querysub) use($params) {
                $querysub->orwhere('leads_view.zip_code', 'like', '%' . $params['search'] . '%')
                        ->orwhere('leads_view.formatted_address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('leads_view.address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('leads_view.county', 'like', '%' . $params['search'] . '%')
                        ->orwhere('leads_view.state', 'like', '%' . $params['search'] . '%')
                        ->orwhere('leads_view.city', 'like', '%' . $params['search'] . '%')
                        ->orwhere('leads_view.title', 'like', '%' . trim($params['search']) . '%');
            });
        }

        $time_clause = '';
        $group_by__clause = '';
        if (!empty($params['time_slot'])) {
            //$time_clauses['today'] = " AND leads_view.created_at >= CURDATE() AND leads_view.created_at < CURDATE() + INTERVAL 1 DAY";
            $time_clauses['today'] = " DATE(leads_view.created_at) = DATE(NOW())";
            $time_clauses['yesterday'] = " DATE(leads_view.created_at) = DATE(NOW() - INTERVAL 1 DAY)";
            $time_clauses['week'] = " leads_view.created_at >= DATE(NOW()) - INTERVAL 7 DAY";
            $time_clauses['last_week'] = " YEARWEEK(leads_view.created_at) = YEARWEEK(NOW() - INTERVAL 1 WEEK)";
            $time_clauses['month'] = " leads_view.created_at >= DATE(NOW()) - INTERVAL 1 MONTH";
            $time_clauses['last_month'] = " year(leads_view.created_at) = year(NOW() - INTERVAL 1 MONTH)  AND month(leads_view.created_at) = Month(NOW() - INTERVAL 1 MONTH) ";
            $time_clauses['year'] = " leads_view.created_at > DATE_SUB(NOW(),INTERVAL 1 YEAR)";
            $time_clauses['last_year'] = " year(leads_view.created_at) = year(NOW() - INTERVAL 1 YEAR)";

            $group_by_clauses['today'] = " hour, day, month, year";
            $group_by_clauses['yesterday'] = " hour, day, month, year";
            $group_by_clauses['week'] = " day, month, year";
            $group_by_clauses['last_week'] = " day, month, year";
            $group_by_clauses['month'] = " day, month, year";
            $group_by_clauses['last_month'] = " day, month, year";
            $group_by_clauses['year'] = " month, year";
            $group_by_clauses['last_year'] = " month, year";

            $slot_types['today'] = " hour";
            $slot_types['yesterday'] = " hour";
            $slot_types['week'] = " day";
            $slot_types['last_week'] = " day";
            $slot_types['month'] = " day";
            $slot_types['last_month'] = " day";
            $slot_types['year'] = " month";
            $slot_types['last_year'] = " month";

            $time_clause = $time_clauses[$params['slot']];
            $group_by__clause = $group_by_clauses[$params['slot']];

            $query->whereRaw("$time_clause");
        }

        // if (!empty($params['latitude']) && !empty($params['longitude']))
        //     $query->selectRaw("{$haversine} AS distance")
        //             ->whereRaw("{$haversine} < ?", [$radius]);

        if ((isset($params['is_status_group_by']) && $params['is_status_group_by'])) {
            $query->select('leads_view.*', \DB::raw('count(leads_view.id) as lead_count'));
            //$query->leftJoin('status','status.id','leads_view.status_id');
            $query->groupBy('leads_view.status_id');

            return $query->get();
        }

        if ((isset($params['is_paginate']) && !$params['is_paginate']) || array_key_exists("export", $params) && $params['export'] === 'true') {
            return $query->get();
        }

        return $query->paginate(Config::get('constants.PAGINATION_PAGE_SIZE'));
    }

    public static function getUserList($params) {

        $lat = $params['latitude'];
        $lng = $params['longitude'];
        $radius = $params['radius'];

        $haversine = "(3959 * acos (
                    cos ( radians($lat) )
                    * cos( radians(`latitude`) )
                    * cos( radians(`longitude`) - radians($lng) )
                    + sin ( radians($lat) )
                    * sin( radians(`latitude`) )
                ))";


        $query = self::select('lead_detail.*');
        $query->where('company_id', $params['company_id']);
        $query->where('assignee_id', $params['user_id']);
        $query->whereNull('lead_detail.deleted_at');


        if (isset($params['status_ids']) && !empty($params['status_ids']))
            $query->whereRaw('lead_detail.status_id IN (' . $params['status_ids'] . ')');

        if (isset($params['start_date']) && isset($params['end_date']) && !empty($params['start_date']) && !empty($params['end_date']))
            $params['start_date'] = dateTimezoneChangeNew($params['start_date'] . ' 00:00:00');
        $params['end_date'] = dateTimezoneChangeNew($params['end_date'] . ' 23:59:59');

        $params['start_date'] = date('Y-m-d H:i:s', strtotime($params['start_date']));
        $params['end_date'] = date('Y-m-d H:i:s', strtotime($params['end_date']));

        $query->whereRaw("created_at >= '{$params['start_date']}' && created_at <= '{$params['end_date']}'");

        if (!empty($params['lead_type_id'])) {
            $query->whereRaw('lead_detail.type_id IN (' . $params['lead_type_id'] . ')');
        }


        if (isset($params['search']) && !empty($params['search'])) {
            $query->leftJoin('type', 'type.id', 'lead_detail.type_id');
            $query->whereRaw("(lead_detail.title like '%{$params['search']}%' OR formatted_address like '%{$params['search']}%' 
             OR address like '%{$params['search']}%' OR type.title like '%{$params['search']}%' )");
        }

        $time_clause = '';
        $group_by__clause = '';
        if (!empty($params['time_slot'])) {
            //$time_clauses['today'] = " AND lead_detail.created_at >= CURDATE() AND lead_detail.created_at < CURDATE() + INTERVAL 1 DAY";
            $time_clauses['today'] = " DATE(lead_detail.created_at) = DATE(NOW())";
            $time_clauses['yesterday'] = " DATE(lead_detail.created_at) = DATE(NOW() - INTERVAL 1 DAY)";
            $time_clauses['week'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 7 DAY";
            $time_clauses['last_week'] = " YEARWEEK(lead_detail.created_at) = YEARWEEK(NOW() - INTERVAL 1 WEEK)";
            $time_clauses['month'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 1 MONTH";
            $time_clauses['last_month'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 MONTH)  AND month(lead_detail.created_at) = Month(NOW() - INTERVAL 1 MONTH) ";
            $time_clauses['year'] = " lead_detail.created_at > DATE_SUB(NOW(),INTERVAL 1 YEAR)";
            $time_clauses['last_year'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 YEAR)";

            $group_by_clauses['today'] = " hour, day, month, year";
            $group_by_clauses['yesterday'] = " hour, day, month, year";
            $group_by_clauses['week'] = " day, month, year";
            $group_by_clauses['last_week'] = " day, month, year";
            $group_by_clauses['month'] = " day, month, year";
            $group_by_clauses['last_month'] = " day, month, year";
            $group_by_clauses['year'] = " month, year";
            $group_by_clauses['last_year'] = " month, year";

            $slot_types['today'] = " hour";
            $slot_types['yesterday'] = " hour";
            $slot_types['week'] = " day";
            $slot_types['last_week'] = " day";
            $slot_types['month'] = " day";
            $slot_types['last_month'] = " day";
            $slot_types['year'] = " month";
            $slot_types['last_year'] = " month";

            $time_clause = $time_clauses[$params['slot']];
            $group_by__clause = $group_by_clauses[$params['slot']];

            $query->whereRaw("$time_clause");
        }


        if (!empty($params['latitude']) && !empty($params['longitude']))
            $query->selectRaw("{$haversine} AS distance")
                    ->whereRaw("{$haversine} < ?", [$radius]);

        $query->with('tenantQuery');
        $query->with('leadCustom');
        $query->with('leadStatus');
        $query->with('leadType');
        $query->with('leadMedia');

        $query->orderBy('lead_detail.id', 'desc');
        return $query->paginate(Config::get('constants.PAGINATION_PAGE_SIZE'));
    }

    public function leadQuery() {
        return self::hasMany('App\Models\LeadQuery', 'lead_id');
    }

    public function tenantQuery() {
        return self::hasMany(TenantQuery::class, 'tenant_id', 'company_id');
        /* ->leftjoin('lead_query',function ($join){

          $join->on('lead_query.query_id','=','tenant_query.id')
          ->where('lead_query.lead_id', 'lead_detail.id');
          //$join->on(\DB::raw('( lead_query.query_id = tenant_query.id AND lead_query.lead_id = lead_detail.id )'));
          //'lead_query.id',

          //$join->on(DB::raw('(  bookings.arrival between ? and ? OR bookings.departure between ? and ? )'), DB::raw(''), DB::raw(''));
          }) */
        /* ->select(['tenant_query.tenant_id','tenant_query.type','tenant_query.query', 'lead_query.created_at',
          'lead_query.lead_id','lead_query.response']) */
        ;
    }

    public function leadMedia() {
        return self::hasMany('App\Models\Media', 'source_id')
                        ->where('source_type', 'lead')
                        ->whereNull('deleted_at');
    }

    public function leadCustom() {
        return self::hasMany('App\Models\LeadCustomField', 'lead_id', 'id')
                        ->select(['lead_custom_field.id', 'lead_custom_field.lead_id', 'tenant_custom_field.key', 'lead_custom_field.value'])
                        ->leftJoin('tenant_custom_field', 'tenant_custom_field.id', 'lead_custom_field.tenant_custom_field_id')
                        ->where('tenant_custom_field.is_active', 1)
                        ->groupBy('tenant_custom_field.id')
                        ->orderBy('tenant_custom_field.order_by');
    }

    public function leadCustomNew() {
        return self::hasMany('App\Models\LeadCustomField', 'lead_id', 'id');
    }

    public function leadStatus() {
        return self::hasOne('App\Models\Status', 'id', 'status_id');
    }

    public function lastLeadKnock() {
        return self::hasOne('App\Models\UserLeadKnocks', 'lead_id', 'id')->latest();
    }

    public function leadType() {
        return self::hasOne('App\Models\Type', 'id', 'type_id');
    }

    public static function getStatsReport($params) {
        $tenant_clause = ' AND lead_detail.company_id = ' . $params['company_id'];

        $user_clause = '';
        if (!empty($params['user_id']))
            $user_clause = ' AND lead_detail.assignee_id IN (' . $params['user_id'] . ')';

        $time_clause = '';
        $group_by__clause = '';
        if (!empty($params['time_slot'])) {
            //$time_clauses['today'] = " AND lead_detail.created_at >= CURDATE() AND lead_detail.created_at < CURDATE() + INTERVAL 1 DAY";
            $time_clauses['today'] = " AND DATE(lead_detail.created_at) = DATE(NOW())";
            $time_clauses['yesterday'] = " AND DATE(lead_detail.created_at) = DATE(NOW() - INTERVAL 1 DAY)";
            $time_clauses['week'] = " AND lead_detail.created_at >= DATE(NOW()) - INTERVAL 7 DAY";
            $time_clauses['last_week'] = " AND YEARWEEK(lead_detail.created_at) = YEARWEEK(NOW() - INTERVAL 1 WEEK)";
            $time_clauses['month'] = " AND lead_detail.created_at >= DATE(NOW()) - INTERVAL 1 MONTH";
            $time_clauses['last_month'] = " AND year(lead_detail.created_at) = year(NOW() - INTERVAL 1 MONTH)  AND month(lead_detail.created_at) = Month(NOW() - INTERVAL 1 MONTH) ";
            $time_clauses['year'] = " AND lead_detail.created_at > DATE_SUB(NOW(),INTERVAL 1 YEAR)";
            $time_clauses['last_year'] = " AND year(lead_detail.created_at) = year(NOW() - INTERVAL 1 YEAR)";

            $group_by_clauses['today'] = " hour, day, month, year";
            $group_by_clauses['yesterday'] = " hour, day, month, year";
            $group_by_clauses['week'] = " day, month, year";
            $group_by_clauses['last_week'] = " day, month, year";
            $group_by_clauses['month'] = " day, month, year";
            $group_by_clauses['last_month'] = " day, month, year";
            $group_by_clauses['year'] = " month, year";
            $group_by_clauses['last_year'] = " month, year";

            $slot_types['today'] = " hour";
            $slot_types['yesterday'] = " hour";
            $slot_types['week'] = " day";
            $slot_types['last_week'] = " day";
            $slot_types['month'] = " day";
            $slot_types['last_month'] = " day";
            $slot_types['year'] = " month";
            $slot_types['last_year'] = " month";

            $time_clause = $time_clauses[$params['slot']];
            $group_by__clause = $group_by_clauses[$params['slot']];
        }
        //print_r($params['status_id']);exit;
        $status_clause = '';
        $status_qry = Status::whereIn('tenant_id', [$params['company_id']])->whereNull('deleted_at');
        if (!empty($params['status_id'])) {
            $status_qry->where('id', $params['status_id']);
            $status_clause = ' AND lead_detail.status_id IN (' . $params['status_id'] . ')';
        } else {
            $status_result = $status_qry->get();
            $status_id = $status_result[0]['id'];
            $status_clause = " AND status_id != $status_id";
        }

        $type_clause = '';
        if (!empty($params['lead_type_id']))
            $type_clause = ' AND lead_detail.type_id IN (' . $params['lead_type_id'] . ')';


        $result = \DB::select("Select count(*) as lead_count from lead_detail WHERE 1 = 1 $status_clause $tenant_clause $time_clause $user_clause $type_clause
                              union all 
                              Select count(*) as lead_count from lead_detail where 1 = 1 $status_clause $tenant_clause $time_clause $user_clause $type_clause
                              union all 
                              Select count(*) as lead_count from lead_detail where appointment_date IS NOT NULL $status_clause $tenant_clause $time_clause $user_clause $type_clause");
        $response = [];
        //print_r($result);exit;

        $total_leads = empty($result[0]->lead_count) ? 1 : $result[0]->lead_count;
        $total_leads_contacted = $result[1]->lead_count;
        $total_leads_appointed = $result[2]->lead_count;

        $response[0]['title'] = 'leads contacted';
        $response[0]['value'] = floatval(number_format(($total_leads_contacted / $total_leads) * 100, 2));
        $response[0]['colour_code'] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));

        $response[1]['title'] = 'leads appointed';
        $response[1]['value'] = floatval(number_format(($total_leads_appointed / $total_leads) * 100, 2));
        $response[1]['colour_code'] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));

        return $response;
    }

    public static function getStatusStatsReport($params) {
        $tenant_clause = ' AND lead_detail.company_id = ' . $params['company_id'];

        $user_clause = '';
        // info($params['user_id']);
        if (!empty($params['user_id'])) {
            $user_clause = ' AND lead_detail.assignee_id IN (' . $params['user_id'] . ')';
        } else {
            $userid = User::where('user_group_id', '!=', 3)->pluck('id')->toArray();
            $userid = implode(', ', $userid);
            $user_clause = ' AND lead_detail.assignee_id IN (' . $userid . ')';
        }

        $time_clause = '';
        $group_by__clause = '';
        if (!empty($params['time_slot'])) {
            //$time_clauses['today'] = " AND lead_detail.created_at >= CURDATE() AND lead_detail.created_at < CURDATE() + INTERVAL 1 DAY";
            $time_clauses['today'] = " AND DATE(lead_detail.created_at) = DATE(NOW())";
            $time_clauses['yesterday'] = " AND DATE(lead_detail.created_at) = DATE(NOW() - INTERVAL 1 DAY)";
            $time_clauses['week'] = " AND lead_detail.created_at >= DATE(NOW()) - INTERVAL 7 DAY";
            $time_clauses['last_week'] = " AND YEARWEEK(lead_detail.created_at) = YEARWEEK(NOW() - INTERVAL 1 WEEK)";
            $time_clauses['month'] = " AND lead_detail.created_at >= DATE(NOW()) - INTERVAL 1 MONTH";
            $time_clauses['last_month'] = " AND year(lead_detail.created_at) = year(NOW() - INTERVAL 1 MONTH)  AND month(lead_detail.created_at) = Month(NOW() - INTERVAL 1 MONTH) ";
            $time_clauses['year'] = " AND lead_detail.created_at > DATE_SUB(NOW(),INTERVAL 1 YEAR)";
            $time_clauses['last_year'] = " AND year(lead_detail.created_at) = year(NOW() - INTERVAL 1 YEAR)";

            $group_by_clauses['today'] = " hour, day, month, year";
            $group_by_clauses['yesterday'] = " hour, day, month, year";
            $group_by_clauses['week'] = " day, month, year";
            $group_by_clauses['last_week'] = " day, month, year";
            $group_by_clauses['month'] = " day, month, year";
            $group_by_clauses['last_month'] = " day, month, year";
            $group_by_clauses['year'] = " month, year";
            $group_by_clauses['last_year'] = " month, year";

            $slot_types['today'] = " hour";
            $slot_types['yesterday'] = " hour";
            $slot_types['week'] = " day";
            $slot_types['last_week'] = " day";
            $slot_types['month'] = " day";
            $slot_types['last_month'] = " day";
            $slot_types['year'] = " month";
            $slot_types['last_year'] = " month";

            $time_clause = $time_clauses[$params['slot']];

            // rajesh
            $dateinput = json_decode($params['time_slot']);

            if (!empty($dateinput->start) && !empty($dateinput->end)) {

                $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
                $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

                $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
                $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));

                $time_clause = "AND DATE(lead_detail.created_at) between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
            }

            // $group_by__clause = $group_by_clauses[$params['slot']];
            // info($group_by__clause);
        }
        //print_r($params['status_id']);exit;
        $status_clause = '';
        $status_qry = Status::whereIn('tenant_id', [$params['company_id']])->whereNull('deleted_at');
        if (!empty($params['status_id'])) {
            $status_qry->where('id', $params['status_id']);
            $status_clause = ' AND lead_detail.status_id IN (' . $params['status_id'] . ')';
        } else {
            $status_qry->select('id');
            $status_qry->where('is_permanent', 1);
            $status_result = $status_qry->get();

            $status_ids = [];
            foreach ($status_result as $row) {
                $status_ids[] = $row->id;
            }
            $status_id = $status_result[0]['id'];
            $status_clause = " AND status_id NOT IN (" . implode(',', $status_ids) . ")";
        }

        $type_clause = '';
        if (!empty($params['lead_type_id']))
            $type_clause = ' AND lead_detail.type_id IN (' . $params['lead_type_id'] . ')';


        /*
         * Select count(*) as lead_count from lead_detail where 1 = 1 $status_clause $tenant_clause $time_clause $user_clause $type_clause
          union all
         * */
        $result = \DB::select("Select count(*) as lead_count, status.title as status_title, color_code from lead_detail LEFT JOIN status ON status.id = lead_detail.status_id WHERE 1 = 1 $status_clause $tenant_clause $time_clause $user_clause $type_clause group by status_id union all                                
                              Select count(*) as lead_count, 'lead_appointed' as status_title, '' as color_code from lead_detail where appointment_date IS NOT NULL $status_clause $tenant_clause $time_clause $user_clause $type_clause");
        $response = [];
        $processed = [];
        $total_leads_contacted = 0;
        foreach ($result as $row) {
            $tmp['title'] = $row->status_title; //'leads contacted';
            $tmp['value'] = $row->lead_count; //floatval(number_format(($total_leads_contacted / $total_leads) * 100, 2));
            $tmp['colour_code'] = (!empty($row->color_code)) ? $row->color_code : sprintf('#%06X', mt_rand(0, 0xFFFFFF));
            $total_leads_contacted += $row->lead_count;

            $processed[] = $tmp;
        }

        if (empty($total_leads_contacted))
            $total_leads_contacted = 1;

        foreach ($processed as $row) {
            $tmp['title'] = $row['title'];
            $tmp['value'] = floatval(number_format(($row['value'] / $total_leads_contacted) * 100, 2));
            $tmp['colour_code'] = $row['colour_code'];

            $response[] = $tmp;
        }

        return ($params['type'] == 'amount') ? $processed : $response;
    }

    public static function getTypesStatsReport($params) {
        $tenant_clause = '';

        $user_clause = '';
        if (!empty($params['user_id'])) {
            $user_clause = ' AND  lead_type_history.assign_id IN (' . $params['user_id'] . ')';
        } else {
            $userid = User::pluck('id')->toArray();
            $userid = implode(', ', $userid);
            $user_clause = ' AND  lead_type_history.assign_id IN (' . $userid . ')';
        }
        $time_clause = '';
        $group_by__clause = '';
        if (!empty($params['time_slot'])) {
            $dateinput = json_decode($params['time_slot']);
            if (!empty($dateinput->start) && !empty($dateinput->end)) {
                $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
                $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

                $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
                $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));

                $time_clause = "AND DATE(lead_type_history.created_at) between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
            }
        }
        $status_clause = '';
        $status_qry = Type::whereIn('tenant_id', [$params['company_id']])->whereNull('deleted_at');
        if (!empty($params['lead_type_id'])) {
            $status_qry->where('id', $params['lead_type_id']);
            $status_clause = ' AND lead_type_history.type_id IN (' . $params['lead_type_id'] . ')';
        } else {
            $status_qry->select('id');
            $status_result = $status_qry->get();

            $status_ids = [];
            foreach ($status_result as $row) {
                $status_ids[] = $row->id;
            }
            $status_id = $status_result[0]['id'];
            $status_clause = " AND lead_type_history.type_id IN (" . implode(',', $status_ids) . ")";
        }
        $type_clause = '';
        if (!empty($params['lead_type_id']))
            $type_clause = ' AND lead_type_history.type_id IN (' . $params['lead_type_id'] . ')';


        /*
         * Select count(*) as lead_count from lead_detail where 1 = 1 $status_clause $tenant_clause $time_clause $user_clause $type_clause
          union all
         * */
//        $time_clause1 = '';
//        if (!empty($params['start_date']) && !empty($params['end_date'])) {
//            $params['start_date'] = date('Y-m-d', strtotime($params['start_date'])) . ' 00:00:00';
//            $params['end_date'] = date('Y-m-d', strtotime($params['end_date'])) . ' 23:59:00';
//            $time_clause1 = "AND lead_detail.created_at >= '" . $params['start_date'] . "' and lead_detail.created_at <= '" . $params['end_date'] . "' ";
//        }
//        echo "Select count(*) as lead_count, type.title as status_title from lead_detail LEFT JOIN type ON type.id = lead_detail.type_id WHERE 1 = 1 $status_clause $tenant_clause $time_clause1 $time_clause $user_clause $type_clause group by type_id union all                                
//                              Select count(*) as lead_count, 'lead_appointed' as status_title from lead_detail where appointment_date IS NOT NULL $status_clause $tenant_clause $time_clause $user_clause $type_clause";
//        exit;

        $result = \DB::select("Select count(*) as lead_count, type.title as status_title from lead_type_history LEFT JOIN type ON type.id = lead_type_history.type_id WHERE 1 = 1 $status_clause $tenant_clause $time_clause1 $time_clause $user_clause $type_clause group by type_id union all                                
                              Select count(*) as lead_count, 'lead_appointed' as status_title from lead_type_history where created_at IS NOT NULL $status_clause $tenant_clause $time_clause $user_clause $type_clause");
        $response = [];
        $processed = [];
        $total_leads_contacted = 0;
        foreach ($result as $row) {
            if ($row->status_title != 'lead_appointed') {
                $tmp['title'] = $row->status_title; //'leads contacted';
                $tmp['value'] = $row->lead_count; //floatval(number_format(($total_leads_contacted / $total_leads) * 100, 2));
                $tmp['colour_code'] = (!empty($row->color_code)) ? $row->color_code : sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                $total_leads_contacted += $row->lead_count;
                $processed[] = $tmp;
            }
        }

        if (empty($total_leads_contacted))
            $total_leads_contacted = 1;

        foreach ($processed as $row) {
            $tmp['title'] = $row['title'];
            $tmp['value'] = floatval(number_format(($row['value'] / $total_leads_contacted) * 100, 2));
            $tmp['colour_code'] = $row['colour_code'];

            $response[] = $tmp;
        }

        return ($params['type'] == 'amount') ? $processed : $response;
    }

    public static function leadTypesStatsReportPie($params) {
        $tenant_clause = '';
        $time_clause1 = '';
        $user_clause = '';
        $time_clause = '';
        $group_by__clause = '';
        if (!empty($params['time_slot'])) {
            $dateinput = json_decode($params['time_slot']);
            if (!empty($dateinput->start) && !empty($dateinput->end)) {

                $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
                $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

                $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
                $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));

                $time_clause = "AND  lead_history.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
            }
        }
        $status_clause = '';
        $status_qry = Status::whereIn('tenant_id', [$params['company_id']])->whereNull('deleted_at');
        if (!empty($params['lead_type_id'])) {
            $status_qry->where('id', $params['lead_type_id']);
            $status_clause = ' AND  lead_history.status_id IN (' . $params['lead_type_id'] . ')';
        } else {
            $status_qry->select('id');
            $status_result = $status_qry->get();

            $status_ids = [];
            foreach ($status_result as $row) {
                $status_ids[] = $row->id;
            }
            $status_id = $status_result[0]['id'];
            $status_clause = " AND  lead_history.status_id IN (" . implode(',', $status_ids) . ")";
        }
        $type_clause = '';
        if (!empty($params['lead_type_id']))
            $type_clause = ' AND  lead_history.status_id IN (' . $params['lead_type_id'] . ')';

        $result = \DB::select("Select count(*) as lead_count, status.title as status_title from  lead_history LEFT JOIN  status ON  status.id = lead_history.status_id WHERE 1 = 1 $status_clause $tenant_clause $time_clause1 $time_clause $user_clause $type_clause group by status_id union all                                
                              Select count(*) as lead_count, 'lead_appointed' as status_title from  lead_history where created_at IS NOT NULL $status_clause $tenant_clause $time_clause $user_clause $type_clause");
        $response = [];
        $processed = [];
        $total_leads_contacted = 0;
        foreach ($result as $row) {
            if ($row->status_title != 'lead_appointed') {
                $tmp['title'] = $row->status_title; //'leads contacted';
                $tmp['value'] = $row->lead_count; //floatval(number_format(($total_leads_contacted / $total_leads) * 100, 2));
                $tmp['colour_code'] = (!empty($row->color_code)) ? $row->color_code : sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                $total_leads_contacted += $row->lead_count;
                $processed[] = $tmp;
            }
        }

        if (empty($total_leads_contacted))
            $total_leads_contacted = 1;

        foreach ($processed as $row) {
            $tmp['title'] = $row['title'];
            $tmp['value'] = floatval(number_format(($row['value'] / $total_leads_contacted) * 100, 2));
            $tmp['colour_code'] = $row['colour_code'];

            $response[] = $tmp;
        }

        return ($params['type'] == 'amount') ? $processed : $response;
    }

    public static function leadTypesStatsReportFollowUpPie($params) {
        $tenant_clause = '';
        $time_clause1 = '';
        $user_clause = '';
        $time_clause = '';
        $group_by__clause = '';
        if (!empty($params['time_slot'])) {
            $dateinput = json_decode($params['time_slot']);
            if (!empty($dateinput->start) && !empty($dateinput->end)) {

                $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
                $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

                $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
                $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));

                $time_clause = "AND lead_history.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
            }
        }
        $status_clause = '';
        $status_qry = FollowStatus::whereNull('deleted_at');
        if (!empty($params['lead_type_id'])) {
            $status_qry->where('id', $params['lead_type_id']);
            $status_clause = ' AND  lead_history.followup_status_id IN (' . $params['lead_type_id'] . ')';
        } else {
            $status_qry->select('id');
            $status_result = $status_qry->get();

            $status_ids = [];
            foreach ($status_result as $row) {
                $status_ids[] = $row->id;
            }
            $status_id = $status_result[0]['id'];
            $status_clause = " AND  lead_history.followup_status_id IN (" . implode(',', $status_ids) . ")";
        }
        $type_clause = '';
        if (!empty($params['lead_type_id']))
            $type_clause = ' AND  lead_history.followup_status_id IN (' . $params['lead_type_id'] . ')';

        $result = \DB::select("Select count(*) as lead_count, follow_statuses.title as status_title from  lead_history LEFT JOIN follow_statuses ON  follow_statuses.id = lead_history.followup_status_id WHERE 1 = 1 $status_clause $tenant_clause $time_clause1 $time_clause $user_clause $type_clause group by followup_status_id union all                                
                              Select count(*) as lead_count, 'lead_appointed' as status_title from  lead_history where created_at IS NOT NULL $status_clause $tenant_clause $time_clause $user_clause $type_clause");
        $response = [];
        $processed = [];
        $total_leads_contacted = 0;
        foreach ($result as $row) {
            if ($row->status_title != 'lead_appointed') {
                $tmp['title'] = $row->status_title; //'leads contacted';
                $tmp['value'] = $row->lead_count; //floatval(number_format(($total_leads_contacted / $total_leads) * 100, 2));
                $tmp['colour_code'] = (!empty($row->color_code)) ? $row->color_code : sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                $total_leads_contacted += $row->lead_count;
                $processed[] = $tmp;
            }
        }

        if (empty($total_leads_contacted))
            $total_leads_contacted = 1;

        foreach ($processed as $row) {
            $tmp['title'] = $row['title'];
            $tmp['value'] = floatval(number_format(($row['value'] / $total_leads_contacted) * 100, 2));
            $tmp['colour_code'] = $row['colour_code'];

            $response[] = $tmp;
        }

        return ($params['type'] == 'amount') ? $processed : $response;
    }

    public static function getKnockReportPie($params) {
        $tenant_clause = '';
        $time_clause1 = '';
        $user_clause = '';
        $time_clause = '';
        $group_by__clause = '';
        if (!empty($params['time_slot'])) {
            $dateinput = json_decode($params['time_slot']);
            if (!empty($dateinput->start) && !empty($dateinput->end)) {

                $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
                $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

                $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
                $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));

                $time_clause = "AND  user_lead_knocks.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
            }
        }
        $status_clause = '';
        $status_qry = Status::whereNull('deleted_at')->where('tenant_id', '=', 4);
        if (!empty($params['lead_type_id'])) {
            $status_qry->where('id', $params['lead_type_id']);
            $status_clause = ' AND user_lead_knocks.status_id IN (' . $params['lead_type_id'] . ')';
        } else {
            $status_qry->select('id');
            $status_result = $status_qry->get();

            $status_ids = [];
            foreach ($status_result as $row) {
                $status_ids[] = $row->id;
            }
            $status_id = $status_result[0]['id'];
            $status_clause = " AND   user_lead_knocks.status_id IN (" . implode(',', $status_ids) . ")";
        }
        $type_clause = '';
        if (!empty($params['lead_type_id']))
            $type_clause = ' AND  user_lead_knocks.status_id IN (' . $params['lead_type_id'] . ')';

        $result = \DB::select("Select count(*) as lead_count, status.title as status_title from user_lead_knocks LEFT JOIN status ON  status.id = user_lead_knocks.status_id WHERE 1=1 $status_clause $tenant_clause $time_clause1 $time_clause $user_clause $type_clause group by status_id union all                                
                              Select count(*) as lead_count, 'lead_appointed' as status_title from  user_lead_knocks where created_at IS NOT NULL $status_clause $tenant_clause $time_clause $user_clause $type_clause");
        $response = [];
        $processed = [];
        $total_leads_contacted = 0;
        foreach ($result as $row) {
            if ($row->status_title != 'lead_appointed') {
                $tmp['title'] = $row->status_title;
                $tmp['value'] = $row->lead_count;
                $tmp['colour_code'] = (!empty($row->color_code)) ? $row->color_code : sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                $total_leads_contacted += $row->lead_count;
                $processed[] = $tmp;
            }
        }

        if (empty($total_leads_contacted))
            $total_leads_contacted = 1;

        foreach ($processed as $row) {
            $tmp['title'] = $row['title'];
            $tmp['value'] = floatval(number_format(($row['value'] / $total_leads_contacted) * 100, 2));
            $tmp['colour_code'] = $row['colour_code'];

            $response[] = $tmp;
        }

        return ($params['type'] == 'amount') ? $processed : $response;
    }

    public static function leadTypesStatsReportCurrentPie($params) {
        $tenant_clause = '';
        $time_clause1 = '';
        $user_clause = '';
        $time_clause = '';
        $group_by__clause = '';
        if (!empty($params['time_slot'])) {
            $dateinput = json_decode($params['time_slot']);
            if (!empty($dateinput->start) && !empty($dateinput->end)) {

                $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
                $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

                $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
                $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));

                $time_clause = "AND lead_detail.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
            }
        }
        $status_clause = '';
        $status_qry = Status::whereIn('tenant_id', [$params['company_id']])->whereNull('deleted_at');
        if (!empty($params['lead_type_id'])) {
            $status_qry->where('id', $params['lead_type_id']);
            $status_clause = ' AND   lead_detail.status_id IN (' . $params['lead_type_id'] . ')';
        } else {
            $status_qry->select('id');
            $status_result = $status_qry->get();

            $status_ids = [];
            foreach ($status_result as $row) {
                $status_ids[] = $row->id;
            }
            $status_id = $status_result[0]['id'];
            $status_clause = " AND   lead_detail.status_id IN (" . implode(',', $status_ids) . ")";
        }
        $type_clause = '';
        if (!empty($params['lead_type_id']))
            $type_clause = ' AND   lead_detail.status_id IN (' . $params['lead_type_id'] . ')';

        $result = \DB::select("Select count(*) as lead_count, status.title as status_title from   lead_detail LEFT JOIN  status ON  status.id =  lead_detail.status_id WHERE lead_detail.deleted_at IS NULL AND is_expired = 0 AND is_follow_up = 0 AND 1 = 1 $status_clause $tenant_clause $time_clause1 $time_clause $user_clause $type_clause group by status_id union all                                
                              Select count(*) as lead_count, 'lead_appointed' as status_title from   lead_detail where lead_detail.deleted_at IS NULL AND is_expired = 0 AND is_follow_up = 0 AND created_at IS NOT NULL $status_clause $tenant_clause $time_clause $user_clause $type_clause");
        $response = [];
        $processed = [];
        $total_leads_contacted = 0;
        foreach ($result as $row) {
            if ($row->status_title != 'lead_appointed') {
                $tmp['title'] = $row->status_title;
                $tmp['value'] = $row->lead_count;
                $tmp['colour_code'] = (!empty($row->color_code)) ? $row->color_code : sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                $total_leads_contacted += $row->lead_count;
                $processed[] = $tmp;
            }
        }

        if (empty($total_leads_contacted))
            $total_leads_contacted = 1;

        foreach ($processed as $row) {
            $tmp['title'] = $row['title'];
            $tmp['value'] = floatval(number_format(($row['value'] / $total_leads_contacted) * 100, 2));
            $tmp['colour_code'] = $row['colour_code'];

            $response[] = $tmp;
        }

        return ($params['type'] == 'amount') ? $processed : $response;
    }

    public static function getStatusReport($params) {
        $tenant_clause = ' AND lead_detail.company_id = ' . $params['company_id'];

        $user_clause = '';
        if (!empty($params['user_id']))
            $user_clause = ' AND lead_detail.assignee_id IN (' . $params['user_id'] . ')';

        $time_clause = '';
        $group_by__clause = '';
        if (!empty($params['time_slot'])) {
            //$time_clauses['today'] = " AND lead_detail.created_at >= CURDATE() AND lead_detail.created_at < CURDATE() + INTERVAL 1 DAY";
            $time_clauses['today'] = " AND DATE(lead_detail.created_at) = DATE(NOW())";
            $time_clauses['yesterday'] = " AND DATE(lead_detail.created_at) = DATE(NOW() - INTERVAL 1 DAY)";
            $time_clauses['week'] = " AND lead_detail.created_at >= DATE(NOW()) - INTERVAL 7 DAY";
            $time_clauses['last_week'] = " AND YEARWEEK(lead_detail.created_at) = YEARWEEK(NOW() - INTERVAL 1 WEEK)";
            $time_clauses['month'] = " AND lead_detail.created_at >= DATE(NOW()) - INTERVAL 1 MONTH";
            $time_clauses['last_month'] = " AND year(lead_detail.created_at) = year(NOW() - INTERVAL 1 MONTH)  AND month(lead_detail.created_at) = Month(NOW() - INTERVAL 1 MONTH) ";
            $time_clauses['year'] = " AND lead_detail.created_at > DATE_SUB(NOW(),INTERVAL 1 YEAR)";
            $time_clauses['last_year'] = " AND year(lead_detail.created_at) = year(NOW() - INTERVAL 1 YEAR)";

            $group_by_clauses['today'] = " hour, day, month, year";
            $group_by_clauses['yesterday'] = " hour, day, month, year";
            $group_by_clauses['week'] = " day, month, year";
            $group_by_clauses['last_week'] = " day, month, year";
            $group_by_clauses['month'] = " day, month, year";
            $group_by_clauses['last_month'] = " day, month, year";
            $group_by_clauses['year'] = " month, year";
            $group_by_clauses['last_year'] = " month, year";

            $slot_types['today'] = " hour";
            $slot_types['yesterday'] = " hour";
            $slot_types['week'] = " day";
            $slot_types['last_week'] = " day";
            $slot_types['month'] = " day";
            $slot_types['last_month'] = " day";
            $slot_types['year'] = " month";
            $slot_types['last_year'] = " month";

            $time_clause = $time_clauses[$params['slot']];
            $group_by__clause = $group_by_clauses[$params['slot']];
        }

        $type_clause = '';
        if (!empty($params['lead_type_id'])) {
            $type_clause = ' AND lead_detail.type_id IN (' . $params['lead_type_id'] . ')';
        }

        $status_clause = '';
        $status_qry = Status::whereIn('tenant_id', [$params['company_id']])->whereNull('deleted_at');
        if (!empty($params['status_id'])) {
            $status_qry->where('id', $params['status_id']);
            $status_clause = ' AND lead_detail.status_id IN (' . $params['status_id'] . ')';
        }

        $status_result = $status_qry->get();

        if (empty($params['status_id'])) {
            $status_clause = ' AND lead_detail.status_id != ' . $status_result[0]['id'];
            unset($status_result[0]);
        }


        $result = \DB::select("SELECT count(lead_detail.id) as lead_count, status.title as status_title, status.color_code, status.code,
                    DATE_FORMAT(lead_detail.created_at, '%b') as month, year(lead_detail.created_at) as year, status.id as status_id, 
                    DATE_FORMAT(lead_detail.created_at,'%d') as day, DATE_FORMAT(lead_detail.created_at, '%a') as dayname, DATE_FORMAT(lead_detail.created_at, '%H') as hour 
                    FROM lead_detail left join status on status.id = lead_detail.status_id 
                    WHERE 1 = 1 $tenant_clause $user_clause $type_clause $status_clause $time_clause 
                    GROUP BY lead_detail.status_id order by lead_detail.status_id");
        $response = [];
        //print_r($result);exit;
        //$fn = 'populate'.ucfirst($params['slot']).'Data';
        $fn = 'populateStatusData';

        $populated_data = self::$fn($status_result);
        $slot_type = $slot_types[$params['slot']];
        //print_r($populated_data);
        foreach ($result as $row) {
            //print_r($row);exit;
            $tmp_slot_type['year'] = $row->month;
            $tmp_slot_type['month'] = $row->day;
            $tmp_slot_type['week'] = $row->dayname;
            $tmp_slot_type['today'] = $row->hour;

            //$label = $tmp_slot_type[$params['slot']];
            $label = $row->code;

            //$tmp['label'] = $label;
            $tmp = [];
            $tmp['label'] = $row->code;
            $tmp[$row->status_title]['value'] = $row->lead_count;
            $tmp[$row->status_title]['svg'] = (object) [];
            $tmp[$row->status_title]['status_id'] = $row->status_id;
            $tmp[$row->status_title]['color_code'] = $row->color_code;
            $tmp[$row->status_title]['code'] = $row->code;

            $populated_data[$label][$row->status_title] = $tmp;
        }
        if ($params['is_web']) {
            foreach ($populated_data as $data) {
                $tmp = [];
                foreach ($data as $label_key => $row) {
                    $label = $row['label'];
                    $tmp['label'] = $label;
                    $tmp['title'] = $label_key;
                    $tmp['long_label'] = $label_key;
                    $tmp['status_id'] = $row[$label_key]['status_id'];
                    $tmp['value'] = $row[$label_key]['value'];
                    $tmp['code'] = $row[$label_key]['code'];
                    $tmp['color_code'] = $row[$label_key]['color_code'];
                }
                $response[] = $tmp;
            }
            return $response;
        }


        foreach ($populated_data as $data) {
            $tmp = [];
            foreach ($data as $row) {
                $label = $row['label'];
                $tmp['label'] = $label;
                unset($row['label']);
                foreach ($row as $key => $value)
                    $tmp[$key] = $value;
            }
            $response[] = $tmp;
        }
        return $response;
    }

    public static function convertArray($firstArray, $secondArray) {
        $convertedArray = array_intersect_key($secondArray, array_flip($firstArray));
        return array_values($convertedArray);
    }

    public static function getUserStatusReport($params) {
        $time_clause = '';
        $commission_tenant_clause = ' AND commission_events.tenant_id = ' . $params['company_id'];
        $profit_tenant_clause = ' AND commission_events.tenant_id = ' . $params['company_id'];
        $user_clause = '';
        if (!empty($params['user_id'])) {
            $user_clause = ' AND user.id IN (' . $params['user_id'] . ')';
        }

        $time_clause = '';
        $group_by__clause = '';

        if (!empty($params['time_slot'])) {

            $table_place_holder = '<!__TABLE_NAME__!>';
            $column_place_holder = '<!__COLUMN_NAME__!>';
            //$time_clauses['today'] = " AND $table_place_holder.$column_place_holder >= CURDATE() AND $table_place_holder.$column_place_holder < CURDATE() + INTERVAL 1 DAY";
            $time_clauses['today'] = " AND DATE($table_place_holder.$column_place_holder) = DATE(NOW())";
            $time_clauses['yesterday'] = " AND DATE($table_place_holder.$column_place_holder) = DATE(NOW() - INTERVAL 1 DAY)";
            $time_clauses['week'] = " AND $table_place_holder.$column_place_holder >= DATE(NOW()) - INTERVAL 7 DAY";
            $time_clauses['last_week'] = " AND YEARWEEK($table_place_holder.$column_place_holder) = YEARWEEK(NOW() - INTERVAL 1 WEEK)";
            $time_clauses['month'] = " AND $table_place_holder.$column_place_holder >= DATE(NOW()) - INTERVAL 1 MONTH";
            $time_clauses['last_month'] = " AND year($table_place_holder.$column_place_holder) = year(NOW() - INTERVAL 1 MONTH)  AND month($table_place_holder.$column_place_holder) = Month(NOW() - INTERVAL 1 MONTH) ";
            $time_clauses['year'] = " AND $table_place_holder.$column_place_holder > DATE_SUB(NOW(),INTERVAL 1 YEAR)";
            $time_clauses['last_year'] = " AND year($table_place_holder.$column_place_holder) = year(NOW() - INTERVAL 1 YEAR)";

            if ($params['slot'] == 'all_time') {
                $params['slot'] = 'last_year';
            }
            //smit changes for date filter
            if ($params['slot'] == 'today') {
                $start_date = date('Y-m-d') . ' 00:00:00';
                $end_date = date('Y-m-d') . ' 23:59:59';
                $params['api_start_date'] = $start_date;
                $params['api_end_date'] = $end_date;
                $startDate = $start_date;
                $endDate = $end_date;
            }
            if ($params['slot'] == 'yesterday') {
                $start_date = date('Y-m-d', strtotime(date('Y-m-d') . ' -1 day')) . ' 00:00:00';
                $end_date = date('Y-m-d', strtotime(date('Y-m-d') . ' -1 day')) . ' 23:59:59';
                $params['api_start_date'] = $start_date;
                $params['api_end_date'] = $end_date;
                $startDate = $start_date;
                $endDate = $end_date;
            }
            if ($params['slot'] == 'week') {
                $start_date = date('Y-m-d', strtotime(date('Y-m-d') . ' -6 day')) . ' 00:00:00';
                $end_date = date('Y-m-d') . ' 23:59:59';
                $params['api_start_date'] = $start_date;
                $params['api_end_date'] = $end_date;
                $startDate = $start_date;
                $endDate = $end_date;
            }

            if ($params['slot'] == 'last_week') {
                $previous_week = strtotime("-1 week +2 day");
                $start_date = strtotime("last monday", $previous_week);
                $end_date = strtotime("next sunday", $start_date);
                $start_date = date('Y-m-d', $start_date) . ' 00:00:00';
                $end_date = date('Y-m-d', $end_date) . ' 23:59:59';
                $params['api_start_date'] = $start_date;
                $params['api_end_date'] = $end_date;
                $startDate = $start_date;
                $endDate = $end_date;
            }

            if ($params['slot'] == 'month') {
                $start_date = date('Y-m-01') . ' 00:00:00';
                $end_date = date('Y-m-d') . ' 23:59:59';
                $params['api_start_date'] = $start_date;
                $params['api_end_date'] = $end_date;
                $startDate = $start_date;
                $endDate = $end_date;
            }

            if ($params['slot'] == 'last_month') {
                $start_date = date('Y-m-d', strtotime('first day of last month')) . ' 00:00:00';
                $end_date = date('Y-m-d', strtotime('last day of last month')) . ' 23:59:59';
                $params['api_start_date'] = $start_date;
                $params['api_end_date'] = $end_date;
                $startDate = $start_date;
                $endDate = $end_date;
            }

            if ($params['slot'] == 'year') {
                $start_date = date('Y-01-01') . ' 00:00:00';
                $end_date = date('Y-m-d') . ' 23:59:59';
                $params['api_start_date'] = $start_date;
                $params['api_end_date'] = $end_date;
                $startDate = $start_date;
                $endDate = $end_date;
            }
            if ($params['slot'] == 'last_year') {
                $start_date = date('Y-m-d', strtotime('first day of last year')) . ' 00:00:00';
                $end_date = date('Y-12-d', strtotime('last day of last year')) . ' 23:59:59';
                $params['api_start_date'] = $start_date;
                $params['api_end_date'] = $end_date;
                $startDate = $start_date;
                $endDate = $end_date;
            }

            $lead_time_clause = str_replace($table_place_holder, 'lead', $time_clause);
            $lead_time_clause = str_replace($column_place_holder, 'created_at', $lead_time_clause);

            $appointment_time_clause = str_replace($table_place_holder, 'user_lead_appointment', $time_clause);
            $appointment_time_clause = str_replace($column_place_holder, 'appointment_date', $appointment_time_clause);

            $commission_time_clause = str_replace($table_place_holder, 'user_commission', $time_clause);
            $commission_time_clause = str_replace($column_place_holder, 'target_month', $commission_time_clause);

            $dateinput = json_decode($params['time_slot']);


            if (!empty($dateinput->start) && !empty($dateinput->end)) {

                $startDate = Carbon::createFromFormat('Y-m-d', $dateinput->start)->format('Y/m/d');
                $endDate = Carbon::createFromFormat('Y-m-d', $dateinput->end)->format('Y/m/d');

                $startDate = dateTimezoneChangeNew($startDate . ' 00:00:00');
                $endDate = dateTimezoneChangeNew($endDate . ' 23:59:59');

                $startDate = date('Y-m-d H:i:s', strtotime($startDate));
                $endDate = date('Y-m-d H:i:s', strtotime($endDate));

                $lead_time_clause = "AND lead.created_at between '" . $startDate . "' and '" . $endDate . "' ";
                $appointment_time_clause = "AND user_lead_appointment.appointment_date between '" . $startDate . "' and '" . $endDate . "' ";
                $commission_time_clause = "AND user_commission.created_at between '" . $startDate . "' and '" . $endDate . "' ";
                $time_clause = "AND lead_history.created_at between '" . $startDate . "' and '" . $endDate . "' ";
            } else {
                
            }
        }
        if (!empty($params['start_date']) && !empty($params['end_date'])) {
            $params['start_date'] = dateTimezoneChangeNew($params['start_date'] . ' 00:00:00');
            $params['end_date'] = dateTimezoneChangeNew($params['end_date'] . ' 23:59:59');

            $params['start_date'] = date('Y-m-d H:i:s', strtotime($params['start_date']));
            $params['end_date'] = date('Y-m-d H:i:s', strtotime($params['end_date']));


            $lead_time_clause = "AND lead.created_at between '" . $params['start_date'] . "' and '" . $params['end_date'] . "' ";
            $appointment_time_clause = "AND user_lead_appointment.appointment_date between '" . $params['start_date'] . "' and '" . $params['end_date'] . "' ";
            $time_clause = "AND lead_history.created_at between '" . $params['start_date'] . "' and '" . $params['end_date'] . "' ";
            $commission_time_clause = "AND user_commission.created_at between '" . $params['start_date'] . "' and '" . $params['end_date'] . "' ";
        }

        //smit changes for date filter
        if (isset($params['api_start_date']) AND isset($params['api_end_date']) AND!empty($params['api_start_date']) AND!empty($params['api_end_date'])) {
            $params['api_start_date'] = dateTimezoneChangeNew($params['api_start_date']);
            $params['api_end_date'] = dateTimezoneChangeNew($params['api_end_date']);

            $params['api_start_date'] = date('Y-m-d H:i:s', strtotime($params['api_start_date']));
            $params['api_end_date'] = date('Y-m-d H:i:s', strtotime($params['api_end_date']));

            info($params['api_start_date']);
            info($params['api_end_date']);

            $lead_time_clause = "AND lead.created_at between '" . $params['api_start_date'] . "' and '" . $params['api_end_date'] . "' ";
            $appointment_time_clause = "AND user_lead_appointment.appointment_date between '" . $params['api_start_date'] . "' and '" . $params['api_end_date'] . "' ";
            $commission_time_clause = "AND user_commission.created_at between '" . $params['api_start_date'] . "' and '" . $params['api_end_date'] . "' ";
        }

        $type_clause = '';
        if (!empty($params['lead_type_id'])) {
            $type_clause = ' AND lead_detail.type_id IN (' . $params['lead_type_id'] . ')';
        }

        $status_clause = '';
        $status_knocks_clause = '';
        $status_qry = Status::whereIn('tenant_id', [$params['company_id']])->whereNull('deleted_at');
        if (!empty($params['status_id'])) {
            $status_qry->whereIn('id', explode(',', $params['status_id']));
            $status_clause = ' AND lead_detail.status_id IN (' . $params['status_id'] . ')';
            $status_knocks_clause = ' AND status_id IN (' . $params['status_id'] . ')';
        }

        //if (empty($params['status_id'])) {
        /* $status_result = $status_qry->get();
          $status_clause = ' AND lead_detail.status_id != ' . $status_result[0]['id'];
          unset($status_result[0]); */

        $status_qry->select('id');
        $status_qry->where('is_permanent', 1);
        $status_result = $status_qry->get();

        $status_ids = [];
        foreach ($status_result as $row) {
            $status_ids[] = $row->id;
        }
        //$status_id = $status_result[0]['id'];
        $status_not_knocks_clause = '';
        if (!empty($status_ids))
            $status_not_knocks_clause = " AND status_id NOT IN (" . implode(',', $status_ids) . ")";

        //}

        $lead_all_clauses = " $type_clause $status_not_knocks_clause $status_clause $lead_time_clause";
        $lead_time_knocks_clause = str_replace('lead', 'user_lead_knocks', $lead_time_clause);
        $lead_all_knocks_clauses = " $status_not_knocks_clause $status_knocks_clause $lead_time_knocks_clause";
//        $lead_results = \DB::select("SELECT `id` FROM `lead_detail` WHERE 1=1 $type_clause $status_clause");
//        $lead_id_collection = [];
//        foreach ($lead_results as $row)
//            $lead_id_collection[] = $row->id;

        $lead_id_collection = \DB::table('lead_detail')
                ->when($type_clause, function ($query) use ($type_clause) {
                    return $query->whereRaw($type_clause);
                })
                ->when($status_clause, function ($query) use ($status_clause) {
                    return $query->whereRaw($status_clause);
                })
                ->pluck('id')
                ->toArray();

        $lead_in_clause = '';
        if (!empty($lead_id_collection))
            $lead_in_clause = ' AND lead_id IN (' . implode(',', $lead_id_collection) . ') ';

        $followStatusApptRequest = FollowStatus::where('title', 'Appt Request')->first();

        $leadStatusApptRequest = Status::where('title', 'Appt Request')->where('tenant_id', $params['company_id'])->first();

        $followStatusApptNotKept = FollowStatus::where('title', 'Appt Kept')->first();

        $new_input = [];
        $followingLeads = getFollowingLeadAll($new_input);
        $new_input_id = [];
        foreach ($followingLeads as $lead) {
            if ($lead->lead_id != '') {
                $new_input_id['lead_ids'][] = $lead->lead_id;
            }
        }
        $final_leads = implode(',', $new_input_id['lead_ids']);
        if (isset($params['api_start_date']) AND isset($params['api_end_date'])) {
            $time_clause = "AND lead_history.created_at between '" . $params['api_start_date'] . "' and '" . $params['api_end_date'] . "' ";
        }

        $result = \DB::select("SELECT 
                    concat(user.first_name, ' ', user.last_name) as agent_name,
                    user.id as agent_id,
                    (SELECT count(*) as lead_count from user_lead_knocks where user_lead_knocks.user_id = user.id AND user_group_id = 2 $lead_all_knocks_clauses ) as lead_count,
                    (
            SELECT count(*) as appointment_count
            FROM lead_history 
            WHERE user.user_group_id != 3 AND lead_history.assign_id = user.id AND (lead_history.status_id = $leadStatusApptRequest->id OR lead_history.followup_status_id = $followStatusApptRequest->id)
            $time_clause
        ) as appointment_count,
        (SELECT count(*) as appointment_kept_count
            FROM lead_history
            WHERE lead_history.assign_id = user.id AND lead_history.lead_id IN ($final_leads) 
            AND lead_history.followup_status_id != 0 AND lead_history.status_id = 0 AND followup_status_id = $followStatusApptNotKept->id
            $time_clause
        ) as appointment_kept_count,
                    (SELECT sum(commission) as user_commission from user_commission where user_commission.user_id = user.id $commission_time_clause  $lead_in_clause) as commission_count,
                    (SELECT count(commission) as user_commission from user_commission LEFT JOIN commission_events ON commission_events.title = user_commission.commission_event
                    AND is_permanent = 1  $profit_tenant_clause   
                    where user_commission.user_id = user.id $commission_time_clause  AND user_commission.commission_event = 'profit'  $lead_in_clause 
                    ORDER BY commission_events.id limit 1) as commission_profit_count,
                    (SELECT count(commission) as user_commission from user_commission LEFT JOIN commission_events ON commission_events.title = user_commission.commission_event
                    AND is_permanent = 1 $commission_tenant_clause   
                    where user_commission.user_id = user.id $commission_time_clause AND user_commission.commission_event = 'contracts'  $lead_in_clause
                    ORDER BY commission_events.id desc limit 1) as commission_contract_count                    
                    FROM user            
                    WHERE 1 = 1 $tenant_clause $user_clause AND user.user_group_id != 4 AND  user.deleted_at IS NULL    
                    order by 2 desc");

        //        WHERE 1 = 1 $tenant_clause $user_clause AND user_group_id = 2 AND user.deleted_at IS NULL             



        $response = [];
        $response['lead_count'] = 0;
        $response['appointment_count'] = 0;
        $response['appointment_kept_count'] = 0;
        $response['commission_profit_count'] = 0;
        $response['commission_contract_count'] = 0;

        $commission_event_main = [];
        $commission_event_month_main = [];
        $knock_details_main = [];
        $commission_event_total = 0;
        $commission_event_month_total = 0;


        $userList = [];
        $commissionEvents = CommissionEvents::latest()->get();
        $status = Status::latest()->get();

//        echo "<pre>";
//        print_r($result);
//        exit;

        foreach ($result as $result_row => $row) {

            $userList[] = $row->agent_id;
            $tmp = [];


            $commission_event = [];
            $commission_event_month = [];
            $knock_details = [];
            $knock_name_details = [];
            $commission_event_name = [];

            $tmp['lead_count'] = $row->lead_count;
            $tmp['appointment_count'] = $row->appointment_count;
            $tmp['appointment_kept_count'] = $row->appointment_kept_count;
            $tmp['commission_count'] = (empty($row->commission_count)) ? 0 : $row->commission_count;
            $tmp['commission_profit_count'] = $row->commission_profit_count;
            $tmp['commission_contract_count'] = $commission_event;
            $tmp['commission_contract_count_name'] = $commission_event_name;
            $tmp['agent_name'] = $row->agent_name;
            $tmp['agent_id'] = $row->agent_id;

            $response['result'][] = $tmp;
            $response['lead_count'] += $row->lead_count;
            $response['appointment_count'] += $row->appointment_count;
            $response['appointment_kept_count'] += $row->appointment_kept_count;
            if (isset($row->commission_count)) {
                $response['commission_count'] += $row->commission_count;
            }

            $response['commission_profit_count'] += $row->commission_profit_count;
            $response['commission_contract_count'] += $row->commission_contract_count;
        }


        $userIds = explode(',', $params['user_id']);

        if (!empty($dateinput)) {
            $startDate = $dateinput->start;
            $endDate = $dateinput->end;
        } else {
            $startDate = '2018-01-01';
            $endDate = date('Y-m-d');
        }

        $startDate = dateTimezoneChangeNew($startDate . ' 00:00:00');
        $endDate = dateTimezoneChangeNew($endDate . ' 23:59:00');

        $startDate = date('Y-m-d H:i:s', strtotime($startDate));
        $endDate = date('Y-m-d H:i:s', strtotime($endDate));

        $commissionData = UserCommission::select("id",
                        "commission_event",
                        DB::raw("SUM(commission) as commission"),
                        DB::raw("DATE_FORMAT(created_at,'%b-%Y') as month_name")
                )
                ->whereBetween('created_at', [Carbon::parse($startDate), Carbon::parse($endDate)])
                ->groupBy(DB::raw("DATE_FORMAT(created_at,'%b-%Y')"), 'commission_event');



        if (!empty($userIds) && !empty($params['user_id'])) {
            $commissionData = $commissionData->wherein('user_id', $userIds);
        }

        $commissionData = $commissionData->get();

        $periodData = [];
        $period = new CarbonPeriod($startDate, '1 month', $endDate);
        foreach ($period as $date) {
            $periodData[$date->format('M-Y')] = 0;
        }

        $events = $commissionData->pluck('commission_event', 'commission_event')->toArray();

        $chartData = [];
        if (!empty($events)) {
            foreach ($events as $key => $value) {
                $chartData[$value] = [
                    'data' => $periodData,
                    'name' => $value,
                ];
            }

            foreach ($commissionData as $key => $value) {

                if (isset($chartData[$value->commission_event]['data'][$value->month_name])) {
                    $chartData[$value->commission_event]['data'][$value->month_name] = $value->commission;
                }

                $commission_event_month_total += $value->commission;
            }
        } else {
            $chartData[] = [
                'data' => $periodData,
                'name' => '',
            ];
        }

        $response['month_name'] = array_keys($periodData);
        $commission_event_month_main = array_values($chartData);

        $commissionUserData = UserCommission::select("id",
                        "commission_event",
                        "user_id",
                        DB::raw("SUM(commission) as commission"),
                )
                ->whereBetween('created_at', [Carbon::parse($startDate), Carbon::parse($endDate)])
                ->groupBy('user_id', 'commission_event');



        if (!empty($userIds) && !empty($params['user_id'])) {
            $commissionUserData = $commissionUserData->wherein('user_id', $userIds);
        }

        $commissionUserData = $commissionUserData->get();

        $eventsUser = $commissionUserData->pluck('commission_event', 'commission_event')->toArray();

        $eventsUserIds = $userList;

        if (empty($params['user_id'])) {
            $eventsUserIds = $commissionUserData->pluck('user_id', 'user_id')->toArray();
        }

        $userListData = [];

        foreach ($eventsUserIds as $key => $eventsUserId) {
            $userListData[$eventsUserId] = 0;
        }

        $chartUserData = [];
        if (!empty($eventsUser)) {
            foreach ($eventsUser as $key => $value) {
                $chartUserData[$value] = [
                    'data' => $userListData,
                    'name' => $value,
                ];
            }


            foreach ($commissionUserData as $key => $value) {

                if (isset($chartUserData[$value->commission_event]['data'][$value->user_id])) {
                    $chartUserData[$value->commission_event]['data'][$value->user_id] = $value->commission;
                    $commission_event_total += $value->commission;
                }
            }

            foreach ($chartUserData as $key => $value) {
                $chartUserData[$key]['data'] = array_values($value['data']);
                $chartUserData[$key]['name'] = $value['name'];
            }
        } else {
            $chartUserData[] = [
                'data' => $userListData,
                'name' => '',
            ];
        }

        $commission_event_main = array_values($chartUserData);


        $userNamesData = [];

        if (!empty($eventsUserIds)) {
            foreach ($eventsUserIds as $key => $id) {
                $user = User::find($id);

                $userNamesData[] = $user->first_name . ' ' . $user->last_name;
            }
        }


        $userLeadKnocksData = UserLeadKnocks::select("user_lead_knocks.id",
                        "user_lead_knocks.user_id",
                        "user_lead_knocks.status_id",
                        "status.title",
                        DB::raw("count(user_lead_knocks.status_id) as status_count"),
                )
                ->join('status', 'user_lead_knocks.status_id', 'status.id')
                ->join('user', 'user_lead_knocks.user_id', 'user.id')
                ->where('user_lead_knocks.status_id', '!=', 77)
                ->where('user.user_group_id', '!=', 3)
                ->whereBetween('user_lead_knocks.created_at', [Carbon::parse($startDate), Carbon::parse($endDate)])
                ->groupBy('user_lead_knocks.user_id', 'user_lead_knocks.status_id');

        $userLeadKnocksData = $userLeadKnocksData->get();

        $eventsTitle = $userLeadKnocksData->pluck('title', 'title')->toArray();

        $eventsUserKnocksIds = $userList;

        if (empty($params['user_id'])) {
            $eventsUserKnocksIds = $userLeadKnocksData->pluck('user_id', 'user_id')->toArray();
        }

        $userListKnocksData = [];

        foreach ($eventsUserKnocksIds as $key => $value) {
            $userListKnocksData[$value] = 0;
        }

        $chartUserKnocksData = [];
        if (!empty($userLeadKnocksData)) {
//             echo "<pre>";
            foreach ($eventsTitle as $key => $value) {
                $chartUserKnocksData[$value] = [
                    'data' => $userListKnocksData,
                    'name' => $value,
                ];
            }



            foreach ($userLeadKnocksData as $key => $value) {

                if (isset($chartUserKnocksData[$value->title]['data'][$value->user_id])) {
                    $chartUserKnocksData[$value->title]['data'][$value->user_id] = $value->status_count;
                }
            }

            $array1 = [];
            $totalChartCount = 0;
            foreach ($chartUserKnocksData as $data) {
                foreach ($data['data'] as $key => $data1) {
                    if (isset($array1[$key])) {
                        $array1[$key] = $data1 + $array1[$key];
                    } else {
                        $array1[$key] = $data1;
                    }

                    $totalChartCount = $data1 + $totalChartCount;
                }
            }

            arsort($array1);
            $userListNameKnocksData = [];
            foreach ($chartUserKnocksData as $k => $data) {
                $convertedArray = [];
                foreach ($array1 as $key => $value) {
                    if (isset($data['data'][$key])) {
                        $user = User::find($key);
                        $userListNameKnocksData[] = $user->first_name . ' ' . $user->last_name;
                        $convertedArray[$key] = $data['data'][$key];
                    }
                }
                $chartUserKnocksData[$k]['data'] = $convertedArray;
            }


            foreach ($chartUserKnocksData as $key => $value) {
                $chartUserKnocksData[$key]['data'] = array_values($value['data']);
                $chartUserKnocksData[$key]['name'] = $value['name'];
            }
        } else {
            $chartUserKnocksData[] = [
                'data' => $userListKnocksData,
                'name' => '',
            ];
        }

        $knock_details_main = array_values($chartUserKnocksData);


        if (!empty($dateinput)) {
            $startDate = $dateinput->start;
            $endDate = $dateinput->end;
        } else {
            $startDate = '2018-01-01';
            $endDate = date('Y-m-d');
        }

        $startDate = dateTimezoneChangeNew($startDate . ' 00:00:00');
        $endDate = dateTimezoneChangeNew($endDate . ' 23:59:00');

        $startDate = date('Y-m-d H:i:s', strtotime($startDate));
        $endDate = date('Y-m-d H:i:s', strtotime($endDate));

        $modal_user_clause = '';
        $db_user_clause = '';
        $db_user_clause_for_knock = '';
        if (!empty($params['user_id'])) {
            $modal_user_clause = '  lead_history.assign_id IN (' . $params['user_id'] . ')';
            $db_user_clause = 'AND  lead_history.assign_id IN (' . $params['user_id'] . ')';
            $db_user_clause_for_knock = ' user_lead_knocks.user_id IN (' . $params['user_id'] . ')';
        }

        $total_knocks_count = UserLeadKnocks::whereBetween('user_lead_knocks.created_at', [Carbon::parse($startDate), Carbon::parse($endDate)])
                ->join('user', 'user_lead_knocks.user_id', 'user.id')
                ->where('user_lead_knocks.status_id', '!=', 77)
                ->where('user.user_group_id', '!=', 3)
                ->when($db_user_clause_for_knock, function ($query, $db_user_clause_for_knock) {
                    return $query->whereRaw($db_user_clause_for_knock);
                })
                ->count();

        $followStatusPurchased = FollowStatus::where('title', 'Purchased')->first();

        $followStatusContract = FollowStatus::where('title', 'Contract')->first();






        $idsFollow = [];

        if (!empty($followStatusPurchased)) {
            array_push($idsFollow, $followStatusPurchased->id);
        }

        if (!empty($followStatusApptNotKept)) {
            array_push($idsFollow, $followStatusApptNotKept->id);
        }

        if (!empty($followStatusApptRequest)) {
            array_push($idsFollow, $followStatusApptRequest->id);
        }

        if (!empty($followStatusContract)) {
            array_push($idsFollow, $followStatusContract->id);
        }

        $idsFollow = implode(',', $idsFollow);

        $month_clause .= " AND lead_history.followup_status_id IN ({$idsFollow}) ";

        $time_clause = '';
        $contract_time_clause = '';
        $purchase_time_clause = '';

        if (!empty($dateinput)) {

            $time_clause = "AND lead_history.created_at between '" . $startDate . "' and '" . $endDate . "' ";
            $contract_time_clause = "(
                        following_leads.contract_date BETWEEN '" . $startDate . "' AND '" . $endDate . "'
                        OR
                        purchase_leads.contract_date BETWEEN '" . $startDate . "' AND '" . $endDate . "'
                    )";
            $purchase_time_clause = "(
                STR_TO_DATE(following_custom_fields.field_value, '%m/%d/%Y') BETWEEN '" . $startDate . "' AND '" . $endDate . "'
                OR
                STR_TO_DATE(purchase_custom_fields.field_value, '%m/%d/%Y') BETWEEN '" . $startDate . "' AND '" . $endDate . "'
            )";
        }

        $total_result = \DB::select("SELECT lead_history.lead_id as lead_id,lead_history.followup_status_id as his_status_id,lead_history.created_at,lead_history.followup_status_id FROM  lead_history
                                      WHERE followup_status_id != 0 OR status_id != 0 $month_clause ORDER BY ID ASC");

        $followStatusCount = count($total_result);

        $resultPurchasedCount = 0;
        $resultPurchasedByCount = 0;

        if (!is_null($followStatusPurchased)) {

//            $time_clause = '';
//            $time_clause1 = '';
//
//            if (!empty($dateinput)) {
//                $time_clause = "lead_history.created_at between '" . $startDate . "' and '" . $endDate . "' ";
//            }
//
//            $followingLeads = getFollowingLeadAll($new_input);
//            $new_input_id = [];
//            foreach ($followingLeads as $lead) {
//                $new_input_id['lead_ids'][] = $lead->lead_id;
//            }
//
//            $followStatusApptNotKept = FollowStatus::where('title', 'Purchased')->first();
//
//            $DisplayedPerScreen = settingForRecordsDisplayedPerScreen();
//
//            $PurchaseStatusleadIds = DB::table('lead_history')
//                            ->select('lead_history.lead_id')
//                            ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
//                            ->whereIn('lead_history.lead_id', $new_input_id['lead_ids'])
//                            ->where('lead_history.followup_status_id', '!=', 0)
//                            ->where('lead_history.status_id', 0)
//                            ->where('lead_history.followup_status_id', $followStatusApptNotKept->id)
//                            ->when($time_clause, function ($query, $time_clause) {
//                                return $query->whereRaw($time_clause);
//                            })
//                            ->when($modal_user_clause, function ($query, $modal_user_clause) {
//                                return $query->whereRaw($modal_user_clause);
//                            })
//                            ->orderBy('lead_history.ID', 'ASC')
//                            ->pluck('lead_history.lead_id')->toArray();
//
//            $time_clause = '';
//            $time_clause1 = '';
//
//            if (!empty($request->date)) {
//                $date = json_decode($request->date);
//                $startDate = Carbon::createFromFormat('Y-m-d', $date->start)->format('Y/m/d');
//                $endDate = Carbon::createFromFormat('Y-m-d', $date->end)->format('Y/m/d');
//                $startDate = dateTimezoneChangeNew($startDate . ' 00:00:00');
//                $endDate = dateTimezoneChangeNew($endDate . ' 23:59:59');
//                $startDate = date('Y-m-d H:i:s', strtotime($startDate));
//                $endDate = date('Y-m-d H:i:s', strtotime($endDate));
//                $time_clause1 = "(
//                STR_TO_DATE(following_custom_fields.field_value, '%m/%d/%Y') BETWEEN '" . $startDate . "' AND '" . $endDate . "'
//                OR
//                STR_TO_DATE(purchase_custom_fields.field_value, '%m/%d/%Y') BETWEEN '" . $startDate . "' AND '" . $endDate . "'
//            )";
//            }
//
//            $followingLeads = DB::table('following_leads')
//                    ->select('following_leads.lead_id')
//                    ->join('following_custom_fields', 'following_leads.id', 'following_custom_fields.followup_lead_id')
//                    ->where('following_custom_fields.followup_view_id', '=', 29)
//                    ->where('following_custom_fields.field_value', '!=', null)
//                    ->whereRaw("STR_TO_DATE(following_custom_fields.field_value, '%m/%d/%Y') BETWEEN '$startDate' AND '$endDate'")
//                    ->orderBy('following_leads.lead_id', 'desc');
//            $followingLeads = $followingLeads->get();
//
//            $leadIds = $followingLeads->pluck('lead_id')->toArray();
//
//
//
//            $purchaseLeads = DB::table('purchase_leads')
//                    ->select('purchase_leads.lead_id')
//                    ->join('purchase_custom_fields', 'purchase_leads.id', 'purchase_custom_fields.followup_lead_id')
//                    ->where('purchase_custom_fields.followup_view_id', '=', 29)
//                    ->where('purchase_custom_fields.field_value', '!=', null)
//                    ->whereRaw("STR_TO_DATE(purchase_custom_fields.field_value, '%m/%d/%Y') BETWEEN '$startDate' AND '$endDate'")
//                    ->orderBy('purchase_leads.lead_id', 'desc');
//            $purchaseLeads = $purchaseLeads->get();
//
//            $pleadIds = $purchaseLeads->pluck('lead_id')->toArray();
//
//            $leadIds = array_merge($leadIds, $PurchaseStatusleadIds);
//
//            $mergedArray = array_merge($leadIds, $pleadIds);
//
//            $uniqueValues = array_values(array_unique($mergedArray));
//
//
//            $resultPurchased = $dashboardData = DB::table('lead_history')
//                    ->select('lead_history.*',
//                            'lead_history.followup_status_id as his_status_id',
//                            'lead_detail.title as lead_title',
//                            'lead_detail.formatted_address as lead_formatted_address',
//                            'lead_detail.address as lead_address', 'user.first_name as invester_first_name',
//                            'user.last_name as invester_last_name')
//                    ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
//                    ->join('following_leads', 'lead_history.lead_id', 'following_leads.lead_id')
//                    ->join('user', 'following_leads.investor_id', 'user.id')
//                    ->whereIn('lead_history.lead_id', $uniqueValues)
//                    ->orderByDesc('id')
//                    ->groupBy('lead_id')
//                    ->get();
//
//            $uniqueValuesPurchaseIds = $uniqueValues;

//            $followingLeads = DB::table('following_leads')
//                    ->select('following_leads.lead_id')
//                    ->join('following_custom_fields', 'following_leads.id', 'following_custom_fields.followup_lead_id')
//                    ->where('following_custom_fields.followup_view_id', '=', 29)
//                    ->where('following_custom_fields.field_value', '!=', null)
//                    ->where('following_leads.is_expired', '=', 0)
//                    ->orderBy('following_leads.lead_id', 'desc');
//            $followingLeads = $followingLeads->get();
//
//            $leadIds = $followingLeads->pluck('lead_id')->toArray();
//
//            $purchaseLeads = DB::table('purchase_leads')
//                    ->select('purchase_leads.lead_id')
//                    ->join('purchase_custom_fields', 'purchase_leads.id', 'purchase_custom_fields.followup_lead_id')
//                    ->where('purchase_custom_fields.followup_view_id', '=', 29)
//                    ->where('purchase_custom_fields.field_value', '!=', null)
//                    ->where('purchase_leads.is_expired', '=', 0)
//                    ->orderBy('purchase_leads.lead_id', 'desc');
//            $purchaseLeads = $purchaseLeads->get();
//
//            $pleadIds = $purchaseLeads->pluck('lead_id')->toArray();
//
//            $mergedArray = array_merge($leadIds, $pleadIds);
//
//            $uniqueValues = array_unique($mergedArray);
//
//            $resultPurchased = $dashboardData = DB::table('lead_history')
//                    ->select('lead_history.*',
//                            'following_custom_fields.field_value as purchase_date',
//                            'purchase_custom_fields.field_value as p_purchase_date',
//                            'lead_history.followup_status_id as his_status_id',
//                            'lead_detail.title as lead_title',
//                            'lead_detail.formatted_address as lead_formatted_address',
//                            'user.first_name as invester_first_name',
//                            'user.last_name as invester_last_name'
//                            )
//                    ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
//                    ->join('following_leads', 'lead_history.lead_id', 'following_leads.lead_id')
//                    
//                    ->join('user', 'following_leads.investor_id', 'user.id')
//                    
//                    ->join('following_custom_fields', 'following_leads.id', 'following_custom_fields.followup_lead_id')
//                    ->where('following_custom_fields.followup_view_id', '=', 29)
//                    ->join('purchase_leads', 'lead_history.lead_id', 'purchase_leads.lead_id')
//                    ->join('purchase_custom_fields', 'purchase_leads.id', 'purchase_custom_fields.followup_lead_id')
//                    ->where('purchase_custom_fields.followup_view_id', '=', 29)
//                    ->where('following_leads.is_lead_up', 0)
//                    ->whereIn('lead_history.lead_id', $uniqueValues)
//                    ->when($purchase_time_clause, function ($query, $purchase_time_clause) {
//                        return $query->whereRaw($purchase_time_clause);
//                    })
//                    ->when($modal_user_clause, function ($query, $modal_user_clause) {
//                        return $query->whereRaw($modal_user_clause);
//                    })
//                    ->orderBy('purchase_custom_fields.field_value', 'DESC')
//                    ->orderBy('following_custom_fields.field_value', 'DESC')
//                    ->groupBy('lead_id')
//                    ->get();

            $followStatusApptNotKept = FollowStatus::where('title', 'Purchased')->first();
            $followupStatus = [];
            $followupStatus[] = $followStatusApptNotKept->id;                        
            $start_date = $startDate;        
            $end_date = $endDate;
        
            $resultPurchased = DB::table('lead_history as lh')
                ->leftJoin('purchase_leads as pl', 'lh.lead_id', '=', 'pl.lead_id')
                ->leftJoin('following_leads as fl', 'lh.lead_id', '=', 'fl.lead_id')
                ->join('lead_detail as ld', 'lh.lead_id', '=', 'ld.id')
                ->join('user', 'fl.investor_id', 'user.id')
                ->leftJoin('following_custom_fields', function($join) {
                    $join->on('fl.id', '=', 'following_custom_fields.followup_lead_id')
                    ->where('following_custom_fields.followup_view_id', 29);
                })
                ->leftJoin('purchase_custom_fields', function($join) {
                    $join->on('pl.id', '=', 'purchase_custom_fields.followup_lead_id')
                    ->where('purchase_custom_fields.followup_view_id', 29);
                })
                ->select('lh.*', 'fl.contract_date as contract_date', 'pl.contract_date as p_contract_date',
                        'fl.id as fl_id',
                        'ld.title as lead_title',
                        'ld.formatted_address as lead_formatted_address',
                        'ld.address as lead_address',
                        'lh.followup_status_id as his_status_id',
                        'purchase_custom_fields.field_value as pl_purchase_date',
                        'following_custom_fields.field_value as fl_purchase_date',
                        'user.first_name as invester_first_name',
                        'user.last_name as invester_last_name'
                )
                ->where(function ($query) use ($start_date, $end_date, $followupStatus) {
                    $query->where(function ($subquery) use ($start_date, $end_date, $followupStatus) {
                        $subquery->whereIn('lh.followup_status_id', $followupStatus)
                        ->whereBetween(DB::raw('DATE(lh.created_at)'), [$start_date, $end_date]);
                    })->where(function ($subquery) use ($start_date, $end_date) {
                        $subquery->where(function ($subquery) use ($start_date, $end_date) {
                            $subquery->whereNotNull('following_custom_fields.field_value')
                            ->whereBetween(
                                    DB::raw("STR_TO_DATE(following_custom_fields.field_value, '%m/%d/%Y')"),
                                    [date('Y-m-d', strtotime($start_date)), date('Y-m-d', strtotime($end_date))]
                            );
                        })->orWhere(function ($subquery) use ($start_date, $end_date) {
                            $subquery->whereNotNull('purchase_custom_fields.field_value')
                            ->whereBetween(
                                    DB::raw("STR_TO_DATE(purchase_custom_fields.field_value, '%m/%d/%Y')"),
                                    [date('Y-m-d', strtotime($start_date)), date('Y-m-d', strtotime($end_date))]
                            );
                        });
                    });
                })
                ->groupBy('lh.lead_id')
                ->get();
                
                
                
            $resultPurchasedCount = count($resultPurchased);
            if ($resultPurchasedCount != 0) {
                $resultPurchasedByCount = $total_knocks_count / $resultPurchasedCount;
            }
        }

        $resultApptNotKeptCount = 0;
        $resultApptNotKeptByCount = 0;
        $followStatusApptNotKept = FollowStatus::where('title', 'Appt Kept')->first();
        if (!is_null($followStatusApptNotKept)) {
            if (!empty($dateinput)) {
                $time_clause = "AND lead_history.created_at between '" . $startDate . "' and '" . $endDate . "' ";
            }

            $resultApptNotKept = \DB::select("SELECT * FROM  lead_history
                                        WHERE lead_history.lead_id IN ($final_leads) AND  lead_history.followup_status_id != 0 AND lead_history.status_id = 0 AND followup_status_id = $followStatusApptNotKept->id $month_clause $time_clause $db_user_clause ORDER BY ID ASC");
            $resultApptNotKeptCount = count($resultApptNotKept);


            if ($resultApptNotKeptCount != 0) {
                $resultApptNotKeptByCount = $total_knocks_count / $resultApptNotKeptCount;
            }
        }

        $resultApptRequestCount = 0;
        $resultApptRequestByCount = 0;

        if (!is_null($followStatusApptRequest)) {
            if (!empty($dateinput)) {
                $time_clause = "AND lead_history.created_at between '" . $startDate . "' and '" . $endDate . "' ";
            }

            $resultApptRequest = \DB::select("SELECT 
    lead_history.status_id, 
    lead_history.followup_status_id as his_status_id,
    lead_history.created_at,
    lead_history.assign_id,
    lead_history.followup_status_id
FROM lead_history
LEFT JOIN user ON user.id = lead_history.assign_id
WHERE lead_history.status_id = $leadStatusApptRequest->id 
    $time_clause $db_user_clause AND user.user_group_id = 2
");
//WHERE lead_history.lead_id IN ($final_leads) AND
            $fresultApptRequest = \DB::select("SELECT 
    lead_history.status_id, 
    lead_history.followup_status_id as his_status_id,
    lead_history.created_at,
    lead_history.assign_id,
    lead_history.followup_status_id
FROM lead_history
LEFT JOIN user ON user.id = lead_history.assign_id
WHERE lead_history.lead_id IN ($final_leads) 
    AND user.user_group_id = 2 AND lead_history.followup_status_id = $followStatusApptRequest->id
    $time_clause $db_user_clause");


            $total_count = count($resultApptRequest) + count($fresultApptRequest);
            $resultApptRequestCount = $total_count;

            if ($resultApptRequestCount != 0) {
                $resultApptRequestByCount = $total_knocks_count / $resultApptRequestCount;
            }
        }

        $resultContractCount = 0;
        $resultContractByCount = 0;
        // old 1 aug 2023
//        if(!is_null($followStatusContract)){            
//            $resultContract = \DB::select("SELECT lead_history.followup_status_id as his_status_id,lead_history.created_at,lead_history.followup_status_id FROM  lead_history
//                                        WHERE lead_history.lead_id IN ($final_leads) AND lead_history.followup_status_id != 0 AND followup_status_id = $followStatusContract->id $month_clause $time_clause  ORDER BY ID ASC");               
//            $resultContractCount = count($resultContract);
//            if($resultContractCount != 0){
//               $resultContractByCount = $total_knocks_count / $resultContractCount;
//            }
//        }

        if (!is_null($followStatusContract)) {

            if (!empty($dateinput)) {
                $time_clause = "lead_history.created_at between '" . $startDate . "' and '" . $endDate . "' ";
            }

//            $followStatusContract = FollowStatus::where('title', 'Contract')->first();
//
//            $contractStatusleadIds = DB::table('lead_history')
//                            ->select('lead_history.lead_id')
//                            ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
//                            ->whereIn('lead_history.lead_id', $new_input_id['lead_ids'])
//                            ->where('lead_history.followup_status_id', '!=', 0)
//                            ->where('lead_history.status_id', 0)
//                            ->where('lead_history.followup_status_id', $followStatusContract->id)
//                            ->when($time_clause, function ($query, $time_clause) {
//                                return $query->whereRaw($time_clause);
//                            })
//                            ->orderBy('lead_history.ID', 'ASC')
//                            ->pluck('lead_history.lead_id')->toArray();
//
//            $time_clause = '';
//            $time_clause1 = '';
//
//            $followingLeads = DB::table('following_leads')
//                    ->select('following_leads.lead_id')
//                    ->join('following_custom_fields', 'following_leads.id', 'following_custom_fields.followup_lead_id')
//                    ->where('following_leads.contract_date', '!=', null)
//                    ->where('following_leads.is_expired', '=', 0)
//                    ->whereRaw("STR_TO_DATE(following_custom_fields.field_value, '%m/%d/%Y') BETWEEN '$startDate' AND '$endDate'")
//                    ->orderBy('following_leads.lead_id', 'desc');
//            $followingLeads = $followingLeads->get();
//
//            $leadIds = $followingLeads->pluck('lead_id')->toArray();
//
//            $purchaseLeads = DB::table('purchase_leads')
//                    ->join('purchase_custom_fields', 'purchase_leads.id', 'purchase_custom_fields.followup_lead_id')
//                    ->select('purchase_leads.lead_id')
//                    ->where('purchase_leads.contract_date', '!=', null)
//                    ->where('purchase_leads.is_expired', '=', 0)
//                    ->whereRaw("STR_TO_DATE(purchase_custom_fields.field_value, '%m/%d/%Y') BETWEEN '$startDate' AND '$endDate'")
//                    ->orderBy('purchase_leads.lead_id', 'desc');
//
//            $purchaseLeads = $purchaseLeads->get();
//
//            $pleadIds = $purchaseLeads->pluck('lead_id')->toArray();
//
//            $leadIds = array_merge($leadIds, $contractStatusleadIds);
//
//            $mergedArray = array_merge($leadIds, $pleadIds);
//
//            $uniqueIds = array_unique($mergedArray);
//
//
//            $resultContract = $dashboardData = DB::table('lead_history')
//                    ->select('lead_history.*', 'following_leads.contract_date as contract_date',
//                            'purchase_leads.contract_date as p_contract_date',
//                            'following_leads.id as fl_id', 'lead_detail.title as lead_title', 'lead_detail.formatted_address as lead_formatted_address', 'following_leads.contract_date as contract_date', 'lead_history.followup_status_id as his_status_id',
//                            'user.first_name as invester_first_name',
//                            'user.last_name as invester_last_name')
//                    ->leftJoin('following_leads', 'lead_history.lead_id', '=', 'following_leads.lead_id')
//                    ->leftJoin('purchase_leads', 'purchase_leads.lead_id', '=', 'following_leads.lead_id')
//                    ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
//                    ->join('user', 'following_leads.investor_id', 'user.id')
//                    ->where('following_leads.is_lead_up', 0)
//                    ->whereIn('lead_history.lead_id', $uniqueIds)
//                    ->whereNotIn('lead_history.lead_id', $uniqueValuesPurchaseIds)
//                    ->orderBy('purchase_leads.contract_date', 'DESC')
//                    ->orderBy('following_leads.contract_date', 'DESC')
//                    ->groupBy('lead_id')
//                    ->get();
            $start_date = date('Y-m-d', strtotime($startDate));
            $end_date = date('Y-m-d', strtotime($endDate));

            $followupStatus = [];
            $followupStatus[] = $followStatusPurchased->id;
            $followupStatus[] = $followStatusContract->id;

            $PurchasedId = $followStatusPurchased->id;
            $ContractId = $followStatusContract->id;

            $resultContract = DB::table('lead_history as lh')
                    ->leftJoin('purchase_leads as pl', 'lh.lead_id', '=', 'pl.lead_id')
                    ->leftJoin('following_leads as fl', 'lh.lead_id', '=', 'fl.lead_id')
                    ->join('lead_detail as ld', 'lh.lead_id', '=', 'ld.id')
                    ->join('user', 'fl.investor_id', 'user.id')
                    ->leftJoin('following_custom_fields', function($join) {
                        $join->on('fl.id', '=', 'following_custom_fields.followup_lead_id')
                        ->where('following_custom_fields.followup_view_id', 29);
                    })
                    ->leftJoin('purchase_custom_fields', function($join) {
                        $join->on('pl.id', '=', 'purchase_custom_fields.followup_lead_id')
                        ->where('purchase_custom_fields.followup_view_id', 29);
                    })
                    ->select('lh.*',
                            'fl.contract_date as contract_date', 'pl.contract_date as p_contract_date',
                            'fl.id as fl_id',
                            'ld.title as lead_title',
                            'ld.formatted_address as lead_formatted_address',
                            'ld.address as lead_address',
                            'lh.followup_status_id as his_status_id',
                            'purchase_custom_fields.field_value as pl_purchase_date',
                            'following_custom_fields.field_value as fl_purchase_date',
                            'user.first_name as invester_first_name',
                            'user.last_name as invester_last_name'
                    )
                    ->where(function ($query) use ($start_date, $end_date, $PurchasedId) {
                        $query->where(function ($subquery) use ($start_date, $end_date, $PurchasedId) {
                            $subquery->where('lh.followup_status_id', '=', $PurchasedId)
                            ->whereBetween(DB::raw('DATE(lh.created_at)'), [$start_date, $end_date])->where(function ($subquery) use ($start_date, $end_date, $ContractId) {
                            $subquery->where('lh.followup_status_id', $ContractId)
                            ->whereBetween(DB::raw('DATE(lh.created_at)'), [$start_date, $end_date]);
                        })->where(function ($subquery) use ($start_date, $end_date) {
                            $subquery->where(function ($subquery) use ($start_date, $end_date) {
                                $subquery->whereNotNull('pl.contract_date')
                                ->whereBetween('pl.contract_date', [$start_date, $end_date]);
                            })->orWhere(function ($subquery) use ($start_date, $end_date) {
                                $subquery->whereNotNull('fl.contract_date')
                                ->whereBetween('fl.contract_date', [$start_date, $end_date]);
                            });
                        });
                        })->where(function ($subquery) use ($start_date, $end_date) {
                            $subquery->where(function ($subquery) use ($start_date, $end_date) {
                                $subquery->whereNotNull('following_custom_fields.field_value')
                                ->whereBetween(
                                        DB::raw("STR_TO_DATE(following_custom_fields.field_value, '%m/%d/%Y')"),
                                        [date('Y-m-d', strtotime($start_date)), date('Y-m-d', strtotime($end_date))]
                                );
                            })->orWhere(function ($subquery) use ($start_date, $end_date) {
                                $subquery->whereNotNull('purchase_custom_fields.field_value')
                                ->whereBetween(
                                        DB::raw("STR_TO_DATE(purchase_custom_fields.field_value, '%m/%d/%Y')"),
                                        [date('Y-m-d', strtotime($start_date)), date('Y-m-d', strtotime($end_date))]
                                );
                            });
                        });
                    })
                    ->orWhere(function ($query) use ($start_date, $end_date, $ContractId) {
                        $query->where(function ($subquery) use ($start_date, $end_date, $ContractId) {
                            $subquery->where('lh.followup_status_id', $ContractId)
                            ->whereBetween(DB::raw('DATE(lh.created_at)'), [$start_date, $end_date]);
                        })->where(function ($subquery) use ($start_date, $end_date) {
                            $subquery->where(function ($subquery) use ($start_date, $end_date) {
                                $subquery->whereNotNull('pl.contract_date')
                                ->whereBetween('pl.contract_date', [$start_date, $end_date]);
                            })->orWhere(function ($subquery) use ($start_date, $end_date) {
                                $subquery->whereNotNull('fl.contract_date')
                                ->whereBetween('fl.contract_date', [$start_date, $end_date]);
                            });
                        });
                    })
                    ->orderBy('pl.contract_date', 'DESC')
                    ->orderBy('fl.contract_date', 'DESC')
                    ->groupBy('lh.lead_id')
                    ->get();
                    
//            $resultContract = DB::table('lead_history as lh')
//                    ->leftJoin('purchase_leads as pl', 'lh.lead_id', '=', 'pl.lead_id')
//                    ->leftJoin('following_leads as fl', 'lh.lead_id', '=', 'fl.lead_id')
//                    ->join('lead_detail as ld', 'lh.lead_id', '=', 'ld.id')
//                    ->join('user', 'fl.investor_id', 'user.id')
//                    ->leftJoin('following_custom_fields', function($join) {
//                        $join->on('fl.id', '=', 'following_custom_fields.followup_lead_id')
//                        ->where('following_custom_fields.followup_view_id', 29);
//                    })
//                    ->leftJoin('purchase_custom_fields', function($join) {
//                        $join->on('pl.id', '=', 'purchase_custom_fields.followup_lead_id')
//                        ->where('purchase_custom_fields.followup_view_id', 29);
//                    })
//                    ->select('lh.*', 'fl.contract_date as contract_date', 'pl.contract_date as p_contract_date',
//                            'fl.id as fl_id',
//                            'ld.title as lead_title',
//                            'ld.formatted_address as lead_formatted_address',
//                            'ld.address as lead_address',
//                            'lh.followup_status_id as his_status_id',
//                            'purchase_custom_fields.field_value as pl_purchase_date',
//                            'following_custom_fields.field_value as fl_purchase_date',
//                            'user.first_name as invester_first_name',
//                            'user.last_name as invester_last_name'
//                    )
//                    ->where(function ($query) use ($start_date, $end_date, $followupStatus) {
//                        $query->where(function ($subquery) use ($start_date, $end_date) {
//                            $subquery->whereNotNull('pl.contract_date')
//                            ->whereBetween(DB::raw('DATE(pl.contract_date)'), [$start_date, $end_date]);
//                        })->orWhere(function ($subquery) use ($start_date, $end_date) {
//                            $subquery->whereNotNull('fl.contract_date')
//                            ->whereBetween(DB::raw('DATE(fl.contract_date)'), [$start_date, $end_date]);
//                        })->orWhere(function ($subquery) use ($start_date, $end_date, $followupStatus) {
//                            $subquery->whereIn('lh.followup_status_id', $followupStatus)
//                            ->whereBetween(DB::raw('DATE(lh.created_at)'), [$start_date, $end_date]);
//                        })->orWhere(function ($subquery) use ($start_date, $end_date) {
//                            $subquery->whereNotNull('following_custom_fields.field_value')
//                            ->whereBetween(DB::raw('DATE(following_custom_fields.field_value)'), [$start_date, $end_date]);
//                        })->orWhere(function ($subquery) use ($start_date, $end_date) {
//                            $subquery->whereNotNull('purchase_custom_fields.field_value')
//                            ->whereBetween(DB::raw('DATE(purchase_custom_fields.field_value)'), [$start_date, $end_date]);
//                        });
//                    })
//                    ->groupBy('lh.lead_id')
//                    ->get();
                    
                    
//            $followingLeads = DB::table('following_leads')
//                    ->select('following_leads.lead_id')
//                    ->where('following_leads.contract_date', '!=', null)
//                    ->where('following_leads.is_expired', '=', 0)
//                    ->orderBy('following_leads.lead_id', 'desc');
//            $followingLeads = $followingLeads->get();
//
//            $leadIds = $followingLeads->pluck('lead_id')->toArray();
//
//            $purchaseLeads = DB::table('purchase_leads')
//                    ->select('purchase_leads.lead_id')
//                    ->where('purchase_leads.contract_date', '!=', null)
//                    ->where('purchase_leads.is_expired', '=', 0)
//                    ->orderBy('purchase_leads.lead_id', 'desc');
//            $purchaseLeads = $purchaseLeads->get();
//
//            $pleadIds = $purchaseLeads->pluck('lead_id')->toArray();
//
//            $mergedArray = array_merge($leadIds, $pleadIds);
//
//            $uniqueIds = array_unique($mergedArray);
//
//            $resultContract = $dashboardData = DB::table('lead_history')
//                    ->select('lead_history.*', 'following_leads.contract_date as contract_date',
//                            'purchase_leads.contract_date as p_contract_date',
//                            'following_leads.id as fl_id', 'lead_detail.title as lead_title', 'lead_detail.formatted_address as lead_formatted_address', 'following_leads.contract_date as contract_date', 'lead_history.followup_status_id as his_status_id',
//                            'user.first_name as invester_first_name',
//                            'user.last_name as invester_last_name')
//                    ->leftJoin('following_leads', 'lead_history.lead_id', '=', 'following_leads.lead_id')
//                    ->leftJoin('purchase_leads', 'purchase_leads.lead_id', '=', 'following_leads.lead_id')
//                    ->join('user', 'following_leads.investor_id', 'user.id')
//                    ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
//                    ->whereIn('lead_history.lead_id', $uniqueIds)
//                    ->when($contract_time_clause, function ($query, $contract_time_clause) {
//                        return $query->whereRaw($contract_time_clause);
//                    })
//                    ->when($modal_user_clause, function ($query, $modal_user_clause) {
//                        return $query->whereRaw($modal_user_clause);
//                    })
//                    ->where('following_leads.is_lead_up', 0)
//                    ->orderBy('purchase_leads.contract_date', 'DESC')
//                    ->orderBy('following_leads.contract_date', 'DESC')
//                    ->groupBy('lead_id')
//                    ->get();
//                    DB::table('lead_history')
//                    ->select('lead_history.*', 'following_leads.contract_date as contract_date', 'following_leads.id as fl_id', 'lead_detail.title as lead_title', 'lead_detail.formatted_address as lead_formatted_address', 'following_leads.contract_date as contract_date', 'lead_history.followup_status_id as his_status_id')
//                    ->leftJoin('following_leads', 'lead_history.lead_id', '=', 'following_leads.lead_id')
//                    ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
//                    ->whereIn('lead_history.lead_id', $new_input_id['lead_ids'])
//                    ->where('lead_history.followup_status_id', '!=', 0)
//                    ->where('lead_history.followup_status_id', $followStatusContract->id)
//                    ->when($contract_time_clause, function ($query, $contract_time_clause) {
//                        return $query->whereRaw($contract_time_clause);
//                    })
//                    ->when($modal_user_clause, function ($query, $modal_user_clause) {
//                        return $query->whereRaw($modal_user_clause);
//                    })
//                    ->orderBy('lead_history.id', 'ASC')
//                    ->get();


            $resultContractCount = count($resultContract);
            if ($resultContractCount != 0) {
                $resultContractByCount = $total_knocks_count / $resultContractCount;
            }
        }

        $resultMainPurchased = '0%';
        if ($resultPurchasedCount != 0 && $total_knocks_count != 0) {
            info($resultPurchasedCount);
            info($total_knocks_coun);
            $resultMainPurchased = floatval(number_format(($resultPurchasedCount * 100 ) / $total_knocks_count, 2)) . '%';
        }

        $resultMainContract = '0%';
        if ($resultContractCount != 0 && $total_knocks_count != 0) {
            $resultMainContract = floatval(number_format(($resultContractCount * 100 ) / $total_knocks_count, 2)) . '%';
        }

        $resultMainApptRequest = '0%';
        if ($resultApptRequestCount != 0 && $total_knocks_count != 0) {
            $resultMainApptRequest = floatval(number_format(($resultApptRequestCount * 100) / $total_knocks_count, 2)) . '%';
        }

        $resultMainApptNotKept = '0%';
        if ($resultApptNotKeptCount != 0 && $total_knocks_count != 0) {
            $resultMainApptNotKept = floatval(number_format(($resultApptNotKeptCount * 100 ) / $total_knocks_count, 2)) . '%';
        }


        $response['commission_event_main'] = $commission_event_main;
        $response['total_knocks_count'] = $total_knocks_count;

        foreach ($resultPurchased as $key => $purchaseData) {
            $purchaeData = \App\Models\PurchaseLead::where('lead_id', '=', $purchaseData->lead_id)
                    ->join('purchase_custom_fields', 'purchase_leads.id', 'purchase_custom_fields.followup_lead_id')
                    ->where('purchase_custom_fields.followup_view_id', '=', 29)
                    ->first();
            if (!empty($purchaeData->field_value)) {
                $resultPurchased[$key]->purchase_date = $purchaeData->field_value;
            } else {
                $FollowingLead = \App\Models\FollowingLead::where('lead_id', '=', $purchaseData->lead_id)
                        ->join('following_custom_fields', 'following_leads.id', 'following_custom_fields.followup_lead_id')
                        ->where('following_custom_fields.followup_view_id', '=', 29)
                        ->first();
                if (!empty($FollowingLead->field_value)) {
                    $resultPurchased[$key]->purchase_date = $FollowingLead->field_value;
                } else {
                    $resultPurchased[$key]->purchase_date = date('m/d/Y', strtotime($purchaseData->created_at));
                }
            }
        }

        $response['resultPurchased'] = $resultPurchased;
        $response['resultContract'] = $resultContract;
        $response['result_main_purchased'] = $resultMainPurchased;
        $response['result_main_purchased_count'] = number_format($resultPurchasedCount);
        $response['result_main_purchased_by_count'] = number_format($resultPurchasedByCount);

        $response['result_main_apptnotkept'] = $resultMainApptNotKept;
        $response['result_main_apptnotkept_count'] = number_format($resultApptNotKeptCount);
        $response['result_main_apptnotkept_by_count'] = number_format($resultApptNotKeptByCount);
        $response['result_main_apptrequest'] = $resultMainApptRequest;
        $response['result_main_apptrequest_count'] = number_format($resultApptRequestCount);
        $response['result_main_apptrequest_by_count'] = number_format($resultApptRequestByCount);
        $response['result_main_contract'] = $resultMainContract;
        $response['result_main_contract_count'] = number_format($resultContractCount);
        $response['result_main_contract_by_count'] = number_format($resultContractByCount);
        $response['commission_event_users'] = $userNamesData;
        $response['knocks_event_users'] = $userListNameKnocksData;
        $response['totalChartCount'] = $totalChartCount;
        $response['commission_event_month'] = $commission_event_month_main;
        $response['knock_details_main'] = $knock_details_main;
        $response['commission_event_total'] = $commission_event_total;
        $response['commission_event_month_total'] = $commission_event_month_total;

        return $response;
    }

    public static function leadStatusUserReport($params) {
        $month_clause = '';
        $status_clause = '';
        $target_user_clause = '';

        if (!empty($params['month']))
            $month_clause .= " AND lead_history.created_at LIKE '{$params['month']}%' ";
        if (!empty($params['target_user_id'])) {
            $month_clause .= " AND lead_history.assign_id IN ({$params['target_user_id']}) ";
            $target_user_clause = " AND id IN ({$params['target_user_id']}) ";
        }
        if (!empty($params['status_id'])) {
            $month_clause .= " AND lead_history.status_id IN ({$params['status_id']}) ";
            $status_clause = " AND id IN ({$params['status_id']}) ";
        }
        if (!empty($params['type_id'])) {
            $lead_type_clause = " AND lead_detail.type_id IN ({$params['type_id']}) "; // AND lead_detail.deleted_at IS NULL
            $lead_type_result = \DB::select("SELECT id FROM lead WHERE company_id = {$params['company_id']} $lead_type_clause");
            $lead_type_ids = [];
            foreach ($lead_type_result as $lead_type_row)
                $lead_type_ids[] = $lead_type_row->id;

            if (count($lead_type_ids))
                $month_clause .= " AND lead_history.lead_id IN (" . implode(',', $lead_type_ids) . ") ";
        }
        if (!empty($params['start_date'])) {
            $params['start_date'] = date('Y-m-d', strtotime($params['start_date']));
            $params['end_date'] = date('Y-m-d', strtotime($params['end_date']));
            $params['end_date'] = (empty($params['end_date'])) ? $params['start_date'] : $params['end_date'];


            $params['start_date'] = dateTimezoneChangeNew($params['start_date'] . ' 00:00:00');
            $params['end_date'] = dateTimezoneChangeNew($params['end_date'] . ' 23:59:00');

            $params['start_date'] = date('Y-m-d H:i:s', strtotime($params['start_date']));
            $params['end_date'] = date('Y-m-d H:i:s', strtotime($params['end_date']));


            $month_clause .= " AND lead_history.created_at >= '{$params['start_date']}' ";
            $month_clause .= " AND lead_history.created_at <= '{$params['end_date']}'";
        }

        $result = \DB::select("SELECT count(*) as user_lead_total, lead_history.assign_id as assignee_id, lead_history.status_id, concat(user.first_name, ' ', user.last_name) as name, 
                              status.title as status_title FROM lead_history
                              INNER JOIN user ON user.id = lead_history.assign_id
                              INNER JOIN status ON status.id = lead_history.status_id
                              WHERE status.id != 77 AND                             
                               user.company_id = {$params['company_id']} AND user.user_group_id != 3  $month_clause
                              group by status_id,assign_id");

        $status_result = \DB::select("SELECT id, title FROM status WHERE tenant_id = {$params['company_id']} $status_clause AND status.id != 77  AND deleted_at IS NULL ORDER BY order_by");
        $user_result = \DB::select("SELECT id as assignee_id, concat(user.first_name, ' ', user.last_name) as name FROM user WHERE company_id = {$params['company_id']} $target_user_clause  AND  user_group_id != 3 AND user_group_id = 2 AND deleted_at IS NULL");

        $response = [];
        $temp_response = [];
        $status_map = [];
        $map_user_collection = [];
        foreach ($status_result as $row) {
            if ($row->id != 134 AND $row->id != 135 AND $row->id != 133) {
                $status_map[$row->id]['name'] = $row->title;
                $status_map[$row->id]['data'][$row->id] = 0;
            }
        }
        foreach ($user_result as $row) {
            $temp_response['user_names'][$row->assignee_id] = $row->name;
            $temp_response['status'][$row->assignee_id] = $status_map;
        }
        foreach ($result as $row) {
            $temp_response['status'][$row->assignee_id][$row->status_id]['data'][$row->status_id] = $row->user_lead_total;
        }
        $status_response = [];
        foreach ($temp_response['status'] as $ass_user_id => $user_row) {
            foreach ($user_row as $status_id => $status_row) {
                if ($status_row['name'] != null) {
                    $status_response[$status_id]['name'] = $status_row['name'];
                    $status_response[$status_id]['data'][] = $status_row['data'][$status_id];
                }
            }
        }
        foreach ($temp_response['user_names'] as $row)
            $response['user_names'][] = $row;

        $s_no = 1;
        foreach ($status_response as $row) {
            $response['status'][] = $row;
            $response['export'][] = array_merge([$s_no++, $row['name']], $row['data']);
        }
        return $response;
    }

    public static function leadTypeUserReport($params) {
        $month_clause = '';
        $status_clause = '';
        $target_user_clause = '';
        $month_clause1 = '';
        if (!empty($params['target_user_id'])) {
            $month_clause .= " AND lead_type_history.assign_id IN ({$params['target_user_id']}) ";
            $target_user_clause = " AND id IN ({$params['target_user_id']}) ";
        }
        if (!empty($params['type_id'])) {
            $month_clause .= " AND lead_type_history.type_id IN ({$params['type_id']}) ";
            $month_clause1 .= " AND id IN ({$params['type_id']}) ";
        }
        $dateinput = json_decode($params['time_slot']);
        if (!empty($dateinput->start) && !empty($dateinput->end)) {
            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));

            $time_clause = "AND lead_type_history.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        }
        $dateinput = json_decode($params['start_date']);
        if (!empty($dateinput->start) && !empty($dateinput->end)) {

            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));

            $time_clause = "AND lead_type_history.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        } else {
            $first_history = LeadType::OrderBy('id', 'asc')->first();
            $last_history = LeadType::OrderBy('id', 'desc')->first();
            $params['time_slot'] = [];
            $params['time_slot']['start'] = date('Y-m-d', strtotime($first_history->created_at));
            $params['time_slot']['end'] = date('Y-m-d', strtotime($last_history->created_at));
            $params['time_slot'] = json_encode($params['time_slot']);
            $dateinput = json_decode($params['time_slot']);
            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
        }

        $result = \DB::select("SELECT count(*) as user_lead_total, lead_type_history.assign_id as assignee_id,  lead_type_history.created_at, lead_type_history.type_id, concat(user.first_name, ' ', user.last_name) as name, 
                              type.title as status_title FROM  lead_type_history
                              INNER JOIN user ON user.id =  lead_type_history.assign_id
                              INNER JOIN type ON type.id =  lead_type_history.type_id                              
                              WHERE user.company_id = {$params['company_id']} AND user.user_group_id != 3 $month_clause $time_clause
                              group by type_id,assign_id");

        $status_result = \DB::select("SELECT id, title FROM type WHERE tenant_id = {$params['company_id']} $status_clause $month_clause1 AND deleted_at IS NULL");
        $user_result = \DB::select("SELECT id as assignee_id, concat(user.first_name, ' ', user.last_name) as name FROM user WHERE company_id = {$params['company_id']} $target_user_clause  AND  user_group_id != 3 AND user_group_id = 2 AND deleted_at IS NULL");

        $response = [];
        $temp_response = [];
        $status_map = [];
        $map_user_collection = [];
        foreach ($status_result as $row) {
            if ($row->id != 134 AND $row->id != 135 AND $row->id != 133) {
                $status_map[$row->id]['name'] = $row->title;
                $status_map[$row->id]['data'][$row->id] = 0;
                $status_map[$row->id]['total'][$row->id] = 0;
            }
        }
        foreach ($user_result as $row) {
            $temp_response['user_names'][$row->assignee_id] = $row->name;
            $temp_response['status'][$row->assignee_id] = $status_map;
        }
        foreach ($result as $row) {
            $temp_response['status'][$row->assignee_id][$row->type_id]['data'][$row->type_id] = $row->user_lead_total;
        }
        $status_response = [];
        foreach ($temp_response['status'] as $ass_user_id => $user_row) {
            foreach ($user_row as $status_id => $status_row) {
                if ($status_row['name'] != null) {
                    $status_response[$status_id]['name'] = $status_row['name'];
                    $status_response[$status_id]['data'][] = $status_row['data'][$status_id];
                    $status_response[$status_id]['total'] = $status_row['data'][$status_id] + $status_response[$status_id]['total'];
                }
            }
        }
        foreach ($temp_response['user_names'] as $row)
            $response['user_names'][] = $row;

        $lead_types = \DB::select("SELECT id, title FROM type WHERE tenant_id = 4 $status_clause $month_clause1 AND deleted_at IS NULL");
        $types = [];
        $colour = [];
        $colour[] = '#fc2c03';
        $colour[] = '#0000FF';
        $colour[] = '#fcf803';
        $colour[] = '#39fc03';
        $colour[] = '#03fcc2';
        $colour[] = '#2803fc';
        $colour[] = '#a903fc';
        $colour[] = '#fc7703';
        $colour[] = '#fc037f';
        $colour[] = '#fc0352';
        $colour[] = '#fc2c03';
        $colour[] = '#0000FF';
        $colour[] = '#fcf803';
        $colour[] = '#39fc03';
        $colour[] = '#03fcc2';
        $colour[] = '#2803fc';
        $colour[] = '#a903fc';
        $colour[] = '#fc7703';
        $colour[] = '#fc037f';
        $colour[] = '#fc0352';
        $colour[] = '#fc2c03';
        $colour[] = '#0000FF';

        $start_date = date('Y-m-d', strtotime($dateinput->start));
        $end_date = date('Y-m-d', strtotime($dateinput->end));
        $datetype = $params['datetype'];
        $total_by_month = [];
        if ($datetype == 'day') {
            $period = CarbonPeriod::create($start_date, $end_date);
            $months = [];
            $months_data = [];
            foreach ($period as $date) {
                $total_by_month[] = 0;
                $months[] = $date->format('Y-m-d');
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'week') {
            $weeks = Lead::findWeeksBetweenTwoDates($start_date, $end_date);
            $months = [];
            $months_data = [];
            foreach ($weeks as $week) {
                $total_by_month[] = 0;
                $months[] = $week[0] . ':' . $week[1];
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'month') {
            $start_date = date('Y-m-01', strtotime($start_date));
            $end_date = date('Y-m-18', strtotime($end_date));
            $period = new CarbonPeriod($start_date, '1 month', $end_date);
            $months = [];
            $months_data = [];
            $exit_year = [];
            foreach ($period as $date) {
                if (!in_array($date->format("Y M"), $exit_year)) {
                    $exit_year[] = $date->format("Y M");
                    $total_by_month[] = 0;
                    $months[] = $date->format("Y M");
                }
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'year') {
            $start_date = date('Y-01-01', strtotime($start_date));
            $end_date = date('Y-12-31', strtotime($end_date));
            $period = new CarbonPeriod($start_date, '1 year', $end_date);
            $months = [];
            $months_data = [];
            $exit_year = [];
            foreach ($period as $date) {
                if (!in_array($date->format('Y'), $exit_year)) {
                    $exit_year[] = $date->format('Y');
                    $total_by_month[] = 0;
                    $months[] = $date->format('Y');
                }
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } else {
            
        }

        $response['months'] = $months;

        $month_status = [];
        $total_result = \DB::select("SELECT lead_type_history.type_id as his_type_id,lead_type_history.created_at,lead_type_history.type_id FROM  lead_type_history
                              WHERE type_id != 0 $month_clause $time_clause  ORDER BY ID ASC");

        $total_by_month_new = $total_by_month;
        foreach ($status_result as $key => $type) {
            if ($key == 0) {
                $total_by_month_new = $total_by_month;
            }
            $month_status[$key]['name'] = $type->title;
            $month_status[$key]['id'] = $type->id;
            $month_status[$key]['data'] = $total_by_month_new;

            $result = \DB::select("SELECT lead_type_history.type_id as his_type_id,lead_type_history.created_at,lead_type_history.type_id FROM  lead_type_history
                              WHERE type_id = $type->id $month_clause $time_clause  ORDER BY ID ASC");
            $type_count = count($result);
            $type_total_count = count($total_result);
            if ($type_count != 0 AND $type_total_count != 0) {
                $month_status[$key]['data'][$total_count_array] = $type_count;
                $month_status[$key]['data'][$total_count_per] = floatval(number_format(($type_count / $type_total_count) * 100, 2)) . '%';
            } else {
                $month_status[$key]['data'][$total_count_array] = $type_count;
                $month_status[$key]['data'][$total_count_per] = '0%';
            }

            foreach ($result as $val) {

                if ($datetype == 'day') {
                    $created_at = date('Y-m-d', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'week') {
                    $created_at = date('Y-m-d', strtotime($val->created_at));

                    foreach ($months as $m => $month) {
                        $month_a = explode(":", $month);
                        if (strtotime($month_a[0]) <= strtotime($created_at) AND strtotime($month_a[1]) >= strtotime($created_at)) {
                            $n_key = $m;
                            break;
                        }
                    }

//                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'month') {
                    $created_at = date('Y M', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'year') {
                    $created_at = date('Y', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } else {
                    
                }
            }
        }

        $key = $key + 1;
        $month_status[$key]['name'] = 'Total Leads';
        $month_status[$key]['id'] = '';
        $month_status[$key]['data'] = $total_by_month;
        $month_status[$key]['data'][$total_count_array] = $type_total_count;
        $month_status[$key]['data'][$total_count_per] = '100%';
        $last_key = $key;

        foreach ($month_status as $key => $month_statu) {
            if ($last_key == $key) {
                $types[$key]['name'] = $month_statu['name'];
                $types[$key]['type'] = 'line';
                $types[$key]['showInLegend'] = 1;
                $types[$key]['toolTipContent'] = null;
                $types[$key]['markerType'] = "square";
                $types[$key]['indexLabelPlacement'] = "outside";
                $types[$key]['color'] = 'transparent';
                if (isset($params['type']) AND $params['type'] == 'percentage') {
                    $types[$key]['indexLabel'] = "{y}%";
                } else {
                    $types[$key]['indexLabel'] = "{y}";
                }
            } else {
                $types[$key]['name'] = $month_statu['name'];
                $types[$key]['type'] = 'stackedColumn';
                $types[$key]['markerType'] = "square";
                $types[$key]['showInLegend'] = 1;
                $types[$key]['color'] = $colour[$key];
                if (count($months) < 15) {
                    $types[$key]['indexLabelFontColor'] = 'white';
                    $types[$key]['indexLabelFontSize'] = 10;
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types[$key]['indexLabel'] = "{y}%";
                    } else {
                        $types[$key]['indexLabel'] = "{y}";
                    }
                }
            }

            $total = 0;
            foreach ($months as $i => $month) {
                if ($month != 'Total' AND $month != 'By%') {
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types[$key]['dataPoints'][$total]['label'] = $month;
                        $total_count = $month_status[$last_key]['data'][$i];
                        if ($month_statu['data'][$i] == 0) {
                            $types[$key]['dataPoints'][$total]['y'] = '0%';
                            $month_status[$key]['data'][$i] = '0%';
                        } else {
                            $types[$key]['dataPoints'][$total]['y'] = floatval(number_format(($month_statu['data'][$i] / $total_count) * 100, 2));
                            $month_status[$key]['data'][$i] = floatval(number_format(($month_statu['data'][$i] / $total_count) * 100, 2)) . '%';
                        }
                    } else {
                        if ($month_statu['data'][$i] == 0) {
                            $types[$key]['dataPoints'][$total]['label'] = $month;
                            $types[$key]['dataPoints'][$total]['y'] = '';
                        } else {
                            $types[$key]['dataPoints'][$total]['label'] = $month;
                            $types[$key]['dataPoints'][$total]['y'] = $month_statu['data'][$i];
                        }
                    }
                    $total = $total + 1;
                }
            }
        }
        $response['types'] = $types;
        $s_no = 1;
        foreach ($month_status as $row) {
            $response['status'][] = $row;
            $response['export'][] = array_merge([$s_no++, $row['name']], $row['data']);
        }
        $response['month_status'] = $month_status;

        return $response;
    }

    public static function leadTypeUserReportNew($params) {
        $month_clause = '';
        $status_clause = '';
        $target_user_clause = '';
        $time_clause = '';
        $select_type = '';
        if (!empty($params['target_user_id'])) {
            $month_clause .= " AND lead_history.assign_id IN ({$params['target_user_id']}) ";
        }
        if (!empty($params['type_id'])) {
            $month_clause .= " AND lead_history.status_id IN ({$params['type_id']}) ";
            $select_type .= " AND id IN ({$params['type_id']}) ";
        }
        $dateinput = json_decode($params['time_slot']);

        info($params);

        if (!empty($dateinput->start) && !empty($dateinput->end)) {

            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));

            $time_clause = "AND lead_history.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        }

        $dateinput = json_decode($params['start_date']);
        if (!empty($dateinput->start) && !empty($dateinput->end)) {
            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
            $time_clause = "AND lead_history.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        } else {
            $first_history = LeadHistory::OrderBy('id', 'asc')->first();
            $last_history = LeadHistory::OrderBy('id', 'desc')->first();
            $params['time_slot'] = [];
            $params['time_slot']['start'] = date('Y-m-d', strtotime($first_history->created_at));
            $params['time_slot']['end'] = date('Y-m-d', strtotime($last_history->created_at));
            $params['time_slot'] = json_encode($params['time_slot']);
            $dateinput = json_decode($params['time_slot']);
            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
        }

        $result = \DB::select("SELECT count(*) as user_lead_total, lead_history.assign_id as assignee_id, lead_history.status_id, concat(user.first_name, ' ', user.last_name) as name, 
                              status.title as status_title FROM lead_history
                              INNER JOIN user ON user.id = lead_history.assign_id
                              INNER JOIN status ON status.id = lead_history.status_id                              
                              WHERE user.company_id = {$params['company_id']} AND user.user_group_id != 3  $month_clause
                              group by status_id,assign_id");

        $status_result = \DB::select("SELECT id, title, color_code FROM status WHERE tenant_id = {$params['company_id']} $status_clause $select_type AND deleted_at IS NULL ORDER BY order_by");
        $user_result = \DB::select("SELECT id as assignee_id, concat(user.first_name, ' ', user.last_name) as name FROM user WHERE company_id = {$params['company_id']} $target_user_clause  AND  user_group_id != 3 AND user_group_id = 2 AND deleted_at IS NULL");
        $response = [];
        $temp_response = [];
        $status_map = [];
        $map_user_collection = [];
        foreach ($status_result as $row) {
            if ($row->id != 134 AND $row->id != 135 AND $row->id != 133) {
                $status_map[$row->id]['name'] = $row->title;
                $status_map[$row->id]['data'][$row->id] = 0;
                $status_map[$row->id]['total'][$row->id] = 0;
            }
        }
        foreach ($user_result as $row) {
            $temp_response['user_names'][$row->assignee_id] = $row->name;
            $temp_response['status'][$row->assignee_id] = $status_map;
        }

        $lead_types = \DB::select("SELECT id, title FROM status WHERE tenant_id = {$params['company_id']} $status_clause AND deleted_at IS NULL ORDER BY order_by");
        $types = [];

        $start_date = date('Y-m-d', strtotime($dateinput->start));
        $end_date = date('Y-m-d', strtotime($dateinput->end));
        $datetype = $params['datetype'];
        $total_by_month = [];
        if ($datetype == 'day') {
            $period = CarbonPeriod::create($start_date, $end_date);
            $months = [];
            $months_data = [];
            foreach ($period as $date) {
                $total_by_month[] = 0;
                $months[] = $date->format('Y-m-d');
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'week') {
            $weeks = Lead::findWeeksBetweenTwoDatesNew($start_date, $end_date);
            $months = [];
            $months_data = [];
            foreach ($weeks as $week) {
                $total_by_month[] = 0;
                $months[] = $week[0] . ':' . $week[1];
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'month') {
            $start_date = date('Y-m-01', strtotime($start_date));
            $end_date = date('Y-m-18', strtotime($end_date));
            $period = new CarbonPeriod($start_date, '1 month', $end_date);
            $months = [];
            $months_data = [];
            $exit_year = [];
            foreach ($period as $date) {
                if (!in_array($date->format("Y M"), $exit_year)) {
                    $exit_year[] = $date->format("Y M");
                    $total_by_month[] = 0;
                    $months[] = $date->format("Y M");
                }
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'year') {
            $start_date = date('Y-01-01', strtotime($start_date));
            $end_date = date('Y-12-31', strtotime($end_date));
            $period = new CarbonPeriod($start_date, '1 year', $end_date);
            $months = [];
            $months_data = [];
            $exit_year = [];
            foreach ($period as $date) {
                if (!in_array($date->format('Y'), $exit_year)) {
                    $exit_year[] = $date->format('Y');
                    $total_by_month[] = 0;
                    $months[] = $date->format('Y');
                }
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        }
        $response['months'] = $months;
        $month_status = [];
        $total_result = \DB::select("SELECT lead_history.status_id as his_status_id,lead_history.created_at,lead_history.status_id FROM  lead_history
                              WHERE status_id != 0 $month_clause $time_clause  ORDER BY ID ASC");
        $total_by_month_new = $total_by_month;
        foreach ($status_result as $key => $type) {
            if ($key == 0) {
                $total_by_month_new = $total_by_month;
            }
            $month_status[$key]['name'] = $type->title;
            $month_status[$key]['colour_code'] = $type->color_code;
            $month_status[$key]['id'] = $type->id;
            $month_status[$key]['data'] = $total_by_month_new;

            $result = \DB::select("SELECT lead_history.status_id as his_status_id,lead_history.created_at,lead_history.status_id FROM  lead_history
                              WHERE lead_history.status_id != 0 AND status_id = $type->id $month_clause $time_clause  ORDER BY ID ASC");
            $type_count = count($result);
            $type_total_count = count($total_result);
            if ($type_count != 0 AND $type_total_count != 0) {
                $month_status[$key]['data'][$total_count_array] = $type_count;
                $month_status[$key]['data'][$total_count_per] = floatval(number_format(($type_count / $type_total_count) * 100, 2)) . '%';
            } else {
                $month_status[$key]['data'][$total_count_array] = $type_count;
                $month_status[$key]['data'][$total_count_per] = '0%';
            }
            foreach ($result as $val) {
                if ($datetype == 'day') {
                    $created_at = date('Y-m-d', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'week') {
                    $created_at = date('Y-m-d', strtotime($val->created_at));

                    foreach ($months as $m => $month) {
                        $month_a = explode(":", $month);
                        if (strtotime($month_a[0]) <= strtotime($created_at) AND strtotime($month_a[1]) >= strtotime($created_at)) {
                            $n_key = $m;
                            break;
                        }
                    }
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'month') {
                    $created_at = date('Y M', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }
                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'year') {
                    $created_at = date('Y', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } else {
                    
                }
            }
        }
        $key = $key + 1;
        $month_status[$key]['name'] = 'Total Leads';
        $month_status[$key]['id'] = '';
        $month_status[$key]['data'] = $total_by_month;
        $month_status[$key]['data'][$total_count_array] = $type_total_count;
        $month_status[$key]['data'][$total_count_per] = '100%';
        $last_key = $key;

        foreach ($month_status as $key => $month_statu) {


            if ($last_key == $key) {
                $types[$key]['name'] = $month_statu['name'];
                $types[$key]['type'] = 'line';
                $types[$key]['showInLegend'] = 1;
                $types[$key]['toolTipContent'] = null;
                $types[$key]['markerType'] = "square";
                $types[$key]['indexLabelPlacement'] = "outside";
                $types[$key]['color'] = 'transparent';
                if (isset($params['type']) AND $params['type'] == 'percentage') {
                    $types[$key]['indexLabel'] = "{y}%";
                } else {
                    $types[$key]['indexLabel'] = "{y}";
                }
            } else {
                $types[$key]['name'] = $month_statu['name'];
                $types[$key]['type'] = 'stackedColumn';
                $types[$key]['markerType'] = "square";
                $types[$key]['showInLegend'] = 1;
                $types[$key]['color'] = $month_statu['colour_code'];
//                $types[$key]['color'] = $colour[$key];
                if (count($months) < 15) {
                    $types[$key]['indexLabelFontColor'] = 'white';
                    $types[$key]['indexLabelFontSize'] = 10;
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types[$key]['indexLabel'] = "{y}%";
                    } else {
                        $types[$key]['indexLabel'] = "{y}";
                    }
                }
            }

            $total = 0;
            foreach ($months as $i => $month) {
                if ($month != 'Total' AND $month != 'By%') {
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types[$key]['dataPoints'][$total]['label'] = $month;
                        $total_count = $month_status[$last_key]['data'][$i];
                        if ($month_statu['data'][$i] == 0) {
                            $types[$key]['dataPoints'][$total]['y'] = '0%';
                            $month_status[$key]['data'][$i] = '0%';
                        } else {
                            $types[$key]['dataPoints'][$total]['y'] = floatval(number_format(($month_statu['data'][$i] / $total_count) * 100, 2));
                            $month_status[$key]['data'][$i] = floatval(number_format(($month_statu['data'][$i] / $total_count) * 100, 2)) . '%';
                        }
                    } else {
                        if ($month_statu['data'][$i] == 0) {
                            $types[$key]['dataPoints'][$total]['label'] = $month;
//                            $types[$key]['dataPoints'][$total]['y'] = '';
                        } else {
                            $types[$key]['dataPoints'][$total]['label'] = $month;
                            $types[$key]['dataPoints'][$total]['y'] = $month_statu['data'][$i];
                        }
                    }
                    $total = $total + 1;
                }
            }
        }

        $response['types'] = $types;

        $s_no = 1;
        foreach ($month_status as $row) {
            $response['status'][] = $row;
            $response['export'][] = array_merge([$s_no++, $row['name']], $row['data']);
        }




        $response['month_status'] = $month_status;

        return $response;
    }

    public static function leadTypeUserReportFollowUp($params) {
        $month_clause = '';
        $status_clause = '';
        $target_user_clause = '';
        $time_clause = '';
        $select_type = '';
        if (!empty($params['target_user_id'])) {
            $month_clause .= " AND lead_history.assign_id IN ({$params['target_user_id']}) ";
        }
        if (!empty($params['type_id'])) {
            $month_clause .= " AND lead_history.followup_status_id IN ({$params['type_id']}) ";
            $select_type .= " AND id IN ({$params['type_id']}) ";
        }
        $dateinput = json_decode($params['time_slot']);
        if (!empty($dateinput->start) && !empty($dateinput->end)) {

            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));

            $time_clause = "AND lead_history.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        }

        $dateinput = json_decode($params['start_date']);
        if (!empty($dateinput->start) && !empty($dateinput->end)) {
            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));

            $time_clause = "AND lead_history.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        } else {
            $first_history = LeadHistory::where('followup_status_id', '!=', 0)->OrderBy('id', 'asc')->first();
            $last_history = LeadHistory::where('followup_status_id', '!=', 0)->OrderBy('id', 'desc')->first();
            $params['time_slot'] = [];
            $params['time_slot']['start'] = date('Y-m-d', strtotime($first_history->created_at));
            $params['time_slot']['end'] = date('Y-m-d', strtotime($last_history->created_at));
            $params['time_slot'] = json_encode($params['time_slot']);
            $dateinput = json_decode($params['time_slot']);

            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
        }

        $result = \DB::select("SELECT count(*) as user_lead_total, lead_history.assign_id as assignee_id, lead_history.status_id, concat(user.first_name, ' ', user.last_name) as name, 
                              status.title as status_title FROM lead_history
                              INNER JOIN user ON user.id = lead_history.assign_id
                              INNER JOIN status ON status.id = lead_history.status_id                              
                              WHERE user.company_id = {$params['company_id']} AND user.user_group_id != 3  $month_clause
                              group by status_id,assign_id");

        $status_result = \DB::select("SELECT id, title, color_code FROM  follow_statuses WHERE 0=0 $status_clause $select_type AND deleted_at IS NULL ORDER BY title ASC");
        $user_result = \DB::select("SELECT id as assignee_id, concat(user.first_name, ' ', user.last_name) as name FROM user WHERE company_id = {$params['company_id']} $target_user_clause  AND  user_group_id != 3 AND user_group_id = 2 AND deleted_at IS NULL");
        $response = [];
        $temp_response = [];
        $status_map = [];
        $map_user_collection = [];
        foreach ($status_result as $row) {
            if ($row->id != 134 AND $row->id != 135 AND $row->id != 133) {
                $status_map[$row->id]['name'] = $row->title;
                $status_map[$row->id]['data'][$row->id] = 0;
                $status_map[$row->id]['total'][$row->id] = 0;
            }
        }
        foreach ($user_result as $row) {
            $temp_response['user_names'][$row->assignee_id] = $row->name;
            $temp_response['status'][$row->assignee_id] = $status_map;
        }

        $lead_types = \DB::select("SELECT id, title FROM status WHERE tenant_id = {$params['company_id']} $status_clause AND deleted_at IS NULL ORDER BY order_by");
        $types = [];

        $start_date = date('Y-m-d', strtotime($dateinput->start));
        $end_date = date('Y-m-d', strtotime($dateinput->end));
        $datetype = $params['datetype'];
        $total_by_month = [];
        if ($datetype == 'day') {
            $period = CarbonPeriod::create($start_date, $end_date);
            $months = [];
            $months_data = [];
            foreach ($period as $date) {
                $total_by_month[] = 0;
                $months[] = $date->format('Y-m-d');
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'week') {
            $weeks = Lead::findWeeksBetweenTwoDatesNew($start_date, $end_date);
            $months = [];
            $months_data = [];
            foreach ($weeks as $week) {
                $total_by_month[] = 0;
                $months[] = $week[0] . ':' . $week[1];
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'month') {
            $start_date = date('Y-m-01', strtotime($start_date));
            $end_date = date('Y-m-18', strtotime($end_date));
            $period = new CarbonPeriod($start_date, '1 month', $end_date);
            $months = [];
            $months_data = [];
            $exit_year = [];
            foreach ($period as $date) {
                if (!in_array($date->format("Y M"), $exit_year)) {
                    $exit_year[] = $date->format("Y M");
                    $total_by_month[] = 0;
                    $months[] = $date->format("Y M");
                }
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'year') {
            $start_date = date('Y-01-01', strtotime($start_date));
            $end_date = date('Y-12-31', strtotime($end_date));
            $period = new CarbonPeriod($start_date, '1 year', $end_date);
            $months = [];
            $months_data = [];
            $exit_year = [];
            foreach ($period as $date) {
                if (!in_array($date->format('Y'), $exit_year)) {
                    $exit_year[] = $date->format('Y');
                    $total_by_month[] = 0;
                    $months[] = $date->format('Y');
                }
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        }
        $response['months'] = $months;
        $month_status = [];
        $total_result = \DB::select("SELECT lead_history.followup_status_id as his_status_id,lead_history.created_at,lead_history.followup_status_id FROM  lead_history
                              WHERE followup_status_id != 0 $month_clause $time_clause  ORDER BY ID ASC");
        $total_by_month_new = $total_by_month;
        foreach ($status_result as $key => $type) {
            if ($key == 0) {
                $total_by_month_new = $total_by_month;
            }
            $month_status[$key]['name'] = $type->title;
            $month_status[$key]['colour_code'] = $type->color_code;
            $month_status[$key]['id'] = $type->id;
            $month_status[$key]['data'] = $total_by_month_new;

            $result = \DB::select("SELECT lead_history.followup_status_id as his_status_id,lead_history.created_at,lead_history.followup_status_id FROM  lead_history
                              WHERE lead_history.followup_status_id != 0 AND followup_status_id = $type->id $month_clause $time_clause  ORDER BY ID ASC");
            $type_count = count($result);
            $type_total_count = count($total_result);
            if ($type_count != 0 AND $type_total_count != 0) {
                $month_status[$key]['data'][$total_count_array] = $type_count;
                $month_status[$key]['data'][$total_count_per] = floatval(number_format(($type_count / $type_total_count) * 100, 2)) . '%';
            } else {
                $month_status[$key]['data'][$total_count_array] = $type_count;
                $month_status[$key]['data'][$total_count_per] = '0%';
            }
            foreach ($result as $val) {
                if ($datetype == 'day') {
                    $created_at = date('Y-m-d', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'week') {
                    $created_at = date('Y-m-d', strtotime($val->created_at));

                    foreach ($months as $m => $month) {
                        $month_a = explode(":", $month);
                        if (strtotime($month_a[0]) <= strtotime($created_at) AND strtotime($month_a[1]) >= strtotime($created_at)) {
                            $n_key = $m;
                            break;
                        }
                    }
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'month') {
                    $created_at = date('Y M', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }
                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'year') {
                    $created_at = date('Y', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } else {
                    
                }
            }
        }
        $key = $key + 1;
        $month_status[$key]['name'] = 'Total Leads';
        $month_status[$key]['id'] = '';
        $month_status[$key]['data'] = $total_by_month;
        $month_status[$key]['data'][$total_count_array] = $type_total_count;
        $month_status[$key]['data'][$total_count_per] = '100%';
        $last_key = $key;

        foreach ($month_status as $key => $month_statu) {


            if ($last_key == $key) {
                $types[$key]['name'] = $month_statu['name'];
                $types[$key]['type'] = 'line';
                $types[$key]['showInLegend'] = 1;
                $types[$key]['toolTipContent'] = null;
                $types[$key]['markerType'] = "square";
                $types[$key]['indexLabelPlacement'] = "outside";
                $types[$key]['color'] = 'transparent';
                if (isset($params['type']) AND $params['type'] == 'percentage') {
                    $types[$key]['indexLabel'] = "{y}%";
                } else {
                    $types[$key]['indexLabel'] = "{y}";
                }
            } else {
                $types[$key]['name'] = $month_statu['name'];
                $types[$key]['type'] = 'stackedColumn';
                $types[$key]['markerType'] = "square";
                $types[$key]['showInLegend'] = 1;
                $types[$key]['color'] = $month_statu['colour_code'];
//                $types[$key]['color'] = $colour[$key];
                if (count($months) < 15) {
                    $types[$key]['indexLabelFontColor'] = 'white';
                    $types[$key]['indexLabelFontSize'] = 10;
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types[$key]['indexLabel'] = "{y}%";
                    } else {
                        $types[$key]['indexLabel'] = "{y}";
                    }
                }
            }

            $total = 0;
            foreach ($months as $i => $month) {
                if ($month != 'Total' AND $month != 'By%') {
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types[$key]['dataPoints'][$total]['label'] = $month;
                        $total_count = $month_status[$last_key]['data'][$i];
                        if ($month_statu['data'][$i] == 0) {
                            $types[$key]['dataPoints'][$total]['y'] = '0%';
                            $month_status[$key]['data'][$i] = '0%';
                        } else {
                            $types[$key]['dataPoints'][$total]['y'] = floatval(number_format(($month_statu['data'][$i] / $total_count) * 100, 2));
                            $month_status[$key]['data'][$i] = floatval(number_format(($month_statu['data'][$i] / $total_count) * 100, 2)) . '%';
                        }
                    } else {
                        if ($month_statu['data'][$i] == 0) {
                            $types[$key]['dataPoints'][$total]['label'] = $month;
//                            $types[$key]['dataPoints'][$total]['y'] = '';
                        } else {
                            $types[$key]['dataPoints'][$total]['label'] = $month;
                            $types[$key]['dataPoints'][$total]['y'] = $month_statu['data'][$i];
                        }
                    }
                    $total = $total + 1;
                }
            }
        }

        $response['types'] = $types;

        $s_no = 1;
        foreach ($month_status as $row) {
            $response['status'][] = $row;
            $response['export'][] = array_merge([$s_no++, $row['name']], $row['data']);
        }


        $response['month_status'] = $month_status;

        return $response;
    }

    public static function userKnockReportsColour($params) {
        if (!isset($params['datetype'])) {
            $params['datetype'] = 'day_part';
        }

        $month_clause = '';
        $status_clause = '';
        $target_user_clause = '';
        $time_clause = '';
        $select_type = '';

        if (!empty($params['target_user_id'])) {
            $month_clause .= " AND  user_lead_knocks.assign_id IN ({$params['target_user_id']}) ";
        }

        if (!empty($params['type_id'])) {
            $month_clause .= " AND  user_lead_knocks.followup_status_id IN ({$params['type_id']}) ";
            $select_type .= " AND id IN ({$params['type_id']}) ";
        }

        $dateinput = json_decode($params['time_slot']);
        if (!empty($dateinput->start) && !empty($dateinput->end)) {
            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');
            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
            $time_clause = "AND lead_history.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        }

        $dateinput = json_decode($params['start_date']);
        if (!empty($dateinput->start) && !empty($dateinput->end)) {
            $date1 = Carbon::parse($dateinput->start);
            $date2 = Carbon::parse($dateinput->end);
            $diffInDays = $date1->diffInDays($date2);

            if ($diffInDays > 8) {
                return false;
            }
            $datetype = $params['datetype'];
            if ($datetype == 'day_part') {
                $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 08:00:00');
                $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 22:00:00');
                $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
                $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
            } else {
                $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 08:00:00');
                $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 22:00:00');
                $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
                $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
            }
            $time_clause = "AND  user_lead_knocks.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        } else {
            $datetype = $params['datetype'];
            if ($datetype == 'day_part') {
                if ($params['time_slot'] !== null) {
                    $params['time_slot'] = [];
                    $params['time_slot']['start'] = date('Y-m-d', strtotime($params['start_date']));
                    $params['time_slot']['end'] = date('Y-m-d', strtotime($params['start_date']));
                    $params['time_slot'] = json_encode($params['time_slot']);
                    $dateinput = json_decode($params['time_slot']);
                } else {
                    $params['time_slot'] = [];
                    $params['time_slot']['start'] = date('Y-m-d');
                    $params['time_slot']['end'] = date('Y-m-d');
                    $params['time_slot'] = json_encode($params['time_slot']);
                    $dateinput = json_decode($params['time_slot']);
                }
                $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 08:00:00');
                $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 22:00:00');
                $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
                $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
            } else {
                $first_history = UserLeadKnocks::OrderBy('id', 'asc')->first();
                $last_history = UserLeadKnocks::OrderBy('id', 'desc')->first();
                $params['time_slot'] = [];
                $params['time_slot']['start'] = date('Y-m-d', strtotime($first_history->created_at));
                $params['time_slot']['end'] = date('Y-m-d', strtotime($last_history->created_at));
                $params['time_slot'] = json_encode($params['time_slot']);
                $dateinput = json_decode($params['time_slot']);
                $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 08:00:00');
                $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 22:00:00');
                $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
                $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
            }
            $time_clause = "AND  user_lead_knocks.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        }

        $status_result = \DB::select("SELECT id, title, color_code FROM status WHERE 0=0 $status_clause $select_type AND tenant_id = 4 AND deleted_at IS NULL ORDER BY title ASC");
        $user_result = \DB::select("SELECT id as assignee_id, concat(user.first_name, ' ', user.last_name) as name FROM user WHERE company_id = {$params['company_id']} $target_user_clause  AND  user_group_id != 3 AND user_group_id = 2 AND deleted_at IS NULL");
        $response = [];
        $temp_response = [];
        $status_map = [];
        $map_user_collection = [];

        foreach ($status_result as $row) {
            if ($row->id != 134 AND $row->id != 135 AND $row->id != 133) {
                $status_map[$row->id]['name'] = $row->title;
                $status_map[$row->id]['data'][$row->id] = 0;
                $status_map[$row->id]['total'][$row->id] = 0;
            }
        }

        foreach ($user_result as $row) {
            $temp_response['user_names'][$row->assignee_id] = $row->name;
        }

        $types = [];
        $start_date = date('Y-m-d', strtotime($dateinput->start));
        $end_date = date('Y-m-d', strtotime($dateinput->end));
        $datetype = $params['datetype'];
        $datetype = 'day_part';
        $total_by_month = [];

        if ($datetype == 'day') {
            $period = CarbonPeriod::create($start_date, $end_date);
            $months = [];
            $months_data = [];
            foreach ($period as $date) {
                $total_by_month[] = 0;
                $months[] = $date->format('Y-m-d');
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'day_part') {
            $start_date = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $end_date = date('Y-m-d H:i:s', strtotime($dateinput->end));
            $date = Carbon::parse($start_date);
            $startTime = Carbon::parse($start_date);
            $endTime = Carbon::parse($end_date);
            $timeSlots = [];
            $currentTime = clone $startTime;
            while ($currentTime <= $endTime) {
                $timeSlots[] = $currentTime->copy();
                $currentTime->addHours(1);
            }
            $months = [];
            $months_data = [];
            foreach ($timeSlots as $date) {
                $total_by_month[] = 0;
                $months[] = $date->format("Y-m-d H:i:s");
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        }

        $response['months'] = $months;
        $month_status = [];
        $total_result = \DB::select("SELECT user.id as user_id,user_lead_knocks.status_id as his_status_id,
            user_lead_knocks.created_at,
            user_lead_knocks.status_id FROM  user_lead_knocks INNER JOIN user ON user_lead_knocks.user_id = user.id
                              WHERE 1=1 AND user_lead_knocks.status_id != 77 AND user.user_group_id != 3 $month_clause $time_clause");

        $total_by_month_new1 = $total_by_month;
        $total_by_month_new = $total_by_month;


        $new_month_status = [];

        $new_month_status[0]['name'] = 'Answered';
        $new_month_status[0]['colour_code'] = '#bd5ce8';
        $new_month_status[0]['data'] = $total_by_month_new1;

        $new_month_status[1]['name'] = 'No Answer';
        $new_month_status[1]['colour_code'] = '#6a75eb';
        $new_month_status[1]['data'] = $total_by_month_new1;

        $no_answer_status_ids = \DB::table('status')
                ->where('tenant_id', $params['company_id'])
                ->whereNull('deleted_at')
                ->where('title', 'LIKE', '%No Ansr%')
                ->pluck('id');

        $no_ids = [];
        foreach ($no_answer_status_ids as $no_answer_status_id) {
            $no_ids[] = $no_answer_status_id;
        }

        $no_id_string = implode(', ', $no_ids);
        $answer_status_ids = \DB::table('status')
                ->where('tenant_id', $params['company_id'])
                ->whereNull('deleted_at')
                ->where('title', 'NOT LIKE', '%No Ansr%')
                ->pluck('id');

        $result1 = \DB::select("SELECT user.id as user_id,user_lead_knocks.status_id as his_status_id,
            user_lead_knocks.created_at,
            user_lead_knocks.status_id FROM  user_lead_knocks INNER JOIN user ON user_lead_knocks.user_id = user.id
                WHERE user_lead_knocks.status_id != 0 AND user_lead_knocks.status_id != 77 AND user.user_group_id != 3 AND 
                user_lead_knocks.status_id IN ($no_id_string) $month_clause $time_clause");
        $type_count1 = count($result1);

        $type_total_count = count($total_result);
        if ($type_count1 != 0 AND $type_total_count != 0) {
            $new_month_status[1]['data'][$total_count_array] = $type_count1;
            $new_month_status[1]['data'][$total_count_per] = floatval(number_format(($type_count1 / $type_total_count) * 100, 2)) . '%';
        } else {
            $new_month_status[1]['data'][$total_count_array] = $type_count1;
            $new_month_status[1]['data'][$total_count_per] = '0%';
        }

        $result2 = \DB::select("SELECT user.id as user_id,user_lead_knocks.status_id as his_status_id,
            user_lead_knocks.created_at,
            user_lead_knocks.status_id FROM  user_lead_knocks INNER JOIN user ON user_lead_knocks.user_id = user.id
                WHERE user_lead_knocks.status_id != 0 AND user_lead_knocks.status_id != 77 AND user.user_group_id != 3 AND 
                user_lead_knocks.status_id NOT IN ($no_id_string) $month_clause $time_clause");
        $type_count2 = count($result2);
        $type_total_count = count($total_result);
        if ($type_count2 != 0 AND $type_total_count != 0) {
            $new_month_status[0]['data'][$total_count_array] = $type_count2;
            $new_month_status[0]['data'][$total_count_per] = floatval(number_format(($type_count2 / $type_total_count) * 100, 2)) . '%';
        } else {
            $new_month_status[0]['data'][$total_count_array] = $type_count2;
            $new_month_status[0]['data'][$total_count_per] = '0%';
        }

        foreach ($status_result as $key => $type) {
            if ($key == 0) {
                $total_by_month_new = $total_by_month;
            }
            $month_status[$key]['name'] = $type->title;
            $month_status[$key]['colour_code'] = $type->color_code;
            $month_status[$key]['id'] = $type->id;
            $month_status[$key]['data'] = $total_by_month_new;
            $result = \DB::select("SELECT user.id as user_id,user_lead_knocks.status_id as his_status_id,
            user_lead_knocks.created_at,
            user_lead_knocks.status_id FROM  user_lead_knocks INNER JOIN user ON user_lead_knocks.user_id = user.id
                WHERE user_lead_knocks.status_id != 0 AND user_lead_knocks.status_id != 77 AND user.user_group_id != 3 AND 
                user_lead_knocks.status_id = $type->id $month_clause $time_clause");

            $type_count = count($result);
            $type_total_count = count($total_result);
            if ($type_count != 0 AND $type_total_count != 0) {
                $month_status[$key]['data'][$total_count_array] = $type_count;
                $month_status[$key]['data'][$total_count_per] = floatval(number_format(($type_count / $type_total_count) * 100, 2)) . '%';
            } else {
                $month_status[$key]['data'][$total_count_array] = $type_count;
                $month_status[$key]['data'][$total_count_per] = '0%';
            }

            foreach ($result as $val) {
                if ($datetype == 'day') {
                    $created_at = date('Y-m-d', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }
                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'day_part') {
                    $created_at = dateTimezoneChangeNew($val->created_at);
                    $created_at = date('Y-m-d H:i:s', strtotime($created_at));
                    $targetDateTime = $created_at;
                    $targetDateTime = new DateTime($targetDateTime);
                    $closestKey = null;
                    $closestDiff = PHP_INT_MAX;
                    foreach ($months as $key1 => $dateTimeString) {
                        if (!DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeString)) {
                            continue;
                        }
                        $dateTime = new DateTime($dateTimeString);
                        $diff = $targetDateTime->getTimestamp() - $dateTime->getTimestamp();
                        if ($diff > 0 && $diff < $closestDiff) {
                            $closestDiff = $diff;
                            $closestKey = $key1;
                        }
                    }
                    if ($closestKey != null) {
                        $n_key = $closestKey;
                        if (isset($month_status[$key]['data'][$n_key])) {
                            $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                        } else {
                            $month_status[$key]['data'][$n_key] = 1;
                        }
                        if (isset($total_by_month[$n_key])) {
                            $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                        } else {
                            $total_by_month[$n_key] = 1;
                        }
                    }

                    $n_key = $closestKey;
                    if (in_array($val->status_id, $no_ids)) {
                        if (isset($new_month_status[1]['data'][$n_key])) {
                            $new_month_status[1]['data'][$n_key] = $new_month_status[1]['data'][$n_key] + 1;
                        } else {
                            $new_month_status[1]['data'][$n_key] = 1;
                        }
                        if (isset($total_by_month_new1[$n_key])) {
                            $total_by_month_new1[$n_key] = $total_by_month_new1[$n_key] + 1;
                        } else {
                            $total_by_month_new1[$n_key] = 1;
                        }
                    } else {
                        if (isset($new_month_status[0]['data'][$n_key])) {
                            $new_month_status[0]['data'][$n_key] = $new_month_status[0]['data'][$n_key] + 1;
                        } else {
                            $new_month_status[0]['data'][$n_key] = 1;
                        }
                        if (isset($total_by_month_new1[$n_key])) {
                            $total_by_month_new1[$n_key] = $total_by_month_new1[$n_key] + 1;
                        } else {
                            $total_by_month_new1[$n_key] = 1;
                        }
                    }
                } else {
                    
                }
            }
        }




        $key = $key + 1;
        $month_status[$key]['name'] = 'Total Leads';
        $month_status[$key]['id'] = '';
        $month_status[$key]['data'] = $total_by_month;
        $month_status[$key]['data'][$total_count_array] = $type_total_count;
        $month_status[$key]['data'][$total_count_per] = '100%';
        $last_key = $key;


        foreach ($month_status as $key => $month_statu) {
            if ($last_key == $key) {
                $types[$key]['name'] = $month_statu['name'];
                $types[$key]['type'] = 'line';
                $types[$key]['showInLegend'] = 1;
                $types[$key]['toolTipContent'] = null;
                $types[$key]['markerType'] = "square";
                $types[$key]['indexLabelPlacement'] = "outside";
                $types[$key]['color'] = 'transparent';
                if (isset($params['type']) AND $params['type'] == 'percentage') {
                    $types[$key]['indexLabel'] = "{y}%";
                } else {
                    $types[$key]['indexLabel'] = "{y}";
                }
            } else {
                $types[$key]['name'] = $month_statu['name'];
                $types[$key]['type'] = 'stackedColumn';
                $types[$key]['markerType'] = "square";
                $types[$key]['showInLegend'] = 1;
                if (isset($colour[$key])) {
                    $types[$key]['color'] = $colour[$key];
                } else {
                    $types[$key]['color'] = $month_statu['colour_code'];
                }


                if (count($months) < 15) {
                    $types[$key]['indexLabelFontColor'] = 'white';
                    $types[$key]['indexLabelFontSize'] = 10;
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types[$key]['indexLabel'] = "{y}%";
                    } else {
                        $types[$key]['indexLabel'] = "{y}";
                    }
                }
            }

            $total = 0;
            foreach ($months as $i => $month) {
                if ($month != 'Total' AND $month != 'By%') {
                    $month = dateTimezoneChangeForGraph($month);
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types[$key]['dataPoints'][$total]['label'] = $month;
                        $total_count = $month_status[$last_key]['data'][$i];
                        if ($month_statu['data'][$i] == 0) {
                            $types[$key]['dataPoints'][$total]['y'] = '0%';
                            $month_status[$key]['data'][$i] = '0%';
                        } else {
                            $types[$key]['dataPoints'][$total]['y'] = floatval(number_format(($month_statu['data'][$i] / $total_count) * 100, 2));
                            $month_status[$key]['data'][$i] = floatval(number_format(($month_statu['data'][$i] / $total_count) * 100, 2)) . '%';
                        }
                    } else {
                        if ($month_statu['data'][$i] == 0) {
                            $types[$key]['dataPoints'][$total]['label'] = $month;
                        } else {
                            $types[$key]['dataPoints'][$total]['label'] = $month;
                            $types[$key]['dataPoints'][$total]['y'] = $month_statu['data'][$i];
                        }
                    }
                    $total = $total + 1;
                }
            }
        }

        $key = 1 + 1;
        $new_month_status[$key]['name'] = 'Total Leads';
        $new_month_status[$key]['colour_code'] = '';
        $new_month_status[$key]['data'] = $total_by_month;
        $new_month_status[$key]['data'][$total_count_array] = $type_total_count;
        $new_month_status[$key]['data'][$total_count_per] = '100%';
        $last_key = $key;

        foreach ($new_month_status as $key => $new_month_statu) {
            if ($last_key == $key) {
                $types1[$key]['name'] = $new_month_statu['name'];
                $types1[$key]['type'] = '';
                $types1[$key]['showInLegend'] = 1;
                $types1[$key]['toolTipContent'] = null;
                $types1[$key]['markerType'] = "square";
                $types1[$key]['indexLabelPlacement'] = "outside";
                $types1[$key]['color'] = 'transparent';
                if (isset($params['type']) AND $params['type'] == 'percentage') {
                    $types1[$key]['indexLabel'] = "{y}%";
                } else {
                    $types1[$key]['indexLabel'] = "{y}";
                }
            } else {
                $types1[$key]['name'] = $new_month_statu['name'];
                $types1[$key]['type'] = 'column';
                $types1[$key]['markerType'] = "square";
                $types1[$key]['showInLegend'] = 1;
                $types1[$key]['indexLabelPlacement'] = "outside";
                if (isset($colour[$key])) {
                    $types[$key]['color'] = $colour[$key];
                } else {
                    $types[$key]['color'] = $new_month_statu['colour_code'];
                }
                if (isset($params['type']) AND $params['type'] == 'percentage') {
                    $types1[$key]['indexLabel'] = "{y}%";
                } else {
                    $types1[$key]['indexLabel'] = "{y}";
                }
            }
            $total = 0;
            foreach ($months as $i => $month) {
                if ($month != 'Total' AND $month != 'By%') {
                    $month = dateTimezoneChangeForGraph($month);
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types1[$key]['dataPoints'][$total]['label'] = $month;
                        $total_count = $new_month_status[$last_key]['data'][$i];
                        if ($new_month_statu['data'][$i] == 0) {
                            $types1[$key]['dataPoints'][$total]['y'] = '0%';
                            $new_month_status[$key]['data'][$i] = '0%';
                        } else {
                            $types1[$key]['dataPoints'][$total]['y'] = floatval(number_format(($new_month_statu['data'][$i] / $total_count) * 100, 2));
                            $new_month_status[$key]['data'][$i] = floatval(number_format(($new_month_statu['data'][$i] / $total_count) * 100, 2)) . '%';
                        }
                    } else {
                        if ($new_month_statu['data'][$i] == 0) {
                            $types1[$key]['dataPoints'][$total]['label'] = $month;
                        } else {
                            $types1[$key]['dataPoints'][$total]['label'] = $month;
                            $types1[$key]['dataPoints'][$total]['y'] = $new_month_statu['data'][$i];
                        }
                    }
                    $total = $total + 1;
                }
            }
        }

        $response['new_types'] = $types1;
        $response['types'] = $types;
        $s_no = 1;

        foreach ($month_status as $row) {
            $response['status'][] = $row;
            $response['export'][] = array_merge([$s_no++, $row['name']], $row['data']);
        }

        $s_no = 1;
        foreach ($new_month_status as $row) {
            $response['status_new'][] = $row;
            $response['export_new'][] = array_merge([$s_no++, $row['name']], $row['data']);
        }

        $response['month_status'] = $month_status;
        $response['month_status_new'] = $new_month_status;

        foreach ($response['months'] as $key => $months) {
            if ($months != 'Total' AND $months != 'By%') {
                $response['months'][$key] = dateTimezoneChangeForGraph($months);
            }
        }

        return $response;
    }

    public static function userKnockReportsColourOld($params) {
        if (!isset($params['datetype'])) {
            $params['datetype'] = 'day_part';
        }

        $month_clause = '';
        $status_clause = '';
        $target_user_clause = '';
        $time_clause = '';
        $select_type = '';

        if (!empty($params['target_user_id'])) {
            $month_clause .= " AND  user_lead_knocks.assign_id IN ({$params['target_user_id']}) ";
        }

        if (!empty($params['type_id'])) {
            $month_clause .= " AND  user_lead_knocks.followup_status_id IN ({$params['type_id']}) ";
            $select_type .= " AND id IN ({$params['type_id']}) ";
        }

        $dateinput = json_decode($params['time_slot']);
        if (!empty($dateinput->start) && !empty($dateinput->end)) {
            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');
            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
            $time_clause = "AND lead_history.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        }

        $dateinput = json_decode($params['start_date']);
        if (!empty($dateinput->start) && !empty($dateinput->end)) {
            $date1 = Carbon::parse($dateinput->start);
            $date2 = Carbon::parse($dateinput->end);
            $diffInDays = $date1->diffInDays($date2);
            if ($diffInDays > 8) {
                return false;
            }

            $datetype = $params['datetype'];
            if ($datetype == 'day_part') {
                $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 08:00:00');
                $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 22:00:00');
                $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
                $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
            } else {
                $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 08:00:00');
                $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 22:00:00');
                $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
                $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
            }
            $time_clause = "AND  user_lead_knocks.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        } else {
            $datetype = $params['datetype'];
            if ($datetype == 'day_part') {

                if ($params['time_slot'] !== null) {
                    $params['time_slot'] = [];
                    $params['time_slot']['start'] = date('Y-m-d', strtotime($params['start_date']));
                    $params['time_slot']['end'] = date('Y-m-d', strtotime($params['start_date']));
                    $params['time_slot'] = json_encode($params['time_slot']);
                    $dateinput = json_decode($params['time_slot']);
                } else {
                    $params['time_slot'] = [];
                    $params['time_slot']['start'] = date('Y-m-d');
                    $params['time_slot']['end'] = date('Y-m-d');
                    $params['time_slot'] = json_encode($params['time_slot']);
                    $dateinput = json_decode($params['time_slot']);
                }

                $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 08:00:00');
                $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 22:00:00');
                $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
                $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
            } else {
                $first_history = UserLeadKnocks::OrderBy('id', 'asc')->first();
                $last_history = UserLeadKnocks::OrderBy('id', 'desc')->first();
                $params['time_slot'] = [];
                $params['time_slot']['start'] = date('Y-m-d', strtotime($first_history->created_at));
                $params['time_slot']['end'] = date('Y-m-d', strtotime($last_history->created_at));
                $params['time_slot'] = json_encode($params['time_slot']);
                $dateinput = json_decode($params['time_slot']);
                $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 08:00:00');
                $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 22:00:00');
                $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
                $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
            }
            $time_clause = "AND  user_lead_knocks.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        }


        $status_result = \DB::select("SELECT id, title, color_code FROM status WHERE 0=0 $status_clause $select_type AND tenant_id = 4 AND deleted_at IS NULL ORDER BY title ASC");
        $user_result = \DB::select("SELECT id as assignee_id, concat(user.first_name, ' ', user.last_name) as name FROM user WHERE company_id = {$params['company_id']} $target_user_clause  AND  user_group_id != 3 AND user_group_id = 2 AND deleted_at IS NULL");
        $response = [];
        $temp_response = [];
        $status_map = [];
        $map_user_collection = [];

        foreach ($status_result as $row) {
            if ($row->id != 134 AND $row->id != 135 AND $row->id != 133) {
                $status_map[$row->id]['name'] = $row->title;
                $status_map[$row->id]['data'][$row->id] = 0;
                $status_map[$row->id]['total'][$row->id] = 0;
            }
        }

        foreach ($user_result as $row) {
            $temp_response['user_names'][$row->assignee_id] = $row->name;
//            $temp_response['status'][$row->assignee_id] = $status_map;
        }

        $types = [];
        $start_date = date('Y-m-d', strtotime($dateinput->start));
        $end_date = date('Y-m-d', strtotime($dateinput->end));
        $datetype = $params['datetype'];
        $datetype = 'day_part';
        $total_by_month = [];

        if ($datetype == 'day') {
            $period = CarbonPeriod::create($start_date, $end_date);
            $months = [];
            $months_data = [];
            foreach ($period as $date) {
                $total_by_month[] = 0;
                $months[] = $date->format('Y-m-d');
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'day_part') {
            $start_date = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $end_date = date('Y-m-d H:i:s', strtotime($dateinput->end));
            $date = Carbon::parse($start_date);
            $startTime = Carbon::parse($start_date);
            $endTime = Carbon::parse($end_date);
            $timeSlots = [];
            $currentTime = clone $startTime;
            while ($currentTime <= $endTime) {
                $timeSlots[] = $currentTime->copy();
                $currentTime->addHours(1);
            }
            $months = [];
            $months_data = [];
            foreach ($timeSlots as $date) {
                $total_by_month[] = 0;
                $months[] = $date->format("Y-m-d H:i:s");
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'week') {
            $weeks = Lead::findWeeksBetweenTwoDatesNew($start_date, $end_date);
            $months = [];
            $months_data = [];
            foreach ($weeks as $week) {
                $total_by_month[] = 0;
                $months[] = $week[0] . ':' . $week[1];
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'month') {
            $start_date = date('Y-m-01', strtotime($start_date));
            $end_date = date('Y-m-18', strtotime($end_date));
            $period = new CarbonPeriod($start_date, '1 month', $end_date);
            $months = [];
            $months_data = [];
            $exit_year = [];
            foreach ($period as $date) {
                if (!in_array($date->format("Y M"), $exit_year)) {
                    $exit_year[] = $date->format("Y M");
                    $total_by_month[] = 0;
                    $months[] = $date->format("Y M");
                }
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'year') {
            $start_date = date('Y-01-01', strtotime($start_date));
            $end_date = date('Y-12-31', strtotime($end_date));
            $period = new CarbonPeriod($start_date, '1 year', $end_date);
            $months = [];
            $months_data = [];
            $exit_year = [];
            foreach ($period as $date) {
                if (!in_array($date->format('Y'), $exit_year)) {
                    $exit_year[] = $date->format('Y');
                    $total_by_month[] = 0;
                    $months[] = $date->format('Y');
                }
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        }

        $response['months'] = $months;
        $month_status = [];
        $total_result = \DB::select("SELECT user.id as user_id,user_lead_knocks.status_id as his_status_id,
            user_lead_knocks.created_at,
            user_lead_knocks.status_id FROM  user_lead_knocks INNER JOIN user ON user_lead_knocks.user_id = user.id
                              WHERE 1=1 AND user_lead_knocks.status_id != 77 AND user.user_group_id != 3 $month_clause $time_clause");

        $total_by_month_new1 = $total_by_month;
        $total_by_month_new = $total_by_month;

        foreach ($status_result as $key => $type) {
            if ($key == 0) {
                $total_by_month_new = $total_by_month;
            }
            $month_status[$key]['name'] = $type->title;
            $month_status[$key]['colour_code'] = $type->color_code;
            $month_status[$key]['id'] = $type->id;
            $month_status[$key]['data'] = $total_by_month_new;
            $result = \DB::select("SELECT user.id as user_id,user_lead_knocks.status_id as his_status_id,
            user_lead_knocks.created_at,
            user_lead_knocks.status_id FROM  user_lead_knocks INNER JOIN user ON user_lead_knocks.user_id = user.id
                WHERE user_lead_knocks.status_id != 0 AND user_lead_knocks.status_id != 77 AND user.user_group_id != 3 AND 
                user_lead_knocks.status_id = $type->id $month_clause $time_clause");

            $type_count = count($result);
            $type_total_count = count($total_result);
            if ($type_count != 0 AND $type_total_count != 0) {
                $month_status[$key]['data'][$total_count_array] = $type_count;
                $month_status[$key]['data'][$total_count_per] = floatval(number_format(($type_count / $type_total_count) * 100, 2)) . '%';
            } else {
                $month_status[$key]['data'][$total_count_array] = $type_count;
                $month_status[$key]['data'][$total_count_per] = '0%';
            }

            foreach ($result as $val) {
                if ($datetype == 'day') {
                    $created_at = date('Y-m-d', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }
                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'day_part') {
                    $created_at = dateTimezoneChangeNew($val->created_at);
                    $created_at = date('Y-m-d H:i:s', strtotime($created_at));
                    $targetDateTime = $created_at;
                    $targetDateTime = new DateTime($targetDateTime);
                    $closestKey = null;
                    $closestDiff = PHP_INT_MAX;
                    foreach ($months as $key1 => $dateTimeString) {
                        if (!DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeString)) {
                            continue;
                        }
                        $dateTime = new DateTime($dateTimeString);
                        $diff = $targetDateTime->getTimestamp() - $dateTime->getTimestamp();
                        if ($diff > 0 && $diff < $closestDiff) {
                            $closestDiff = $diff;
                            $closestKey = $key1;
                        }
                    }
                    if ($closestKey != null) {
                        $n_key = $closestKey;
                        if (isset($month_status[$key]['data'][$n_key])) {
                            $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                        } else {
                            $month_status[$key]['data'][$n_key] = 1;
                        }
                        if (isset($total_by_month[$n_key])) {
                            $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                        } else {
                            $total_by_month[$n_key] = 1;
                        }
                    }
                } elseif ($datetype == 'week') {
                    $created_at = date('Y-m-d', strtotime($val->created_at));
                    foreach ($months as $m => $month) {
                        $month_a = explode(":", $month);
                        if (strtotime($month_a[0]) <= strtotime($created_at) AND strtotime($month_a[1]) >= strtotime($created_at)) {
                            $n_key = $m;
                            break;
                        }
                    }
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'month') {
                    $created_at = date('Y M', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }
                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'year') {
                    $created_at = date('Y', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }
                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } else {
                    
                }
            }
        }

        $new_month_status = [];

        $new_month_status[0]['name'] = 'Answered';
        $new_month_status[0]['colour_code'] = '#bd5ce8';
        $new_month_status[0]['data'] = $total_by_month_new1;

        $new_month_status[1]['name'] = 'No Answer';
        $new_month_status[1]['colour_code'] = '#6a75eb';
        $new_month_status[1]['data'] = $total_by_month_new1;

        $no_answer_status_ids = \DB::table('status')
                ->where('tenant_id', $params['company_id'])
                ->whereNull('deleted_at')
                ->where('title', 'LIKE', '%No Ansr%')
                ->pluck('id');

        $no_ids = [];
        foreach ($no_answer_status_ids as $no_answer_status_id) {
            $no_ids[] = $no_answer_status_id;
        }

        $no_id_string = implode(', ', $no_ids);
        $answer_status_ids = \DB::table('status')
                ->where('tenant_id', $params['company_id'])
                ->whereNull('deleted_at')
                ->where('title', 'NOT LIKE', '%No Ansr%')
                ->pluck('id');

        $result1 = \DB::select("SELECT user.id as user_id,user_lead_knocks.status_id as his_status_id,
            user_lead_knocks.created_at,
            user_lead_knocks.status_id FROM  user_lead_knocks INNER JOIN user ON user_lead_knocks.user_id = user.id
                WHERE user_lead_knocks.status_id != 0 AND user_lead_knocks.status_id != 77 AND user.user_group_id != 3 AND 
                user_lead_knocks.status_id IN ($no_id_string) $month_clause $time_clause");
        $type_count1 = count($result1);

        $type_total_count = count($total_result);
        if ($type_count1 != 0 AND $type_total_count != 0) {
            $new_month_status[1]['data'][$total_count_array] = $type_count1;
            $new_month_status[1]['data'][$total_count_per] = floatval(number_format(($type_count1 / $type_total_count) * 100, 2)) . '%';
        } else {
            $new_month_status[1]['data'][$total_count_array] = $type_count1;
            $new_month_status[1]['data'][$total_count_per] = '0%';
        }

        $result2 = \DB::select("SELECT user.id as user_id,user_lead_knocks.status_id as his_status_id,
            user_lead_knocks.created_at,
            user_lead_knocks.status_id FROM  user_lead_knocks INNER JOIN user ON user_lead_knocks.user_id = user.id
                WHERE user_lead_knocks.status_id != 0 AND user_lead_knocks.status_id != 77 AND user.user_group_id != 3 AND 
                user_lead_knocks.status_id NOT IN ($no_id_string) $month_clause $time_clause");
        $type_count2 = count($result2);
        $type_total_count = count($total_result);
        if ($type_count2 != 0 AND $type_total_count != 0) {
            $new_month_status[0]['data'][$total_count_array] = $type_count2;
            $new_month_status[0]['data'][$total_count_per] = floatval(number_format(($type_count2 / $type_total_count) * 100, 2)) . '%';
        } else {
            $new_month_status[0]['data'][$total_count_array] = $type_count2;
            $new_month_status[0]['data'][$total_count_per] = '0%';
        }

        foreach ($status_result as $key => $type) {
            $result = \DB::select("SELECT user.id as user_id,user_lead_knocks.status_id as his_status_id,
            user_lead_knocks.created_at,
            user_lead_knocks.status_id FROM  user_lead_knocks INNER JOIN user ON user_lead_knocks.user_id = user.id
                WHERE user_lead_knocks.status_id != 0 AND user_lead_knocks.status_id != 77 AND user.user_group_id != 3 AND 
                user_lead_knocks.status_id = $type->id $month_clause $time_clause");
            foreach ($result as $val) {
                if ($datetype == 'day_part') {
                    $created_at = dateTimezoneChangeNew($val->created_at);
                    $created_at = date('Y-m-d H:i:s', strtotime($created_at));
                    $targetDateTime = $created_at;
                    $targetDateTime = new DateTime($targetDateTime);
                    $closestKey = null;
                    $closestDiff = PHP_INT_MAX;
                    foreach ($months as $key1 => $dateTimeString) {
                        if (!DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeString)) {
                            continue;
                        }
                        $dateTime = new DateTime($dateTimeString);
                        $diff = $targetDateTime->getTimestamp() - $dateTime->getTimestamp();
                        if ($diff > 0 && $diff < $closestDiff) {
                            $closestDiff = $diff;
                            $closestKey = $key1;
                        }
                    }
                    if ($closestKey != null) {
                        $n_key = $closestKey;
                        if (in_array($val->status_id, $no_ids)) {
                            if (isset($new_month_status[1]['data'][$n_key])) {
                                $new_month_status[1]['data'][$n_key] = $new_month_status[1]['data'][$n_key] + 1;
                            } else {
                                $new_month_status[1]['data'][$n_key] = 1;
                            }
                            if (isset($total_by_month_new1[$n_key])) {
                                $total_by_month_new1[$n_key] = $total_by_month_new1[$n_key] + 1;
                            } else {
                                $total_by_month_new1[$n_key] = 1;
                            }
                        } else {
                            if (isset($new_month_status[0]['data'][$n_key])) {
                                $new_month_status[0]['data'][$n_key] = $new_month_status[0]['data'][$n_key] + 1;
                            } else {
                                $new_month_status[0]['data'][$n_key] = 1;
                            }
                            if (isset($total_by_month_new1[$n_key])) {
                                $total_by_month_new1[$n_key] = $total_by_month_new1[$n_key] + 1;
                            } else {
                                $total_by_month_new1[$n_key] = 1;
                            }
                        }
                    }
                } else {
                    
                }
            }
        }

        $key = $key + 1;
        $month_status[$key]['name'] = 'Total Leads';
        $month_status[$key]['id'] = '';
        $month_status[$key]['data'] = $total_by_month;
        $month_status[$key]['data'][$total_count_array] = $type_total_count;
        $month_status[$key]['data'][$total_count_per] = '100%';
        $last_key = $key;


        foreach ($month_status as $key => $month_statu) {
            if ($last_key == $key) {
                $types[$key]['name'] = $month_statu['name'];
                $types[$key]['type'] = 'line';
                $types[$key]['showInLegend'] = 1;
                $types[$key]['toolTipContent'] = null;
                $types[$key]['markerType'] = "square";
                $types[$key]['indexLabelPlacement'] = "outside";
                $types[$key]['color'] = 'transparent';
                if (isset($params['type']) AND $params['type'] == 'percentage') {
                    $types[$key]['indexLabel'] = "{y}%";
                } else {
                    $types[$key]['indexLabel'] = "{y}";
                }
            } else {
                $types[$key]['name'] = $month_statu['name'];
                $types[$key]['type'] = 'stackedColumn';
                $types[$key]['markerType'] = "square";
                $types[$key]['showInLegend'] = 1;
                if (isset($colour[$key])) {
                    $types[$key]['color'] = $colour[$key];
                } else {
                    $types[$key]['color'] = $month_statu['colour_code'];
                }


                if (count($months) < 15) {
                    $types[$key]['indexLabelFontColor'] = 'white';
                    $types[$key]['indexLabelFontSize'] = 10;
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types[$key]['indexLabel'] = "{y}%";
                    } else {
                        $types[$key]['indexLabel'] = "{y}";
                    }
                }
            }
            $total = 0;
            foreach ($months as $i => $month) {
                if ($month != 'Total' AND $month != 'By%') {
                    $month = dateTimezoneChangeNewMinus($month);
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types[$key]['dataPoints'][$total]['label'] = $month;
                        $total_count = $month_status[$last_key]['data'][$i];
                        if ($month_statu['data'][$i] == 0) {
                            $types[$key]['dataPoints'][$total]['y'] = '0%';
                            $month_status[$key]['data'][$i] = '0%';
                        } else {
                            $types[$key]['dataPoints'][$total]['y'] = floatval(number_format(($month_statu['data'][$i] / $total_count) * 100, 2));
                            $month_status[$key]['data'][$i] = floatval(number_format(($month_statu['data'][$i] / $total_count) * 100, 2)) . '%';
                        }
                    } else {
                        if ($month_statu['data'][$i] == 0) {
                            $types[$key]['dataPoints'][$total]['label'] = $month;
                        } else {
                            $types[$key]['dataPoints'][$total]['label'] = $month;
                            $types[$key]['dataPoints'][$total]['y'] = $month_statu['data'][$i];
                        }
                    }
                    $total = $total + 1;
                }
            }
        }

        $key = 1 + 1;
        $new_month_status[$key]['name'] = 'Total Leads';
        $new_month_status[$key]['colour_code'] = '';
        $new_month_status[$key]['data'] = $total_by_month;
        $new_month_status[$key]['data'][$total_count_array] = $type_total_count;
        $new_month_status[$key]['data'][$total_count_per] = '100%';
        $last_key = $key;

        foreach ($new_month_status as $key => $new_month_statu) {
            if ($last_key == $key) {
                $types1[$key]['name'] = $new_month_statu['name'];
                $types1[$key]['type'] = 'line';
                $types1[$key]['showInLegend'] = 1;
                $types1[$key]['toolTipContent'] = null;
                $types1[$key]['markerType'] = "square";
                $types1[$key]['indexLabelPlacement'] = "outside";
                $types1[$key]['color'] = 'transparent';
                if (isset($params['type']) AND $params['type'] == 'percentage') {
                    $types1[$key]['indexLabel'] = "{y}%";
                } else {
                    $types1[$key]['indexLabel'] = "{y}";
                }
            } else {
                $types1[$key]['name'] = $new_month_statu['name'];
                $types1[$key]['type'] = 'stackedColumn';
                $types1[$key]['markerType'] = "square";
                $types1[$key]['showInLegend'] = 1;
                if (isset($colour[$key])) {
                    $types[$key]['color'] = $colour[$key];
                } else {
                    $types[$key]['color'] = $new_month_statu['colour_code'];
                }
                if (count($months) < 15) {
                    $types1[$key]['indexLabelFontColor'] = 'white';
                    $types1[$key]['indexLabelFontSize'] = 10;
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types1[$key]['indexLabel'] = "{y}%";
                    } else {
                        $types1[$key]['indexLabel'] = "{y}";
                    }
                }
            }
            $total = 0;
            foreach ($months as $i => $month) {
                if ($month != 'Total' AND $month != 'By%') {
                    $month = dateTimezoneChangeNewMinus($month);
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types1[$key]['dataPoints'][$total]['label'] = $month;
                        $total_count = $new_month_status[$last_key]['data'][$i];
                        if ($new_month_statu['data'][$i] == 0) {
                            $types1[$key]['dataPoints'][$total]['y'] = '0%';
                            $new_month_status[$key]['data'][$i] = '0%';
                        } else {
                            $types1[$key]['dataPoints'][$total]['y'] = floatval(number_format(($new_month_statu['data'][$i] / $total_count) * 100, 2));
                            $new_month_status[$key]['data'][$i] = floatval(number_format(($new_month_statu['data'][$i] / $total_count) * 100, 2)) . '%';
                        }
                    } else {
                        if ($new_month_statu['data'][$i] == 0) {
                            $types1[$key]['dataPoints'][$total]['label'] = $month;
                        } else {
                            $types1[$key]['dataPoints'][$total]['label'] = $month;
                            $types1[$key]['dataPoints'][$total]['y'] = $new_month_statu['data'][$i];
                        }
                    }
                    $total = $total + 1;
                }
            }
        }

        $response['new_types'] = $types1;
        $response['types'] = $types;
        $s_no = 1;

        foreach ($month_status as $row) {
            $response['status'][] = $row;
            $response['export'][] = array_merge([$s_no++, $row['name']], $row['data']);
        }

        $s_no = 1;
        foreach ($new_month_status as $row) {
            $response['status_new'][] = $row;
            $response['export_new'][] = array_merge([$s_no++, $row['name']], $row['data']);
        }

        $response['month_status'] = $month_status;
        $response['month_status_new'] = $new_month_status;

        foreach ($response['months'] as $key => $months) {
            if ($months != 'Total' AND $months != 'By%') {
                $response['months'][$key] = dateTimezoneChangeNewMinus($months);
            }
        }

        return $response;
    }

    public static function userKnockReports($params) {
        $month_clause = '';
        $status_clause = '';
        $target_user_clause = '';
        $time_clause = '';
        $select_type = '';
        if (!empty($params['target_user_id'])) {
            $month_clause .= " AND  user_lead_knocks.assign_id IN ({$params['target_user_id']}) ";
        }
        if (!empty($params['type_id'])) {
            $month_clause .= " AND  user_lead_knocks.followup_status_id IN ({$params['type_id']}) ";
            $select_type .= " AND id IN ({$params['type_id']}) ";
        }
        $dateinput = json_decode($params['time_slot']);
        if (!empty($dateinput->start) && !empty($dateinput->end)) {

            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));

            $time_clause = "AND lead_history.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        }

        $dateinput = json_decode($params['start_date']);
        if (!empty($dateinput->start) && !empty($dateinput->end)) {
            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));

            $time_clause = "AND  user_lead_knocks.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        } else {
            $first_history = UserLeadKnocks::OrderBy('id', 'asc')->first();
            $last_history = UserLeadKnocks::OrderBy('id', 'desc')->first();
            $params['time_slot'] = [];
            $params['time_slot']['start'] = date('Y-m-d', strtotime($first_history->created_at));
            $params['time_slot']['end'] = date('Y-m-d', strtotime($last_history->created_at));
            $params['time_slot'] = json_encode($params['time_slot']);
            $dateinput = json_decode($params['time_slot']);

            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
            $time_clause = "AND  user_lead_knocks.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        }

        $result = \DB::select("SELECT count(*) as user_lead_total, lead_history.assign_id as assignee_id, lead_history.status_id, concat(user.first_name, ' ', user.last_name) as name, 
                              status.title as status_title FROM lead_history
                              INNER JOIN user ON user.id = lead_history.assign_id
                              INNER JOIN status ON status.id = lead_history.status_id                              
                              WHERE user.company_id = {$params['company_id']} AND user.user_group_id != 3  $month_clause
                              group by status_id,assign_id");

        $status_result = \DB::select("SELECT id, title, color_code FROM status WHERE 0=0 $status_clause $select_type AND tenant_id = 4 AND deleted_at IS NULL ORDER BY title ASC");
        $user_result = \DB::select("SELECT id as assignee_id, concat(user.first_name, ' ', user.last_name) as name FROM user WHERE company_id = {$params['company_id']} $target_user_clause  AND  user_group_id != 3 AND user_group_id = 2 AND deleted_at IS NULL");
        $response = [];
        $temp_response = [];
        $status_map = [];
        $map_user_collection = [];
        foreach ($status_result as $row) {
            if ($row->id != 134 AND $row->id != 135 AND $row->id != 133) {
                $status_map[$row->id]['name'] = $row->title;
                $status_map[$row->id]['data'][$row->id] = 0;
                $status_map[$row->id]['total'][$row->id] = 0;
            }
        }
        foreach ($user_result as $row) {
            $temp_response['user_names'][$row->assignee_id] = $row->name;
            $temp_response['status'][$row->assignee_id] = $status_map;
        }

        $lead_types = \DB::select("SELECT id, title FROM status WHERE tenant_id = {$params['company_id']} $status_clause AND deleted_at IS NULL ORDER BY order_by");
        $types = [];

        $start_date = date('Y-m-d', strtotime($dateinput->start));
        $end_date = date('Y-m-d', strtotime($dateinput->end));
        $datetype = $params['datetype'];
        $total_by_month = [];
        if ($datetype == 'day') {
            $period = CarbonPeriod::create($start_date, $end_date);
            $months = [];
            $months_data = [];
            foreach ($period as $date) {
                $total_by_month[] = 0;
                $months[] = $date->format('Y-m-d');
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'week') {
            $weeks = Lead::findWeeksBetweenTwoDatesNew($start_date, $end_date);
            $months = [];
            $months_data = [];
            foreach ($weeks as $week) {
                $total_by_month[] = 0;
                $months[] = $week[0] . ':' . $week[1];
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'month') {
            $start_date = date('Y-m-01', strtotime($start_date));
            $end_date = date('Y-m-18', strtotime($end_date));
            $period = new CarbonPeriod($start_date, '1 month', $end_date);
            $months = [];
            $months_data = [];
            $exit_year = [];
            foreach ($period as $date) {
                if (!in_array($date->format("Y M"), $exit_year)) {
                    $exit_year[] = $date->format("Y M");
                    $total_by_month[] = 0;
                    $months[] = $date->format("Y M");
                }
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'year') {
            $start_date = date('Y-01-01', strtotime($start_date));
            $end_date = date('Y-12-31', strtotime($end_date));
            $period = new CarbonPeriod($start_date, '1 year', $end_date);
            $months = [];
            $months_data = [];
            $exit_year = [];
            foreach ($period as $date) {
                if (!in_array($date->format('Y'), $exit_year)) {
                    $exit_year[] = $date->format('Y');
                    $total_by_month[] = 0;
                    $months[] = $date->format('Y');
                }
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        }
        $response['months'] = $months;
        $month_status = [];

        $total_result = \DB::select("SELECT user.id as user_id,user_lead_knocks.status_id as his_status_id,
            user_lead_knocks.created_at,
            user_lead_knocks.status_id FROM  user_lead_knocks INNER JOIN user ON user_lead_knocks.user_id = user.id
                              WHERE 1=1 AND user_lead_knocks.status_id != 77 AND user.user_group_id != 3 $month_clause $time_clause");

        $total_by_month_new = $total_by_month;
        foreach ($status_result as $key => $type) {
            if ($key == 0) {
                $total_by_month_new = $total_by_month;
            }
            $month_status[$key]['name'] = $type->title;
            $month_status[$key]['colour_code'] = $type->color_code;
            $month_status[$key]['id'] = $type->id;
            $month_status[$key]['data'] = $total_by_month_new;

            $result = \DB::select("SELECT user.id as user_id,user_lead_knocks.status_id as his_status_id,
            user_lead_knocks.created_at,
            user_lead_knocks.status_id FROM  user_lead_knocks INNER JOIN user ON user_lead_knocks.user_id = user.id
                WHERE user_lead_knocks.status_id != 0 AND user_lead_knocks.status_id != 77 AND user.user_group_id != 3 AND 
                user_lead_knocks.status_id = $type->id $month_clause $time_clause");


            $type_count = count($result);
            $type_total_count = count($total_result);
            if ($type_count != 0 AND $type_total_count != 0) {
                $month_status[$key]['data'][$total_count_array] = $type_count;
                $month_status[$key]['data'][$total_count_per] = floatval(number_format(($type_count / $type_total_count) * 100, 2)) . '%';
            } else {
                $month_status[$key]['data'][$total_count_array] = $type_count;
                $month_status[$key]['data'][$total_count_per] = '0%';
            }
            foreach ($result as $val) {
                if ($datetype == 'day') {
//                    $val->created_at = dateTimezoneChange($val->created_at);
                    $created_at = date('Y-m-d', strtotime($val->created_at));

                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'week') {
                    $created_at = date('Y-m-d', strtotime($val->created_at));

                    foreach ($months as $m => $month) {
                        $month_a = explode(":", $month);
                        if (strtotime($month_a[0]) <= strtotime($created_at) AND strtotime($month_a[1]) >= strtotime($created_at)) {
                            $n_key = $m;
                            break;
                        }
                    }
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'month') {
                    $created_at = date('Y M', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }
                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'year') {
                    $created_at = date('Y', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } else {
                    
                }
            }
        }
        $key = $key + 1;
        $month_status[$key]['name'] = 'Total Leads';
        $month_status[$key]['id'] = '';
        $month_status[$key]['data'] = $total_by_month;
        $month_status[$key]['data'][$total_count_array] = $type_total_count;
        $month_status[$key]['data'][$total_count_per] = '100%';
        $last_key = $key;

        foreach ($month_status as $key => $month_statu) {
            if ($last_key == $key) {
//                $types[$key]['name'] = $month_statu['name'];
                $types[$key]['name'] = '';
                $types[$key]['type'] = 'stackedColumn';
                $types[$key]['showInLegend'] = 1;
                $types[$key]['toolTipContent'] = null;
                $types[$key]['markerType'] = "square";
                $types[$key]['indexLabelPlacement'] = "outside";
                $types[$key]['color'] = '#113F85';
                if (isset($params['type']) AND $params['type'] == 'percentage') {
                    $types[$key]['indexLabel'] = "{y}%";
                } else {
                    $types[$key]['indexLabel'] = "{y}";
                }
            } else {
                $types[$key]['name'] = '';
                $types[$key]['type'] = '';
                $types[$key]['markerType'] = "square";
                $types[$key]['showInLegend'] = 1;
                $types[$key]['color'] = $month_statu['colour_code'];
                if (count($months) < 15) {
                    $types[$key]['indexLabelFontColor'] = '#113F85';
                    $types[$key]['indexLabelFontSize'] = 0;
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types[$key]['indexLabel'] = "{y}%";
                    } else {
                        $types[$key]['indexLabel'] = "{y}";
                    }
                }
            }

            $total = 0;
            foreach ($months as $i => $month) {
                if ($month != 'Total' AND $month != 'By%') {
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types[$key]['dataPoints'][$total]['label'] = $month;
                        $total_count = $month_status[$last_key]['data'][$i];
                        if ($month_statu['data'][$i] == 0) {
                            $types[$key]['dataPoints'][$total]['y'] = '0%';
                            $month_status[$key]['data'][$i] = '0%';
                        } else {
                            $types[$key]['dataPoints'][$total]['y'] = floatval(number_format(($month_statu['data'][$i] / $total_count) * 100, 2));
                            $month_status[$key]['data'][$i] = floatval(number_format(($month_statu['data'][$i] / $total_count) * 100, 2)) . '%';
                        }
                    } else {
                        if ($month_statu['data'][$i] == 0) {
                            $types[$key]['dataPoints'][$total]['label'] = $month;
//                            $types[$key]['dataPoints'][$total]['y'] = '';
                        } else {
                            $types[$key]['dataPoints'][$total]['label'] = $month;
                            $types[$key]['dataPoints'][$total]['y'] = $month_statu['data'][$i];
                        }
                    }
                    $total = $total + 1;
                }
            }
        }

        $response['types'] = $types;

        $s_no = 1;
        foreach ($month_status as $row) {
            $response['status'][] = $row;
            $response['export'][] = array_merge([$s_no++, $row['name']], $row['data']);
        }


        $response['month_status'] = $month_status;




        return $response;
    }

    public static function userReportKnockNotContracted($params) {
        $month_clause = '';
        $status_clause = '';
        $target_user_clause = '';
        $time_clause = '';
        $select_type = '';
        if (!empty($params['target_user_id'])) {
            $month_clause .= " AND  user_lead_knocks.assign_id IN ({$params['target_user_id']}) ";
        }
        if (!empty($params['type_id'])) {
            $month_clause .= " AND  user_lead_knocks.status_id IN ({$params['type_id']}) ";
            $select_type .= " AND id IN ({$params['type_id']}) ";
        }
        $dateinput = json_decode($params['time_slot']);
        if (!empty($dateinput->start) && !empty($dateinput->end)) {

            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));

            $time_clause = "AND lead_history.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        }

        $dateinput = json_decode($params['start_date']);
        if (!empty($dateinput->start) && !empty($dateinput->end)) {
            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));

            $time_clause = "AND  user_lead_knocks.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        } else {
            $first_history = UserLeadKnocks::OrderBy('id', 'asc')->first();
            $last_history = UserLeadKnocks::OrderBy('id', 'desc')->first();
            $params['time_slot'] = [];
            $params['time_slot']['start'] = date('Y-m-d', strtotime($first_history->created_at));
            $params['time_slot']['end'] = date('Y-m-d', strtotime($last_history->created_at));
            $params['time_slot'] = json_encode($params['time_slot']);
            $dateinput = json_decode($params['time_slot']);

            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
            $time_clause = "AND  user_lead_knocks.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        }

//        $result = \DB::select("SELECT count(*) as user_lead_total, lead_history.assign_id as assignee_id, lead_history.status_id, concat(user.first_name, ' ', user.last_name) as name, 
//                              status.title as status_title FROM lead_history
//                              INNER JOIN user ON user.id = lead_history.assign_id
//                              INNER JOIN status ON status.id = lead_history.status_id                              
//                              WHERE user.company_id = {$params['company_id']} AND user.user_group_id != 3  $month_clause
//                              group by status_id,assign_id");

        $status_result = \DB::select("SELECT id, title, color_code FROM status WHERE 0=0 $status_clause $select_type AND tenant_id = 4 AND deleted_at IS NULL ORDER BY title ASC");
        $user_result = \DB::select("SELECT id as assignee_id, concat(user.first_name, ' ', user.last_name) as name FROM user WHERE company_id = {$params['company_id']} $target_user_clause  AND  user_group_id != 3 AND user_group_id = 2 AND deleted_at IS NULL");
        $response = [];
        $temp_response = [];
        $status_map = [];
        $map_user_collection = [];
        foreach ($status_result as $row) {
            if ($row->id != 134 AND $row->id != 135 AND $row->id != 133) {
                $status_map[$row->id]['name'] = $row->title;
                $status_map[$row->id]['data'][$row->id] = 0;
                $status_map[$row->id]['total'][$row->id] = 0;
            }
        }
        foreach ($user_result as $row) {
            $temp_response['user_names'][$row->assignee_id] = $row->name;
            $temp_response['status'][$row->assignee_id] = $status_map;
        }

        $lead_types = \DB::select("SELECT id, title FROM status WHERE tenant_id = {$params['company_id']} $status_clause AND deleted_at IS NULL ORDER BY order_by");
        $types = [];

        $start_date = date('Y-m-d', strtotime($dateinput->start));
        $end_date = date('Y-m-d', strtotime($dateinput->end));
        $datetype = $params['datetype'];
        $total_by_month = [];
        if ($datetype == 'day') {
            $period = CarbonPeriod::create($start_date, $end_date);
            $months = [];
            $months_data = [];
            foreach ($period as $date) {
                $total_by_month[] = 0;
                $months[] = $date->format('Y-m-d');
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'week') {
            $weeks = Lead::findWeeksBetweenTwoDatesNew($start_date, $end_date);
            $months = [];
            $months_data = [];
            foreach ($weeks as $week) {
                $total_by_month[] = 0;
                $months[] = $week[0] . ':' . $week[1];
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'month') {
            $start_date = date('Y-m-01', strtotime($start_date));
            $end_date = date('Y-m-18', strtotime($end_date));
            $period = new CarbonPeriod($start_date, '1 month', $end_date);
            $months = [];
            $months_data = [];
            $exit_year = [];
            foreach ($period as $date) {
                if (!in_array($date->format("Y M"), $exit_year)) {
                    $exit_year[] = $date->format("Y M");
                    $total_by_month[] = 0;
                    $months[] = $date->format("Y M");
                }
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'year') {
            $start_date = date('Y-01-01', strtotime($start_date));
            $end_date = date('Y-12-31', strtotime($end_date));
            $period = new CarbonPeriod($start_date, '1 year', $end_date);
            $months = [];
            $months_data = [];
            $exit_year = [];
            foreach ($period as $date) {
                if (!in_array($date->format('Y'), $exit_year)) {
                    $exit_year[] = $date->format('Y');
                    $total_by_month[] = 0;
                    $months[] = $date->format('Y');
                }
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        }
        $response['months'] = $months;
        $month_status = [];
        $total_result = \DB::select("SELECT user.id as user_id,user_lead_knocks.status_id as his_status_id,
            user_lead_knocks.created_at,
            user_lead_knocks.status_id FROM  user_lead_knocks INNER JOIN user ON user_lead_knocks.user_id = user.id
                              WHERE 1=1 AND user_lead_knocks.status_id = 77 AND user.user_group_id != 3 $month_clause $time_clause");

        $total_by_month_new = $total_by_month;
        foreach ($status_result as $key => $type) {
            if ($key == 0) {
                $total_by_month_new = $total_by_month;
            }
            $month_status[$key]['name'] = $type->title;
            $month_status[$key]['colour_code'] = $type->color_code;
            $month_status[$key]['id'] = $type->id;
            $month_status[$key]['data'] = $total_by_month_new;

            $result = \DB::select("SELECT user.id as user_id,user_lead_knocks.status_id as his_status_id,
            user_lead_knocks.created_at,
            user_lead_knocks.status_id FROM  user_lead_knocks INNER JOIN user ON user_lead_knocks.user_id = user.id
                WHERE user_lead_knocks.status_id != 0 AND user_lead_knocks.status_id = 77 AND user.user_group_id != 3 AND 
                user_lead_knocks.status_id = $type->id $month_clause $time_clause");
            $type_count = count($result);
            $type_total_count = count($total_result);
            if ($type_count != 0 AND $type_total_count != 0) {
                $month_status[$key]['data'][$total_count_array] = $type_count;
                $month_status[$key]['data'][$total_count_per] = floatval(number_format(($type_count / $type_total_count) * 100, 2)) . '%';
            } else {
                $month_status[$key]['data'][$total_count_array] = $type_count;
                $month_status[$key]['data'][$total_count_per] = '0%';
            }
            foreach ($result as $val) {
                if ($datetype == 'day') {
                    $created_at = date('Y-m-d', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'week') {
                    $created_at = date('Y-m-d', strtotime($val->created_at));

                    foreach ($months as $m => $month) {
                        $month_a = explode(":", $month);
                        if (strtotime($month_a[0]) <= strtotime($created_at) AND strtotime($month_a[1]) >= strtotime($created_at)) {
                            $n_key = $m;
                            break;
                        }
                    }
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'month') {
                    $created_at = date('Y M', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }
                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'year') {
                    $created_at = date('Y', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } else {
                    
                }
            }
        }
        $key = $key + 1;
        $month_status[$key]['name'] = 'Total Leads';
        $month_status[$key]['id'] = '';
        $month_status[$key]['data'] = $total_by_month;
        $month_status[$key]['data'][$total_count_array] = $type_total_count;
        $month_status[$key]['data'][$total_count_per] = '100%';
        $last_key = $key;

        foreach ($month_status as $key => $month_statu) {


            if ($last_key == $key) {
                $types[$key]['name'] = $month_statu['name'];
                $types[$key]['type'] = 'line';
                $types[$key]['showInLegend'] = 1;
                $types[$key]['toolTipContent'] = null;
                $types[$key]['markerType'] = "square";
                $types[$key]['indexLabelPlacement'] = "outside";
                $types[$key]['color'] = 'transparent';
                if (isset($params['type']) AND $params['type'] == 'percentage') {
                    $types[$key]['indexLabel'] = "{y}%";
                } else {
                    $types[$key]['indexLabel'] = "{y}";
                }
            } else {
                $types[$key]['name'] = $month_statu['name'];
                $types[$key]['type'] = 'stackedColumn';
                $types[$key]['markerType'] = "square";
                $types[$key]['showInLegend'] = 1;
                $types[$key]['color'] = $month_statu['colour_code'];
//                $types[$key]['color'] = $colour[$key];
                if (count($months) < 15) {
                    $types[$key]['indexLabelFontColor'] = 'white';
                    $types[$key]['indexLabelFontSize'] = 10;
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types[$key]['indexLabel'] = "{y}%";
                    } else {
                        $types[$key]['indexLabel'] = "{y}";
                    }
                }
            }

            $total = 0;
            foreach ($months as $i => $month) {
                if ($month != 'Total' AND $month != 'By%') {
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types[$key]['dataPoints'][$total]['label'] = $month;
                        $total_count = $month_status[$last_key]['data'][$i];
                        if ($month_statu['data'][$i] == 0) {
                            $types[$key]['dataPoints'][$total]['y'] = '0%';
                            $month_status[$key]['data'][$i] = '0%';
                        } else {
                            $types[$key]['dataPoints'][$total]['y'] = floatval(number_format(($month_statu['data'][$i] / $total_count) * 100, 2));
                            $month_status[$key]['data'][$i] = floatval(number_format(($month_statu['data'][$i] / $total_count) * 100, 2)) . '%';
                        }
                    } else {
                        if ($month_statu['data'][$i] == 0) {
                            $types[$key]['dataPoints'][$total]['label'] = $month;
//                            $types[$key]['dataPoints'][$total]['y'] = '';
                        } else {
                            $types[$key]['dataPoints'][$total]['label'] = $month;
                            $types[$key]['dataPoints'][$total]['y'] = $month_statu['data'][$i];
                        }
                    }
                    $total = $total + 1;
                }
            }
        }

        $response['types'] = $types;

        $s_no = 1;
        foreach ($month_status as $row) {
            $response['status'][] = $row;
            $response['export'][] = array_merge([$s_no++, $row['name']], $row['data']);
        }

        $response['month_status'] = $month_status;

        return $response;
    }

    public static function userReportKnockDayReport($params) {
        $month_clause = '';
        $status_clause = '';
        $target_user_clause = '';
        $time_clause = '';
        $select_type = '';
        if (!empty($params['target_user_id'])) {
            $month_clause .= " AND  user_lead_knocks.assign_id IN ({$params['target_user_id']}) ";
        }
        if (!empty($params['type_id'])) {
            $month_clause .= " AND  user_lead_knocks.status_id IN ({$params['type_id']}) ";
            $select_type .= " AND id IN ({$params['type_id']}) ";
        }
        $dateinput = json_decode($params['time_slot']);
        if (!empty($dateinput->start) && !empty($dateinput->end)) {
            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');
            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
            $time_clause = "AND lead_history.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        }

        $dateinput = json_decode($params['start_date']);
        if (!empty($dateinput->start) && !empty($dateinput->end)) {
            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');
            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
            $time_clause = "AND  user_lead_knocks.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        } else {
            $first_history = UserLeadKnocks::OrderBy('id', 'asc')->first();
            $last_history = UserLeadKnocks::OrderBy('id', 'desc')->first();
            $params['time_slot'] = [];
            $params['time_slot']['start'] = date('Y-m-d', strtotime($first_history->created_at));
            $params['time_slot']['end'] = date('Y-m-d', strtotime($last_history->created_at));
            $params['time_slot'] = json_encode($params['time_slot']);
            $dateinput = json_decode($params['time_slot']);
            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');
            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
            $time_clause = "AND  user_lead_knocks.created_at between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        }

        $status_result = \DB::select("SELECT id, title, color_code FROM status WHERE 0=0 $status_clause $select_type AND tenant_id = 4 AND deleted_at IS NULL ORDER BY title ASC");
        $user_result = \DB::select("SELECT id as assignee_id, concat(user.first_name, ' ', user.last_name) as name FROM user WHERE company_id = {$params['company_id']} $target_user_clause  AND  user_group_id != 3 AND user_group_id = 2 AND deleted_at IS NULL");
        $response = [];
        $temp_response = [];
        $status_map = [];
        $map_user_collection = [];
        foreach ($status_result as $row) {
            if ($row->id != 134 AND $row->id != 135 AND $row->id != 133) {
                $status_map[$row->id]['name'] = $row->title;
                $status_map[$row->id]['data'][$row->id] = 0;
                $status_map[$row->id]['total'][$row->id] = 0;
            }
        }
        foreach ($user_result as $row) {
            $temp_response['user_names'][$row->assignee_id] = $row->name;
            $temp_response['status'][$row->assignee_id] = $status_map;
        }

        $lead_types = \DB::select("SELECT id, title FROM status WHERE tenant_id = {$params['company_id']} $status_clause AND deleted_at IS NULL ORDER BY order_by");
        $types = [];

        $start_date = date('Y-m-d', strtotime($dateinput->start));
        $end_date = date('Y-m-d', strtotime($dateinput->end));
        $datetype = $params['datetype'];
        $total_by_month = [];
        if ($datetype == 'day') {
            $period = CarbonPeriod::create($start_date, $end_date);
            $months = [];
            $months_data = [];
            foreach ($period as $date) {
                $total_by_month[] = 0;
                $months[] = $date->format('Y-m-d');
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'week') {
            $weeks = Lead::findWeeksBetweenTwoDatesNew($start_date, $end_date);
            $months = [];
            $months_data = [];
            foreach ($weeks as $week) {
                $total_by_month[] = 0;
                $months[] = $week[0] . ':' . $week[1];
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'month') {
            $start_date = date('Y-m-01', strtotime($start_date));
            $end_date = date('Y-m-18', strtotime($end_date));
            $period = new CarbonPeriod($start_date, '1 month', $end_date);
            $months = [];
            $months_data = [];
            $exit_year = [];
            foreach ($period as $date) {
                if (!in_array($date->format("Y M"), $exit_year)) {
                    $exit_year[] = $date->format("Y M");
                    $total_by_month[] = 0;
                    $months[] = $date->format("Y M");
                }
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'year') {
            $start_date = date('Y-01-01', strtotime($start_date));
            $end_date = date('Y-12-31', strtotime($end_date));
            $period = new CarbonPeriod($start_date, '1 year', $end_date);
            $months = [];
            $months_data = [];
            $exit_year = [];
            foreach ($period as $date) {
                if (!in_array($date->format('Y'), $exit_year)) {
                    $exit_year[] = $date->format('Y');
                    $total_by_month[] = 0;
                    $months[] = $date->format('Y');
                }
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        }
        $response['months'] = $months;
        $month_status = [];
        $total_result = \DB::select("SELECT user.id as user_id,user_lead_knocks.status_id as his_status_id,
            user_lead_knocks.created_at,
            user_lead_knocks.status_id FROM  user_lead_knocks INNER JOIN user ON user_lead_knocks.user_id = user.id
                              WHERE 1=1 AND user_lead_knocks.status_id = 77 AND user.user_group_id != 3 $month_clause $time_clause");

        $total_by_month_new = $total_by_month;
        foreach ($status_result as $key => $type) {
            if ($key == 0) {
                $total_by_month_new = $total_by_month;
            }
            $month_status[$key]['name'] = $type->title;
            $month_status[$key]['colour_code'] = $type->color_code;
            $month_status[$key]['id'] = $type->id;
            $month_status[$key]['data'] = $total_by_month_new;

            $result = \DB::select("SELECT user.id as user_id,user_lead_knocks.status_id as his_status_id,
            user_lead_knocks.created_at,
            user_lead_knocks.status_id FROM  user_lead_knocks INNER JOIN user ON user_lead_knocks.user_id = user.id
                WHERE user_lead_knocks.status_id != 0 AND user_lead_knocks.status_id = 77 AND user.user_group_id != 3 AND 
                user_lead_knocks.status_id = $type->id $month_clause $time_clause");
            $type_count = count($result);
            $type_total_count = count($total_result);
            if ($type_count != 0 AND $type_total_count != 0) {
                $month_status[$key]['data'][$total_count_array] = $type_count;
                $month_status[$key]['data'][$total_count_per] = floatval(number_format(($type_count / $type_total_count) * 100, 2)) . '%';
            } else {
                $month_status[$key]['data'][$total_count_array] = $type_count;
                $month_status[$key]['data'][$total_count_per] = '0%';
            }
            foreach ($result as $val) {
                if ($datetype == 'day') {
                    $created_at = date('Y-m-d', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'week') {
                    $created_at = date('Y-m-d', strtotime($val->created_at));

                    foreach ($months as $m => $month) {
                        $month_a = explode(":", $month);
                        if (strtotime($month_a[0]) <= strtotime($created_at) AND strtotime($month_a[1]) >= strtotime($created_at)) {
                            $n_key = $m;
                            break;
                        }
                    }
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'month') {
                    $created_at = date('Y M', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }
                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'year') {
                    $created_at = date('Y', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } else {
                    
                }
            }
        }
        $key = $key + 1;
        $month_status[$key]['name'] = 'Total Leads';
        $month_status[$key]['id'] = '';
        $month_status[$key]['data'] = $total_by_month;
        $month_status[$key]['data'][$total_count_array] = $type_total_count;
        $month_status[$key]['data'][$total_count_per] = '100%';
        $last_key = $key;

        foreach ($month_status as $key => $month_statu) {
            if ($last_key == $key) {
                $types[$key]['name'] = $month_statu['name'];
                $types[$key]['type'] = 'line';
                $types[$key]['showInLegend'] = 1;
                $types[$key]['toolTipContent'] = null;
                $types[$key]['markerType'] = "square";
                $types[$key]['indexLabelPlacement'] = "outside";
                $types[$key]['color'] = 'transparent';
                if (isset($params['type']) AND $params['type'] == 'percentage') {
                    $types[$key]['indexLabel'] = "{y}%";
                } else {
                    $types[$key]['indexLabel'] = "{y}";
                }
            } else {
                $types[$key]['name'] = $month_statu['name'];
                $types[$key]['type'] = 'stackedColumn';
                $types[$key]['markerType'] = "square";
                $types[$key]['showInLegend'] = 1;
                $types[$key]['color'] = $month_statu['colour_code'];
                if (count($months) < 15) {
                    $types[$key]['indexLabelFontColor'] = 'white';
                    $types[$key]['indexLabelFontSize'] = 10;
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types[$key]['indexLabel'] = "{y}%";
                    } else {
                        $types[$key]['indexLabel'] = "{y}";
                    }
                }
            }

            $total = 0;
            foreach ($months as $i => $month) {
                if ($month != 'Total' AND $month != 'By%') {
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types[$key]['dataPoints'][$total]['label'] = $month;
                        $total_count = $month_status[$last_key]['data'][$i];
                        if ($month_statu['data'][$i] == 0) {
                            $types[$key]['dataPoints'][$total]['y'] = '0%';
                            $month_status[$key]['data'][$i] = '0%';
                        } else {
                            $types[$key]['dataPoints'][$total]['y'] = floatval(number_format(($month_statu['data'][$i] / $total_count) * 100, 2));
                            $month_status[$key]['data'][$i] = floatval(number_format(($month_statu['data'][$i] / $total_count) * 100, 2)) . '%';
                        }
                    } else {
                        if ($month_statu['data'][$i] == 0) {
                            $types[$key]['dataPoints'][$total]['label'] = $month;
                        } else {
                            $types[$key]['dataPoints'][$total]['label'] = $month;
                            $types[$key]['dataPoints'][$total]['y'] = $month_statu['data'][$i];
                        }
                    }
                    $total = $total + 1;
                }
            }
        }
        $response['types'] = $types;
        $s_no = 1;
        foreach ($month_status as $row) {
            $response['status'][] = $row;
            $response['export'][] = array_merge([$s_no++, $row['name']], $row['data']);
        }
        $response['month_status'] = $month_status;
        return $response;
    }

    public static function leadTypeUserReportDashboardKnocksStatistics($params) {
        $month_clause = '';
        $status_clause = '';
        $target_user_clause = '';
        $time_clause = '';
        $select_type = '';
        if (!empty($params['target_user_id'])) {
            $month_clause .= " AND lead_history.assign_id IN ({$params['target_user_id']}) ";
        }

        if (!empty($params['type_id'])) {
            $month_clause .= " AND lead_history.followup_status_id IN ({$params['type_id']}) ";
            $select_type .= " AND id IN ({$params['type_id']}) ";
        } else {
            //local
//            $params['type_id'] = '8,3,1,22';
            //staging
//            $params['type_id'] = '31,30,1,11';
            //production
            $params['type_id'] = '25,3,1,11';
            $month_clause .= " AND lead_history.followup_status_id IN ({$params['type_id']}) ";
            $select_type .= " AND id IN ({$params['type_id']}) ";
        }
        $dateinput = json_decode($params['time_slot']);
        if (!empty($dateinput->start) && !empty($dateinput->end)) {

            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));

            $time_clause = "AND DATE(lead_history.created_at) between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        }

        $dateinput = json_decode($params['start_date']);
        if (!empty($dateinput->start) && !empty($dateinput->end)) {
            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));

            $time_clause = "AND DATE(lead_history.created_at) between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        } else {
            $first_history = LeadHistory::where('followup_status_id', '!=', 0)->OrderBy('id', 'asc')->first();
            $last_history = LeadHistory::where('followup_status_id', '!=', 0)->OrderBy('id', 'desc')->first();
            $params['time_slot'] = [];
            $params['time_slot']['start'] = date('Y-m-d', strtotime($first_history->created_at));
            $params['time_slot']['end'] = date('Y-m-d', strtotime($last_history->created_at));
            $params['time_slot'] = json_encode($params['time_slot']);
            $dateinput = json_decode($params['time_slot']);
            $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
            $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

            $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
            $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
        }

        $result = \DB::select("SELECT count(*) as user_lead_total, lead_history.assign_id as assignee_id, lead_history.status_id, concat(user.first_name, ' ', user.last_name) as name, 
                              status.title as status_title FROM lead_history
                              INNER JOIN user ON user.id = lead_history.assign_id
                              INNER JOIN status ON status.id = lead_history.status_id                              
                              WHERE user.company_id = {$params['company_id']} AND user.user_group_id != 3  $month_clause
                              group by status_id,assign_id");

        $status_result = \DB::select("SELECT id, title, color_code FROM  follow_statuses WHERE 0=0 $status_clause $select_type AND deleted_at IS NULL ORDER BY title ASC");
        $user_result = \DB::select("SELECT id as assignee_id, concat(user.first_name, ' ', user.last_name) as name FROM user WHERE company_id = {$params['company_id']} $target_user_clause  AND  user_group_id != 3 AND user_group_id = 2 AND deleted_at IS NULL");

        $response = [];
        $temp_response = [];
        $status_map = [];
        $map_user_collection = [];
        foreach ($status_result as $row) {
            if ($row->id != 134 AND $row->id != 135 AND $row->id != 133) {
                $status_map[$row->id]['name'] = $row->title;
                $status_map[$row->id]['data'][$row->id] = 0;
                $status_map[$row->id]['total'][$row->id] = 0;
            }
        }
        foreach ($user_result as $row) {
            $temp_response['user_names'][$row->assignee_id] = $row->name;
            $temp_response['status'][$row->assignee_id] = $status_map;
        }

        $lead_types = \DB::select("SELECT id, title FROM status WHERE tenant_id = {$params['company_id']} $status_clause AND deleted_at IS NULL ORDER BY order_by");
        $types = [];

        $start_date = date('Y-m-d', strtotime($dateinput->start));
        $end_date = date('Y-m-d', strtotime($dateinput->end));
        $datetype = $params['datetype'];
        $total_by_month = [];
        if ($datetype == 'day') {
            $period = CarbonPeriod::create($start_date, $end_date);
            $months = [];
            $months_data = [];
            foreach ($period as $date) {
                $total_by_month[] = 0;
                $months[] = $date->format('Y-m-d');
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array;
            $total_by_month[$total_count_per] = 0;
//            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'week') {
            $weeks = Lead::findWeeksBetweenTwoDatesNew($start_date, $end_date);
            $months = [];
            $months_data = [];
            foreach ($weeks as $week) {
                $total_by_month[] = 0;
                $months[] = $week[0] . ':' . $week[1];
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array;
            $total_by_month[$total_count_per] = 0;
//            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'month') {
            $start_date = date('Y-m-01', strtotime($start_date));
            $end_date = date('Y-m-18', strtotime($end_date));
            $period = new CarbonPeriod($start_date, '1 month', $end_date);
            $months = [];
            $months_data = [];
            $exit_year = [];
            foreach ($period as $date) {
                if (!in_array($date->format("Y M"), $exit_year)) {
                    $exit_year[] = $date->format("Y M");
                    $total_by_month[] = 0;
                    $months[] = $date->format("Y M");
                }
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array;
            $total_by_month[$total_count_per] = 0;
//            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'year') {
            $start_date = date('Y-01-01', strtotime($start_date));
            $end_date = date('Y-12-31', strtotime($end_date));
            $period = new CarbonPeriod($start_date, '1 year', $end_date);
            $months = [];
            $months_data = [];
            $exit_year = [];
            foreach ($period as $date) {
                if (!in_array($date->format('Y'), $exit_year)) {
                    $exit_year[] = $date->format('Y');
                    $total_by_month[] = 0;
                    $months[] = $date->format('Y');
                }
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array;
            $total_by_month[$total_count_per] = 0;
//            $months[] = 'Total';
            $months[] = 'By%';
        }
        $response['months'] = $months;
        $month_status = [];
        $total_result = \DB::select("SELECT lead_history.followup_status_id as his_status_id,lead_history.created_at,lead_history.followup_status_id FROM  lead_history
                              WHERE followup_status_id != 0 OR status_id != 0 $month_clause ORDER BY ID ASC");
        $total_by_month_new = $total_by_month;
        foreach ($status_result as $key => $type) {
            if ($key == 0) {
                $total_by_month_new = $total_by_month;
            }
            $month_status[$key]['name'] = $type->title;
            $month_status[$key]['colour_code'] = $type->color_code;
            $month_status[$key]['id'] = $type->id;
            $month_status[$key]['data'] = $total_by_month_new;

            $result = \DB::select("SELECT lead_history.followup_status_id as his_status_id,lead_history.created_at,lead_history.followup_status_id FROM  lead_history
                              WHERE lead_history.followup_status_id != 0 AND followup_status_id = $type->id $month_clause $time_clause  ORDER BY ID ASC");
            $type_count = count($result);
            $type_total_count = count($total_result);
            if ($type_count != 0 AND $type_total_count != 0) {
                $month_status[$key]['data'][$total_count_array] = 11;
                $month_status[$key]['data'][$total_count_per] = floatval(number_format(($type_count / $type_total_count) * 100, 2)) . '%';
            } else {
                $month_status[$key]['data'][$total_count_array] = 11;
                $month_status[$key]['data'][$total_count_per] = '0%';
            }
            foreach ($result as $val) {
                if ($datetype == 'day') {
                    $created_at = date('Y-m-d', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_knocks_count = \DB::select("SELECT lead_history.status_id,lead_history.followup_status_id as his_status_id,lead_history.created_at,lead_history.followup_status_id FROM  lead_history
                              WHERE lead_history.followup_status_id != 0 OR lead_history.status_id != 0  $month_clause $time_clause  ORDER BY ID ASC");

                        $total_by_month[$n_key] = count($total_knocks_count);
                    } else {
                        $total_knocks_count = \DB::select("SELECT lead_history.status_id,lead_history.followup_status_id as his_status_id,lead_history.created_at,lead_history.followup_status_id FROM  lead_history
                              WHERE lead_history.followup_status_id != 0 OR lead_history.status_id != 0  $month_clause $time_clause  ORDER BY ID ASC");
                        $total_by_month[$n_key] = count($total_knocks_count);
                    }
                } elseif ($datetype == 'week') {
                    $created_at = date('Y-m-d', strtotime($val->created_at));

                    foreach ($months as $m => $month) {
                        $month_a = explode(":", $month);
                        if (strtotime($month_a[0]) <= strtotime($created_at) AND strtotime($month_a[1]) >= strtotime($created_at)) {
                            $n_key = $m;
                            break;
                        }
                    }
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_knocks_count = \DB::select("SELECT lead_history.status_id,lead_history.followup_status_id as his_status_id,lead_history.created_at,lead_history.followup_status_id FROM  lead_history
                              WHERE lead_history.followup_status_id != 0 OR lead_history.status_id != 0  $month_clause $time_clause  ORDER BY ID ASC");

                        $total_by_month[$n_key] = count($total_knocks_count);
                    } else {
                        $total_knocks_count = \DB::select("SELECT lead_history.status_id,lead_history.followup_status_id as his_status_id,lead_history.created_at,lead_history.followup_status_id FROM  lead_history
                              WHERE lead_history.followup_status_id != 0 OR lead_history.status_id != 0  $month_clause $time_clause  ORDER BY ID ASC");
                        $total_by_month[$n_key] = count($total_knocks_count);
                    }
                } elseif ($datetype == 'month') {
                    $created_at = date('Y M', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }
                    if (isset($total_by_month[$n_key])) {
                        $total_knocks_count = \DB::select("SELECT lead_history.status_id,lead_history.followup_status_id as his_status_id,lead_history.created_at,lead_history.followup_status_id FROM  lead_history
                              WHERE lead_history.followup_status_id != 0 OR lead_history.status_id != 0  $month_clause $time_clause  ORDER BY ID ASC");

                        $total_by_month[$n_key] = count($total_knocks_count);
                    } else {
                        $total_knocks_count = \DB::select("SELECT lead_history.status_id,lead_history.followup_status_id as his_status_id,lead_history.created_at,lead_history.followup_status_id FROM  lead_history
                              WHERE lead_history.followup_status_id != 0 OR lead_history.status_id != 0  $month_clause $time_clause  ORDER BY ID ASC");
                        $total_by_month[$n_key] = count($total_knocks_count);
                    }
                } elseif ($datetype == 'year') {
                    $created_at = date('Y', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_knocks_count = \DB::select("SELECT lead_history.status_id,lead_history.followup_status_id as his_status_id,lead_history.created_at,lead_history.followup_status_id FROM  lead_history
                              WHERE lead_history.followup_status_id != 0 OR lead_history.status_id != 0  $month_clause $time_clause  ORDER BY ID ASC");

                        $total_by_month[$n_key] = count($total_knocks_count);
                    } else {
                        $total_knocks_count = \DB::select("SELECT lead_history.status_id,lead_history.followup_status_id as his_status_id,lead_history.created_at,lead_history.followup_status_id FROM  lead_history
                              WHERE lead_history.followup_status_id != 0 OR lead_history.status_id != 0  $month_clause $time_clause  ORDER BY ID ASC");
                        $total_by_month[$n_key] = count($total_knocks_count);
                    }
                } else {
                    
                }
            }
        }

        $total_knocks_count = UserLeadKnocks::whereBetween('created_at', [$dateinput->start, $dateinput->end])
//                                whereDate('created_at', '<=', $dateinput->start)
//                                ->whereDate('created_at', '>=', $dateinput->end)
                ->count();
        // Local
//        $result = \DB::select("SELECT lead_history.followup_status_id as his_status_id,lead_history.created_at,lead_history.followup_status_id FROM  lead_history
//                              WHERE lead_history.followup_status_id != 0 AND followup_status_id = 22 $month_clause $time_clause  ORDER BY ID ASC");
        //staging
        $result = \DB::select("SELECT lead_history.followup_status_id as his_status_id,lead_history.created_at,lead_history.followup_status_id FROM  lead_history
                              WHERE lead_history.followup_status_id != 0 AND followup_status_id = 11 $month_clause $time_clause  ORDER BY ID ASC");
        $type_count = count($result);


        $total_count = $total_knocks_count;

        if ($total_knocks_count != 0 AND $type_count != 0) {
            $total_knocks_count = $total_count / $type_count;
            $message = $total_knocks_count . ' Knocks required to purchase 1 home';
        } else {
            $message = '';
        }

        $response['message'] = $message;
        $key = $key + 1;
        $month_status[$key]['name'] = 'Total Leads';
        $month_status[$key]['id'] = '';
        $month_status[$key]['data'] = $total_by_month;
//        $month_status[$key]['data'][$total_count_array] = 111;
        $month_status[$key]['data'][$total_count_per] = '100%';
        $last_key = $key;

        foreach ($month_status as $key => $month_statu) {


            if ($last_key == $key) {
                $types[$key]['name'] = $month_statu['name'];
                $types[$key]['type'] = 'line';
                $types[$key]['showInLegend'] = 1;
                $types[$key]['toolTipContent'] = null;
                $types[$key]['markerType'] = "square";
                $types[$key]['indexLabelPlacement'] = "outside";
                $types[$key]['color'] = 'transparent';
                if (isset($params['type']) AND $params['type'] == 'percentage') {
                    $types[$key]['indexLabel'] = "{y}%";
                } else {
                    $types[$key]['indexLabel'] = "{y}";
                }
            } else {
                $types[$key]['name'] = $month_statu['name'];
                $types[$key]['type'] = 'stackedColumn';
                $types[$key]['markerType'] = "square";
                $types[$key]['showInLegend'] = 1;
                $types[$key]['color'] = $month_statu['colour_code'];
//                $types[$key]['color'] = $colour[$key];
                if (count($months) < 15) {
                    $types[$key]['indexLabelFontColor'] = 'white';
                    $types[$key]['indexLabelFontSize'] = 10;
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types[$key]['indexLabel'] = "{y}%";
                    } else {
                        $types[$key]['indexLabel'] = "{y}";
                    }
                }
            }

            $total = 0;
            foreach ($months as $i => $month) {
                if ($month != 'Total' AND $month != 'By%') {
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types[$key]['dataPoints'][$total]['label'] = $month;
                        $total_count = $month_status[$last_key]['data'][$i];
                        if ($month_statu['data'][$i] == 0) {
                            $types[$key]['dataPoints'][$total]['y'] = '0%';
                            $month_status[$key]['data'][$i] = '0%';
                        } else {
                            $types[$key]['dataPoints'][$total]['y'] = floatval(number_format(($month_statu['data'][$i] / $total_count) * 100, 2));
                            $month_status[$key]['data'][$i] = floatval(number_format(($month_statu['data'][$i] / $total_count) * 100, 2)) . '%';
                        }
                    } else {
                        if ($month_statu['data'][$i] == 0) {
                            $types[$key]['dataPoints'][$total]['label'] = $month;
//                            $types[$key]['dataPoints'][$total]['y'] = '';
                        } else {
                            $types[$key]['dataPoints'][$total]['label'] = $month;
                            $types[$key]['dataPoints'][$total]['y'] = $month_statu['data'][$i];
                        }
                    }
                    $total = $total + 1;
                }
            }
        }

        $response['types'] = $types;

        $s_no = 1;
        foreach ($month_status as $row) {
            $response['status'][] = $row;
            $response['export'][] = array_merge([$s_no++, $row['name']], $row['data']);
        }
        $response['month_status'] = $month_status;

        return $response;
    }

    public static function leadTypeUserReportCurrentNew($params) {
        $month_clause = '';
        $status_clause = '';
        $target_user_clause = '';
        $time_clause = '';
        $select_type = '';
        if (!empty($params['type_id'])) {
            $month_clause .= " AND lead_detail.status_id IN ({$params['type_id']}) ";
            $select_type .= " AND id IN ({$params['type_id']}) ";
        }

        $first_history = Lead::whereNull('deleted_at')->where('status_id', '!=', 0)->where('is_expired', '=', 0)->OrderBy('id', 'asc')->first();
        $last_history = Lead::whereNull('deleted_at')->where('status_id', '!=', 0)->where('is_expired', '=', 0)->OrderBy('id', 'desc')->first();
        $params['time_slot'] = [];
        $params['time_slot']['start'] = date('Y-m-d', strtotime($first_history->created_at));
        $params['time_slot']['end'] = date('Y-m-d', strtotime($last_history->created_at));
        $params['time_slot'] = json_encode($params['time_slot']);
        $dateinput = json_decode($params['time_slot']);
        $dateinput->start = dateTimezoneChangeNew($dateinput->start . ' 00:00:00');
        $dateinput->end = dateTimezoneChangeNew($dateinput->end . ' 23:59:59');

        $dateinput->start = date('Y-m-d H:i:s', strtotime($dateinput->start));
        $dateinput->end = date('Y-m-d H:i:s', strtotime($dateinput->end));
        $status_result = \DB::select("SELECT id, title, color_code FROM status WHERE tenant_id = {$params['company_id']} $status_clause $select_type AND deleted_at IS NULL ORDER BY order_by");

        $response = [];
        $temp_response = [];
        $status_map = [];
        $map_user_collection = [];
        foreach ($status_result as $row) {
            if ($row->id != 134 AND $row->id != 135 AND $row->id != 133) {
                $status_map[$row->id]['name'] = $row->title;
                $status_map[$row->id]['data'][$row->id] = 0;
                $status_map[$row->id]['total'][$row->id] = 0;
            }
        }
        $lead_types = \DB::select("SELECT id, title FROM status WHERE tenant_id = {$params['company_id']} $status_clause AND deleted_at IS NULL ORDER BY order_by");
        $types = [];

        $start_date = date('Y-m-d', strtotime($dateinput->start));
        $end_date = date('Y-m-d', strtotime($dateinput->end));
        $datetype = 'year';
        $total_by_month = [];
        if ($datetype == 'year') {
            $start_date = date('Y-01-01', strtotime($start_date));
            $end_date = date('Y-12-31', strtotime($end_date));
            $period = new CarbonPeriod($start_date, '1 year', $end_date);
            $months = [];
            $months_data = [];
            $exit_year = [];

            $total_by_month[] = 0;
            $exit_year[] = 0;
            $months[] = 'Total';
            $total_count_array = count($months);
            $total_count_per = $total_count_array;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'By%';
        }
        $response['months'] = $months;
        $month_status = [];
        $total_result = \DB::select("SELECT  lead_detail.status_id as his_status_id, lead_detail.created_at, lead_detail.status_id FROM   lead_detail
                              WHERE is_expired = 0 AND is_follow_up = 0 AND deleted_at IS NULL AND status_id != 0 $month_clause $time_clause  ORDER BY ID ASC");


        $total_by_month_new = $total_by_month;
        foreach ($status_result as $key => $type) {
            if ($key == 0) {
                $total_by_month_new = $total_by_month;
            }
            $month_status[$key]['name'] = $type->title;
            $month_status[$key]['colour_code'] = $type->color_code;
            $month_status[$key]['id'] = $type->id;
            $month_status[$key]['data'] = $total_by_month_new;

            $result = \DB::select("SELECT  lead_detail.status_id as his_status_id, lead_detail.created_at, lead_detail.status_id FROM   lead_detail
                              WHERE is_expired = 0 AND is_follow_up = 0 AND lead_detail.deleted_at IS NULL AND lead_detail.status_id != 0 AND status_id = $type->id $month_clause $time_clause  ORDER BY ID ASC");
            $type_count = count($result);
            $type_total_count = count($total_result);
            if ($type_count != 0 AND $type_total_count != 0) {

                $month_status[$key]['data'][$total_count_per] = floatval(number_format(($type_count / $type_total_count) * 100, 2)) . '%';
            } else {

                $month_status[$key]['data'][$total_count_per] = '0%';
            }
            foreach ($result as $val) {
                if ($datetype == 'year') {
                    if (isset($month_status[$key]['data'][0])) {
                        $month_status[$key]['data'][0] = $month_status[$key]['data'][0] + 1;
                    } else {
                        $month_status[$key]['data'][0] = 1;
                    }

                    if (isset($total_by_month[0])) {
                        $total_by_month[0] = $total_by_month[0] + 1;
                    } else {
                        $total_by_month[0] = 1;
                    }
                } else {
                    
                }
            }
        }

        $key = $key + 1;
        $month_status[$key]['name'] = 'Total Leads';
        $month_status[$key]['id'] = '';
        $month_status[$key]['data'] = $total_by_month;
        $month_status[$key]['data'][$total_count_array] = $type_total_count;
        $month_status[$key]['data'][$total_count_per] = '100%';
        $last_key = $key;
        foreach ($month_status as $key => $month_statu) {
            if ($last_key == $key) {
                
            } else {

                $types[$key]['barPercentage'] = 1;
                $types[$key]['name'] = $month_statu['name'];
                $types[$key]['type'] = 'column';
                $types[$key]['markerType'] = "square";
                $types[$key]['showInLegend'] = 1;
                $types[$key]['color'] = $month_statu['colour_code'];
                $types[$key]['indexLabelFontColor'] = 'black';
                $types[$key]['indexLabelFontSize'] = 10;
                if (isset($params['type']) AND $params['type'] == 'percentage') {
                    $types[$key]['indexLabel'] = "{y}%";
                } else {
                    $types[$key]['indexLabel'] = "{y}";
                }

                $total = 0;
                foreach ($months as $i => $month) {

                    if ($month != 'By%') {
                        if (isset($params['type']) AND $params['type'] == 'percentage') {
                            $types[$key]['dataPoints'][$total]['label'] = $month;
                            $total_count = $month_status[$last_key]['data'][$i];
                            if ($month_statu['data'][$i] == 0) {
                                
                            } else {
                                $types[$key]['dataPoints'][$total]['y'] = floatval(number_format(($month_statu['data'][$i] / $total_count) * 100, 2));
                                $month_status[$key]['data'][$i] = floatval(number_format(($month_statu['data'][$i] / $total_count) * 100, 2)) . '%';
                            }
                        } else {
                            if ($month_statu['data'][$i] == 0) {
                                
                            } else {
                                $types[$key]['dataPoints'][$total]['label'] = $month;
                                $types[$key]['dataPoints'][$total]['y'] = $month_statu['data'][$i];
                            }
                        }
                        $total = $total + 1;
                    }
                }
            }
        }

        $response['types'] = $types;
        $s_no = 1;
        foreach ($month_status as $row) {
            $response['status'][] = $row;
            $response['export'][] = array_merge([$s_no++, $row['name']], $row['data']);
        }
        $response['month_status'] = $month_status;

        return $response;
    }

    public static function findWeeksBetweenTwoDatesNew($startDate, $endDate) {
        $weeks = [];
        while (strtotime($startDate) <= strtotime($endDate)) {
            $oldStartDate = $startDate;
            $startDate = date('Y-m-d', strtotime('+7 day', strtotime($startDate)));
            if (strtotime($startDate) > strtotime($endDate)) {
                $week = [$oldStartDate, $endDate];
            } else {
                $week = [$oldStartDate, date('Y-m-d', strtotime('-1 day', strtotime($startDate)))];
            }

            $weeks[] = $week;
        }

        return $weeks;
    }

    public function findWeeksBetweenTwoDates($startDate, $endDate) {
        $weeks = [];
        while (strtotime($startDate) <= strtotime($endDate)) {
            $oldStartDate = $startDate;
            $startDate = date('Y-m-d', strtotime('+7 day', strtotime($startDate)));
            if (strtotime($startDate) > strtotime($endDate)) {
                $week = [$oldStartDate, $endDate];
            } else {
                $week = [$oldStartDate, date('Y-m-d', strtotime('-1 day', strtotime($startDate)))];
            }

            $weeks[] = $week;
        }

        return $weeks;
    }

    public static function populateStatusData($status_result) {
        $response = [];

        foreach ($status_result as $status) {
            $tmp = [];
            $tmp['label'] = $status->code;
            $tmp[$status->title]['value'] = 0;
            $tmp[$status->title]['svg'] = (object) [];
            $tmp[$status->title]['status_id'] = $status->id;
            $tmp[$status->title]['color_code'] = $status->color_code;
            $tmp[$status->title]['code'] = $status->code;

            $response[$tmp['label']][$status->title] = $tmp;
        }

        return $response;
    }

    public static function populateYearData($status_result) {
        $months = Helper::getYearMonthsFromToday();
        $response = [];
        foreach ($status_result as $row) {
            foreach ($months as $month) {
                $tmp = [];
                $tmp['label'] = $month;
                $tmp[$row->title]['value'] = 0;
                $tmp[$row->title]['svg'] = (object) [];
                $tmp[$row->title]['status_id'] = $row->id;
                $tmp[$row->title]['color_code'] = $row->color_code;

                $response[$month][$row->title] = $tmp;
            }
        }
        return $response;
    }

    public static function populateMonthData($status_result) {
        //$months = Helper::getWeekDaysFromToday();
        $months = Helper::getMonthDaysFromToday();
        $response = [];
        foreach ($status_result as $row) {
            foreach ($months as $month) {
                $tmp = [];
                $tmp['label'] = $month;
                $tmp[$row->title]['value'] = 0;
                $tmp[$row->title]['status_id'] = $row->id;
                $tmp[$row->title]['color_code'] = $row->color_code;

                $response[$month][$row->title] = $tmp;
            }
        }
        return $response;
    }

    public static function populateWeekData($status_result) {
        $months = Helper::getWeekDaysFromToday();

        $response = [];
        foreach ($status_result as $row) {
            foreach ($months as $month) {
                $tmp = [];
                $tmp['label'] = $month;
                $tmp[$row->title]['value'] = 0;
                $tmp[$row->title]['status_id'] = $row->id;
                $tmp[$row->title]['color_code'] = $row->color_code;

                $response[$month][$row->title] = $tmp;
            }
        }
        return $response;
    }

    public static function populateTodayData($status_result) {
        $months = Helper::getHoursFromToday();

        $response = [];
        foreach ($status_result as $row) {
            foreach ($months as $month) {
                $tmp = [];
                $tmp['label'] = $month;
                $tmp[$row->title]['value'] = 0;
                $tmp[$row->title]['status_id'] = $row->id;
                $tmp[$row->title]['color_code'] = $row->color_code;

                $response[$month][$row->title] = $tmp;
            }
        }
        return $response;
    }

    public static function getLeadWCustomField($params) {
        $query = Lead::with('latestnotes')->select('lead_detail.*', 'type.title as lead_type', 'status.title as lead_status', 'user.first_name as assign_first_name', 'user.last_name as assign_last_name');

        $query->leftJoin('type', 'lead_detail.type_id', 'type.id');
        $query->leftJoin('status', 'lead_detail.status_id', 'status.id');
        $query->leftJoin('user', 'lead_detail.assignee_id', 'user.id');
//        $query->leftJoin('lead_query', function ($join) {
//            $join->on('lead_detail.id', '=', 'lead_query.lead_id')
//                    ->where('lead_query.query_id', '=', 8);
//        });
        $query->where('lead_detail.company_id', $params['company_id']);
        $query->where('lead_detail.is_follow_up', 0);
        $query->whereNull('lead_detail.deleted_at');

        if (!is_null($params['is_retired']) && $params['is_retired'] == 1) {
            $query->where('lead_detail.is_expired', $params['is_retired']);
        } elseif (!is_null($params['is_retired']) && $params['is_retired'] == 2) {
            $query->where('lead_detail.is_expired', 0);
        }

        if (isset($params['status_ids']) && !empty($params['status_ids']))
            $query->whereRaw('lead_detail.status_id  IN (' . $params['status_ids'] . ')');

        if (!empty($params['lead_type_id'])) {
            $query->whereRaw('lead_detail.type_id  IN (' . $params['lead_type_id'] . ')');
        }

        if (isset($params['lead_ids']) && !empty($params['lead_ids']) && !empty(implode(',', $params['lead_ids'])))
            $query->whereRaw('lead_detail.id  IN (' . implode(',', $params['lead_ids']) . ')');

        if (isset($params['user_ids']) && !empty($params['user_ids']))
            $query->whereRaw('lead_detail.assignee_id  IN (' . $params['user_ids'] . ')');

        if (isset($params['start_date']) && isset($params['end_date']) && !empty($params['start_date']) && !empty($params['end_date'])) {
            $params['start_date'] = dateTimezoneChangeNew($params['start_date'] . ' 00:00:00');
            $params['end_date'] = dateTimezoneChangeNew($params['end_date'] . ' 23:59:59');

            $params['start_date'] = date('Y-m-d H:i:s', strtotime($params['start_date']));
            $params['end_date'] = date('Y-m-d H:i:s', strtotime($params['end_date']));
            $query->whereRaw("lead_detail.created_at >= '{$params['start_date']}' && lead_detail.created_at <= '{$params['end_date']}'");
        }

        if (isset($params['search']) && !empty($params['search'])) {
            $query->where(function($querysub) use($params) {
                $querysub->orwhere('zip_code', 'like', '%' . $params['search'] . '%')
                        ->orwhere('formatted_address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('county', 'like', '%' . $params['search'] . '%')
                        ->orwhere('state', 'like', '%' . $params['search'] . '%')
                        ->orwhere('city', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.title', 'like', '%' . trim($params['search']) . '%');
            });
        }



        $response = [];
        return $lead_field_data = $query->groupBy('lead_detail.id')->orderBy('lead_detail.id', 'desc')->get();





//        $tmp_fields = [];
//        $field_columns = [];
//        foreach ($lead_field_data as $field_data) {
//            $field_data = json_decode(json_encode($field_data), true);
//            $tmp = $field_data;
//
//            $tmp_fields[$field_data['id']][$field_data['key']] = $field_data['value'];
//
//            $tmp = array_merge($tmp, $tmp_fields[$field_data['id']]);
//            $response[$field_data['id']] = $tmp;
//            foreach ($tmp as $field => $data) {
//                $field_columns[$field] = $field;
//            }
//        }
//
//
//        $response_data['data'] = $response;
//        $response_data['field_columns'] = $field_columns;
//
//        $default_columns = [];
//        $result_default_columns = TenantCustomField::getTenantDefaultFields($params['company_id']);
//
//        $tenantQuery = TenantQuery::where('tenant_id', $params['company_id'])->where('type', 'summary')->pluck('query', 'id')->toArray();
//
//        foreach ($result_default_columns as $result_default_column) {
//            if ($result_default_column['key'] == 'lead_name') {
//                // $result_default_column['key_mask'] = 'title';
//                // $result_default_column['key'] = 'title';
//            }
//            // info($result_default_column['key']);
//            // info($result_default_column['key_mask']);
//
//            $default_columns[] = str_replace(Config::get('constants.SPECIAL_CHARACTERS.IGNORE'), Config::get('constants.SPECIAL_CHARACTERS.REPLACE'), $result_default_column['key']);
//            // $default_columns_title[] = str_replace(Config::get('constants.SPECIAL_CHARACTERS.IGNORE'), Config::get('constants.SPECIAL_CHARACTERS.REPLACE'),$result_default_column['key_mask']);
//            $default_columns_title[] = empty($result_default_column['key_mask']) ? '' : $result_default_column['key_mask'];
//        }
//
//        $datafilde['columns'] = (count($default_columns)) ? $default_columns : Config::get('constants.LEAD_DEFAULT_COLUMNS');
//        $datafilde['columns_title'] = (count($default_columns_title)) ? $default_columns_title : Config::get('constants.LEAD_DEFAULT_COLUMNS');
//
//        $tenantQueryNew = [];
//
//        if (!empty($tenantQuery)) {
//
//            foreach ($tenantQuery as $key => $value) {
//                if ($key == 8) {
////                if ($value == 'Notes (Add to Top, Include Date, Your Name and Notes)' AND $value == 'Notes (Add to Top, Include Date and Your Name)') {
//                    $tenantQueryNew[$key] = preg_replace('/[^A-Za-z0-9. -]/', '', $value);
////                }
//                }
//            }
//
//            $datafilde['columns'] = array_merge($datafilde['columns'], $tenantQueryNew);
//            $datafilde['columns_title'] = array_merge($datafilde['columns_title'], $tenantQueryNew);
//        }
//
//        $response_data_detail = [];
//
//        foreach ($datafilde['columns'] as $value) {
//            $response_data_detail[$value] = $value;
//        }
//
//        $response_data['data'] = $response;
//        $response_data['field_columns'] = $response_data_detail;
//
//        return $response_data;
    }

    public static function getLeadWCustomFieldOld($params) {
        $lat = $params['latitude'];
        $lng = $params['longitude'];
        $radius = $params['radius'];

        $haversine = "(3959 * acos (
                    cos ( radians($lat) )
                    * cos( radians(`latitude`) )
                    * cos( radians(`longitude`) - radians($lng) )
                    + sin ( radians($lat) )
                    * sin( radians(`latitude`) )
                ))";

        $query = self::select('lcf.*', 'tenant_custom_field.order_by As field_order_by', 'lead_detail.*', \DB::raw('type.title as lead_type'), \DB::raw('status.title as lead_status', 'lead_query.response as Notes_Add_to_Top_Include_Date_Your_Name_and_Notes'));
        $query->Join('type', 'type.id', 'lead_detail.type_id');
        $query->Join('status', 'status.id', 'lead_detail.status_id');
        $query->Join('lead_custom_field AS lcf', 'lcf.lead_id', '=', 'lead_detail.id');
        $query->Join('lead_query', 'lead_detail.id', '=', 'lead_query.lead_id');
        $query->Join('tenant_custom_field', 'lead_detail.company_id', '=', 'tenant_custom_field.tenant_id');
        $query->where('lead_query.query_id', 8);
        $query->where('lead_detail.company_id', $params['company_id']);
        $query->where('lead_detail.is_follow_up', 0);
        $query->whereNull('lead_detail.deleted_at');

        if (!is_null($params['is_retired']) && $params['is_retired'] == 1) {
            $query->where('lead_detail.is_expired', $params['is_retired']);
        } elseif (!is_null($params['is_retired']) && $params['is_retired'] == 2) {
            $query->where('lead_detail.is_expired', 0);
        }

        if (!empty($params['lead_type_id'])) {
            $query->whereRaw('lead_detail.type_id  IN (' . $params['lead_type_id'] . ')');
        }

        if (isset($params['lead_ids']) && !empty($params['lead_ids']) && !empty(implode(',', $params['lead_ids'])))
            $query->whereRaw('lead_detail.id  IN (' . implode(',', $params['lead_ids']) . ')');

        if (isset($params['user_ids']) && !empty($params['user_ids']))
            $query->whereRaw('lead_detail.assignee_id  IN (' . $params['user_ids'] . ')');

        if (isset($params['start_date']) && isset($params['end_date']) && !empty($params['start_date']) && !empty($params['end_date'])) {
            $params['start_date'] = dateTimezoneChangeNew($params['start_date'] . ' 00:00:00');
            $params['end_date'] = dateTimezoneChangeNew($params['end_date'] . ' 23:59:59');

            $params['start_date'] = date('Y-m-d H:i:s', strtotime($params['start_date']));
            $params['end_date'] = date('Y-m-d H:i:s', strtotime($params['end_date']));
            $query->whereRaw("lead_detail.created_at >= '{$params['start_date']}' && lead_detail.created_at <= '{$params['end_date']}'");
        }

        if (!empty($params['lead_type_id'])) {
            $query->whereRaw('lead_detail.type_id  IN (' . $params['lead_type_id'] . ')');
        }

        if (isset($params['search']) && !empty($params['search'])) {
            $query->where(function($querysub) use($params) {
                $querysub->orwhere('zip_code', 'like', '%' . $params['search'] . '%')
                        ->orwhere('formatted_address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('county', 'like', '%' . $params['search'] . '%')
                        ->orwhere('state', 'like', '%' . $params['search'] . '%')
                        ->orwhere('city', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.title', 'like', '%' . trim($params['search']) . '%');
            });
        }

        if (!isset($params['order_by'])) {
            $params['order_by'] = 'id';
            $params['order_type'] = 'desc';
        }

        $order_by = 'lead_detail.' . $params['order_by'];
        if (strtolower($params['order_by']) == 'lead_type')
            $order_by = 'type.title';

        if (strtolower($params['order_by']) == 'first_name' || strtolower($params['order_by']) == 'last_name')
            $order_by = 'lead_detail.owner';

        $query->orderBy($order_by, $params['order_type']);

        if (!empty($params['time_slot'])) {
            //$time_clauses['today'] = " AND lead_detail.created_at >= CURDATE() AND lead_detail.created_at < CURDATE() + INTERVAL 1 DAY";
            $time_clauses['today'] = " DATE(lead_detail.created_at) = DATE(NOW())";
            $time_clauses['yesterday'] = " DATE(lead_detail.created_at) = DATE(NOW() - INTERVAL 1 DAY)";
            $time_clauses['week'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 7 DAY";
            $time_clauses['last_week'] = " YEARWEEK(lead_detail.created_at) = YEARWEEK(NOW() - INTERVAL 1 WEEK)";
            $time_clauses['month'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 1 MONTH";
            $time_clauses['last_month'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 MONTH)  AND month(lead_detail.created_at) = Month(NOW() - INTERVAL 1 MONTH) ";
            $time_clauses['year'] = " lead_detail.created_at > DATE_SUB(NOW(),INTERVAL 1 YEAR)";
            $time_clauses['last_year'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 YEAR)";

            $group_by_clauses['today'] = " hour, day, month, year";
            $group_by_clauses['yesterday'] = " hour, day, month, year";
            $group_by_clauses['week'] = " day, month, year";
            $group_by_clauses['last_week'] = " day, month, year";
            $group_by_clauses['month'] = " day, month, year";
            $group_by_clauses['last_month'] = " day, month, year";
            $group_by_clauses['year'] = " month, year";
            $group_by_clauses['last_year'] = " month, year";

            $slot_types['today'] = " hour";
            $slot_types['yesterday'] = " hour";
            $slot_types['week'] = " day";
            $slot_types['last_week'] = " day";
            $slot_types['month'] = " day";
            $slot_types['last_month'] = " day";
            $slot_types['year'] = " month";
            $slot_types['last_year'] = " month";

            $time_clause = $time_clauses[$params['slot']];
            $group_by__clause = $group_by_clauses[$params['slot']];

            $query->whereRaw("$time_clause");
        }

        if (!empty($params['latitude']) && !empty($params['longitude'])) {
            $query->selectRaw("{$haversine} AS distance")->whereRaw("{$haversine} < ?", [$radius]);
        }

        $response = [];
        $lead_field_data = $query->orderBy('field_order_by', 'Asc')->orderBy('lead_detail.id', 'desc')->toSql();

        echo $lead_field_data;
        exit;
        $tmp_fields = [];
        $field_columns = [];
        foreach ($lead_field_data as $field_data) {
            $field_data = json_decode(json_encode($field_data), true);
            $tmp = $field_data;

            $tmp_fields[$field_data['id']][$field_data['key']] = $field_data['value'];

            $tmp = array_merge($tmp, $tmp_fields[$field_data['id']]);
            $response[$field_data['id']] = $tmp;
            foreach ($tmp as $field => $data) {
                $field_columns[$field] = $field;
            }
        }


        $response_data['data'] = $response;
        $response_data['field_columns'] = $field_columns;

        $default_columns = [];
        $result_default_columns = TenantCustomField::getTenantDefaultFields($params['company_id']);

        $tenantQuery = TenantQuery::where('tenant_id', $params['company_id'])->where('type', 'summary')->pluck('query', 'id')->toArray();

        foreach ($result_default_columns as $result_default_column) {
            if ($result_default_column['key'] == 'lead_name') {
                // $result_default_column['key_mask'] = 'title';
                // $result_default_column['key'] = 'title';
            }
            // info($result_default_column['key']);
            // info($result_default_column['key_mask']);

            $default_columns[] = str_replace(Config::get('constants.SPECIAL_CHARACTERS.IGNORE'), Config::get('constants.SPECIAL_CHARACTERS.REPLACE'), $result_default_column['key']);
            // $default_columns_title[] = str_replace(Config::get('constants.SPECIAL_CHARACTERS.IGNORE'), Config::get('constants.SPECIAL_CHARACTERS.REPLACE'),$result_default_column['key_mask']);
            $default_columns_title[] = empty($result_default_column['key_mask']) ? '' : $result_default_column['key_mask'];
        }

        $datafilde['columns'] = (count($default_columns)) ? $default_columns : Config::get('constants.LEAD_DEFAULT_COLUMNS');
        $datafilde['columns_title'] = (count($default_columns_title)) ? $default_columns_title : Config::get('constants.LEAD_DEFAULT_COLUMNS');

        $tenantQueryNew = [];

        if (!empty($tenantQuery)) {

            foreach ($tenantQuery as $key => $value) {
                if ($key == 8) {
//                if ($value == 'Notes (Add to Top, Include Date, Your Name and Notes)' AND $value == 'Notes (Add to Top, Include Date and Your Name)') {
                    $tenantQueryNew[$key] = preg_replace('/[^A-Za-z0-9. -]/', '', $value);
//                }
                }
            }

            $datafilde['columns'] = array_merge($datafilde['columns'], $tenantQueryNew);
            $datafilde['columns_title'] = array_merge($datafilde['columns_title'], $tenantQueryNew);
        }

        $response_data_detail = [];

        foreach ($datafilde['columns'] as $value) {
            $response_data_detail[$value] = $value;
        }

        $response_data['data'] = $response;
        $response_data['field_columns'] = $response_data_detail;

        return $response_data;
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function getlistPro($params) {
        if (!empty($params['latitude']) && !empty($params['longitude'])) {
            $lat = $params['latitude'];
            $lng = $params['longitude'];
            $radius = $params['radius'];

            $haversine = "(3959 * acos (
                    cos ( radians($lat) )
                    * cos( radians(`latitude`) )
                    * cos( radians(`longitude`) - radians($lng) )
                    + sin ( radians($lat) )
                    * sin( radians(`latitude`) )
                ))";
        }


        if (!empty($params['auction_start_date']) && !empty($params['auction_end_date'])) {
            $query = self::select('lead_detail.*', 'type.title as type_title', 'status.title as status_title', 'lead_query.response as Notes_Add_to_Top_Include_Date_Your_Name_and_Notes', 'user.first_name as assignee_first', 'user.last_name as assignee_last', 'lead_custom_field.value as auction_date_value');
            $query->leftJoin('lead_custom_field', 'lead_detail.id', '=', 'lead_custom_field.lead_id');
            $query->where('lead_custom_field.tenant_custom_field_id', 157);
        } else {
            $query = self::select('lead_detail.*', 'type.title as type_title', 'status.title as status_title', 'lead_query.response as Notes_Add_to_Top_Include_Date_Your_Name_and_Notes', 'user.first_name as assignee_first', 'user.last_name as assignee_last');
        }


        $query->with('leadCustom', 'tenantquery');
        $query->leftJoin('lead_query', 'lead_detail.id', '=', 'lead_query.lead_id');
        $query->leftJoin('status', 'lead_detail.status_id', '=', 'status.id');
        $query->leftJoin('type', 'lead_detail.type_id', '=', 'type.id');
        $query->leftJoin('user', 'lead_detail.assignee_id', '=', 'user.id');
        $query->where('lead_query.query_id', 8);
        $query->where('lead_detail.is_follow_up', 0);
        $query->where('lead_detail.company_id', $params['company_id']);
        $query->whereNull('lead_detail.deleted_at');

        if (isset($params['user_ids']) && !empty($params['user_ids']))
            $query->whereRaw('lead_detail.assignee_id  IN (' . $params['user_ids'] . ')');

        if (isset($params['status_ids']) && !empty($params['status_ids']))
            $query->whereRaw('lead_detail.status_id  IN (' . $params['status_ids'] . ')');

        if (!isset($params['is_web']) || $params['is_web'] == 0) {
            $query->where('lead_detail.is_expired', 0);
        }

        if (isset($params['auction_start_date']) && isset($params['auction_end_date']) && !empty($params['auction_start_date']) && !empty($params['auction_end_date'])) {

            $params['auction_start_date'] = date('n/j/Y', strtotime($params['auction_start_date']));
            $params['auction_end_date'] = date('n/j/Y', strtotime($params['auction_end_date']));

            $query->whereRaw("lead_custom_field.value >= '{$params['auction_start_date']}' && lead_custom_field.value <= '{$params['auction_end_date']}'");
        }

        if (!empty($params['start_date']) && !empty($params['end_date'])) {
            $params['start_date'] = dateTimezoneChangeNew($params['start_date'] . ' 00:00:00');
            $params['end_date'] = dateTimezoneChangeNew($params['end_date'] . ' 23:59:59');

            $params['start_date'] = date('Y-m-d H:i:s', strtotime($params['start_date']));
            $params['end_date'] = date('Y-m-d H:i:s', strtotime($params['end_date']));

            $query->whereRaw("lead_detail.created_at >= '{$params['start_date']}' && lead_detail.created_at <= '{$params['end_date']}'");
        }


        if (!is_null($params['is_retired']) && $params['is_retired'] == 1) {
            $query->where('lead_detail.is_expired', $params['is_retired']);
        } elseif (!is_null($params['is_retired']) && $params['is_retired'] == 2) {
            $query->where('lead_detail.is_expired', 0);
        }

        if (!empty($params['lead_type_id'])) {
            $query->whereRaw('lead_detail.type_id  IN (' . $params['lead_type_id'] . ')');
        }
        if (!isset($params['order_by'])) {
            $params['order_by'] = 'id';
            $params['order_type'] = 'desc';
        }
        $order_by = 'lead_detail.' . $params['order_by'];
        if (strtolower($params['order_by']) == 'lead_type') {
            $order_by = 'type.title';
        }

        if (strtolower($params['order_by']) == 'first_name' || strtolower($params['order_by']) == 'last_name') {
            $order_by = 'lead_detail.owner';
        }

        if (strtolower($params['order_by']) == 'lead_name' || strtolower($params['order_by']) == 'homeowner name') {
            $order_by = 'lead_detail.title';
        }

        if (strtolower($params['order_by']) == 'admin notes') {
            $order_by = 'lead_detail.admin_notes';
        }

        $query->orderBy($order_by, $params['order_type']);

        if (isset($params['search']) && !empty($params['search'])) {
            // $query->leftJoin('type', 'type.id', 'lead_detail.type_id');
            // $query->whereRaw("(lead_detail.title like '%{$params['search']}%' OR formatted_address like '%{$params['search']}%' 
            // OR address like '%{$params['search']}%' OR type.title like '%{$params['search']}%' )");

            $query->where(function($querysub) use($params) {
                $querysub->orwhere('lead_detail.zip_code', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.formatted_address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.address', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.county', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.state', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.city', 'like', '%' . $params['search'] . '%')
                        ->orwhere('lead_detail.title', 'like', '%' . trim($params['search']) . '%');
            });
        }

        $time_clause = '';
        $group_by__clause = '';
        if (!empty($params['time_slot'])) {
            //$time_clauses['today'] = " AND lead_detail.created_at >= CURDATE() AND lead_detail.created_at < CURDATE() + INTERVAL 1 DAY";
            $time_clauses['today'] = " DATE(lead_detail.created_at) = DATE(NOW())";
            $time_clauses['yesterday'] = " DATE(lead_detail.created_at) = DATE(NOW() - INTERVAL 1 DAY)";
            $time_clauses['week'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 7 DAY";
            $time_clauses['last_week'] = " YEARWEEK(lead_detail.created_at) = YEARWEEK(NOW() - INTERVAL 1 WEEK)";
            $time_clauses['month'] = " lead_detail.created_at >= DATE(NOW()) - INTERVAL 1 MONTH";
            $time_clauses['last_month'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 MONTH)  AND month(lead_detail.created_at) = Month(NOW() - INTERVAL 1 MONTH) ";
            $time_clauses['year'] = " lead_detail.created_at > DATE_SUB(NOW(),INTERVAL 1 YEAR)";
            $time_clauses['last_year'] = " year(lead_detail.created_at) = year(NOW() - INTERVAL 1 YEAR)";

            $group_by_clauses['today'] = " hour, day, month, year";
            $group_by_clauses['yesterday'] = " hour, day, month, year";
            $group_by_clauses['week'] = " day, month, year";
            $group_by_clauses['last_week'] = " day, month, year";
            $group_by_clauses['month'] = " day, month, year";
            $group_by_clauses['last_month'] = " day, month, year";
            $group_by_clauses['year'] = " month, year";
            $group_by_clauses['last_year'] = " month, year";

            $slot_types['today'] = " hour";
            $slot_types['yesterday'] = " hour";
            $slot_types['week'] = " day";
            $slot_types['last_week'] = " day";
            $slot_types['month'] = " day";
            $slot_types['last_month'] = " day";
            $slot_types['year'] = " month";
            $slot_types['last_year'] = " month";

            $time_clause = $time_clauses[$params['slot']];
            $group_by__clause = $group_by_clauses[$params['slot']];

            $query->whereRaw("$time_clause");
        }

        // if (!empty($params['latitude']) && !empty($params['longitude']))
        //     $query->selectRaw("{$haversine} AS distance")
        //             ->whereRaw("{$haversine} < ?", [$radius]);

        $leadview = $query->get();

        $data = [];
        foreach ($leadview as $key => $value) {
            if (!isset($data[$value->id])) {
                $data[$value->id] = $value->toArray();
            }
            // print_r($value->key);
            $data[$value->id][$value->key] = $value->value;
        }

        return Lead::paginate($data);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function paginate($items, $perPage = 100, $page = null, $options = []) {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function latestnotes() {
        return self::hasOne(LeadQuery::class, 'lead_id', 'id')
                        ->select('lead_query.*')
                        ->where('query_id', '=', 8)
                        ->where('response', '!=', null)
                        ->orderBy('id', 'desc');
    }

}
