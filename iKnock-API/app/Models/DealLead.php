<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as Auditables;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class DealLead extends Model  implements Auditable
{
    use HasFactory,Auditables,Cachable;

    protected $guarded = array();

    public $dealStatus = [1=>'Active',2=>'Completed',3=>'Hold'];
    
    public $dealType = [1=>'Short Sale',2=>'Assignment',3=>'Sell As Is',4=>'Flip',5=>'Rental',6=>'Lease to Buy'];
    
    public $purchaseFinance = [1=>'Full Cash',2=>'Subject To'];

    public $ownershipList = [1=>'Owner',2=>'Partnership'];

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function getInvestor()
    {
        return self::hasOne(User::class, 'id', 'investor_id');
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function getCloser()
    {
        return self::hasOne(User::class, 'id', 'closer_id');
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function getDealStatusLabelAttribute()
    {
        if(empty($this->deal_status)){
            return '';
        }

        return $this->dealStatus[$this->deal_status];
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function getDealTypeLabelAttribute()
    {
        if(empty($this->deal_type)){
            return '';
        }

        return $this->dealType[$this->deal_type];
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function getPurchaseFinanceLabelAttribute()
    {
        if(empty($this->purchase_finance)){
            return '';
        }

        return $this->purchaseFinance[$this->purchase_finance];
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function getOwnershipLabelAttribute()
    {
        if(empty($this->ownership)){
            return '';
        }
        return $this->ownershipList[$this->ownership];
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    /**
     * Write code on Method
     *
     * @return response()
     */
    protected static function boot() {

      parent::boot();

      static::updating(function ($input) {
        if($input->isDirty('deal_status') == 1){
            $title = $input->dealStatusLabel.' deal status updated from Deal Lead Management.  ';
            createLeadHistoryTitle($input->lead_id, $title);
        }

        if($input->isDirty('purchase_finance') == 1){
            $title = $input->purchaseFinanceLabel.' deal purchase finance updated from Deal Lead Management.  ';
            createLeadHistoryTitle($input->lead_id, $title);
        }

        if($input->isDirty('deal_type') == 1){
            $title = $input->dealTypeLabel.' deal type updated from Deal Lead Management.  ';
            createLeadHistoryTitle($input->lead_id, $title);
        }

        if($input->isDirty('ownership') == 1){
            $title = $input->ownershipLabel.'deal ownership updated from Deal Lead Management.  ';
            createLeadHistoryTitle($input->lead_id, $title);
        }

        if($input->isDirty('investor_id') == 1){
            $title = $input->getInvestor->fullName.'deal investor updated from Deal Lead Management.';
            createLeadHistoryTitle($input->lead_id, $title);
        }

          if($input->isDirty('closer_id') == 1){
            $title = $input->getCloser->fullName.'deal closer updated from Deal Lead Management. ';
            createLeadHistoryTitle($input->lead_id, $title);
        }
        
      });
    }
}
