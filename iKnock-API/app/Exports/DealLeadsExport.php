<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\DealLeadViewSetp;

class DealLeadsExport implements FromCollection, WithHeadings
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

        $dealLeadViewSetp = DealLeadViewSetp::whereNotIn('title_slug',['action','no','all_delete'])->where('is_show','1')->orderBy('order','asc')->pluck('title')->toArray();

        return $dealLeadViewSetp;
    } 
}
