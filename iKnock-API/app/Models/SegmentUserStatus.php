<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SegmentUserStatus extends Model
{
    use HasFactory;

    protected $guarded = array();

     /**
     * Write code on Method
     *
     * @return response()
     */
    public function user() {
        return self::hasOne(CampaignUser::class, 'id', 'user_id');
    }
}
