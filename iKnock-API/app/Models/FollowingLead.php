<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Kyslik\ColumnSortable\Sortable;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as Auditables;

class FollowingLead extends Model implements Auditable {

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

    public function Appointment() {
        return self::hasOne(UserLeadAppointment::class, 'lead_id', 'lead_id')
                        ->select('appointment_date', 'result', 'additional_notes', 'phone', 'email', 'person_meeting')
                        ->latest();
    }

    public function AppointmentLatest() {
        return self::hasOne(UserLeadAppointment::class, 'lead_id', 'lead_id')
                        ->select('appointment_date', 'result', 'additional_notes', 'phone', 'email', 'person_meeting')
                        ->latest()
                        ->limit(1);
    }

    public function latestNote() {
        return self::hasOne(LeadQuery::class, 'lead_id', 'lead_id')
                        ->select('response')
                        ->where('query_id', '=', 8)
                        ->orderBy('id', 'desc');
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
        return self::hasMany(FollowingCustomFields::class, 'followup_lead_id', 'id');
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
        return $this->hasMany(FollowingCustomFields::class, 'followup_lead_id', 'id')
                        ->whereHas('followupLeadViewSetp', function ($query) {
                            $query->where('view_type', 1)
                            ->orderBy('order_no', 'Asc');
                        });
    }

    public function customFields() {
        return $this->hasMany(FollowingCustomFields::class, 'followup_lead_id');
    }

}
