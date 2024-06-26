<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as Auditables;

class Notification extends Model  implements Auditable
{
    protected $table = "notification";
    use Cachable,Auditables;

      /**
     * The attributes that are mass assignable.
     *
     * @var array
    */
    protected $fillable = [
        'is_active','field','index','index_map','order_by',
    ];
    /**
     * Write code on Method
     *
     * @return response()
     */
    public static function getById($id){

        $query = self::select();
        return $query->where('id', $id)
            ->get();
    }
}
