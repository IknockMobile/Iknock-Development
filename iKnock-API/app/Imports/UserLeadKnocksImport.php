<?php

namespace App\Imports;

use App\Models\UserLeadKnocks;
use App\Models\LeadHistory;
use App\Models\Status;
use App\Models\User;
use App\Models\UserKnocksImport;
use App\Models\Lead;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;
use Session;

class UserLeadKnocksImport implements ToModel ,WithHeadingRow
{   
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {   
        $input['status'] = $row['status'];
        $input['year'] = $row['year'];
        $input['month'] = $row['month'];
        $input['investor'] = $row['investor'];
        $input['of_knocks'] = $row['of_knocks'];
        $input['appt_scheduled'] = $row['appt_scheduled'];


       $userKnocksImport = UserKnocksImport::where('status',$input['status'])
                                    ->where('year',$input['year'])
                                    ->where('month',$input['month'])
                                    ->where('investor',$input['investor'])
                                    ->first();
    
        if(is_null($userKnocksImport) && !empty($input['investor'])){
            $userKnocksImport = UserKnocksImport::create($input);
        }

        return $userKnocksImport;
    }
}
