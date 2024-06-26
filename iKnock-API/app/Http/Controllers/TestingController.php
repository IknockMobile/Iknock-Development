<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\LeadQuery;
use App\Models\LeadView;
use App\Models\Audit;
use App\Models\User;
use App\Models\Company;
use App\Models\LeadHistory;
use App\Models\FollowingLead;
use App\Http\Resources\FollowingLeadResource;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\LeadIndex;
Use App\Models\UserLeadKnocks;
use Google\Client;
use Config;
use Carbon\Carbon;
use Google\Service\Books;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Twilio\Rest\Client as TwilioClient;
use App\Models\Alerts;
use Illuminate\Support\Facades\Mail;
use App\Models\LeadType;
use App\Models\CarbonPeriod;
use App\Models\Campaign;
use App\Models\CampaignSegment;
use App\Models\UserKnocksImport;
use App\Models\FollowStatus;
use App\Models\DealLead;
use App\Models\Status;
use App\Models\CampaignUser;
use App\Models\MarketingCampaign;
use App\Models\SegmentUserStatus;
use App\Models\FollowUpLeadViewSetp;
use App\Models\FollowingCustomFields;
use App\Models\UserLeadAppointment;
use Illuminate\Support\Facades\DB;

class TestingController extends Controller {

    public function KNockLatLongUpdate(Request $request) {
        $leads = UserLeadKnocks::WhereNull('lead_lat')
                ->WhereNull('lead_long')
                ->Where('application_lat', '!=', null)
                ->Where('application_long', '!=', null)
                ->where('id', '>', 12060)
                ->get();

        if (!isset($leads[0])) {
            echo "records updated susccesfully";
            exit;
        }

        foreach ($leads as $lead) {
            if (isset($lead->lead->latitude) AND $lead->lead->latitude != '' AND isset($lead->lead->longitude)) {
                $distance = calculateDistance($lead->lead->latitude, $lead->lead->longitude, $lead->application_lat, $lead->application_long);
                $lead->lead_lat = $lead->lead->latitude;
                $lead->lead_long = $lead->lead->longitude;
                $lead->distance = $distance;
                $lead->backend_distance = $distance;
                if ($distance < 100) {
                    $lead->is_verified = 1;
                } else {
                    $lead->is_verified = 0;
                }
                $lead->update();
            }
            echo $lead->id;
            echo "<br>";
        }
    }

    public function updateApptAddress(Request $request) {
        $leads = UserLeadAppointment::where('result', 'like', '%Address: ,%')
                ->get();
        foreach ($leads as $lead) {
            $lead_data = Lead::where('id', '=', $lead->lead_id)->first();
            if (isset($lead_data->id)) {
                $text = str_replace('Address: ,', 'Address: ' . $lead_data->formatted_address . ',', $lead->result);
                $lead->result = $text;
                $lead->save();
                echo $lead->id;
                echo "<br>";
            }
        }
    }

    public function LatLongUpdate(Request $request) {
        $leads = Lead::where('latitude', '=', '0.00000000')
                ->where('longitude', '=', '0.00000000')
                ->get();

        if (!isset($leads[0])) {
            echo "records updated susccesfully";
            exit;
        }

        foreach ($leads as $lead) {
            $lat_long_response = $this->getLatLongFromAddress($lead->address . ',' . $lead->city);
            $lead->latitude = $lat_long_response['lat'];
            $lead->longitude = $lat_long_response['long'];
            $lead->formatted_address = $lat_long_response['formatted_address'];
            $lead->update();

            echo $lead->id;
            echo "<br>";
        }
    }

    public function AppRemove() {

        $appointmentDate = '2023-08-31';

        $appoiment_datas = UserLeadAppointment::where(DB::raw('DATE(appointment_date)'), 'LIKE', "$appointmentDate%")->get();

        foreach ($appoiment_datas as $appoiment_data) {
            UserLeadAppointment::destroy($appoiment_data->id);

            if (isset($appoiment_data->calendar_event_id) AND $appoiment_data->calendar_event_id != '') {
                $client = new Client();

                if ($credentials_file = $this->checkServiceAccountCredentialsFile()) {
                    $client->setAuthConfig($credentials_file);
                } elseif (getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
                    $client->useApplicationDefaultCredentials();
                } else {
                    echo missingServiceAccountDetailsWarning();
                    return;
                }

                $client->setApplicationName("Client_Library_Examples");
                $client->setScopes(['https://www.googleapis.com/auth/calendar']);

                $user_to_impersonate = 'mobiledev@semaphoremobile.com';
                $client->setSubject($user_to_impersonate);

                $service = new Calendar($client);
                $event_id = $appoiment_data->calendar_event_id;
                $calendar_id = 'c_6f9bf513c524a8b73219803a02364d3367065fd0ab70932f9e0756cd0f02b1cf@group.calendar.google.com';
                $event = $service->events->get($calendar_id, $event_id);

                $service->events->delete($calendar_id, $event->getId());

                echo "remvoed appointment";
                echo "<br>";
            }
        }




        echo "123";
        exit;
    }

    public function checkServiceAccountCredentialsFile() {
        $application_creds = storage_path('app/service-account-credentials.json');

        return file_exists($application_creds) ? $application_creds : false;
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function mailchipClear(Request $request) {

        if ($request->status == "clear") {
            Campaign::truncate();
            CampaignSegment::truncate();
            CampaignUser::truncate();
            MarketingCampaign::truncate();
            SegmentUserStatus::truncate();
        }

        dd('done');
    }

    public function findWeeksBetweenTwoDates($startDate, $endDate) {
        $weeks = [];
        while (strtotime($startDate) <= strtotime($endDate)) {
            $oldStartDate = $startDate;
            $startDate = date('Y-m-d', strtotime('+7 day', strtotime($startDate)));
            if (strtotime($startDate) > strtotime($endDate)) {
                $week = [$oldStartDate, $endDate];
            } else {
                $week = [$oldStartDate, date('Y-m-d', strtotime('-1 day', strtotime($startDate)))];
            }

            $weeks[] = $week;
        }

        return $weeks;
    }

    public function isfollowup($id) {
        $lead = Lead::find($id);

        $lead->is_follow_up = 1;

        $lead->save();

        $followingLead = FollowingLead::where('lead_id', $id)->first();

        if (!isset($followingLead->id)) {
            $input['lead_id'] = $id;
            $input['title'] = $lead->title;
            $input['owner'] = $lead->owner;
            $input['address'] = $lead->address;
            $input['admin_notes'] = $lead->admin_notes;
            $input['foreclosure_date'] = $lead->foreclosure_date;
            $input['identifier'] = $lead->identifier;
            $input['formatted_address'] = $lead->formatted_address;
            $input['city'] = $lead->city;
            $input['county'] = $lead->county;
            $input['state'] = $lead->state;
            $input['zip_code'] = $lead->zip_code;
            $input['type_id'] = $lead->type_id;
            $input['status_id'] = $lead->status_id;
            $status = Status::find($lead->status_id);

//            if (!is_null($status)) {
//                $followStatus = FollowStatus::where('title', $status->title)->first();
//                if (!is_null($followStatus)) {
//                    $input['follow_status'] = $followStatus->id;
//
//                    LeadHistory::create([
//                            'lead_id' => $id,
//                            'title' => $followStatus->title.' status updated where lead move in to Follow Up Lead Management.',
//                            'assign_id' => $request['user_id'],
//                            'status_id' => 0,
//                            'followup_status_id' => $followStatus->id
//                    ]);
//
//                }
//            }

            $input['is_verified'] = $lead->is_verified;
            $input['creator_id'] = $lead->creator_id;
            $input['company_id'] = $lead->company_id;
            $input['assignee_id'] = $lead->assignee_id;
            $input['is_expired'] = $lead->is_expired;
            $input['latitude'] = $lead->latitude;
            $input['longitude'] = $lead->longitude;
            $input['appointment_date'] = $lead->appointment_date;
            $input['appointment_result'] = $lead->appointment_result;
            $input['auction'] = $lead->auction;
            $input['lead_value'] = $lead->lead_value;
            $input['original_loan'] = $lead->original_loan;
            $input['loan_date'] = $lead->loan_date;
            $input['sq_ft'] = $lead->sq_ft;
            $input['yr_blt'] = $lead->yr_blt;
            $input['eq'] = $lead->eq;
            $input['mortgagee'] = $lead->mortgagee;
            $input['loan_type'] = $lead->loan_type;
            $input['loan_mod'] = $lead->loan_mod;
            $input['trustee'] = $lead->trustee;
            $input['owner_address'] = $lead->owner_address;
            $input['source'] = $lead->source;
            $input['created_by'] = $lead->created_by;
            $input['updated_by'] = $lead->updated_by;
            $input['sq_ft_2'] = $lead->sq_ft_2;
            $input['original_loan_2'] = $lead->original_loan_2;
            $input['investor_id'] = $lead->assignee_id;
            $input['is_lead_up'] = 0;

            $leadQuery = LeadQuery::where('query_id', 8)->where('lead_id', $id)->latest()->first();

            $input['investor_notes'] = $leadQuery->response ?? null;

            $leadCustomField = LeadCustomField::where('lead_id', $id)->get();

            $inputCustom = [];

            if (!empty($leadCustomField) && count($leadCustomField) != 0) {
                foreach ($leadCustomField as $key => $value) {
                    $inputCustom[$key]['field_id'] = $value->id;
                    $inputCustom[$key]['field_key'] = $value->key ?? '';
                    $inputCustom[$key]['field_value'] = $value->value ?? '';
                }
            }

            $followingLead = FollowingLead::create($input);

            updateCustomFiled($followingLead->id);
        } else {
            $followingLead->auction = $lead->auction;
            $followingLead->is_retired = $lead->is_expired;
            $followingLead->investor_id = $lead->assignee_id;
            $followingLead->admin_notes = $lead->admin_notes;
            $followingLead->formatted_address = $lead->address;
            $followingLead->title = $lead->title;
            $followingLead->is_lead_up = 0;
            $followingLead->save();
        }

//        $obj_lead_history = LeadHistory::create([
//                    'lead_id' => $id,
//                    'title' => 'Lead Moved into Follow Up Lead Management.',
//                    'assign_id' => $request['user_id'],
//                    'status_id' => 0
//        ]);


        return response()->json(['success' => 1]);
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function setupFollowupAddressUpdate() {
        $followingLeads = FollowingLead::whereNull('address')->get();


        if (!empty($followingLeads)) {
            foreach ($followingLeads as $key => $followingLead) {
                $lead = Lead::find($followingLead->lead_id);

                $followingLead->address = $lead->address ?? null;
                $followingLead->save();
            }
        }

        return true;
    }

    public function index(Request $request) {

        dd(csrf_token());

        $request->id = 420;

        $followingLead = FollowingLead::find($request->id);

        if (!is_null($followingLead)) {
            $followingLead->is_purchase = 1;
            $followingLead->save();
            $PurchaseLead = \App\Models\PurchaseLead::where('lead_id', $followingLead->lead_id)->first();

            $input['lead_id'] = $followingLead->lead_id;
            $input['title'] = $followingLead->title;
            $input['owner'] = $followingLead->owner;
            $input['address'] = $followingLead->address;
            $input['admin_notes'] = $followingLead->admin_notes;
            $input['foreclosure_date'] = $followingLead->foreclosure_date;
            $input['identifier'] = $followingLead->identifier;
            $input['formatted_address'] = $followingLead->formatted_address;
            $input['city'] = $followingLead->city;
            $input['county'] = $followingLead->county;
            $input['state'] = $followingLead->state;
            $input['zip_code'] = $followingLead->zip_code;
            $input['type_id'] = $followingLead->type_id;
            $input['status_id'] = $followingLead->status_id;
            $input['is_verified'] = $followingLead->is_verified;
            $input['creator_id'] = $followingLead->creator_id;
            $input['company_id'] = $followingLead->company_id;
            $input['assignee_id'] = $followingLead->assignee_id;
            $input['is_expired'] = $followingLead->is_expired;
            $input['latitude'] = $followingLead->latitude;
            $input['longitude'] = $followingLead->longitude;
            $input['appointment_date'] = $followingLead->appointment_date;
            $input['appointment_result'] = $followingLead->appointment_result;
            $input['auction'] = $followingLead->auction;
            $input['lead_value'] = $followingLead->lead_value;
            $input['original_loan'] = $followingLead->original_loan;
            $input['loan_date'] = $followingLead->loan_date;
            $input['sq_ft'] = $followingLead->sq_ft;
            $input['yr_blt'] = $followingLead->yr_blt;
            $input['eq'] = $followingLead->eq;
            $input['mortgagee'] = $followingLead->mortgagee;
            $input['loan_type'] = $followingLead->loan_type;
            $input['loan_mod'] = $followingLead->loan_mod;
            $input['trustee'] = $followingLead->trustee;
            $input['owner_address'] = $followingLead->owner_address;
            $input['source'] = $followingLead->source;
            $input['created_by'] = $followingLead->created_by;
            $input['updated_by'] = $followingLead->updated_by;
            $input['sq_ft_2'] = $followingLead->sq_ft_2;
            $input['original_loan_2'] = $followingLead->original_loan_2;
            $input['investor_notes'] = $followingLead->investor_notes;
            $input['investor_id'] = $followingLead->investor_id;
            $input['is_followup'] = 0;

            if (is_null($PurchaseLead)) {
                $PurchaseLead = \App\Models\PurchaseLead::create($input);
            } else {
                $PurchaseLead->update($input);
            }

            $followUpLeadViewSetps = FollowUpLeadViewSetp::orderBy('order_no', 'asc')
                    ->get();
            foreach ($followUpLeadViewSetps as $followUpLeadView) {
                echo $followUpLeadView->id;
                exit;
                $FollowingCustomFields = FollowingCustomFields::where('followup_lead_id', '=', $request->id)
                        ->where('followup_view_id', '=', $followUpLeadView->id)
                        ->first();

                echo $FollowingCustomFields->field_value;
                exit;
                $PurchaseLeadViewSetp = \App\Models\PurchaseLeadViewSetp::where('title', '=', $followUpLeadView->title)
                        ->first();

                if (isset($PurchaseLeadViewSetp->id)) {
                    $PurchaseCustomFieldsExits = \App\Models\PurchaseCustomFields::where('followup_lead_id', '=', $PurchaseLead->id)
                            ->where('followup_view_id', '=', $PurchaseLeadViewSetp->id)
                            ->first();

                    if (isset($PurchaseCustomFieldsExits->id)) {
                        $new_data = [];
                        $new_data['field_value'] = $FollowingCustomFields->field_value;
                        $new_data['updated_at'] = NOW();
                        $PurchaseCustomFieldsExits->update($new_data);
                    } else {
                        $new_data = [];
                        $new_data['followup_lead_id'] = $PurchaseLead->id;
                        $new_data['followup_view_id'] = $PurchaseLeadViewSetp->id;
                        $new_data['field_value'] = $FollowingCustomFields->field_value;
                        $new_data['created_at'] = NOW();
                        $new_data['updated_at'] = NOW();
                        \App\Models\PurchaseCustomFields::create($new_data);
                    }
                }
            }
        }


        echo "123";
        exit;



        $status = Status::whereNotNull('deleted_at')->pluck('id')->toArray();

        $userLeadKnocks = UserLeadKnocks::whereIn('status_id', $status)->get();

        $history = LeadHistory::whereIn('status_id', $status)->get();
        dd($history);
        dd($userLeadKnocks);

//        $leadHistorysDatas = LeadHistory::where('status_id', '=',95)
//                ->where('assign_id', '!=',0)    
//                ->whereNotNull('lead_history.lead_id')
//                ->get();
//        $k = 0;
//        foreach($leadHistorysDatas as $key => $data){
//            echo $k . ' ' . $data->id . ' '.$data->title;
//            echo "<br>";
//            $k = $k + 1;
//        }
//        
//        $leadHistorysDatas = LeadHistory::where('followup_status_id', '=',25)->get();
//        foreach($leadHistorysDatas as $key => $data){
//            echo $k . ' ' . $data->id . ' '.$data->title;
//            echo "<br>";
//            $k = $k + 1;
//        }
//        exit;


        $leadHistorysDatas = LeadHistory::where('title', '=', '')->where('status_id', '=', 95)->get();

        foreach ($leadHistorysDatas as $Datas) {
            $Datas->title = 'Admin Edited Status to Appt Request';
            $Datas->save();
        }

        $followStatusPurchased = FollowStatus::where('title', 'Purchased')->first();

        $followStatusContract = FollowStatus::where('title', 'Contract')->first();

        $followStatusApptRequest = FollowStatus::where('title', 'Appt Request')->first();

        $followStatusApptKept = FollowStatus::where('title', 'Appt Kept')->first();


        $changeTitlesToId = [
            [
                'title' => 'Purchased status updated from Follow Up Lead Management.',
                'id' => $followStatusPurchased->id,
            ],
            [
                'title' => 'Contract status updated from Follow Up Lead Management.',
                'id' => $followStatusContract->id,
            ],
            [
                'title' => 'Appt Kept status updated from Follow Up Lead Management.',
                'id' => $followStatusApptKept->id,
            ],
            [
                'title' => 'Appt Request status updated from Follow Up Lead Management.',
                'id' => $followStatusApptRequest->id,
            ],
        ];

        foreach ($changeTitlesToId as $key => $value) {
            $leadHistorys = LeadHistory::where('title', $value['title'])->where('followup_status_id', 0)->get();

            if (!empty($leadHistorys)) {
                foreach ($leadHistorys as $key => $leadHistory) {
                    echo $leadHistory->id;
                    echo '<br>';
                    echo $leadHistory->followup_status_id = $value['id'];
                    echo '<br>';
                    echo '<br>';
                    $leadHistory->save();
                }
            }
        }



        echo 'Done ' . NOW();
        exit;

        dd('done');

        // \Artisan::call('command:userKnockImportCommand');
        // \Artisan::call('view:clear');
        // \Artisan::call('config:clear');
        // \Artisan::call('cache:clear');
        // \Artisan::call('route:clear');
        // $leadHistory = LeadHistory::where('status_id','=',0)->where('followup_status_id','!=',0)->whereNotNull('followup_status_id')->pluck('id')->toArray();
        // $userLeadKnocks = UserLeadKnocks::whereIn('lead_history_id',$leadHistory)->get();


        if (!empty($request->is_historical) && $request->is_historical = 1) {
            $leadId = Lead::where('title', 'History Knock lead')->pluck('id')->toArray();
            $leads = Lead::where('title', 'History Knock lead')->get();

            $leadHistory = LeadHistory::whereIn('lead_id', $leadId)->get();

            if (!empty($leadHistory)) {
                foreach ($leadHistory as $key => $value) {
                    $value->delete();
                }
            }

            $userLeadKnocks = UserLeadKnocks::where('is_historical', 1)->get();

            if (!empty($userLeadKnocks)) {
                foreach ($userLeadKnocks as $key => $value) {
                    $value->delete();
                }
            }

            $users = User::whereIn('user_group_id', [5, 4])->get();

            if (!empty($users)) {
                foreach ($users as $key => $user) {
                    $user->delete();
                }
            }

            UserKnocksImport::truncate();

            if (!empty($leads)) {
                foreach ($leads as $key => $lead) {
                    $lead->delete();
                }
            }
        }




        dd('done');

        // if(!empty($request->audit_start_id) && !empty($request->audit_end_id)){
        //    $audits  = Audit::whereBetween('id', [$request->audit_start_id, $request->audit_end_id])->get();
        //    if(!empty($audits)){
        //         foreach ($audits as $key => $audit) {
        //             $audit->delete();
        //         }
        //    }
        // }


        DealLead::truncate();

        dd(env('TIMEZONE_SYSTEM', 'America/Chicago'));

        if (isset($request->remove_true) AND $request->remove_true = 'yesgo') {
            $userLeadKnocks = UserLeadKnocks::where('created_at', '<', Carbon::parse('12/16/2022'))->get();
            if (!empty($userLeadKnocks)) {
                foreach ($userLeadKnocks as $key => $userLeadKnock) {
                    $userLeadKnock->delete();
                }
            }
        }

        LeadType::where('id', '=', 1)->delete();

        if (isset($request->test) AND $request->test == 1) {
            LeadType::where('id', '!=', 1)->delete();

            $leads = Lead::get();
            foreach ($leads as $lead) {
                $data = new LeadType();
                $data->lead_id = $lead->id;
                $data->assign_id = $lead->assignee_id;
                $data->type_id = $lead->type_id;
                $data->title = 'Lead Type created';
                $data->created_at = $lead->created_at;
                $data->save();
            }
        }



        echo "<pre>";
        echo $type = 'year';
        echo "<br>";
        echo $start_date = "2022-01-01";
        echo "<br>";
        echo $end_date = "2023-02-08";
        echo "<br>";
        if ($type == 'day') {
            echo "<br>";
            echo "day type formate applied";
            echo "<br>";

            $period = CarbonPeriod::create($start_date, $end_date);
            $months = [];
            foreach ($period as $date) {
                $months[] = $date->format('Y-m-d');
            }
            print_r($months);
        }

        if ($type == 'week') {
            $weeks = $this->findWeeksBetweenTwoDates($start_date, $end_date);
            $week_titles = [];
            foreach ($weeks as $week) {
                $week_titles[] = $week[0] . ' / ' . $week[1];
            }
            print_r($week_titles);
        }

        if ($type == 'months') {
            $period = new CarbonPeriod($start_date, '1 month', $end_date);

//            $period = CarbonPeriod::create($start_date, $end_date);            
            $months = [];
            $months_data = [];
            $exit_year = [];
            foreach ($period as $key => $date) {
                echo $key;
                echo "<br>";
                if (!in_array($date->format("Y-m"), $exit_year)) {
                    $exit_year[] = $date->format("Y-m");
                    $months[] = $date->format("Y-m");
                }
            }
            print_r($months);
        }

        if ($type == 'year') {
            $period = new CarbonPeriod($start_date, '1 year', $end_date);
//            $period = \Carbon\CarbonPeriod::create($start_date, '1 year', $end_date);
            $months = [];
            foreach ($period as $key => $dt) {
                echo $key;
                echo "<br>";
                $months[] = $dt->format("Y");
            }
            print_r($months);
        }




        exit;


        $month_clause = '';
        $status_clause = '';
        $target_user_clause = '';

        if (!empty($params['target_user_id'])) {
            $month_clause .= " AND lead_type_history.assign_id IN ({$params['target_user_id']}) ";
            $target_user_clause = " AND id IN ({$params['target_user_id']}) ";
        }
        if (!empty($params['type_id'])) {
            $month_clause .= " AND lead_type_history.type_id IN ({$params['type_id']}) ";
        }

        $dateinput = json_decode($params['time_slot']);
        if (!empty($dateinput->start) && !empty($dateinput->end)) {
            $dateinput->start = date('Y-m-d', strtotime($dateinput->start)) . ' 00:00:00';
            $dateinput->end = date('Y-m-d', strtotime($dateinput->end)) . ' 23:59:59';
            $time_clause = "AND DATE(lead_type_history.created_at) between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        }

        echo $params['start_date']['start'] = '2022-02-01';
        echo $params['start_date']['end'] = '2023-02-08';
        $params['start_date'] = json_encode($params['start_date']);
        $dateinput = json_decode($params['start_date']);

        if (!empty($dateinput->start) && !empty($dateinput->end)) {
            $dateinput->start = date('Y-m-d', strtotime($dateinput->start)) . ' 00:00:00';
            $dateinput->end = date('Y-m-d', strtotime($dateinput->end)) . ' 23:59:59';
            $time_clause = "AND DATE(lead_type_history.created_at) between '" . $dateinput->start . "' and '" . $dateinput->end . "' ";
        }

        $result = \DB::select("SELECT count(*) as user_lead_total, lead_type_history.assign_id as assignee_id,  lead_type_history.created_at, lead_type_history.type_id, concat(user.first_name, ' ', user.last_name) as name, 
                              type.title as status_title FROM  lead_type_history
                              INNER JOIN user ON user.id =  lead_type_history.assign_id
                              INNER JOIN type ON type.id =  lead_type_history.type_id                              
                              WHERE user.company_id = 4 AND user.user_group_id != 3 $month_clause $time_clause
                              group by type_id,assign_id");

        $status_result = \DB::select("SELECT id, title FROM type WHERE tenant_id = 4 $status_clause AND deleted_at IS NULL");
        $user_result = \DB::select("SELECT id as assignee_id, concat(user.first_name, ' ', user.last_name) as name FROM user WHERE company_id = 4 $target_user_clause  AND  user_group_id != 3 AND user_group_id = 2 AND deleted_at IS NULL");
        info($status_result);
        $response = [];
        $temp_response = [];
        $status_map = [];
        $map_user_collection = [];
        foreach ($status_result as $row) {
            if ($row->id != 134 AND $row->id != 135 AND $row->id != 133) {
                $status_map[$row->id]['name'] = $row->title;
                $status_map[$row->id]['data'][$row->id] = 0;
                $status_map[$row->id]['total'][$row->id] = 0;
            }
        }
        foreach ($user_result as $row) {
            $temp_response['user_names'][$row->assignee_id] = $row->name;
            $temp_response['status'][$row->assignee_id] = $status_map;
        }
        foreach ($result as $row) {
            $temp_response['status'][$row->assignee_id][$row->type_id]['data'][$row->type_id] = $row->user_lead_total;
        }
        $status_response = [];
        foreach ($temp_response['status'] as $ass_user_id => $user_row) {
            foreach ($user_row as $status_id => $status_row) {
                if ($status_row['name'] != null) {
                    $status_response[$status_id]['name'] = $status_row['name'];
                    $status_response[$status_id]['data'][] = $status_row['data'][$status_id];
                    $status_response[$status_id]['total'] = $status_row['data'][$status_id] + $status_response[$status_id]['total'];
                }
            }
        }
        foreach ($temp_response['user_names'] as $row)
            $response['user_names'][] = $row;



        if (isset($params['type']) AND $params['type'] == 'percentage') {
            $total = 0;
            foreach ($response['status'] as $key => $row) {
                if ($row['total'] != 0) {
                    $total = $total + $row['total'];
                }
            }
            foreach ($response['status'] as $key => $row) {
                foreach ($row['data'] as $j => $row1) {
                    if ($row1 != 0 AND $row['total'] != 0) {
                        $response['status'][$key]['data'][$j] = floatval(number_format(($row1 / $row['total']) * 100, 2));
                    } else {
                        $response['status'][$key]['data'][$j] = 0;
                    }
                }
            }
        }

        $lead_types = \DB::select("SELECT id, title FROM type WHERE tenant_id = 4 $status_clause AND deleted_at IS NULL");
        $types = [];
        $colour = [];
        $colour[] = '#fc2c03';
        $colour[] = '#0000FF';
        $colour[] = '#fcf803';
        $colour[] = '#39fc03';
        $colour[] = '#03fcc2';
        $colour[] = '#2803fc';
        $colour[] = '#a903fc';
        $colour[] = '#fc7703';
        $colour[] = '#fc037f';
        $colour[] = '#fc0352';



//        foreach ($lead_types as $key => $lead_type) {
//            $types[$key]['name'] = $lead_type->title;
//            $types[$key]['type'] = 'stackedColumn';
//            $types[$key]['showInLegend'] = 1;
//            $types[$key]['markerType'] = "square";
//            $types[$key]['color'] = $colour[$key];
//            $types[$key]['xValueType'] = "dateTime";
//            $types[$key]['yValueFormatString'] = "#";
//            $types[$key]['indexLabel'] = "{y}";
//            $types[$key]['indexLabelFontColor'] = "#fff";
//
//            $result = \DB::select("SELECT lead_type_history.type_id as his_type_id,lead_type_history.created_at,lead_type_history.type_id FROM  lead_type_history
//                              WHERE type_id = $lead_type->id $month_clause $time_clause  ORDER BY ID ASC");
//            $total_count = count($result);
//            $datetime_data = [];
//            $total_data = [];
//            foreach ($result as $j => $val) {
//                $created_at = date('Y-m-d', strtotime($val->created_at));
//                $datetime = strtotime($created_at) * 1000;
//                if (isset($datetime_data[$datetime])) {
//                    $datetime_data[$datetime] = $datetime_data[$datetime] + 1;
//                } else {
//                    $datetime_data[$datetime] = 1;
//                }
//            }
//            $total = 0;
//            foreach ($datetime_data as $i => $final_data) {
//                if (isset($params['type']) AND $params['type'] == 'percentage') {
//                    $types[$key]['dataPoints'][$total]['x'] = $i;
//                    $types[$key]['dataPoints'][$total]['y'] = floatval(number_format(($final_data / $total_count) * 100, 2));
//                } else {
//                    $types[$key]['dataPoints'][$total]['x'] = $i;
//                    $types[$key]['dataPoints'][$total]['y'] = $final_data;
//                }
//
//                $total = $total + 1;
//            }
//        }
//
//        
//        $response['types'] = $types;

        $start_date = date('Y-m-d', strtotime($dateinput->start));
        $end_date = date('Y-m-d', strtotime($dateinput->end));
        echo $datetype = 'year';
        $total_by_month = [];
        if ($datetype == 'day') {
            $period = CarbonPeriod::create($start_date, $end_date);
            $months = [];
            $months_data = [];
            foreach ($period as $date) {
                $total_by_month[] = 0;
                $months[] = $date->format('Y-m-d');
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'week') {

            $weeks = Lead::findWeeksBetweenTwoDates($start_date, $end_date);
            $months = [];
            $months_data = [];
            foreach ($weeks as $week) {

                $total_by_month[] = 0;
                $months[] = $week[0] . ' / ' . $week[1];
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'month') {

            $period = \Carbon\CarbonPeriod::create($start_date, '1 month', $end_date);
            $months = [];
            $months_data = [];
            foreach ($period as $dt) {
                $total_by_month[] = 0;
                $months[] = $dt->format("Y-m");
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } elseif ($datetype == 'year') {

            $period = CarbonPeriod::create($start_date, $end_date);
            $months = [];
            $months_data = [];
            $exit_year = [];
            foreach ($period as $date) {
                if (!in_array($date->format('Y'), $exit_year)) {
                    $exit_year[] = $date->format('Y');
                    $total_by_month[] = 0;
                    $months[] = $date->format('Y');
                }
            }
            $total_count_array = count($months);
            $total_by_month[$total_count_array] = 0;
            $total_count_per = $total_count_array + 1;
            $total_by_month[$total_count_per] = 0;
            $months[] = 'Total';
            $months[] = 'By%';
        } else {
            $months = [];
            $months[] = 'January';
            $months[] = 'February';
            $months[] = 'March';
            $months[] = 'April';
            $months[] = 'May';
            $months[] = 'June';
            $months[] = 'July';
            $months[] = 'August';
            $months[] = 'September';
            $months[] = 'October';
            $months[] = 'November';
            $months[] = 'December';
            $months[] = 'Total For year';
            $months[] = 'By%';
        }

        $response['months'] = $months;

        $month_status = [];
        $total_result = \DB::select("SELECT lead_type_history.type_id as his_type_id,lead_type_history.created_at,lead_type_history.type_id FROM  lead_type_history
                              WHERE type_id != 0 $month_clause $time_clause  ORDER BY ID ASC");


        $total_by_month_new = $total_by_month;
        foreach ($status_result as $key => $type) {
            if ($key == 0) {
                $total_by_month_new = $total_by_month;
            }

            $month_status[$key]['name'] = $type->title;
            $month_status[$key]['id'] = $type->id;
            $month_status[$key]['data'] = $total_by_month_new;

            $result = \DB::select("SELECT lead_type_history.type_id as his_type_id,lead_type_history.created_at,lead_type_history.type_id FROM  lead_type_history
                              WHERE type_id = $type->id $month_clause $time_clause  ORDER BY ID ASC");
            $type_count = count($result);
            $type_total_count = count($total_result);
            if ($type_count != 0 AND $type_total_count != 0) {
                $month_status[$key]['data'][$total_count_array] = $type_count;
                $month_status[$key]['data'][$total_count_per] = floatval(number_format(($type_count / $type_total_count) * 100, 2)) . '%';
            } else {
                $month_status[$key]['data'][$total_count_array] = $type_count;
                $month_status[$key]['data'][$total_count_per] = '0%';
            }

            foreach ($result as $val) {

                if ($datetype == 'day') {
                    $created_at = date('Y-m-d', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'week') {
                    $created_at = date('Y-m-d', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'month') {
                    $created_at = date('Y-m', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } elseif ($datetype == 'year') {
                    $created_at = date('Y', strtotime($val->created_at));
                    $n_key = array_search($created_at, $months);
                    if (isset($month_status[$key]['data'][$n_key])) {
                        $month_status[$key]['data'][$n_key] = $month_status[$key]['data'][$n_key] + 1;
                    } else {
                        $month_status[$key]['data'][$n_key] = 1;
                    }

                    if (isset($total_by_month[$n_key])) {
                        $total_by_month[$n_key] = $total_by_month[$n_key] + 1;
                    } else {
                        $total_by_month[$n_key] = 1;
                    }
                } else {
                    $created_at = date('m', strtotime($val->created_at));
                    $created_at = str_replace("0", "", $created_at);
                    $created_at = $created_at - 1;
                    if (isset($month_status[$key]['data'][$created_at])) {
                        $month_status[$key]['data'][$created_at] = $month_status[$key]['data'][$created_at] + 1;
                    } else {
                        $month_status[$key]['data'][$created_at] = 1;
                    }

                    if (isset($total_by_month[$created_at])) {
                        $total_by_month[$created_at] = $total_by_month[$created_at] + 1;
                    } else {
                        $total_by_month[$created_at] = 1;
                    }
                }
            }
        }


        foreach ($month_status as $key => $month_statu) {
            $types[$key]['name'] = $month_statu['name'];
            $types[$key]['type'] = 'stackedColumn';
            $types[$key]['showInLegend'] = 1;
            $types[$key]['markerType'] = "square";
            $types[$key]['color'] = $colour[$key];
//            $types[$key]['xValueType'] = "dateTime";
            $types[$key]['yValueFormatString'] = "#";
            $types[$key]['indexLabel'] = "{y}";
            $types[$key]['indexLabelFontColor'] = "#fff";
            $total = 0;
            foreach ($months as $i => $month) {
                if ($month != 'Total' AND $month != 'By%') {
                    if (isset($params['type']) AND $params['type'] == 'percentage') {
                        $types[$key]['dataPoints'][$total]['x'] = $month;
                        $types[$key]['dataPoints'][$total]['y'] = floatval(number_format(($final_data / $total_count) * 100, 2));
                    } else {
                        $types[$key]['dataPoints'][$total]['x'] = $month;
                        $types[$key]['dataPoints'][$total]['y'] = $month_statu['data'][$i];
                    }

                    $total = $total + 1;
                }
            }
        }


        $response['types'] = $types;
        echo "<pre>";
        print_r($types);
        exit;



        $key = $key + 1;
        $month_status[$key]['name'] = 'Total Leads';
        $month_status[$key]['id'] = '';
        $month_status[$key]['data'] = $total_by_month;


        $month_status[$key]['data'][$total_count_array] = $type_total_count;
        $month_status[$key]['data'][$total_count_per] = '100%';

        echo "<pre>";
        print_r($months);
        print_r($total_by_month);
        print_r($month_status);

        exit;
        $s_no = 1;
        foreach ($month_status as $row) {
            $response['status'][] = $row;
            $response['export'][] = array_merge([$s_no++, $row['name']], $row['data']);
        }

        echo "<pre>";
        print_r($month_status);
        exit;
        $response['month_status'] = $month_status;
























        exit;





//        \Artisan::call('view:clear');
//        \Artisan::call('config:clear');
//        \Artisan::call('cache:clear');
//        \Artisan::call('route:clear');
//
//        echo "cache clear";
//        exit;
//        LeadType::where('id', '!=', 1)->delete();


        if (isset($request->test) AND $request->test == 1) {
            $leads = Lead::get();

            foreach ($leads as $lead) {

                $data = new LeadType();
                $data->lead_id = $lead->id;
                $data->assign_id = $lead->assignee_id;
                $data->type_id = $lead->type_id;
                $data->title = 'Lead Type created';
                $data->created_at = $lead->created_at;
                $data->save();
            }
        }


        \Artisan::call('view:clear');
        \Artisan::call('config:clear');
        \Artisan::call('cache:clear');
        \Artisan::call('route:clear');

        echo "cache clear";
        exit;

        $sid = env('TWILIO_ACCOUNT_SID', '');
        $twiliophoneNo = env("TWILIO_PHONE_NO", '');
        $token = env("TWILIO_AUTH_TOKEN", '');
        $message = "Hey twillio send sms succsfully";
        $numbers = [];
        $numbers[0]['mobile_number'] = '6156823765';
        foreach ($numbers as $number) {

            if ($number['mobile_number'] != '') {
                $contact_number = "+1" . $number['mobile_number'];

                $twilio = new TwilioClient($sid, $token);
//                try {
                $twilio_message = $twilio->messages
                        ->create($contact_number,
                        ["from" => $twiliophoneNo, "body" => $message]
                );

//                } catch (\Twilio\Exceptions\TwilioException $exception) {
//                    
//                }
            }
        }

        exit;

        echo $request['start_date'] = '12/28/2022 05:26 pm';
        $request['end_date'] = $request['start_date'];
        echo "<br>";
        $start_date = date('Y-m-d', strtotime($request['start_date']));
        $start_time = date('H:i:s', strtotime('-1 hour', strtotime($request['start_date'])));
        echo $start_time;
        $end_date = date('Y-m-d', strtotime($request['end_date']));
        $end_time = date('H:i:s', strtotime('-1 hours', $request['end_date']));

        exit;



        $client = new Client();

        if ($credentials_file = $this->checkServiceAccountCredentialsFile()) {
            $client->setAuthConfig($credentials_file);
        } elseif (getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            $client->useApplicationDefaultCredentials();
        } else {
            echo missingServiceAccountDetailsWarning();
            return;
        }

        $client->setApplicationName("Client_Library_Examples");
        $client->setScopes(['https://www.googleapis.com/auth/calendar']);

        $user_to_impersonate = 'mobiledev@semaphoremobile.com';
        $client->setSubject($user_to_impersonate);

        $service = new Calendar($client);
        $event_id = '7lchs00nvc6me1sdmkb70cf1oc';
        $calendar_id = 'c_75a155c18eb405d91ed4d3c8ee7d0e2cbd205389d33503ac4de1744b2c6666d9@group.calendar.google.com';
        $event = $service->events->get($calendar_id, $event_id);

        $service->events->delete($calendar_id, $event->getId());



        exit;

        $client = new Client();

        if ($credentials_file = $this->checkServiceAccountCredentialsFile()) {
            $client->setAuthConfig($credentials_file);
        } elseif (getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            $client->useApplicationDefaultCredentials();
        } else {
            echo missingServiceAccountDetailsWarning();
            return;
        }

        $client->setApplicationName("Client_Library_Examples");
        $client->setScopes(['https://www.googleapis.com/auth/calendar']);

        $user_to_impersonate = 'mobiledev@semaphoremobile.com';
        $client->setSubject($user_to_impersonate);

        $service = new Calendar($client);
        $event_id = '7toppalc468pi0u5a3pe77s280';
        $calendar_id = 'c_75a155c18eb405d91ed4d3c8ee7d0e2cbd205389d33503ac4de1744b2c6666d9@group.calendar.google.com';
        $event = $service->events->get($calendar_id, $event_id);
        $event->setSummary('Event Updated');
        $event->setDescription('Event Updated description');


        $start = new EventDateTime();
        $start->setDateTime('2022-12-28T20:00:00+08:00');
        $event->setStart($start);
        $end = new EventDateTime();
        $end->setDateTime('2022-12-28T21:00:00+08:00');
        $event->setEnd($end);


        $service->events->update($calendar_id, $event->getId(), $event);


        exit;


        $client = new Client();

        if ($credentials_file = $this->checkServiceAccountCredentialsFile()) {
            $client->setAuthConfig($credentials_file);
        } elseif (getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            $client->useApplicationDefaultCredentials();
        } else {
            echo missingServiceAccountDetailsWarning();
            return;
        }

        $client->setApplicationName("Client_Library_Examples");
        $client->setScopes(['https://www.googleapis.com/auth/calendar']);

        $user_to_impersonate = 'mobiledev@semaphoremobile.com';
        $client->setSubject($user_to_impersonate);

        $service = new Calendar($client);
        $calendarList = $service->calendarList->listCalendarList();

        $event = new Event(array(
            'summary' => 'Google I/O 2015',
            'title' => 'Google I/O 2015',
            'location' => '800 Howard St., San Francisco, CA 94103',
            'description' => 'A chance to hear more about Google\'s developer products.',
            'start' => array(
                'dateTime' => '2022-12-25T09:00:00-07:00',
                'timeZone' => 'UTC',
            ),
            'end' => array(
                'dateTime' => '2022-12-25T17:00:00-07:00',
                'timeZone' => 'UTC',
            ),
//            'recurrence' => array(
//                'RRULE:FREQ=DAILY;COUNT=2'
//            ),
//            'attendees' => array(
//                array('email' => 'mobiledev@semaphoremobile.com'),                
//            ),
//            'reminders' => array(
//                'useDefault' => FALSE,
//                'overrides' => array(
//                    array('method' => 'email', 'minutes' => 24 * 60),
//                    array('method' => 'popup', 'minutes' => 10),
//                ),
//            ),
        ));
        $calendarId = 'c_75a155c18eb405d91ed4d3c8ee7d0e2cbd205389d33503ac4de1744b2c6666d9@group.calendar.google.com';
        $event = $service->events->insert($calendarId, $event);

        echo "Event created : <br><br> ";

        printf($event->htmlLink);
        exit;

//        $client->setApplicationName("Client_Library_Examples");
//        $client->setScopes(['https://www.googleapis.com/auth/books']);
//        $service = new Books($client);
//        $query = 'Henry David Thoreau';
//        $optParams = [
//            'filter' => 'free-ebooks',
//        ];
//        $results = $service->volumes->listVolumes($query, $optParams);
//        echo "<h3>Books API integrate in iKNOCK and Results Of Call:</h3>";
//        foreach ($results as $item) {
//            echo $item['volumeInfo']['title'] . "<br>";
//        }


        $client->setApplicationName("test_calendar");

        $client->setScopes(Calendar::CALENDAR);
        $client->setAccessType('offline');


//      $client->setSubject('staging-iknock@test-iknock.iam.gserviceaccount.com');
        $service = new Calendar($client);

        $calendarList = $service->calendarList->listCalendarList();
//      $calendarList = $service->calendarList;
//      echo "<pre>";
//      print_r($calendarList);
//      exit;

        $event = new Event(array(
            'summary' => 'Google I/O 2015',
            'title' => 'Google I/O 2015',
            'location' => '800 Howard St., San Francisco, CA 94103',
            'description' => 'A chance to hear more about Google\'s developer products.',
            'start' => array(
                'dateTime' => '2022-12-25T09:00:00-07:00',
                'timeZone' => 'UTC',
            ),
            'end' => array(
                'dateTime' => '2022-12-25T17:00:00-07:00',
                'timeZone' => 'UTC',
            ),
//            'recurrence' => array(
//                'RRULE:FREQ=DAILY;COUNT=2'
//            ),
//            'attendees' => array(
//                array('email' => 'mobiledev@semaphoremobile.com'),                
//            ),
//            'reminders' => array(
//                'useDefault' => FALSE,
//                'overrides' => array(
//                    array('method' => 'email', 'minutes' => 24 * 60),
//                    array('method' => 'popup', 'minutes' => 10),
//                ),
//            ),
        ));

        //smit
        //$calendarId = '8073f9c7f5c8c938ac811777044d5037bee53f5ba6ec750b43576675d8ba3dd9@group.calendar.google.com';
        //mobile        
        $calendarId = 'c_75a155c18eb405d91ed4d3c8ee7d0e2cbd205389d33503ac4de1744b2c6666d9@group.calendar.google.com';

//        $calendarId = 'primary';
        $event = $service->events->insert($calendarId, $event);

        echo "Event created : <br><br> ";

        printf($event->htmlLink);

        exit;






















































        echo "check";
        exit;
        $e = Event::get();
        dd($e);
        $event = new Event;

        $event->name = 'A new event';
        $event->startDateTime = Carbon::now();
        $event->endDateTime = Carbon::now()->addHour();

        $event->save();

        exit;
        \Artisan::call('view:clear');
        \Artisan::call('config:clear');
        \Artisan::call('cache:clear');
        \Artisan::call('route:clear');

        echo "cache clear";
        exit;


        //create a new event
        $event = new Event;

        $event->name = 'A new event';
        $event->description = 'Event description';
        $event->startDateTime = Carbon::now();
        $event->endDateTime = Carbon::now()->addHour();
        $event->addAttendee([
            'email' => 'dharmiktank128@gmail.com',
            'name' => 'John Doe',
            'comment' => 'Lorum ipsum',
        ]);

        $event->save();

        dd($event);

        $leads = Lead::get();

        if (!empty($leads)) {
            foreach ($leads as $lead) {
                $original_loan = $lead->original_loan_2;

                if (!is_null($original_loan)) {
                    $lead->original_loan = '$' . number_format($original_loan);
                    $lead->save();
                }
            }
        }



        dd('done');
        $lead = Lead::find(9448);

        dd($lead);

        // ini_set('max_execution_time', '3000'); //300 seconds = 5 minutes
        // ini_set('memory_limit', '-1');  
        // $leads = Lead::latest()->get();
        // if(!empty($leads)){
        //   foreach ($leads as $key => $value) {
        //       $response['custom'] = [];
        //       // $value->auction = null;
        //       // $value->lead_value = null;
        //       // $value->original_loan = null;
        //       // $value->loan_date = null;
        //       // $value->sq_ft = null;
        //       // $value->yr_blt = null;
        //       // $value->eq = null;
        //       // $value->save();
        //       foreach ($value->leadCustomNew as $field) {
        //           $field['value'] = str_replace(["'"],['&#039;'], $field['value']);
        //           $field['key'] = str_replace(["'"],['&#039;'], $field['key']);
        //           $field['key'] = str_replace(Config::get('constants.SPECIAL_CHARACTERS.IGNORE'), Config::get('constants.SPECIAL_CHARACTERS.REPLACE'), $field['key']);
        //           switch ($field['key']) {
        //             case 'Auction':
        //               $value->auction = $field['value'];
        //               $value->save();
        //             break;
        //             case 'Lead Value':
        //                 $value->lead_value =  str_replace(',','',str_replace('$',' ',$field['value']));
        //                 // $value->lead_value =  $field['value'];
        //                 $value->save();
        //             break;
        //             case 'Original Loan':
        //               // $field['value'] =  str_replace('$',' ',$field['value']);
        //               $value->original_loan = str_replace(',','',str_replace('$',' ',$field['value']));
        //               // $value->original_loan = $field['value'];
        //               $value->save();
        //             break;
        //             case 'Loan Date':
        //                 $value->loan_date = $field['value'];
        //                 $value->save();
        //             break;
        //             case 'Sq Ft':
        //                 $value->sq_ft = $field['value'];
        //                 $value->save();
        //             break;
        //             case 'Yr Blt':
        //                 $value->yr_blt = $field['value'];
        //                 $value->save();
        //             break;
        //             case 'EQ':
        //               $value->eq = $field['value'];
        //               $value->save();
        //             break;
        //             default:
        //               // code...
        //               break;
        //           }
        //           $response[$field['key']] = $field['value'];
        //           $response['custom'][] = json_decode(json_encode($field));
        //       }
        //       if(!empty($response)){
        //       }
        //   }
        // }
        // dd($leads);
        // $params['company_id'] = 4;
        // $leadview = Lead::select(
        //                 "lead_detail.*", 
        //                 "lead_custom_field.id as lead_custom_field_id",
        //                 "lead_custom_field.key",
        //                 "lead_custom_field.value"
        //               )
        //                 ->leftjoin("lead_custom_field", "lead_custom_field.lead_id", "=", "lead_detail.id")
        //                 ->where("lead_detail.company_id", 4)
        //                 // ->whereIn("lead_detail.id", [7664, 6727])
        //                 ->get();
        // $data = [];
        // foreach ($leadview as $key => $value) {
        //     if (!isset($data[$value->id])) {
        //       $data[$value->id] = $value->toArray();
        //     }
        //     // print_r($value->key);
        //     $data[$value->id][$value->key] = $value->value;
        // }
        // $dataP = $this->paginate($data);
        // foreach ($dataP as $key => $value) {
        //     // dd($value);
        // }
        // $params['company_id'] = 4;
        // $leds = Lead::getlistPro($params);
        // dd($leds);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function paginate($items, $perPage = 5, $page = null, $options = []) {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function debug() {
        $leadHistory = LeadHistory::whereNull('status_id')->get();

        foreach ($leadHistory as $key => $value) {
            $value->status_id = 1;
            $value->save();
        }

        dd('done');
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function migrateRun() {
        dd(\Artisan::call('migrate') . ' migration runnnn...');
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function cacheClear() {
        \Artisan::call('view:clear');
        \Artisan::call('config:clear');
        \Artisan::call('cache:clear');
        \Artisan::call('route:clear');

        dd('view, config, cache, route clear...........');
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function followupAddressUpdate() {
        setupFollowupAddressUpdate();

        dd('done');
    }

}
