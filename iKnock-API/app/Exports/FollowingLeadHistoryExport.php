<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\FollowUpLeadViewSetp;
use App\Models\FollowingLead;

class FollowingLeadHistoryExport implements FromCollection, WithHeadings {

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function __construct($data, $headings = []) {
        $this->data = $data;
        $this->headings = $headings;
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

        return [
            'Homeowner Name',
            'Address',
            'Is Retired',
            'Information Updated',
            'Status',
            'Lead',
            'Investor',
            'Date to Follow Up',
            'Auction',
            'Purchase Date',
            'Contract Date',
            'Updated By',
            'Who',
            'Lead Updated Date',
            'Lead Updated Time',
            'History Created Date',
            'History Created Time',
            'Created By',
            'Lead Type',
            'Lead Status',
            'Investor Notes',
            'Notes',
            'Auth Signed Date',
            'Appointment Date',
            'Appointment Time',
            'Appointment Result',
            'Appointment Additional Notes',
            'Appointment Mobile',
            'Appointment Email',
            'Appointment Person To Meeting'
        ];
    }

}
