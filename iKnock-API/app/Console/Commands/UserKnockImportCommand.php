<?php

namespace App\Console\Commands;
use App\Models\UserLeadKnocks;
use App\Models\LeadHistory;
use App\Models\Status;
use App\Models\User;
use App\Models\UserKnocksImport;
use App\Models\Lead;
use Carbon\Carbon;
use Session;

use Illuminate\Console\Command;

class UserKnockImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:userKnockImportCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {       
        $userknocksimports = UserKnocksImport::where('is_run',0)->take(200)->get();

            if(!empty($userknocksimports)){
                foreach ($userknocksimports as $key => $userknocksimport) {
                     if(!empty($userknocksimport['investor'])){
                           $user = User::where('user_group_id',4)->where('first_name', $userknocksimport['investor'])->first();

                           $userInvestor['first_name'] = $userknocksimport['investor'];
                           $userInvestor['user_group_id'] = 4;

                           if(empty($userknocksimport->status) && $userknocksimport->status == 'Inactive'){
                               $userInvestor['status_id'] = 0;
                           }else{
                               $userInvestor['status_id'] = 1;
                           }


                           if(is_null($user)){
                                $user = User::create($userInvestor);
                           }else{
                                $user->update($userInvestor);
                           }
                            
                            $knockInput['user_id'] = $user->id;
                        }

                        $user = Session()->get('user');

                        $leadInput['title'] = 'History Knock lead';
                        $leadInput['is_disabled'] = 1;
                        $leadInput['company_id'] = $user->company_id ?? null;
                        $leadInput['assignee_id'] = $knockInput['user_id'];

                        $lead = Lead::where('assignee_id',$knockInput['user_id'])
                                        ->where('title',$leadInput['title'])
                                        ->first();

                        if(is_null($lead)){
                            $lead = Lead::create($leadInput);
                        }

                        $knocks_no = $userknocksimport['of_knocks']; 
                        $knocks_appt_scheduled = $userknocksimport['appt_scheduled']; 

                        $status = Status::where('title','Appt Request')->first();
                        $statusHistory = Status::where('title','History')->first();
                        $knockInput['created_at'] = dynamicDateFormat($userknocksimport['year'].'-'.$userknocksimport['month'].'-1 18:59:59',1);

                        if($knocks_no != 0){
                            for ($i=1; $i <= $knocks_no ; $i++) {
                                
                                $knockInput['is_historical'] =  1;
                                
                                if($knocks_appt_scheduled != 0  && $i <= $knocks_appt_scheduled){

                                    $lead_history = [
                                             'lead_id' =>  $lead->id,
                                             'is_historical' => 1,
                                             'title' => "Lead status updated.",
                                             'assign_id' => $knockInput['user_id'],
                                             'status_id' => $status->id,
                                             'latest_status_id' => null,
                                             'historical_id' => $userknocksimport->id,
                                             'created_at' => dynamicDateFormat($userknocksimport['year'].'-'.$userknocksimport['month'].'-1 18:59:59',1),
                                         ];

                                         // info($lead_history);
                                         // info($knocks_appt_scheduled);

                                    $leadHistory = LeadHistory::create($lead_history);

                                    $knockInput['lead_history_id'] =  $leadHistory;
                                    $knockInput['historical_id'] =  $userknocksimport->id;
                                    $knockInput['status_id'] =  $status->id;
                                    $knockInput['lead_id'] =  $lead->id;
                                    UserLeadKnocks::create($knockInput);
                                         info('appt_knocks_rqust');

                                }else{
                                    
                                $lead_historyElse = [
                                            'lead_id' =>  $lead->id,
                                            'is_historical' => 1,
                                            'title' => "Lead History Knocks Create",
                                            'assign_id' => $knockInput['user_id'],
                                            'status_id' => $statusHistory->id ?? 0,
                                            'latest_status_id' => null,
                                            'historical_id' => $userknocksimport->id,
                                            'created_at' => dynamicDateFormat($userknocksimport['year'].'-'.$userknocksimport['month'].'-1 18:59:59',1),
                                        ];

                                        
                                    $leadHistory = LeadHistory::create($lead_historyElse);

                                    $knockInput['lead_history_id'] =  $leadHistory;
                                    $knockInput['historical_id'] =  $userknocksimport->id;
                                    $knockInput['status_id'] =  $statusHistory->id ?? 0;
                                    $knockInput['lead_id'] =  $lead->id;
                                    UserLeadKnocks::create($knockInput);
                                         info('appt_knocks');
                                } 
                            }
                        }

                    $userknocksimport->is_run = 1;
                    $userknocksimport->save();
                }
            }

        return 0;
    }
}
