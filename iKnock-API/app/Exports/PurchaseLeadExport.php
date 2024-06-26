<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\FollowUpLeadViewSetp;
use App\Models\FollowingLead;
use App\Models\PurchaseLeadViewSetp;

class PurchaseLeadExport implements FromCollection, WithHeadings {

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function collection() {
        return collect($this->data);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function headings(): array {

        $followUpLeadViewSetp = PurchaseLeadViewSetp::whereNotIn('title_slug', ['no', 'is_deal', 'is_marketing', 'lead_mgmt', 'action', 'is_lead_up', 'all_delete', 'notes_and_actions'])->orderBy('order_no', 'asc')->pluck('title')->toArray();

        return $followUpLeadViewSetp;
    }

}
