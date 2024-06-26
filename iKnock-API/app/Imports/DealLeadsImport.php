<?php

namespace App\Imports;

use App\Models\DealLead;
use App\Models\DealLeadViewSetp;
use App\Models\DealLeadViewCustomFields;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class DealLeadsImport implements ToModel, WithHeadingRow, WithCalculatedFormulas {

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row) {
                
        $input['deal_no'] = $row['id'] ?? '';
        $input['title'] = $row['homeowner_name'] ?? '';
        $input['address'] = $row['street_address'] ?? '';        
        $input['city'] = $row['city'];
        $input['state'] = $row['state'];
        $input['county'] = $row['county'];
        $input['zip_code'] = $row['zip'];
        $input['sq_ft'] = $row['sq_ft'];
        $input['yr_blt'] = $row['year_built'];
        $dealLeadobj = new DealLead();
        $rowDealStatus = array_search($row['deal_status'] ?? '', $dealLeadobj->dealStatus);
        $input['deal_status'] = $rowDealStatus ? $rowDealStatus : null;

        if (!empty($row['investor'])) {
            $user = User::where('user_group_id', 4)->where('first_name', $row['investor'])->first();
            if (is_null($user)) {
                $userInvestor['first_name'] = $row['investor'];
                $userInvestor['user_group_id'] = 4;
                $user = User::create($userInvestor);
            }
            $input['investor_id'] = $user->id;
        }

        if (!empty($row['closer'])) {
            $user = User::where('user_group_id', 4)->where('first_name', $row['closer'])->first();
            if (is_null($user)) {
                $userCloser['first_name'] = $row['closer'];
                $userCloser['user_group_id'] = 4;
                $user = User::create($userCloser);
            }
            $input['closer_id'] = $user->id;
        }

        // $rowDealType = array_search($row['deal_type'], $dealLeadobj->dealType);
        // $input['deal_type'] = $rowDealType ? $rowDealType : null;

        $rowPurchaseFinance = array_search($row['purchase_finance'], $dealLeadobj->purchaseFinance);
        $input['purchase_finance'] = $rowPurchaseFinance ? $rowPurchaseFinance : null;
        $rowOwnership = array_search($row['ownership'] ?? '', $dealLeadobj->ownershipList);
        $input['ownership'] = $rowOwnership ? $rowOwnership : null;

        if (!empty($row['purchase_date']) && is_int($row['purchase_date']) AND isset($row['purchase_date'])) {
            $row['purchase_date'] = Date::excelToDateTimeObject($row['purchase_date']);
            $input['purchase_date'] = dynamicDateFormat($row['purchase_date'], 2);
        }

        if (!empty($row['sell_date']) AND isset($row['sell_date']) AND!empty($row['sell_date']) && $row['sell_date'] != 'TBD') {
            $row['sell_date'] = Date::excelToDateTimeObject($row['sell_date']);
            $input['sell_date'] = dynamicDateFormat($row['sell_date'], 2);
        }

        $input['purchase_price'] = getPriceInt($row['purchase_price']);
        $input['purchase_closing_costs'] = getPriceInt($row['purchase_commitment_no_closing_cost']);
        $input['cash_in_at_purchase'] = getPriceInt($row['cash_in_at_purchase'] ?? '');
        $input['rehab_and_other_costs'] = getPriceInt($row['rehab_and_other_costs']);
        $input['total_cash_in'] = getPriceInt($row['total_cash_in'] ?? '');
        $input['Investor_commission'] = getPriceInt($row['investor_commission'] ?? '');
        $input['total_cost'] = getPriceInt($row['total_cost'] ?? '');
        $input['sales_value'] = getPriceInt($row['sales_value'] ?? '');
        $input['sales_cash_proceeds'] = getPriceInt($row['sales_cash_proceeds'] ?? '');
        $input['lh_profit_after_sharing'] = getPriceInt($row['lh_profit_after_partnership'] ?? '');
        
        $input['custum_fields'] = json_encode($row);
        $input['notes'] = $row['notes'] ?? '';

        $dealLead = null;

        $dealLead = DealLead::whereAddress($input['address'])->first();
        if (!empty($input['address']) && is_null($dealLead)) {
            $dealLead = DealLead::create($input);           
        }

        if(!empty($dealLead->id)){
            updateCustomFiledDeal($dealLead->id);

            $dealLeadViewSetp = DealLeadViewSetp::where('view_type',1)->get();

            if(!empty($dealLeadViewSetp)){
                foreach ($dealLeadViewSetp as $key => $dealLeadView) {
                    $value = $row[$dealLeadView->title_slug] ?? '';
                    info($row['deal_type']);
                    $dealLeadViewCustomFields = DealLeadViewCustomFields::where('deal_lead_id',$dealLead->id)->where('deal_view_id',$dealLeadView->id)->first();

                    if(!is_null($dealLeadViewCustomFields) && !empty($value)){

                            if($dealLeadView->input_type == 2 && is_numeric($value)){
                                $value = Date::excelToDateTimeObject($value);
                                $value = dynamicDateFormat($value, 5);
                            }

                             if($dealLeadView->input_type == 4){
                                $value = str_replace('$', '', $value);
                                $value = str_replace(',', '', $value);
                            }

                            $dealLeadViewCustomFields->field_value = $value;
                            $dealLeadViewCustomFields->save();
                    }
                }
            }
        }



        return $dealLead;
    }

}
