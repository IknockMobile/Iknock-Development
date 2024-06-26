<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DealLeadsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $deal['deal_no'] = $this->deal_no;
        $deal['homeowner_name'] = $this->title;
        $deal['address'] = $this->address;
        $deal['city'] = $this->city;
        $deal['county'] = $this->county;
        $deal['state'] = $this->state;
        $deal['zip_code'] = $this->zip_code;
        $deal['sq_ft'] = $this->sq_ft;
        $deal['yr_blt'] = $this->yr_blt;
        $deal['investor'] = getUser($dealLead->investor_id)->fullName;
        $deal['closer'] = getUser($dealLead->closer_id)->fullName;
        $deal['deal_status'] = $this->dealStatusLabel;
        $deal['deal_type'] = $this->dealTypeLabel;
        $deal['purchase_finance'] = $this->purchaseFinanceLabel;
        $deal['ownership'] = $this->ownershipLabel;
        $deal['purchase_date'] = dynamicDateFormat($dealLead->purchase_date,5) ;
        $deal['sell_date'] = dynamicDateFormat($dealLead->sell_date,5);
        $deal['purchase_price'] = numberDollor($dealLead->purchase_price);
        $deal['purchase_closing_costs'] =  numberDollor($dealLead->purchase_closing_costs);
        $deal['cash_in_at_purchase'] = numberDollor($dealLead->cash_in_at_purchase);
        $deal['rehab_and_other_costs'] = numberDollor($dealLead->rehab_and_other_costs);
        $deal['total_cash_in'] = numberDollor($dealLead->total_cash_in);
        $deal['investor_commission'] = numberDollor($dealLead->Investor_commission);
        $deal['total_cost'] = numberDollor($dealLead->total_cost);
        $deal['sales_value'] = numberDollor($dealLead->sales_value);
        $deal['sales_cash_proceeds'] = numberDollor($dealLead->sales_cash_proceeds);
        $deal['lh_profit_after_sharing'] = numberDollor($dealLead->lh_profit_after_sharing);
        $deal['notes'] = $this->notes;

        return $deal;
    }
}
