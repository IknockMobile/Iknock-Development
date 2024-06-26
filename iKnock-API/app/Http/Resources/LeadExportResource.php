<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class LeadExportResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request) {
        $data['Id'] = $this->id;
        $data['Is Retired'] = $this->is_expired == 1 ? 'YES' : 'NO';
        $data['Assigned To'] = $this->assign_first_name . ' ' . $this->assign_last_name;
        $data['Lead Status'] = $this->lead_status;
        $data['Lead Type'] = $this->lead_type;
        $data['Auction'] = $this->auction_date != '' ? date('m/d/y', strtotime($this->auction_date)) : '';
        $data['Address'] = $this->address;
        $data['City'] = $this->city;
        $data['State'] = $this->state;
        $data['Zip'] = $this->zip_code;
        $data['County'] = $this->county;
        $data['Homeowner Name'] = $this->title;
        $data['Admin Notes'] = $this->admin_notes;
        $data['Lead Value'] = $this->lead_value;
        $data['Mortgagee'] = $this->mortgagee;
        $data['Original Loan'] = $this->original_loan;
        $data['Loan Date'] = $this->loan_date;
        $data['Loan Type'] = $this->loan_type;
        $data['Loan Mod'] = $this->loan_mod;
        $data['Trustee'] = $this->trustee;
        $data['Sq Ft'] = $this->sq_ft;
        $data['Yr Blt'] = $this->yr_blt;
        $data['Owner Address - If Not Owner Occupied'] = $this->owner_address;
        $data['EQ'] = $this->eq;
        $data['Source'] = $this->source;
        $data['Is Verified'] = $this->is_verified == 1 ? 'YES' : 'NO';
        $data['Notes'] = $this->latestnotes->response;
        $data['Created By'] = $this->created_by;
        $data['Updated By'] = $this->updated_by;
        $data['Created Date'] = dynamicDateFormat(dateTimezoneChangeFullDateTimeReturn($this->created_at), 5);
        $data['Created Time'] = dynamicDateFormat(dateTimezoneChangeFullDateTimeReturn($this->created_at), 9);
        $data['Updated Date'] = dynamicDateFormat(dateTimezoneChangeFullDateTimeReturn($this->updated_at), 5);
        $data['Updated Time'] = dynamicDateFormat(dateTimezoneChangeFullDateTimeReturn($this->updated_at), 9);
        return $data;
    }

}
