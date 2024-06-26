<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeadExport implements FromCollection, WithHeadings {

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
        return[
            'Id',
            'Is Retired',
            'Assigned To',
            'Lead Status',
            'Lead Type',
            'Auction',
            'Address',
            'City',
            'State',
            'Zip',
            'County',
            'Homeowner Name',
            'Admin Notes',
            'Lead Value',
            'Mortgagee',
            'Original Loan',
            'Loan Date',
            'Loan Type',
            'Loan Mod',
            'Trustee',
            'Sq Ft',
            'Yr Blt',
            'Owner Address - If Not Owner Occupied',
            'EQ',
            'Source',
            'Is Verified',
            'Notes',
            'Created By',
            'Updated By',
            'Created Date',
            'Created Time',
            'Updated Date',
            'Updated Time'
        ];
    }

}
