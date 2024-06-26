<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Kyslik\ColumnSortable\Sortable;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as Auditables;
use App\Models\PurchaseCustomFields;
class PurchaseLead extends Model implements Auditable {

    use HasFactory,
        Cachable,
        Sortable,
        Auditables,
        SoftDeletes;

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
    public function investor() {
        return self::hasOne(User::class, 'id', 'investor_id');
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function leadFollowStatus() {
        return self::hasOne(FollowStatus::class, 'id', 'follow_status');
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function leadFollowupCustomFields() {
        return self::hasMany(PurchaseCustomFields::class, 'followup_lead_id', 'id');
    }

    /**
     * Write code photo path
     *
     * @return response()
     */
    public function getFollowCustomDataAttribute() {
        return json_decode($this->follow_custom, true);
    }

    /**
     * Get the listing Category detail
     */
    public function getFollowingLeadCustomFileds() {
        return $this->hasMany(PurchaseCustomFields::class, 'followup_lead_id', 'id')
                        ->whereHas('PurchaseLeadViewSetp', function ($query) {
                            $query->where('view_type', 1)
                            ->orderBy('order_no', 'Asc');
                        });
    }

}
