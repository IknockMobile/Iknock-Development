<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as Auditables;

class LeadCustomField extends Model implements Auditable
{   
    use Cachable,Auditables;

    protected $table = "lead_custom_field";

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
    */
    protected $fillable = [
        'lead_id','tenant_custom_field_id','key','value','created_at','is_active'
    ];

    public static function insert($lead_id, $ignore_fields, $data)
    {
        $field_collection = [];
        $custom_field_ids = [];
        $param['company_id'] = $data['company_id'];
        foreach($data as $key => $value) {
            if (!in_array($key, $ignore_fields)) {
                $field_collection[$key] = $value;
                $param['ids'][] = $key;
            }
        }
        $tenant_fields = TenantCustomField::getList($param);
        $field_values = [];

        $field_pdo_values = [];
        foreach ($tenant_fields as $field){
            
            $key = $field['key'];

            $field_values[] = "($lead_id, {$field['id']}, ".$key.", null, NOW())";

            $fieldData =  ['lead_id'=>$lead_id,'tenant_custom_field_id'=>$field['id'], 'key'=>$key, 'value'=>$field_collection[$field['id']],'created_at'=>NOW()];
            
            if($field_collection[$field['id']] != null){
                LeadCustomField::create($fieldData);

            }

        }
        // if(count($field_values)) {
            
        //     \DB::statement("Insert INTO lead_custom_field (lead_id, tenant_custom_field_id, `key`, `value`, created_at)
        //               VALUES " . implode(',', $field_values), $field_pdo_values);
        // }
    }

    public static function getList($params)
    {
        $query = self::select();

//        if(!empty($params['company_id']))
//            $query->where('tenant_id', $params['company_id']);

        if(!empty($params['ids']))
            $query->wherein('id', $params['ids']);

        return $query->get();

    }
}
