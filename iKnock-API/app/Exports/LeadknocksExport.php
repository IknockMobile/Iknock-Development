<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeadknocksExport implements FromCollection, WithHeadings
{

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function collection()
    {
        return collect($this->data);
    }

    /**
    * @return \Illuminate\Support\Collection
    */ 
    public function headings():array{
        return[
            'User Name',
            'Homeowner Name',
            'Homeowner Address',
            'Is Verified',
            'Status',
            'Distance',
            'Created Date',
            'Created Time',
            'Lead Latitude',
            'Lead Longitude',
            'User Latitude',
            'User Longitude',
            'Backend Distance',
        ];
    } 
}
