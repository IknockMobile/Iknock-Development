<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as Auditables;

class TenantCustomField extends Model  implements Auditable
{
    protected $table = "tenant_custom_field";

    // protected $guarded = array();

    use Cachable,Auditables;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
    */
    protected $guarded = array();

    public static function insertTenantDefaultFields($tenant_id, $columns){
        $order_by = 1;
        foreach ($columns as $column) {
            $statements[] = "($tenant_id, 0, '$column','$column', $order_by, NOW(), NOW())";
            $order_by++;
        }

        \DB::statement("INSERT INTO tenant_custom_field (tenant_id, template_id, `key`, key_mask, order_by, created_at, updated_at) VALUES " .
            implode(',', $statements));
        return true;
    }

    public static function getTenantDefaultFields($tenant_id){
        $query = self::select();
        return $query->where('tenant_id', $tenant_id)
            //->where('template_id', 0)
            ->where('is_active', 1)
            ->orderBy('order_by','asc')
            ->get()
            ->toArray();
    }

    public static function getById($id){
        $query = self::select('id', 'tenant_id', 'key as query', \DB::raw('"lead_detail" as type'));
        return $query->where('id', $id)
            ->first();
    }

    public static function getByKey($key, $params){
        $query = self::select('id', 'tenant_id', 'key as query', \DB::raw('"lead_detail" as type'));
        $query->where('tenant_id', $params['company_id']);
        return $query->where('key', $key)
            ->first();
    }

    public static function getList($params)
    {
        $query = self::select('id', 'key');
        $query->join('template_fields', 'template_fields.field', 'tenant_custom_field.id');
        $query->where('tenant_id', $params['company_id']);
        $query->whereNull('deleted_at');
        $query->where('tenant_custom_field.is_active', 1);
        $query->whereNotIn('tenant_custom_field.key',['EQ','Sq Ft','Auction','Lead Value','Original Loan','Yr Blt','Loan Date','Admin Notes','Address','City','State','Zip','County','Lead Type','Lead Status','Mortgagee','Loan Type','Loan Mod','created_by','Assigned To','Source','Owner Address - If Not Owner Occupied','Trustee','Is Retired ']);
        $query->where('tenant_custom_field.key','!=','updated_at')->where('tenant_custom_field.key','!=','created_at')->where('tenant_custom_field.key','!=','updated_by');
        $query->groupBy('tenant_custom_field.id');
        $query->orderBy('tenant_custom_field.order_by', 'ASC');
        if(!empty($params['ids']))
            $query->whereIn('id', $params['ids']);
        return $query->where('template_fields.is_active', 1)->get();

    }

     public static function getCustomList($params)
    {
        $query = self::select('id', 'key');
        $query->join('template_fields', 'template_fields.field', 'tenant_custom_field.id');
        $query->where('tenant_id', $params['company_id']);
        $query->whereNull('deleted_at');
        $query->whereNotIn('tenant_custom_field.key',['EQ','Sq Ft','Auction','Lead Value','Original Loan','Yr Blt','Loan Date','Admin Notes','Address','City','State','Zip','County','Lead Type','Lead Status','Mortgagee','Loan Type','Loan Mod','created_by','Assigned To','Source','Owner Address - If Not Owner Occupied','Trustee','Is Retired ']);
        $query->where('tenant_custom_field.is_active', 1);
        $query->where('tenant_custom_field.key','!=','updated_at')->where('tenant_custom_field.key','!=','created_at')->where('tenant_custom_field.key','!=','updated_by');
        $query->groupBy('tenant_custom_field.id');
        $query->orderBy('tenant_custom_field.order_by', 'ASC');
        if(!empty($params['ids']))
            $query->whereIn('id', $params['ids']);
        return $query->where('template_fields.is_active', 1)->get();

    }
}
