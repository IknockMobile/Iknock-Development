<?php

namespace App\Imports;

use App\Models\UserCommission;
use App\Models\User;
use App\Models\CommissionEvents;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Session;

class CommissionImport implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        $commissionEvents = CommissionEvents::where('title',$row['type'])->first();
        $input['tenant_id'] = $user->company_id;

        if(is_null($commissionEvents) && !empty($row['type'])){
            
            $user = Session()->get('user');
            $input['title'] = $row['type'];

            CommissionEvents::create($input);
        }

        if(!empty($row['date'])){
            $userCommission['target_month'] = dynamicDateFormat($row['date'],2);
            $userCommission['created_at'] = dynamicDateFormat($row['date'],2);
        }else{
            $userCommission['target_month'] = null;
        }

        $userCommission['commission'] = abs($row['amount']);
        $userCommission['commission_event'] = $row['type'];
        $userCommission['comments'] = $row['memodescription'];
        $userCommission['tenant_id'] = $user->company_id;

        if(!empty($row['investor'])){
           $user = User::where('user_group_id',5)->where('first_name', $row['investor'])->first();

           if(is_null($user)){
                $userInvestor['first_name'] = $row['investor'];
                $userInvestor['user_group_id'] = 5;

                $user = User::create($userInvestor);
           }
            
            $userCommission['user_id'] = $user->id;
        }

        $userCommissionDB = UserCommission::where('user_id',$userCommission['user_id'])
                                            ->where('target_month',$userCommission)
                                            ->where('commission_event',$row['type'])
                                            ->first();

        if(!is_null($userCommission['commission']) && $userCommission['commission'] != 0 &&  is_null($userCommissionDB)){
            $userCommissionDB = UserCommission::create($userCommission);
        }

        return $userCommissionDB;
    }
}
