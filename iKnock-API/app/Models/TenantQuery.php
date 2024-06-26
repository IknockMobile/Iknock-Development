<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as Auditables;

class TenantQuery extends Model implements Auditable
{
    use Cachable,Auditables;
    
    protected $table = "tenant_query";

    protected $guarded = array();

    public static function getById($id){

        $query = self::select();
        return $query->where('id', $id)
            ->first();
    }
}
