<?php

namespace App\Http\Controllers;

use App\Http\Middleware\LoginAuth;
use Illuminate\Http\Request;
use App\Models\FollowStatus;
use App\Models\FollowingLead;
use App\Models\Status;
use App\Models\Marketing;
use App\Models\LeadQuery;
use App\Models\LeadCustomField;
use App\Exports\FollowingLeadExport;
use App\Exports\FollowingLeadHistoryExport;
use App\Models\User;
use App\Models\FollowUpLeadViewSetp;
use App\Models\DealLead;
use App\Models\FollowingCustomFields;
use DateTime;
use App\Http\Resources\FollowingLeadResource;
use Carbon\Carbon;
use App\Models\Type;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Lead;
use App\Models\LeadHistory;
use App\Models\UserLeadAppointment;
use App\Models\PurchaseLead;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use App\Models\Alerts;
use App\Http\Resources\MarketingLeadResource;
use App\Exports\MarketingLeadExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FollowingLeadsController extends Controller {

    function __construct() {

        parent::__construct();
        $this->middleware(LoginAuth::class, ['only' => ['index', 'indexList', 'edit', 'update', 'editableField'
        ]]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {

        if (!is_array($request->user_id_search)) {
            $request->user_id_search = explode(',', $request->user_id_search);
        }

        if (!is_array($request->lead_status_id)) {
            $request->lead_status_id = explode(',', $request->lead_status_id);
        }

        $followUpLeadViewSetps = FollowUpLeadViewSetp::where('is_show', 1)
                ->orderBy('order_no', 'asc')
                ->get();
        $followUpLeadViewSetpsDropDown = FollowUpLeadViewSetp::orderBy('order_no', 'asc')
                ->get();
        $followUpLeadViewSetpsSlug = FollowUpLeadViewSetp::orderBy('order_no', 'asc')
                ->pluck('title_slug');
        $followUpLeadViewSetpsSlug = json_encode($followUpLeadViewSetpsSlug, JSON_INVALID_UTF8_IGNORE);

        $input = $request->all();

        $followingLeads = getFollowingLead($input);



        $statusList = FollowStatus::where('is_followup', '=', 1)->latest()->get();

        $users = User::orderby('status_id', 'desc')
                ->where('user_group_id', '!=', 4)
                ->where('status_id', 1)
                ->get();

        $followingLeadsData = dataViewFollowLeadNew($input, $followingLeads, $followUpLeadViewSetps);

        return view('tenant.followinglead.index', compact('followingLeads',
                        'mobileusers',
                        'users',
                        'statues',
                        'followUpLeadViewSetps',
                        'followUpLeadViewSetpsSlug',
                        'followingLeadsData',
                        'statusList',
                        'followUpLeadViewSetpsDropDown'
                )
        );

//        $followingLeadsData = dataViewFollowLeadNew($input,$followingLeads,$followUpLeadViewSetps);
//        
//        return view('tenant.followinglead.index', compact('followingLeads', 'mobileusers', 'users', 'statues', 'followUpLeadViewSetps', 'followUpLeadViewSetpsSlug', 'followingLeadsData', 'statusList', 'followUpLeadViewSetpsDropDown'));
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function indexList(Request $request) {
        $input = $request->all();
        $followingLeads = getFollowingLead($input);

        $followUpLeadViewSetps = FollowUpLeadViewSetp::where('is_show', 1)->
                orderBy('order_no', 'asc')
                ->get();

        $followingLeadsData = dataViewFollowLeadNew($input, $followingLeads, $followUpLeadViewSetps);


        $data = view('tenant.followinglead.followingLeadsMain', compact('followingLeadsData', 'followUpLeadViewSetps', 'followingLeads', 'input'))->render();

        $data = preg_replace('/\s+/', ' ', $data);

        $data = preg_replace('/<!--(.|\s)*?-->/', '', $data);

        return response()->json(['success' => utf8_encode($data)]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(FollowingLead $followup_lead) {
        $users = User::orderby('status_id', 'desc')->where('status_id', 1)->get();

        $mobileusers = User::latest()->where('status_id', 1)->where('user_group_id', 2)->get();

        $auctionDate = '';


        $date = explode('/', $followup_lead->auction);


        if (!empty($date) && strlen($date[1]) == 1) {
            $month = $date[1];
            $date[1] = '0' . $month;
        }

        if (!empty($followup_lead->date_status_updated)) {
            $followup_lead->date_status_updated = Carbon::parse($followup_lead->date_status_updated)->format('m/d/Y');
        }

        if (!empty($date[1]) && checkdate($date[0], $date[1], $date[2])) {
            $auctionDate = Carbon::parse($followup_lead->auction)->format('m/d/Y');
        }

        if (!empty($followup_lead->contract_date)) {
            $followup_lead->contract_date = Carbon::parse($followup_lead->contract_date)->format('m/d/Y');
        }

        $followup_lead->auction = $auctionDate;

        $statusList = FollowStatus::where('is_followup', '=', 1)->latest()->get();

        $statues = Status::latest()->get();

        $data['status'] = Status::getList($param);
        $data['agent'] = User::getTenantUserList($param);

        $data['type'] = Type::whereIn('tenant_id', [$request['company_id']])->whereNull('deleted_at')->get();
        $data['lead'] = Lead::getById($id);

        updateCustomFiled($followup_lead->id);


        $userLeadAppointment = UserLeadAppointment::where('lead_id', '=', $followup_lead->lead_id)
                ->orderBy('id', 'desc')
                ->first();

        return view('tenant.followinglead.edit', compact('data', 'followup_lead', 'users', 'statusList', 'mobileusers', 'statues', 'followingCustomFields', 'userLeadAppointment'));
    }

    public function marketingEdit(FollowingLead $followup_lead) {
        $marketing_data = Marketing::select('marketings.*', 'user.first_name as in_first_name', 'user.last_name as in_last_name', 'user2.first_name as lead_first_name', 'user2.last_name as lead_last_name')
                ->leftjoin("user", "marketings.investore_id", "=", "user.id")
                ->leftjoin("user as user2", "marketings.investore_id", "=", "user2.id")
                ->where('lead_id', '=', $followup_lead->lead_id)
                ->first();


        $users = User::orderby('status_id', 'desc')->where('status_id', 1)->get();

        $mobileusers = User::latest()->where('user_group_id', 2)->get();

        $auctionDate = '';


        $date = explode('/', $followup_lead->auction);


        if (!empty($date) && strlen($date[1]) == 1) {
            $month = $date[1];
            $date[1] = '0' . $month;
        }

        if (!empty($followup_lead->date_status_updated)) {
            $followup_lead->date_status_updated = Carbon::parse($followup_lead->date_status_updated)->format('m/d/Y');
        }

        if (!empty($date[1]) && checkdate($date[0], $date[1], $date[2])) {
            $auctionDate = Carbon::parse($followup_lead->auction)->format('m/d/Y');
        }

        if (!empty($followup_lead->contract_date)) {
            $followup_lead->contract_date = Carbon::parse($followup_lead->contract_date)->format('m/d/Y');
        }

        $followup_lead->auction = $auctionDate;

        $statusList = FollowStatus::latest()->get();

        $statues = Status::latest()->get();

        $data['status'] = Status::getList($param);
        $data['agent'] = User::getTenantUserList($param);

        $data['type'] = Type::whereIn('tenant_id', [$request['company_id']])->whereNull('deleted_at')->get();
        $data['lead'] = Lead::getById($id);

        updateCustomFiled($followup_lead->id);

        return view('tenant.followinglead.editMarkting', compact('marketing_data', 'data', 'followup_lead', 'users', 'statusList', 'mobileusers', 'statues', 'followingCustomFields'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function ApptUpdate(Request $request) {
        if (!isset($request->appointment_id)) {

            $start_date = date('Y-m-d', strtotime($request['appointment_appointment_date']));
            $start_time = date('H:i:s', strtotime('-1 hours', strtotime($request['appointment_appointment_date'])));
            $end_date = date('Y-m-d', strtotime($request['appointment_appointment_date']));
            $end_time = date('H:i:s', strtotime('-1 hours', strtotime($request['appointment_appointment_date'])));
            $check_daylight = date('d-M-Y', strtotime($request['appointment_appointment_date']));
            $date = new DateTime($check_daylight . ' America/Los_Angeles');
            $check_daylight_result = $date->format('I');
            if ($check_daylight_result == 1) {
                $final_start_date = $start_date . 'T' . $start_time . '-06:00';
                $final_end_date = $end_date . 'T' . $end_time . '-06:00';
            } else {
                $final_start_date = $start_date . 'T' . $start_time . '-07:00';
                $final_end_date = $end_date . 'T' . $end_time . '-07:00';
            }

            $request['start_date'] = date('Y-m-d H:i:s', strtotime($request['appointment_appointment_date']));
            $request['end_date'] = date('Y-m-d H:i:s', date('Y-m-d H:i:s', strtotime('+1 hours', strtotime($request['appointment_appointment_date']))));
            $request['lead_id'][] = $request->appointment_lead_id;
            $request['slot_type'] = 'leads';

            $date = DateTime::createFromFormat('m-d-Y h:i a', $request['appointment_appointment_date']);
            $appointment_appointment_date = $date->format('Y-m-d H:i:s');

            $date->modify('+1 hour');
            $appointment_appointment_end_date = $date->format('Y-m-d H:i:s');

            $inputData = [];
            $inputData['lead_id'] = $request->appointment_lead_id;
            $inputData['user_id'] = $request->user_id;
            $inputData['appointment_date'] = $appointment_appointment_date;
            $inputData['appointment_end_date'] = $appointment_appointment_end_date;
            $inputData['type'] = $request->slot_type;
            $inputData['phone'] = $request->appointment_phone;
            $inputData['email'] = $request->appointment_email;
            $inputData['person_meeting'] = $request->appointment_person_meeting;
            $inputData['additional_notes'] = $request->appointment_additional_notes;
            UserLeadAppointment::create($inputData);

            $calender_sync = true;

            $client = new Client();
            if ($credentials_file = $this->checkServiceAccountCredentialsFile()) {
                $client->setAuthConfig($credentials_file);
            } elseif (env('GOOGLE_APPLICATION_CREDENTIALS', '/storage/app/service-account-credentials.json')) {
                $client->useApplicationDefaultCredentials();
            } else {
                $calender_sync = false;
            }
            if ($calender_sync == true) {
                $user_session = $request->session()->get('user');
                if (isset($user_session->id)) {
                    $user = User::getById($user_session->id);
                }
                $scheduled_user = '';
                if (isset($user->id)) {
                    if (isset($user->first_name) AND $user->first_name != '') {
                        $scheduled_user .= $user->first_name . ' ';
                    }
                    if (isset($user->last_name) AND $user->last_name != '') {
                        $scheduled_user .= $user->last_name;
                    }

                    if ($scheduled_user != '') {
                        $request['note'] .= ",User who scheduled the appointment: " . $scheduled_user . ';';
                    }
                }

                $client->setApplicationName("Client_Library_Examples");
                $client->setScopes(['https://www.googleapis.com/auth/calendar']);
                $user_to_impersonate = env('GOOGLE_CALENDAR_EMAIL', 'investors@letsgetpaid.com');
                $client->setSubject($user_to_impersonate);
                $service = new Calendar($client);
                $calendarList = $service->calendarList->listCalendarList();
                $event = new Event(array(
                    'summary' => $request['note'],
                    'location' => '',
                    'description' => $request['note'],
                    'start' => array(
                        'dateTime' => $final_start_date,
                        'timeZone' => 'CST',
                    ),
                    'end' => array(
                        'dateTime' => $final_end_date,
                        'timeZone' => 'CST',
                    ),
                ));
                $calendarId = env('GOOGLE_CALENDAR_ID', 'c_75a155c18eb405d91ed4d3c8ee7d0e2cbd205389d33503ac4de1744b2c6666d9@group.calendar.google.com');
                $event = $service->events->insert($calendarId, $event);

                if (isset($event->id)) {
                    $last_appoiment = UserLeadAppointment::orderBy('id', 'desc')->first();
                    $update_data = [];
                    $update_data['calendar_event_id'] = $event->id;
                    UserLeadAppointment::where('id', '=', $last_appoiment->id)->update($update_data);
                }
            }


            if (isset($param_rules['lead_id']) AND $param_rules['lead_id'] != '') {
                $lead = Lead::find($lead_id);
                if (isset($lead->title) AND $lead->title != '' AND isset($lead->formatted_address) AND $lead->formatted_address != '') {
                    $title = $lead->title;
                    $address = $lead->formatted_address;
                } else {
                    $title = '';
                    $address = '';
                }
            } else {
                $title = '';
                $address = '';
            }

            $user_session = $request->session()->get('user');
            if (isset($user_session->id)) {
                $user = User::getById($user_session->id);
            }
            $scheduled_user = '';
            if (isset($user->id)) {
                if (isset($user->first_name) AND $user->first_name != '') {
                    $scheduled_user .= $user->first_name . ' ';
                }
                if (isset($user->last_name) AND $user->last_name != '') {
                    $scheduled_user .= $user->last_name;
                }
            }


            $data['note'] = $request['note'];
            $data['start'] = $request['start_date'];
            $data['scheduled_user'] = $scheduled_user;
            $data['homeowner_name'] = $title;
            $data['address'] = $address;
            $data['person_meeting'] = '';
            $data['phone'] = '';
            $data['email'] = '';
            $data['additional_notes'] = '';

            $mails = Alerts::where('type', '=', 1)
                    ->whereNull('deleted_at')
                    ->get();
            if (isset($mails[0])) {
                foreach ($mails as $mail) {
                    $data['name'] = $mail->value;
                    $subject = 'iKnock New Appointment Scheduled ' . date('m-d-Y g:i a', strtotime($request['start_date']));
                    $to_email = $mail->value;
                    $email = new \SendGrid\Mail\Mail();
                    $email->setFrom(env('MAIL_FROM_ADDRESS'), env('APP_NAME'));
                    $email->setSubject($subject);
                    $email->addTo($to_email, env('APP_NAME'));

                    $dataView = view('emails.scheduling', compact('data'))->render();

                    $email->addContent("text/html", $dataView);
                    $sendgrid = new \SendGrid(getenv('MAIL_PASSWORD'));

                    try {
                        $response = $sendgrid->send($email);
                    } catch (Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Mail not sended please check SMTP details!');
                    }
                }
            }

            $message = '';
            $message .= "iKnock New Appointment Scheduled. A new appointment has been scheduled in iKnock "
                    . date('m-d-Y g:i a', strtotime($request['start_date'])) . ". ";
            if ($data['scheduled_user'] != '') {
                $message .= "User who scheduled the appointment: " . $data['scheduled_user'] . ';';
            }
            if ($data['homeowner_name'] != '') {
                $message .= "Homeowner Name: " . $title . ';';
            }
            if ($data['address'] != '') {
                $message .= " " . $data['address'] . " ";
            }
            if ($data['person_meeting'] != '') {
                $message .= "Person With Whom You Are Meeting: " . $data['person_meeting'] . ';';
            }
            if ($data['phone'] != '') {
                $message .= "Phone: " . $data['phone'] . ';';
            }
            if ($data['email'] != '') {
                $message .= "E mail: " . $data['email'] . ';';
            }
            if ($data['additional_notes'] != '') {
                $message .= "Additional notes:  " . $data['additional_notes'] . ';';
            }

            $numbers = Alerts::where('type', '=', 2)
                    ->whereNull('deleted_at')
                    ->get();
            if (isset($numbers[0])) {
                $sid = env('TWILIO_ACCOUNT_SID', '');
                $twiliophoneNo = env("TWILIO_PHONE_NO", '');
                $token = env("TWILIO_AUTH_TOKEN", '');

                foreach ($numbers as $number) {
                    if ($number->value != '') {
                        $contact_number = "+1" . $number->value;
                        $twilio = new TwilioClient($sid, $token);
                        try {
                            $message = substr($message, 0, 349);

                            $twilio_message = $twilio->messages
                                    ->create($contact_number,
                                    ["from" => $twiliophoneNo, "body" => $message]
                            );
                        } catch (\Twilio\Exceptions\TwilioException $exception) {
                            \Illuminate\Support\Facades\Log::error('Twilio test sms not sended please check details!');
                        }
                    }
                }
            }
        } else {



            $start_date = date('Y-m-d', strtotime($request['appointment_appointment_date']));
            $start_time = date('H:i:s', strtotime('-1 hours', strtotime($request['appointment_appointment_date'])));
            $end_date = date('Y-m-d', strtotime($request['appointment_appointment_date']));
            $end_time = date('H:i:s', strtotime('+1 hours', strtotime($request['appointment_appointment_date'])));
            $final_start_date = $start_date . 'T' . $start_time . '-07:00';
            $final_end_date = $end_date . 'T' . $end_time . '-07:00';

            $request['start_date'] = date('Y-m-d H:i', strtotime($request['start_date']));

            $request['end_date'] = date('Y-m-d H:i', strtotime($request['end_date']));

            $obj = UserLeadAppointment::find($request['appointment_id']);
//            if ($request['slot_type'] == 'leads') {
            $obj->lead_id = $request->appointment_lead_id;
            $obj->user_id = $request['user_id'];
//            } else
//                $obj->user_id = $request['target_user_id'];


            $date = DateTime::createFromFormat('m-d-Y h:i a', $request['appointment_appointment_date']);
            $appointment_appointment_date = $date->format('Y-m-d H:i:s');

            $date->modify('+1 hour');
            $appointment_appointment_end_date = $date->format('Y-m-d H:i:s');
            $obj->appointment_date = $appointment_appointment_date;
            $obj->appointment_end_date = $appointment_appointment_end_date;
            $obj->person_meeting = $request['appointment_person_meeting'];
            $obj->phone = $request['appointment_phone'];
            $obj->email = $request['appointment_email'];
            $obj->additional_notes = $request['appointment_additional_notes'];
            $obj->save();
            $newArray = [];
            $newArray['id'] = $request['appointment_id'];
            $newArray['company_id'] = 4;
            $response = UserLeadAppointment::getById($newArray);


            if (isset($obj->calendar_event_id) AND $obj->calendar_event_id != '') {

                $calender_sync = true;
                $client = new Client();
                if ($credentials_file = $this->checkServiceAccountCredentialsFile()) {
                    $client->setAuthConfig($credentials_file);
                } elseif (env('GOOGLE_APPLICATION_CREDENTIALS', '/storage/app/service-account-credentials.json')) {
                    $client->useApplicationDefaultCredentials();
                } else {
                    $calender_sync = false;
                }

                if ($calender_sync == true) {
                    $user_session = $request->session()->get('user');
                    if (isset($user_session->id)) {
                        $user = User::getById($user_session->id);
                    }
                    $scheduled_user = '';
                    if (isset($user->id)) {
                        if (isset($user->first_name) AND $user->first_name != '') {
                            $scheduled_user .= $user->first_name . ' ';
                        }
                        if (isset($user->last_name) AND $user->last_name != '') {
                            $scheduled_user .= $user->last_name;
                        }

                        if ($scheduled_user != '') {
                            $request['note'] .= " "
                                    . ",User who scheduled the appointment: " . $scheduled_user . ';';
                        }
                    }

                    $client->setApplicationName("Client_Library_Examples");
                    $client->setScopes(['https://www.googleapis.com/auth/calendar']);
                    $user_to_impersonate = env('GOOGLE_CALENDAR_EMAIL', 'investors@letsgetpaid.com');
                    $client->setSubject($user_to_impersonate);
                    $service = new Calendar($client);
                    $event_id = $obj->calendar_event_id;
                    $calendar_id = env('GOOGLE_CALENDAR_ID', 'c_6f9bf513c524a8b73219803a02364d3367065fd0ab70932f9e0756cd0f02b1cf@group.calendar.google.com');
                    $event = $service->events->get($calendar_id, $event_id);
                    $event->setDescription($request['note']);
                    $start = new EventDateTime();
                    $start->setDateTime($final_start_date);
                    $event->setStart($start);
                    $end = new EventDateTime();
                    $end->setDateTime($final_end_date);
                    $event->setEnd($end);
                    $service->events->update($calendar_id, $event->getId(), $event);
                }
            }


            $user_session = $request->session()->get('user');
            if (isset($user_session->id)) {
                $user = User::getById($user_session->id);
            }
            $scheduled_user = '';
            if (isset($user->id)) {
                if (isset($user->first_name) AND $user->first_name != '') {
                    $scheduled_user .= $user->first_name . ' ';
                }
                if (isset($user->last_name) AND $user->last_name != '') {
                    $scheduled_user .= $user->last_name;
                }
            }


            $data['note'] = $request['note'];
            $data['start'] = $request['start_date'];
            $data['scheduled_user'] = $scheduled_user;
            $data['homeowner_name'] = $title;
            $data['address'] = $address;
            $data['person_meeting'] = $request['person_meeting'];
            $data['phone'] = $request['phone'];
            $data['email'] = $request['email'];
            $data['additional_notes'] = $request['additional_notes'];


            $mails = Alerts::where('type', '=', 1)
                    ->whereNull('deleted_at')
                    ->get();
            if (isset($mails[0])) {
                foreach ($mails as $mail) {
                    $data['name'] = $mail->value;
                    $to_email = $mail->value;
                    $subject = 'iKnock Scheduled Appointment Updated ' . date('m-d-Y g:i a', strtotime($request['start_date']));

                    $email = new \SendGrid\Mail\Mail();
                    $email->setFrom(env('MAIL_FROM_ADDRESS'), env('APP_NAME'));
                    $email->setSubject($subject);
                    $email->addTo($to_email, env('APP_NAME'));

                    $dataView = view('emails.scheduling_update', compact('data'))->render();

                    $email->addContent("text/html", $dataView);
                    $sendgrid = new \SendGrid(getenv('MAIL_PASSWORD'));

                    try {
                        $response = $sendgrid->send($email);
                    } catch (Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Mail not sended please check SMTP details!');
                    }
                }
            }




            $message = '';
            $message .= "iKnock Scheduled Appointment Updated. A scheduled appointment has been updated in iKnock. "
                    . date('m-d-Y g:i a', strtotime($request['start_date'])) . ". ";
            if ($data['scheduled_user'] != '') {
                $message .= "User who scheduled the appointment: " . $data['scheduled_user'] . ';';
            }
            if ($data['homeowner_name'] != '') {
                $message .= "Homeowner Name: " . $title . ';';
            }
            if ($data['address'] != '') {
                $message .= " " . $data['address'] . " ";
            }
            if ($data['person_meeting'] != '') {
                $message .= "Person With Whom You Are Meeting: " . $data['person_meeting'] . ';';
            }
            if ($data['phone'] != '') {
                $message .= "Phone: " . $data['phone'] . ';';
            }
            if ($data['email'] != '') {
                $message .= "E mail: " . $data['email'] . ';';
            }
            if ($data['additional_notes'] != '') {
                $message .= "Additional notes:  " . $data['additional_notes'] . ';';
            }


            $numbers = Alerts::where('type', '=', 2)
                    ->whereNull('deleted_at')
                    ->get();
            if (isset($numbers[0])) {
                $sid = env('TWILIO_ACCOUNT_SID', '');
                $twiliophoneNo = env("TWILIO_PHONE_NO", '');
                $token = env("TWILIO_AUTH_TOKEN", '');

                foreach ($numbers as $number) {
                    if ($number->value != '') {
                        $contact_number = "+1" . $number->value;
                        $twilio = new TwilioClient($sid, $token);
                        try {

                            $message = substr($message, 0, 349);

                            $twilio_message = $twilio->messages
                                    ->create($contact_number,
                                    ["from" => $twiliophoneNo, "body" => $message]
                            );
                        } catch (\Twilio\Exceptions\TwilioException $exception) {
                            \Illuminate\Support\Facades\Log::error('Twilio test sms not sended please check details!');
                        }
                    }
                }
            }
        }
        exit;
    }

    public function update(Request $request, FollowingLead $followup_lead) {

        $input['title'] = $request->title;
        $input['address'] = $request->formatted_address;
        $input['admin_notes'] = $request->admin_notes;
        $input['user_detail'] = $request->user_detail;
        $input['follow_status'] = $request->follow_status;
        $input['contract_date'] = $request->contract_date;
        $input['investor_id'] = $request->investor_id;
        $input['status_id'] = $request->status_id;
        $input['investor_notes'] = $request->investor_notes;

        if ($request->auction != '') {
            $input['auction'] = Carbon::parse($request->auction)->format('m/d/Y');
        } else {
            $input['auction'] = '';
        }

        $input['date_status_updated'] = Carbon::parse($request->date_status_updated)->format('Y-m-d');

        if ($request->follow_status != $followup_lead->follow_status) {
            $followup_status = FollowStatus::where('id', '=', $request->follow_status)->first();
            if (isset($followup_status->id)) {
                $obj_lead_history = LeadHistory::create([
                            'lead_id' => $followup_lead->lead_id,
                            'title' => $followup_status->title . ' status updated from Follow Up Lead Management.',
                            'assign_id' => $request['user_id'],
                            'status_id' => 0,
                            'followup_status_id' => $followup_status->id
                ]);
            }
        }

        if (!empty($input['contract_date'])) {
            $input['contract_date'] = Carbon::parse($input['contract_date'])->format('Y-m-d');
        }

        if (!empty($request->followingCustomField)) {
            foreach ($request->followingCustomField as $key => $followingCustomField) {
                $followingCustomFields = FollowingCustomFields::find($followingCustomField['id']);

                if (!is_null($followingCustomFields) && !empty($followingCustomField['value'])) {

                    $followingCustomFields->field_value = $followingCustomField['value'];
                    $followingCustomFields->save();
                }
            }
        }

        $followup_lead->update($input);

        \Session::put('success', 'Follow up lead updated successfully');
        return response()->json(['success' => true]);
    }

    public function updateMarketing(Request $request, Marketing $marketing) {

        $m_input['title'] = $request->m_title;
        $m_input['address'] = $request->m_address;
        $m_input['appt_email'] = $request->m_appt_email;
        $m_input['appt_phone'] = $request->m_appt_phone;
        $m_input['marketing_mail'] = $request->m_marketing_mail;
        $m_input['marketing_address'] = $request->m_marketing_address;
        $m_input['admin_notes'] = $request->m_admin_notes;
        $m_input['investore_note'] = $request->m_investore_note;

        if (isset($request->m_appt_email) AND trim($request->m_appt_email) != '') {
            $mailchimpMarketing = marketingCampaignObj();
            $listId = env('MAILCHIMP_LIST_ID');
            $formatted_address = '';
            if (isset($m_input['address']) AND trim($m_input['address']) != '') {
                $formatted_address = $m_input['address'];
            }

            if (isset($m_input['marketing_address']) AND trim($m_input['marketing_address']) != '') {
                $formatted_address = $m_input['marketing_address'];
            }
            $city = '';
            $state = '';
            $zip = '';
            $country = '';

            $body = [];
            $body['email_address'] = trim($request->m_appt_email);
            $body['status_if_new'] = 'subscribed';
            $body['status'] = 'subscribed';
            $body['merge_fields']['FNAME'] = $request->m_title;
            $body['merge_fields']['LNAME'] = '-';
            $body['merge_fields']['PHONE'] = '-';
            $body['merge_fields']['ADDRESS']['addr1'] = $formatted_address;
            $body['merge_fields']['ADDRESS']['city'] = $city;
            $body['merge_fields']['ADDRESS']['state'] = $state;
            $body['merge_fields']['ADDRESS']['zip'] = $zip;
            $body['merge_fields']['ADDRESS']['country'] = $country;
            $data = json_encode($body);
            try {
                $mailchimpMarketing->lists->addListMember($listId, $data, true);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                
            }
        }

        if (isset($request->m_marketing_mail) AND trim($request->m_marketing_mail) != '') {
            $mailchimpMarketing = marketingCampaignObj();
            $listId = env('MAILCHIMP_LIST_ID');

            $formatted_address = '';
            if (isset($m_input['address']) AND trim($m_input['address']) != '') {
                $formatted_address = $m_input['address'];
            }

            if (isset($m_input['marketing_address']) AND trim($m_input['marketing_address']) != '') {
                $formatted_address = $m_input['marketing_address'];
            }
            $city = '';
            $state = '';
            $zip = '';
            $country = '';

            $body = [];
            $body['email_address'] = $request->m_marketing_mail;
            $body['status_if_new'] = 'subscribed';
            $body['status'] = 'subscribed';
            $body['merge_fields']['FNAME'] = $request->m_title;
            $body['merge_fields']['LNAME'] = '-';
            $body['merge_fields']['PHONE'] = '-';
            $body['merge_fields']['ADDRESS']['addr1'] = $formatted_address;
            $body['merge_fields']['ADDRESS']['city'] = $city;
            $body['merge_fields']['ADDRESS']['state'] = $state;
            $body['merge_fields']['ADDRESS']['zip'] = $zip;
            $body['merge_fields']['ADDRESS']['country'] = $country;

            $data = json_encode($body);
            try {
                $mailchimpMarketing->lists->addListMember($listId, $data, true);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                
            }
        }


        $marketing->update($m_input);


        $followup_lead = FollowingLead::where('lead_id', '=', $marketing->lead_id)->first();
        if (isset($followup_lead->id)) {
            $input['title'] = $request->title;
            $input['address'] = $request->formatted_address;
            $input['admin_notes'] = $request->admin_notes;
            $input['user_detail'] = $request->user_detail;
            $input['follow_status'] = $request->follow_status;
            $input['contract_date'] = $request->contract_date;
            $input['investor_id'] = $request->investor_id;
            $input['status_id'] = $request->status_id;
            $input['investor_notes'] = $request->investor_notes;

            if ($request->auction != '') {
                $input['auction'] = Carbon::parse($request->auction)->format('m/d/Y');
            } else {
                $input['auction'] = '';
            }

            $input['date_status_updated'] = Carbon::parse($request->date_status_updated)->format('Y-m-d');

            if ($request->follow_status != $followup_lead->follow_status) {
                $followup_status = FollowStatus::where('id', '=', $request->follow_status)->first();
                if (isset($followup_status->id)) {
                    $obj_lead_history = LeadHistory::create([
                                'lead_id' => $followup_lead->lead_id,
                                'title' => $followup_status->title . ' status updated from Follow Up Lead Management.',
                                'assign_id' => $request['user_id'],
                                'status_id' => 0,
                                'followup_status_id' => $followup_status->id
                    ]);
                }
            }

            if (!empty($input['contract_date'])) {
                $input['contract_date'] = Carbon::parse($input['contract_date'])->format('Y-m-d');
            }

            if (!empty($request->followingCustomField)) {
                foreach ($request->followingCustomField as $key => $followingCustomField) {
                    $followingCustomFields = FollowingCustomFields::find($followingCustomField['id']);

                    if (!is_null($followingCustomFields) && !empty($followingCustomField['value'])) {

                        $followingCustomFields->field_value = $followingCustomField['value'];
                        $followingCustomFields->save();
                    }
                }
            }

            $followup_lead->update($input);
        }

        \Session::put('success', 'Follow up lead updated successfully');
        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function editableField(Request $request) {
        $followingLead = FollowingLead::find($request->pk);

        if (!empty($request->name)) {
            $inputValue = $request->value;
            $inputName = $request->name;

            if ($request->name == 'status_update' || $request->name == 'contract_date') {
                $inputValue = dateFormatMDYtoYMD($inputValue);
            }

            if ($request->name == 'status_update') {
                $inputName = 'date_status_updated';
            }
            $input = [$inputName => $inputValue];
            $followingLead->update($input);

            if ($request->name == 'follow_status') {
                $followup_status = FollowStatus::where('id', '=', $request->value)->first();
                if (isset($followup_status->id)) {
                    $obj_lead_history = LeadHistory::create([
                                'lead_id' => $followingLead->lead_id,
                                'title' => $followup_status->title . ' status updated from Follow Up Lead Management.',
                                'assign_id' => $request['user_id'],
                                'status_id' => 0,
                                'followup_status_id' => $followup_status->id
                    ]);
                }
            }
        }

        return response()->json(['success']);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function editableCustomField(Request $request) {

        $followingCustomFields = FollowingCustomFields::find($request->name);

        if (!empty($followingCustomFields)) {
            $followingCustomFields->field_value = $request->value;

            $followingCustomFields->save();
        }

        return response()->json(['success']);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function indexListDelete(Request $request) {
        if (!empty($request->ids)) {
            foreach ($request->ids as $key => $id) {
                $followingLead = FollowingLead::find($id);

                if (!is_null($followingLead)) {
                    $followingLead->delete();
                }
            }
        }

        return response()->json(['success', 'code' => 200]);
    }

    public function MarketingListDelete(Request $request) {
        if (!empty($request->ids)) {
            foreach ($request->ids as $key => $id) {
                $marketingLead = Marketing::find($id);
                if (!is_null($marketingLead)) {
                    $marketingLead->delete();
                }
            }
        }

        return response()->json(['success', 'code' => 200]);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function updateIsRetired(Request $request) {

        $followingLead = FollowingLead::find($request->id);

        if ($request->is_retired == 'true') {
            $is_retired = 1;
        } else {
            $is_retired = 0;
        }

        $followingLead->is_retired = $is_retired;
        $followingLead->is_expired = $is_retired;
        $followingLead->save();

        return response()->json(['success', 'code' => 200]);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function isMarketing(Request $request) {
        $followingLead = FollowingLead::find($request->id);

        if (!is_null($followingLead)) {
            $followingLead->is_marketing = 1;
            $followingLead->save();
            $marketingLead = Marketing::where('lead_id', $followingLead->lead_id)->first();
            if (is_null($marketingLead)) {
                $UserLeadAppointment = UserLeadAppointment::where('lead_id', '=', $followingLead->lead_id)
                        ->orderBy('id', 'desc')
                        ->first();
                if (isset($UserLeadAppointment->id)) {
                    if (isset($UserLeadAppointment->phone) AND $UserLeadAppointment->phone != '') {
                        $UserLeadAppointment->phone;
                        $input['appt_phone'] = $UserLeadAppointment->phone;
                    }
                    if (isset($UserLeadAppointment->email) AND $UserLeadAppointment->email != '') {
                        $input['appt_email'] = $UserLeadAppointment->email;
                        $mailchimpMarketing = marketingCampaignObj();
                        $listId = env('MAILCHIMP_LIST_ID');

                        $formatted_address = $followingLead->formatted_address;
                        if ($formatted_address != '') {
                            $addr1 = $formatted_address;
                            $city = '';
                            $state = '';
                            $zip = '';
                            $country = '';
                        } else {
                            $addr1 = '';
                            $city = '';
                            $state = '';
                            $zip = '';
                            $country = '';
                        }

                        $body = [];
                        $body['email_address'] = $UserLeadAppointment->email;
                        $body['status_if_new'] = 'subscribed';
                        $body['status'] = 'subscribed';
                        $body['merge_fields']['FNAME'] = $followingLead->title;
                        $body['merge_fields']['LNAME'] = '-';
                        $body['merge_fields']['PHONE'] = '-';
                        $body['merge_fields']['ADDRESS']['addr1'] = $addr1;
                        $body['merge_fields']['ADDRESS']['city'] = $city;
                        $body['merge_fields']['ADDRESS']['state'] = $state;
                        $body['merge_fields']['ADDRESS']['zip'] = $zip;
                        $body['merge_fields']['ADDRESS']['country'] = $country;

                        $data = json_encode($body);
                        try {
                            $mailchimpMarketing->lists->addListMember($listId, $data, true);
                        } catch (\GuzzleHttp\Exception\ClientException $e) {
                            
                        }
                    }
                }
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
                $input['investore_note'] = $followingLead->investor_notes;
                $input['investore_id'] = $followingLead->investor_id;
                $input['user_detail'] = $followingLead->user_detail;
                $input['is_followup'] = 0;

                $marketing = Marketing::create($input);
            } else {
                $UserLeadAppointment = UserLeadAppointment::where('lead_id', '=', $followingLead->lead_id)
                        ->orderBy('id', 'desc')
                        ->first();
                if (isset($UserLeadAppointment->id)) {
                    if (isset($UserLeadAppointment->phone) AND $UserLeadAppointment->phone != '') {
                        $UserLeadAppointment->phone;
                        $input['appt_phone'] = $UserLeadAppointment->phone;
                    }
                    if (isset($UserLeadAppointment->email) AND $UserLeadAppointment->email != '') {
                        $input['appt_email'] = $UserLeadAppointment->email;
                        $mailchimpMarketing = marketingCampaignObj();
                        $listId = env('MAILCHIMP_LIST_ID');

                        $formatted_address = $followingLead->formatted_address;
                        if ($formatted_address != '') {
                            $addr1 = $formatted_address;
                            $city = '';
                            $state = '';
                            $zip = '';
                            $country = '';
                        } else {
                            $addr1 = '';
                            $city = '';
                            $state = '';
                            $zip = '';
                            $country = '';
                        }

                        $body = [];
                        $body['email_address'] = $UserLeadAppointment->email;
                        $body['status_if_new'] = 'subscribed';
                        $body['status'] = 'subscribed';
                        $body['merge_fields']['FNAME'] = $followingLead->title;
                        $body['merge_fields']['LNAME'] = '-';
                        $body['merge_fields']['PHONE'] = '-';
                        $body['merge_fields']['ADDRESS']['addr1'] = $addr1;
                        $body['merge_fields']['ADDRESS']['city'] = $city;
                        $body['merge_fields']['ADDRESS']['state'] = $state;
                        $body['merge_fields']['ADDRESS']['zip'] = $zip;
                        $body['merge_fields']['ADDRESS']['country'] = $country;

                        $data = json_encode($body);
                        try {
                            $mailchimpMarketing->lists->addListMember($listId, $data, true);
                        } catch (\GuzzleHttp\Exception\ClientException $e) {
                            
                        }
                    }
                }
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
                $input['investore_note'] = $followingLead->investor_notes;
                $input['investore_id'] = $followingLead->investor_id;
                $input['user_detail'] = $followingLead->user_detail;
                $input['is_followup'] = 0;

                $marketingLead->update($input);
            }
        }

        return response()->json(['success', 'code' => 200]);
    }

    public function isPurchase(Request $request) {
        $followingLead = FollowingLead::find($request->id);

        if (!is_null($followingLead)) {
            $followingLead->is_purchase = 1;
            $followingLead->save();
            $PurchaseLead = PurchaseLead::where('lead_id', $followingLead->lead_id)->first();

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
            $input['contract_date'] = $followingLead->contract_date;
            $input['user_detail'] = $followingLead->user_detail;
            $input['follow_status'] = $followingLead->follow_status;

            $input['is_followup'] = 0;
            if (is_null($PurchaseLead)) {
                $PurchaseLead = PurchaseLead::create($input);
            } else {
                $PurchaseLead->update($input);
            }

            $followUpLeadViewSetps = FollowUpLeadViewSetp::orderBy('order_no', 'asc')
                    ->get();

            foreach ($followUpLeadViewSetps as $followUpLeadView) {
                $FollowingCustomFields = FollowingCustomFields::where('followup_lead_id', '=', $request->id)
                        ->where('followup_view_id', '=', $followUpLeadView->id)
                        ->first();

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




        return response()->json(['success', 'code' => 200]);
    }

    public function isFollowup(Request $request) {
        $PurchaseLead = PurchaseLead::find($request->id);

        if (!is_null($PurchaseLead)) {
            $PurchaseLead->is_followup = 1;
            $PurchaseLead->save();

            $FollowingLead = FollowingLead::where('lead_id', $PurchaseLead->lead_id)->first();

            $input['lead_id'] = $PurchaseLead->lead_id;
            $input['title'] = $PurchaseLead->title;
            $input['owner'] = $PurchaseLead->owner;
            $input['address'] = $PurchaseLead->address;
            $input['admin_notes'] = $PurchaseLead->admin_notes;
            $input['foreclosure_date'] = $PurchaseLead->foreclosure_date;
            $input['identifier'] = $PurchaseLead->identifier;
            $input['formatted_address'] = $PurchaseLead->formatted_address;
            $input['city'] = $PurchaseLead->city;
            $input['county'] = $PurchaseLead->county;
            $input['state'] = $PurchaseLead->state;
            $input['zip_code'] = $PurchaseLead->zip_code;
            $input['type_id'] = $PurchaseLead->type_id;
            $input['status_id'] = $PurchaseLead->status_id;
            $input['is_verified'] = $PurchaseLead->is_verified;
            $input['creator_id'] = $PurchaseLead->creator_id;
            $input['company_id'] = $PurchaseLead->company_id;
            $input['assignee_id'] = $PurchaseLead->assignee_id;
            $input['is_expired'] = $PurchaseLead->is_expired;
            $input['latitude'] = $PurchaseLead->latitude;
            $input['longitude'] = $PurchaseLead->longitude;
            $input['appointment_date'] = $PurchaseLead->appointment_date;
            $input['appointment_result'] = $PurchaseLead->appointment_result;
            $input['auction'] = $PurchaseLead->auction;
            $input['lead_value'] = $PurchaseLead->lead_value;
            $input['original_loan'] = $PurchaseLead->original_loan;
            $input['loan_date'] = $PurchaseLead->loan_date;
            $input['sq_ft'] = $PurchaseLead->sq_ft;
            $input['yr_blt'] = $PurchaseLead->yr_blt;
            $input['eq'] = $PurchaseLead->eq;
            $input['mortgagee'] = $PurchaseLead->mortgagee;
            $input['loan_type'] = $PurchaseLead->loan_type;
            $input['loan_mod'] = $PurchaseLead->loan_mod;
            $input['trustee'] = $PurchaseLead->trustee;
            $input['owner_address'] = $PurchaseLead->owner_address;
            $input['source'] = $PurchaseLead->source;
            $input['created_by'] = $PurchaseLead->created_by;
            $input['updated_by'] = $PurchaseLead->updated_by;
            $input['sq_ft_2'] = $PurchaseLead->sq_ft_2;
            $input['original_loan_2'] = $PurchaseLead->original_loan_2;
            $input['investor_notes'] = $PurchaseLead->investor_notes;
            $input['investor_id'] = $PurchaseLead->investor_id;
            $input['contract_date'] = $PurchaseLead->contract_date;
            $input['user_detail'] = $PurchaseLead->user_detail;
            $input['follow_status'] = $PurchaseLead->follow_status;
            $input['is_purchase'] = 0;

            if (is_null($FollowingLead)) {
                $FollowingLead = FollowingLead::create($input);
            } else {
                $FollowingLead->update($input);
            }

            $followUpLeadViewSetps = \App\Models\PurchaseLeadViewSetp::orderBy('order_no', 'asc')
                    ->get();

            foreach ($followUpLeadViewSetps as $followUpLeadView) {

                $FollowingCustomFields = \App\Models\PurchaseCustomFields::where('followup_lead_id', '=', $request->id)
                        ->where('followup_view_id', '=', $followUpLeadView->id)
                        ->first();

                $PurchaseLeadViewSetp = FollowUpLeadViewSetp::where('title', '=', $followUpLeadView->title)
                        ->first();

                if (isset($PurchaseLeadViewSetp->id)) {
                    $PurchaseCustomFieldsExits = FollowingCustomFields::where('followup_lead_id', '=', $FollowingLead->id)
                            ->where('followup_view_id', '=', $PurchaseLeadViewSetp->id)
                            ->first();

                    if (isset($PurchaseCustomFieldsExits->id)) {
                        $new_data = [];
                        $new_data['field_value'] = $FollowingCustomFields->field_value;
                        $new_data['updated_at'] = NOW();
                        $PurchaseCustomFieldsExits->update($new_data);
                    } else {
                        $new_data = [];
                        $new_data['followup_lead_id'] = $FollowingLead->id;
                        $new_data['followup_view_id'] = $PurchaseLeadViewSetp->id;
                        $new_data['field_value'] = $FollowingCustomFields->field_value;
                        $new_data['created_at'] = NOW();
                        $new_data['updated_at'] = NOW();

                        FollowingCustomFields::create($new_data);
                    }
                }
            }
        }




        return response()->json(['success', 'code' => 200]);
    }

    public function isFollowupMarketing(Request $request) {
        $MarketingLead = Marketing::find($request->id);

        if (!is_null($MarketingLead)) {
            $MarketingLead->is_followup = 1;
            $MarketingLead->save();

            $FollowingLead = FollowingLead::where('lead_id', $MarketingLead->lead_id)->first();


            $input['title'] = $MarketingLead->title;
            $input['owner'] = $MarketingLead->owner;
            $input['address'] = $MarketingLead->address;
            $input['admin_notes'] = $MarketingLead->admin_notes;
            $input['investor_notes'] = $MarketingLead->investore_note;
            $input['investor_id'] = $MarketingLead->investore_id;
            $input['user_detail'] = $MarketingLead->user_detail;
            $input['is_marketing'] = 0;

            if (is_null($FollowingLead)) {
                $FollowingLead = FollowingLead::create($input);
            } else {
                $FollowingLead->update($input);
            }
        }
        return response()->json(['success', 'code' => 200]);
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function isDeal(Request $request) {
        $followingLead = FollowingLead::find($request->id);

        $dealLead = DealLead::where('followup_id', $followingLead->id)
                ->first();

        if (!is_null($followingLead) && is_null($dealLead)) {
            $input['lead_id'] = $followingLead->lead_id;
            $input['followup_id'] = $followingLead->id;
            $input['title'] = $followingLead->title;
            $input['owner'] = $followingLead->owner;
            $input['address'] = $followingLead->address;
            $input['formatted_address'] = $followingLead->formatted_address;
            $input['city'] = $followingLead->city;
            $input['county'] = $followingLead->county;
            $input['state'] = $followingLead->state;
            $input['investor_id'] = $followingLead->investor_id;

            $input['zip_code'] = $followingLead->zip_code;
            $input['sq_ft'] = $followingLead->sq_ft;
            $input['yr_blt'] = $followingLead->yr_blt;

            DealLead::create($input);

            $followingLead->is_deal = 1;
            $followingLead->save();

            $obj_lead_history = LeadHistory::create([
                        'lead_id' => $followingLead->lead_id,
                        'title' => 'Lead Moved into Deal Lead Management.',
                        'assign_id' => $request->user_id,
                        'status_id' => 0
            ]);

            info($obj_lead_history);
        }

        return response()->json('success');
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function showField(Request $request) {
        $followUpLeadViewSetp = FollowUpLeadViewSetp::find($request->id);

        if (!is_null($followUpLeadViewSetp)) {
            $followUpLeadViewSetp->is_show = $request->value;

            $followUpLeadViewSetp->save();
        }

        return response()->json(['success', 'code' => 200]);
    }

    public function showFieldPurchase(Request $request) {
        $followUpLeadViewSetp = \App\Models\PurchaseLeadViewSetp::find($request->id);

        if (!is_null($followUpLeadViewSetp)) {
            $followUpLeadViewSetp->is_show = $request->value;

            $followUpLeadViewSetp->save();
        }

        return response()->json(['success', 'code' => 200]);
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function exportLead(Request $request) {
        $input = $request->all();

        $followingLeads = getFollowingLead($input, 1);

        $followingLeads = followUpLeadDataSetup($followingLeads);

        $followingLead = FollowingLeadResource::collection($followingLeads);

        return Excel::download(new FollowingLeadExport($followingLead), 'following-lead-' . date('m-d-Y') . '-' . time() . '.csv');
    }

    public function exportMarketing(Request $request) {
        $input = $request->all();

        $followingLeads = getMarketingLead($input, 1);

        $followingLeads = marketingLeadDataSetup($followingLeads);

        $followingLead = MarketingLeadResource::collection($followingLeads);

        return Excel::download(new MarketingLeadExport($followingLead), 'marketing-lead-' . date('m-d-Y') . '-' . time() . '.csv');
    }

    public function followupLeadsHistoryExport(Request $request) {
        $input = $request->all();

        $followingLeads = getFollowingLeadAllExport($input);

        $param_rules['search'] = 'sometimes';
        $param['search'] = isset($request['search']) ? $request['search'] : '';
        $param['latitude'] = isset($request['latitude']) ? $request['latitude'] : '';
        $param['longitude'] = isset($request['longitude']) ? $request['longitude'] : '';
        $param['radius'] = isset($request['radius']) ? $request['radius'] : 500;
        $param['auction_start_date'] = isset($request['auction_start_date']) ? $request['auction_start_date'] : '';
        $param['auction_end_date'] = isset($request['auction_end_date']) ? $request['auction_end_date'] : '';
        $param['is_retired'] = isset($request['is_retired']) ? $request['is_retired'] : '';
        if (is_array($request['user_ids'])) {
            $request['user_ids'] = implode(",", $request['user_ids']);
            $param['user_ids'] = isset($request['user_ids']) ? trim($request['user_ids']) : '';
        } else {
            $param['user_ids'] = isset($request['user_ids']) ? trim($request['user_ids']) : '';
        }
        $param['status_ids'] = isset($request['status_ids']) ? trim($request['status_ids']) : '';
        $param['start_date'] = isset($request['start_date']) ? $request['start_date'] : '';
        $param['end_date'] = isset($request['end_date']) ? $request['end_date'] : '';
        $param['order_by'] = isset($request['order_by']) ? $request['order_by'] : 'id';
        $param['order_type'] = isset($request['order_type']) ? $request['order_type'] : 'desc';
        $param['lead_type_id'] = isset($request['lead_type_id']) ? $request['lead_type_id'] : '';

        if ($param['lead_type_id'] == '') {
            $param['lead_type_id'] = isset($request['type_ids_arr']) ? $request['type_ids_arr'] : '';
        }

        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];

        $this->__is_paginate = false;
        if (empty($param['lead_ids'])) {
            foreach ($followingLeads as $lead) {
                $param['lead_ids'][] = $lead->lead_id;
            }
        }
        $data = [];
        $count = 0;

        $query = LeadHistory::select('status.title as status_title',
                        'lead_detail.id',
                        'lead_detail.title',
                        'lead_detail.owner',
                        'lead_detail.address',
                        'lead_detail.zip_code',
                        'lead_detail.city',
                        'lead_detail.state',
                        'lead_detail.county',
                        'lead_detail.creator_id',
                        'lead_history.status_id',
                        'lead_detail.is_verified',
                        'lead_detail.type_id',
                        'lead_detail.admin_notes',
                        'lead_detail.is_expired',
                        'lead_detail.assignee_id',
//                        'lead_detail.status_id',
                        'lead_detail.type_id',
                        'lead_detail.admin_notes',
                        'lead_detail.lead_value',
                        'lead_detail.mortgagee',
                        'lead_detail.original_loan',
                        'lead_detail.loan_date',
                        'lead_detail.loan_mod',
                        'lead_detail.trustee',
                        'lead_detail.sq_ft',
                        'lead_detail.yr_blt',
                        'lead_detail.eq',
                        'lead_detail.owner_address',
                        'lead_detail.source',
                        'lead_detail.created_by',
                        'lead_detail.updated_by',
                        'lead_history.created_at',
                        'lead_detail.updated_at',
                        'lead_status.title as lead_status_title',
                        'lead_user.first_name as lead_assignee_first_name',
                        'lead_user.last_name as lead_assignee_last_name',
                        'lead_user.email as lead_assignee_email',
                        'lead_history.assign_id',
                        DB::raw("concat(user.first_name,' ', user.last_name) as name")
                        , DB::raw("(IF(lead_history.status_id = 0, lead_history.title, concat(status.title, ' status updated'))) as lead_history_title")
                        , 'lead_history.created_at', 'lead_history.followup_status_id', 'lead_history.key_history', 'lead_history.value_history', 'lead_history.latest_status_id');
        $query->leftJoin('lead_detail', 'lead_detail.id', 'lead_history.lead_id');
        $query->leftJoin('status', 'status.id', 'lead_history.status_id');
        $query->leftJoin('user', 'user.id', 'lead_history.assign_id');


        $query->leftJoin('type', 'lead_detail.type_id', '=', 'type.id');
        $query->leftJoin('user as lead_user', 'lead_detail.assignee_id', '=', 'lead_user.id');
        $query->leftJoin('status as lead_status', 'lead_detail.status_id', '=', 'lead_status.id');


        if (isset($param['lead_id']) && !empty($param['lead_id']))
            $query->where('lead_id', $param['lead_id']);

        if (isset($param['lead_ids']) && !empty($param['lead_ids']))
            $query->whereIn('lead_id', $param['lead_ids']);

        if (isset($param['search']) && !empty($param['search']))
            $query->whereRaw("lead_detail.title like '%{$param['search']}%'");

        $query->with('leadStatus');
        $query->with('leadType');
        $query->with('leadMedia');
        $query->with('followLeadStatus');
        $query->with('followLeadStatus');


        if (isset($param['is_lead_export']) AND $param['is_lead_export'] === 'true') {
            $query->where('lead_history.assign_id', '!=', 0);
            $query->whereNotNull('lead_history.lead_id');
            $query->orderBy('lead_detail.title');
            return $query->get();
        }
        $query->orderBy('lead_history.lead_id', 'desc');

        $perPage = 4000;
        $page = 1;

        $allRecords = [];

        do {
            $offset = ($page - 1) * $perPage;
            $chunks = $query->skip($offset)->take($perPage)->get();
            if ($chunks->isNotEmpty()) {
                if (!isset($chunks)) {
                    break;
                }
                foreach ($chunks as $chunk) {
                    $allRecords[] = $chunk->toArray();
                }
            } else {
                break;
            }
            $page++;
        } while (true);

        $allRecords = [];

        $query->chunk($perPage, function ($chunks) use (&$allRecords) {
            foreach ($chunks as $row) {
                if (!isset($followup_result[$row->id])) {
                    $followingLead = FollowingLead::with('userLead', 'investor', 'AppointmentLatest', 'latestNote')
                            ->where('lead_id', $row->id)
                            ->first();
                    if ($followingLead->Appointment == null) {
                        $appt = UserLeadAppointment::where('lead_id', '=', $row->id)
                                ->select('appointment_date', 'result', 'additional_notes', 'phone', 'email', 'person_meeting')
                                ->first();
                        $followingLead->Appointment = $appt;
                    }

                    $followingLead = followUpLeadDataSetupOneNew($followingLead);

                    $followup_result[$row->id] = $followingLead;
                } else {
                    $followingLead = $followup_result[$row->id];
                }

                $data = new \stdClass();

                $data->title = $row->title;
                $data->Address = $row->address . ' ' . $row->zip_code . ' ' . $row->city;
                $data->is_retired = $followingLead['is_retired'] == 0 ? 'Yes' : 'No';
                $data->lead_status = $row->lead_history_title;
                if (isset($row->status_id) AND $row->status_id == 0 AND $row->followup_status_id != 0) {
                    $status_title = $row->followLeadStatus->title;
                } elseif (isset($row->followup_status_id) AND $row->followup_status_id == 0 AND $row->status_id != 0) {
                    $status_title = $row->leadStatus->title;
                } elseif (isset($row->status_id) AND $row->status_id == 0 AND isset($row->followup_status_id) AND $row->followup_status_id == 0) {
                    $status_title = '';
                } else {
                    $status_title = $followStatus->leadFollowStatus->title;
                }
                $data->followStatus = $status_title;
                $data->lead = ($followingLead['userLead']->first_name ?? '') . ' ' . ($followingLead['userLead']->last_name ?? '');
                $data->investor = ($followingLead['investor']->first_name ?? '') . ' ' . ($followingLead['investor']->last_name ?? '');
                $data->date_to_follow_up = $followingLead['date_to_follow_up'] ?? '';
                $data->auction = $followingLead['auction'] ?? '';
                $data->purchase_date = $followingLead['purchase_date'] ?? '';
                $data->contract_date = $followingLead['contract_date'] ?? '';
                $data->updated_by = $row->updated_by;
                $data->Who = $row->lead_assignee_email;

                $data->updated_at = dynamicDateFormat(dateTimezoneChangeFullDateTimeReturn($row->updated_at), 5);
                $data->updated_at_time = dynamicDateFormat(dateTimezoneChangeFullDateTimeReturn($row->updated_at), 9);
                $data->created_at = dynamicDateFormat(dateTimezoneChangeFullDateTimeReturn($row->created_at), 5);
                $data->created_at_time = dynamicDateFormat(dateTimezoneChangeFullDateTimeReturn($row->created_at), 9);


                $data->created_by = $row->created_by;
                if (isset($row->leadType->title) AND $row->leadType->title != '') {
                    $data->lead_type = $row->leadType->title;
                } else {
                    $data->lead_type = '';
                }
                $data->lead_status_title = $row->status_title;

                if (isset($followingLead['latestNote']->response) AND $followingLead['latestNote']->response != '') {
                    $data->Notes = $followingLead['latestNote']->response;
                } else {
                    $data->Notes = '';
                }
                $data->investor_notes = $followingLead['investor_notes'] ?? '';
                $data->auth_signed_date = $followingLead['auth_signed_date'] ?? '';

                if (!is_null($followingLead['Appointment'])) {
                    $appointment_date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $followingLead['Appointment']->appointment_date)
                            ->format('m/d/Y h:i:s');
                    $data->appointment_date = $appointment_date == '' ? '' : date('m/d/Y', strtotime($appointment_date));
                    $data->appointment_time = $appointment_date == '' ? '' : date('H:i:s', strtotime($appointment_date));
                    $data->appointment_result = $followingLead['Appointment']->result;
                    $data->additional_note = $followingLead['Appointment']->additional_notes;
                    $data->additional_mobile = $followingLead['Appointment']->phone;
                    $data->additional_email = $followingLead['Appointment']->email;
                    $data->additional_person = $followingLead['Appointment']->person_meeting;
                    $allRecords[] = $data;
                }
            }
        });
        
        return Excel::download(new FollowingLeadHistoryExport($allRecords), 'following-lead-history-' . date('m-d-Y') . '-' . time() . '.csv');

//        $result = LeadHistory::getList($param);

        $followup_result = [];
        foreach ($result as $row) {
            if (!isset($followup_result[$row->id])) {
                $followingLead = FollowingLead::with('userLead', 'investor', 'AppointmentLatest', 'latestNote')
                        ->where('lead_id', $row->id)
                        ->first();
                if ($followingLead->Appointment == null) {
                    $appt = UserLeadAppointment::where('lead_id', '=', $row->id)
                            ->select('appointment_date', 'result', 'additional_notes', 'phone', 'email', 'person_meeting')
                            ->first();
                    $followingLead->Appointment = $appt;
                }

                $followingLead = followUpLeadDataSetupOneNew($followingLead);

                $followup_result[$row->id] = $followingLead;
            } else {
                $followingLead = $followup_result[$row->id];
            }


            $data[$count]['title'] = $row->title;
            $data[$count]['Address'] = $row->address . ' ' . $row->zip_code . ' ' . $row->city;
            $data[$count]['is_retired'] = $followingLead['is_retired'] == 0 ? 'Yes' : 'No';
            $data[$count]['lead_status'] = $row->lead_history_title;
            if (isset($row->status_id) AND $row->status_id == 0 AND $row->followup_status_id != 0) {
                $status_title = $row->followLeadStatus->title;
            } elseif (isset($row->followup_status_id) AND $row->followup_status_id == 0 AND $row->status_id != 0) {
                $status_title = $row->leadStatus->title;
            } elseif (isset($row->status_id) AND $row->status_id == 0 AND isset($row->followup_status_id) AND $row->followup_status_id == 0) {
                $status_title = '';
            } else {
                $status_title = $followStatus->leadFollowStatus->title;
            }
            $data[$count]['followStatus'] = $status_title;
            $data[$count]['lead'] = ($followingLead['userLead']->first_name ?? '') . ' ' . ($followingLead['userLead']->last_name ?? '');
            $data[$count]['investor'] = ($followingLead['investor']->first_name ?? '') . ' ' . ($followingLead['investor']->last_name ?? '');
            $data[$count]['date_to_follow_up'] = $followingLead['date_to_follow_up'] ?? '';
            $data[$count]['auction'] = $followingLead['auction'] ?? '';
            $data[$count]['purchase_date'] = $followingLead['purchase_date'] ?? '';
            $data[$count]['contract_date'] = $followingLead['contract_date'] ?? '';
            $data[$count]['updated_by'] = $row->updated_by;
            $data[$count]['Who'] = $row->lead_assignee_email;

            $data[$count]['updated_at'] = dynamicDateFormat(dateTimezoneChangeFullDateTimeReturn($row->updated_at), 5);
            $data[$count]['updated_at_time'] = dynamicDateFormat(dateTimezoneChangeFullDateTimeReturn($row->updated_at), 9);
            $data[$count]['created_at'] = dynamicDateFormat(dateTimezoneChangeFullDateTimeReturn($row->created_at), 5);
            $data[$count]['created_at_time'] = dynamicDateFormat(dateTimezoneChangeFullDateTimeReturn($row->created_at), 9);


            $data[$count]['created_by'] = $row->created_by;
            if (isset($row->leadType->title) AND $row->leadType->title != '') {
                $data[$count]['lead_type'] = $row->leadType->title;
            } else {
                $data[$count]['lead_type'] = '';
            }
            $data[$count]['lead_status_title'] = $row->status_title;

            if (isset($followingLead['latestNote']->response) AND $followingLead['latestNote']->response != '') {
                $data[$count]['Notes'] = $followingLead['latestNote']->response;
            } else {
                $data[$count]['Notes'] = '';
            }
            $data[$count]['investor_notes'] = $followingLead['investor_notes'] ?? '';
            $data[$count]['auth_signed_date'] = $followingLead['auth_signed_date'] ?? '';

            if (!is_null($followingLead['Appointment'])) {
                $appointment_date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $followingLead['Appointment']->appointment_date)
                        ->format('m/d/Y h:i:s');
                $data[$count]['appointment_date'] = $appointment_date == '' ? '' : date('m/d/Y', strtotime($appointment_date));
                $data[$count]['appointment_time'] = $appointment_date == '' ? '' : date('H:i:s', strtotime($appointment_date));
                $data[$count]['appointment_result'] = $followingLead['Appointment']->result;
                $data[$count]['additional_note'] = $followingLead['Appointment']->additional_notes;
                $data[$count]['additional_mobile'] = $followingLead['Appointment']->phone;
                $data[$count]['additional_email'] = $followingLead['Appointment']->email;
                $data[$count]['additional_person'] = $followingLead['Appointment']->person_meeting;
            }
            $count++;
        }
        return Excel::download(new FollowingLeadHistoryExport($data), 'following-lead-history-' . date('m-d-Y') . '-' . time() . '.csv');
    }

}
