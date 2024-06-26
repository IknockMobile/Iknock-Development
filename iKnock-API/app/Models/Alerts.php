<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alerts extends Model {

    use HasFactory,
        SoftDeletes;

    protected $guarded = array();

    public static function getById($id) {
        $query = self::select();
        return $query->where('id', $id)
                        ->first();
    }

    public static function getList($params) {
        $query = self::select();
        return $query->whereNull('deleted_at')
                        ->orderBy('id')
                        ->get();
    }

    public static function deleteStatus($id) {
        if (is_array($id))
            $id = implode(',', $id);

        \DB::statement("Update alerts SET deleted_at = NOW() WHERE id IN ($id)");
        return true;
    }

}
