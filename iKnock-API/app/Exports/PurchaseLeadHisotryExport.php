<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PurchaseLeadHisotryExport implements FromCollection, WithHeadings {

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
            'Sr No',
            'Lead Id',
            'Homeowner Name',
            'Homeowner Address',
            'Purchase Date',
        ];
    }

}
