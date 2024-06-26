<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingCampaign extends Model
{
    use HasFactory;

    protected $guarded = array();

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function campaign() {
        return self::hasOne(Campaign::class,'id','campaign_db_id');
    }
}
