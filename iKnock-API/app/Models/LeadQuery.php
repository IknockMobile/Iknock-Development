<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as Auditables;

class LeadQuery extends Model implements Auditable {

    use Cachable,
        Auditables;

    protected $table = "lead_query";

    const UPDATED_AT = null;

    protected $guarded = array();

    public function lead() {
        return self::hasOne(Lead::class, 'id', 'lead_id')
                        ->select('lead.*');
    }

    public static function getById($id) {

        $query = self::select();
        return $query->where('id', $id)
                        ->first();
    }

    public static function getList($params) {
        $query = self::select();
        $query->where('company_id', $params['company_id']);

        $query->orderBy('lead.id', 'desc');

        if (isset($params['search']) && !empty($params['search']))
            $query->whereRaw("title like '%{$params['search']}%'");

        return $query->paginate(Config::get('constants.PAGINATION_PAGE_SIZE'));
    }

    public static function insertBulk($lead_id, $tenant_id) {
        \DB::statement("Insert INTO lead_query (query_id,lead_id, query, response, created_at)
                      SELECT id, $lead_id, query, '', NOW() FROM tenant_query WHERE tenant_id = $tenant_id");
    }

    public static function getByLeadId($lead_id, $query_obj) {

        $query_collector = [];
        $response_collector = [];
        $query_type_collector = [];

        if (!empty($query_obj)) {
            foreach ($query_obj as $row) {
                $tmp = new \stdClass();
                $tmp->id = '';
                $tmp->query_id = $row->id;
                $tmp->type = $row->type;
                $tmp->query = $row->query;
                $tmp->response = '';

                $query_collector[$row->type][(int) $row->id] = $tmp;
                $query_type_collector[$row->type] = $row->type;
                $response_collector[$row->type] = [];
            }
        }


        $query = self::select();
        $lead_query = $query->where('lead_id', $lead_id)->get();
        foreach ($lead_query as $query_row) {
            foreach ($query_type_collector as $type) {
                $query_id = (int) $query_row->query_id;
                if (isset($query_collector[$type][$query_id])) {
                    $query_collector[$type][$query_id]->id = $query_row->id;
                    $query_collector[$type][$query_id]->response = $query_row->response;
                }
            }
        }
        foreach ($query_collector as $type => $query_type_row) {
            foreach ($query_type_row as $row) {
                $response_collector[$type][] = $row;
            }
        }
        return $response_collector;
    }

    public static function updateQuery($lead_id, $query) {

        // insert update implementation
        $insert_collector = [];
        foreach ($query as $row) {

            // if(empty($row['id'])){
            //     $insert_collector[] = "($lead_id, {$row['query_id']}, '{$row['query']}', '{$row['response']}', NOW())";
            //     continue;
            // }

            if (empty($row['id'])) {
                $input['lead_id'] = $lead_id;
                $input['query_id'] = $row['query_id'];
                $input['query'] = $row['query'];
                $input['response'] = $row['response'];

                LeadQuery::create($input);
                // $insert_collector[] = "($lead_id, {$row['query_id']}, '{$row['query']}', '{$row['response']}', NOW())";
                continue;
            }

            $obj_lead_query = Self::find($row['id']);

            if (!is_null($obj_lead_query)) {
                $obj_lead_query->response = $row['response'];
                $obj_lead_query->save();
            }
        }

        // if(count($insert_collector)){
        //     info('hi');
        //     info($insert_collector);
        //     \DB::statement('INSERT INTO lead_query (lead_id, query_id, query, response, created_at) VALUES' . implode(',', $insert_collector));
        // }
        return;
    }

}
