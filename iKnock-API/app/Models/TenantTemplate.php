<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class TenantTemplate extends Model
{
    use Cachable;

    protected $table = "tenant_template";

    public static function getById($id){

        $query = self::select();
        return $query->where('id', $id)
            ->first();
    }

    public static function getByMax($company_id){

        $query = self::select();
        return $query->where('tenant_id', $company_id)
            ->orderBy('id', 'desc')
            ->first();
    }

}
