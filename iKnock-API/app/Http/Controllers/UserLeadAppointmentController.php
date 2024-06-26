<?php

namespace App\Http\Controllers;

use App\Http\Middleware\LoginAuth;
use App\Models\Lead;
use App\Models\MailTemplate;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserLeadAppointment;
use Illuminate\Http\Request;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Facades\Mail;
use Twilio;
use Twilio\Rest\Client as TwilioClient;
use App\Models\Alerts;
use DateTime;

class UserLeadAppointmentController extends Controller {

    function __construct() {

        parent::__construct();
        $this->middleware(LoginAuth::class, ['only' => ['index', 'schedulingView', 'storeView', 'store', 'getAppointments', 'createAppointmentView',
                'createAppointment', 'update', 'destroy', 'show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $param_rules['search'] = 'sometimes';
        $param_rules['appointment_date'] = 'nullable'; //date("Y-n-j G:i");
        $param_rules['is_out_bound'] = 'nullable|IN:1,0'; //date("Y-n-j G:i");


        $param['search'] = isset($request['search']) ? $request['search'] : '';
        $param['is_out_bound'] = isset($request['is_out_bound']) ? ($request['is_out_bound'] === '0') ? 0 : 1 : '';
        $param['company_id'] = $request['company_id'];
        $param['appointment_date'] = '';
        if (isset($request['appointment_date'])) {
            $date_format = '|date_format:"n-Y"';
            $date_str = explode('-', $request['appointment_date']);
            $param['appointment_date'] = date("m-Y", strtotime($request['appointment_date']));

            if (count($date_str) > 2) {
                $date_format = '|date_format:"n-j-Y"';
                $param['appointment_date'] = date("Y-m-d", strtotime($request['appointment_date']));
            }
            $param_rules['appointment_date'] .= $date_format;
            $this->__is_paginate = false;
        }
        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        if (isset($request['appointment_date'])) {
            $request['appointment_date'] = "{$date_str[1]}-{$date_str[0]}";
            $param['appointment_date'] = date("Y-m", strtotime($request['appointment_date']));

            if (count($date_str) > 2) {
                $request['appointment_date'] = "{$date_str[2]}-{$date_str[0]}-{$date_str[1]}";
                $param['appointment_date'] = date("Y-m-d", strtotime($request['appointment_date']));
            }
        }
        $param['user_id'] = $request['user_id'];
        $param['ignore_out_bound'] = false;
        if (isset($request['start'])) {
            $param['appointment_start_date'] = explode('T', $request['start'])[0] . ' 00:00:00';
            $param['appointment_end_date'] = explode('T', $request['end'])[0] . ' 23:59:59';
        }

        if ($this->call_mode == 'api') {
            //print_r($param);

            $param['userIds'] = !isset($request['userIds']) ? '' : $request['userIds'];
            $param['ignore_out_bound'] = true;
            $param['API'] = true;
            $this->__is_paginate = true;
        }

        if ($this->call_mode != 'api') {
            //print_r($param);

            $param['appointment_start_date'] = explode('T', $request['start'])[0] . ' 00:00:00';
            $param['appointment_end_date'] = explode('T', $request['end'])[0] . ' 23:59:59';

            $param['appointment_start_date'] = date("Y-m-d 00:00:00", strtotime("-5 months", strtotime($param['appointment_start_date'])));
            //$param['appointment_end_date'] = date("Y-m-d 00:00:00", strtotime("+2 months",strtotime($param['appointment_start_date'])));


            $param['userIds'] = !isset($request['userIds']) ? '' : $request['userIds'];
            $param['ignore_out_bound'] = true;
            $this->__is_paginate = false;
        }

        $param['type'] = 'lead';
        $response = UserLeadAppointment::getList($param);

        return $this->__sendResponse('UserLeadAppointment', $response, 200, 'Lead list retrieved successfully.');
    }

    public function createAppointment(Request $request) {
        $param_rules['lead_id'] = 'required|exists:lead_detail,id';
        $param_rules['mail_appointment_date'] = 'nullable|date_format:"Y-n-j G:i"|after_or_equal:' . date("Y-n-j G:i");
        $param_rules['phone_appointment_date'] = 'nullable|date_format:"Y-n-j G:i"|after_or_equal:' . date("Y-n-j G:i");

        if ($request['mail_appointment_date'] && !empty($request['mail_appointment_date']))
            $param_rules['template_id'] = 'required|exists:mail_template,id';

        if (!empty($request['mail_appointment_date'])) {
            $appointment_date = explode(':', $request['mail_appointment_date']);
            $appointment_date_min = (isset($appointment_date[1])) ? ((strlen($appointment_date[1]) > 1) ? $appointment_date[1] : "0{$appointment_date[1]}") : '00';
            $request['mail_appointment_date'] = "{$appointment_date[0]}:$appointment_date_min";
        }

        if (!empty($request['phone_appointment_date'])) {
            $appointment_date = explode(':', $request['phone_appointment_date']);
            $appointment_date_min = (isset($appointment_date[1])) ? ((strlen($appointment_date[1]) > 1) ? $appointment_date[1] : "0{$appointment_date[1]}") : '00';
            $request['phone_appointment_date'] = "{$appointment_date[0]}:$appointment_date_min";
        }

        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        if (empty($request['mail_appointment_date']) && empty($request['phone_appointment_date'])) {
            $errors['mail_appointment_date'] = 'Appointment date is required';
            return $this->__sendError('Validation Error.', $errors);
        }

        if (!empty($request['mail_appointment_date'])) {
            $appointments = UserLeadAppointment::whereRaw("('{$request['mail_appointment_date']}:00'" . ' between `appointment_date` and `appointment_end_date`)')
                    //->where('user_id', $request['user_id'])
                    ->whereRaw("(user_id = {$request['user_id']} OR lead_id = {$request['lead_id']})")
                    ->whereNull('deleted_at')
                    ->orderBy('id', 'desc')
                    ->first();

            if (isset($appointments->id)) {
                $appointment_date = date("n-j-Y G:i", strtotime($appointments->appointment_date));
                if ($appointments->is_out_bound == 1) {
                    $appointment_end_date = date("n-j-Y G:i", strtotime($appointments->appointment_end_date));
                    $appointment_date = "from $appointment_date to $appointment_end_date";
                } else
                    $appointment_date = "for $appointment_date";
                $errors['appointment_date'] = 'Appointment is already scheduled ' . $appointment_date;
                return $this->__sendError('Validation Error.', $errors);
            }
        }

        if (!empty($request['phone_appointment_date'])) {
            $appointments = UserLeadAppointment::whereRaw("('{$request['phone_appointment_date']}:00'" . ' between `appointment_date` and `appointment_end_date`)')
                    //->where('user_id', $request['user_id'])
                    ->whereRaw("(user_id = {$request['user_id']} OR lead_id = {$request['lead_id']})")
                    ->whereNull('deleted_at')
                    ->orderBy('id', 'desc')
                    ->first();

            if (isset($appointments->id)) {
                $appointment_date = date("n-j-Y G:i", strtotime($appointments->appointment_date));
                if ($appointments->is_out_bound == 1) {
                    $appointment_end_date = date("n-j-Y G:i", strtotime($appointments->appointment_end_date));
                    $appointment_date = "from $appointment_date - $appointment_end_date";
                } else
                    $appointment_date = "for $appointment_date";

                $errors['appointment_date'] = 'Appointment is already scheduled ' . $appointment_date;
                return $this->__sendError('Validation Error.', $errors);
            }
        }

        if (!empty($request['mail_appointment_date'])) {
            $obj_appointment = new UserLeadAppointment();
            $obj_appointment->lead_id = $request['lead_id'];
            $obj_appointment->user_id = $request['user_id'];
            $obj_appointment->result = $request['template_id'];
            $obj_appointment->appointment_date = $request['mail_appointment_date'];
            $obj_appointment->appointment_end_date = $request['mail_appointment_date'];
            $obj_appointment->type = 'marketing_mail';
            $obj_appointment->save();
        }
        if (!empty($request['phone_appointment_date'])) {
            $obj_appointment = new UserLeadAppointment();
            $obj_appointment->lead_id = $request['lead_id'];
            $obj_appointment->user_id = $request['user_id'];
            $obj_appointment->result = $request['template_id'];
            $obj_appointment->appointment_date = $request['phone_appointment_date'];
            $obj_appointment->appointment_end_date = $request['phone_appointment_date'];
            $obj_appointment->type = 'marketing_phone';
            $obj_appointment->save();
        }

        $lead_detail = Lead::getById($request['lead_id']);
        if (!empty($request['mail_appointment_date'])) {
            $printer_email = Setting::geTenantSettingtById(7, $request['company_id'])->value;
            $mail_params['LEAD_TITLE'] = $lead_detail['title'];
            if (!empty($lead_detail['owner']))
                $mail_params['LEAD_OWNER'] = $lead_detail['owner'];
            $mail_params['LEAD_ADDRESS'] = $lead_detail['address'];
            $mail_params['LEAD_TYPE'] = $lead_detail['leadType']->title;
            $mail_params['LEAD_CITY'] = $lead_detail['city'];
            $mail_params['LEAD_ZIPCODE'] = $lead_detail['zip_code'];
            $mail_params['APPOINTMENT_DATE'] = $request['mail_appointment_date'];
            $mail_params['APP_NAME'] = env('APP_NAME');

            $this->__sendMail($request['template_id'], $printer_email, $mail_params);
        }

        $this->__is_paginate = false;
        $this->__is_collection = false;
        return $this->__sendResponse('Lead', $lead_detail, 200, 'Lead has been retrieved successfully.');
    }

    public function createAppointmentView(Request $request) {
        $param_rules = [];
        $param['user_id'] = $request['user_id'];
        $param['leadUserIds'] = $request['userIds'];
        $param['company_id'] = $request['company_id'];
        $param['is_paginate'] = false;

        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        $this->__view = 'tenant.scheduling.add_schedule';
        $this->__is_paginate = false;
        $this->__collection = false;

        $response['leadUserIds'] = explode(',', $request['userIds']);
        $response['agent'] = User::getTenantUserList($param);
//        echo"<pre>";print_r( $response['agent']); die;
        return $this->__sendResponse('Lead', $response, 200, 'Your lead bulk has been added successfully.');
    }

    public function schedulingView(Request $request) {

        $param_rules = [];
        $param['user_id'] = $request['user_id'];
        $param['leadUserIds'] = $request['userIds'];
        $param['company_id'] = $request['company_id'];
        $param['is_paginate'] = false;

        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        $this->__view = 'tenant.scheduling.scheduling';

        $this->__is_paginate = false;
        $this->__collection = false;

        $response['leadUserIds'] = explode(',', $request['userIds']);
        $param['user_status_id'] = 1;
        $response['agent'] = User::getTenantUserList($param);
        // dd( $response['agent']);
        // dd($response);
        return $this->__sendResponse('Lead', $response, 200, 'Your lead bulk has been added successfully.');
    }

    public function getAppointments(Request $request) {
        $param_rules = [];
//        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];
//        $param['startDate'] = $request['startDate'];
//        $param['endDate'] = $request['endDate'];
        $param['is_paginate'] = false;
        $param['type'] = 'lead';

        $response = $this->__validateRequestParams($request->all(), $param_rules);
        if ($this->__is_error == true)
            return $response;

        $this->__is_ajax = true;

        $response = UserLeadAppointment::getList($param);

        return $this->__sendResponse('UserLeadAppointment', $response, 200, 'Your appointments has been fetched successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeView(Request $request) {
        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];
        $param['is_paginate'] = false;
        $param['is_all'] = true;
        $param['user_status_id'] = 1;
        $response['agent'] = User::getTenantUserList($param,true);
        
        $response['leads'] = Lead::getList($param);

        $this->__view = 'tenant.scheduling.add_schedule';

        $this->__is_paginate = false;
        $this->__collection = false;

        return $this->__sendResponse('Lead', $response, 200, 'Your appointment viewd has been retrieved successfully.');
    }

    public function checkServiceAccountCredentialsFile() {
        $application_creds = storage_path('app/service-account-credentials.json');

        return file_exists($application_creds) ? $application_creds : false;
    }

    public function store(Request $request) {
        $request['start_date'] = str_replace('-', '/', $request['start_date']);
        $request['end_date'] = str_replace('-', '/', $request['end_date']);        
        $param_rules = [];
        $param_rules['start_date'] = 'required|date|date_format:m/d/Y h:i a|after_or_equal:' . date("m/d/Y 00:00");
        $param_rules['end_date'] = 'required|date|date_format:m/d/Y h:i a|after_or_equal:' . date("m/d/Y h:i a", strtotime($request['start_date']));

        $param_rules['note'] = 'required';

        if (isset($request['slot_type']) && $request['slot_type'] == 'leads')
            $param_rules['lead_id'] = 'required';
        else
            $param_rules['target_user_id'] = 'required';

        $param_rules_messages = array(
            'target_user_id.required' => 'User id is required.'
        );

        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules, $param_rules_messages);

        if ($this->__is_error == true)
            return $response;

        $start_date = date('Y-m-d', strtotime($request['start_date']));
        $start_time = date('H:i:s', strtotime('-1 hours', strtotime($request['start_date'])));
        $end_date = date('Y-m-d', strtotime($request['end_date']));
        $end_time = date('H:i:s', strtotime('-1 hours', strtotime($request['end_date'])));
        $check_daylight = date('d-M-Y', strtotime($request['start_date']));
        $date = new DateTime($check_daylight . ' America/Los_Angeles');
        $check_daylight_result = $date->format('I');
        if ($check_daylight_result == 1) {
            $final_start_date = $start_date . 'T' . $start_time . '-06:00';
            $final_end_date = $end_date . 'T' . $end_time . '-06:00';
        } else {
            $final_start_date = $start_date . 'T' . $start_time . '-07:00';
            $final_end_date = $end_date . 'T' . $end_time . '-07:00';
        }
        $request['start_date'] = date('Y-m-d H:i', strtotime($request['start_date']));
        $request['end_date'] = date('Y-m-d H:i', strtotime($request['end_date']));
        UserLeadAppointment::bulkInsertion($request->all());


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
                // \Illuminate\Support\Facades\Log::error(json_encode($event));
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
                        $message = substr($message,0,349);
                        
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



        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('Lead', [], 200, 'Your appointment has been added successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id) {
        
       
        $param_rules['id'] = 'required|exists:user_lead_appointment,id,deleted_at,NULL';

        $request['id'] = $id;
        $this->__is_redirect = true;
        $this->__view = 'tenant/scheduling';
        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];
        $param['is_paginate'] = false;
        $param['is_all'] = true;

        $response['detail'] = UserLeadAppointment::getById($request->all());

        $response['detail']->appointment_date = date('m-d-Y H:i', strtotime($response['detail']->appointment_date));
        $response['detail']->appointment_end_date = date('m-d-Y H:i', strtotime($response['detail']->appointment_end_date));
        $param['user_status_id'] = 1;
        $response['agent'] = User::getTenantUserList($param,true);
        
        $response['leads'] = Lead::getList($param);
        $response['slot_type'] = (!empty($response['detail']->is_out_bound) && !empty($response['detail']->lead_id)) ? 'leads' : 'user';

        $this->__is_paginate = false;
        $this->__collection = false;
        $this->__is_redirect = false;
        $this->__view = 'tenant.scheduling.edit_schedule';
        return $this->__sendResponse('UserLeadAppointment', $response, 200, 'Your appointment has been retrieved successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request) {
        $request['start_date'] = str_replace('-', '/', $request['start_date']);
        $request['end_date'] = str_replace('-', '/', $request['end_date']);

        $param_rules['id'] = 'required|exists:user_lead_appointment,id';
        $param_rules['start_date'] = 'date|date_format:m/d/Y h:i a|after_or_equal:' . date("m/d/Y 00:00");
        $param_rules['end_date'] = 'date|date_format:m/d/Y h:i a|after_or_equal:' . date("m/d/Y h:i a", strtotime($request['start_date']));
        $param_rules['target_user_id'] = 'required';
        //$param_rules['target_user_id'] = "required|exists:user,id,{$request['target_user_id']},company_id,".$request['company_id'];
        $param_rules['note'] = 'required';

        $param_rules_messages = array(
            'id.required' => 'Scehduling id is required.',
            'target_user_id.required' => 'User id is required.'
        );

        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules, $param_rules_messages);

        if ($this->__is_error == true)
            return $response;

        $start_date = date('Y-m-d', strtotime($request['start_date']));
        $start_time = date('H:i:s', strtotime('-1 hours', strtotime($request['start_date'])));
        $end_date = date('Y-m-d', strtotime($request['end_date']));
        $end_time = date('H:i:s', strtotime('-1 hours', strtotime($request['end_date'])));

        $final_start_date = $start_date . 'T' . $start_time . '-07:00';
        $final_end_date = $end_date . 'T' . $end_time . '-07:00';


        $request['start_date'] = date('Y-m-d H:i', strtotime($request['start_date']));
        $request['end_date'] = date('Y-m-d H:i', strtotime($request['end_date']));

        $obj = UserLeadAppointment::find($request['id']);
        if ($request['slot_type'] == 'leads') {
            $obj->lead_id = $request['lead_id'];
            $obj->user_id = $request['user_id'];
        } else
            $obj->user_id = $request['target_user_id'];
        $obj->appointment_date = $request['start_date'];
        $obj->appointment_end_date = $request['end_date'];
        $obj->person_meeting = $request['person_meeting'];
        $obj->phone = $request['phone'];
        $obj->result = $request['note'];
        $obj->email = $request['email'];
        $obj->additional_notes = $request['additional_notes'];
        $obj->save();

        $response = UserLeadAppointment::getById($request->all());


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
                        
                        $message = substr($message,0,349);
                        
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
        $response = json_decode(json_encode($response), true);

        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('UserLeadAppointment', $obj, 200, 'Your appointment has been updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id) {
        $param_rules['id'] = 'required|exists:user_lead_appointment,id';
        $param_rules['company_id'] = "required|exists:company,id";

        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        $appoiment_data = UserLeadAppointment::where('id', '=', $request['id'])->first();
        UserLeadAppointment::destroy($request['id']);

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
        }




        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('UserLeadAppointment', [], 200, 'Appointment has been deleted successfully.');
    }

}
