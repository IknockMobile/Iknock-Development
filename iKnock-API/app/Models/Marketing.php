<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as Auditables;

class Marketing extends Model  implements Auditable
{
    use HasFactory,Auditables;

    protected $guarded = array();

    
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function leadStatus() {
        return self::hasOne(Status::class, 'id', 'status_id');
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function userLead() {
        return self::hasOne(User::class, 'id', 'user_detail');
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function marketingCampaign() {
        return self::hasMany(MarketingCampaign::class, 'marketing_id', 'id')->latest();
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function userAssignee() {
        return self::hasOne(User::class, 'id', 'assignee_id');
    }
}
