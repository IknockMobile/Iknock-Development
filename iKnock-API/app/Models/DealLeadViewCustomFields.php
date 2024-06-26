<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use OwenIt\Auditing\Auditable as Auditables;

class DealLeadViewCustomFields extends Model implements Auditable
{
     use HasFactory,
        Auditables,
        Cachable;

    protected $guarded = array();

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function dealLeadViewSetp() {
        return self::hasOne(DealLeadViewSetp::class, 'id', 'deal_view_id');
    }
}
