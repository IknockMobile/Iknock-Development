<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\DealLeadViewSetp;

class UserExport implements FromCollection, WithHeadings
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
        $headers = [];
        $headers[] = 'id'; 
        $headers[] = 'code'; 
        $headers[] = 'first_name'; 
        $headers[] = 'last_name'; 
        $headers[] = 'email'; 
        $headers[] = 'mobile_no'; 
        $headers[] = 'date_of_join'; 
        $headers[] = 'status'; 
        $headers[] = 'startup_paid'; 
        $headers[] = 'startup_reimbursed'; 
        $headers[] = 'user_group_id'; 
        $headers[] = 'last_app_activity'; 
        $headers[] = 'created_at'; 
        $headers[] = 'updated_at'; 
        return $headers;
    } 
}
