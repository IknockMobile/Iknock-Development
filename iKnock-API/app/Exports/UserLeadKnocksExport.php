<?php

namespace App\Exports;

use App\Models\UserLeadKnocks;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserLeadKnocksExport implements FromCollection,WithHeadings
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
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return UserLeadKnocks::all();
    }
}
