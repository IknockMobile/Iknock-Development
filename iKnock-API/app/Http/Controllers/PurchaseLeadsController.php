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
use App\Models\PurchaseLeadViewSetp;
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
use App\Models\PurchaseCustomFields;
use App\Models\PurchaseLead;
use App\Http\Resources\PurchaseLeadResource;
use App\Exports\PurchaseLeadExport;
class PurchaseLeadsController extends Controller {

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
        $followUpLeadViewSetps = PurchaseLeadViewSetp::where('is_show', 1)->orderBy('order_no', 'asc')->get();
        $followUpLeadViewSetpsDropDown = PurchaseLeadViewSetp::orderBy('order_no', 'asc')->get();
        $followUpLeadViewSetpsSlug = PurchaseLeadViewSetp::orderBy('order_no', 'asc')->pluck('title_slug');
        $followUpLeadViewSetpsSlug = json_encode($followUpLeadViewSetpsSlug, JSON_INVALID_UTF8_IGNORE);

        $input = $request->all();

        $followingLeads = getPurchaseLead($input);                
        
        $statusList = FollowStatus::where('is_purchase','=',1)->latest()->get();

        $users = User::orderby('status_id', 'desc')->where('user_group_id', '!=', 4)
                        ->where('status_id', 1)->get();

        $followingLeadsData = dataViewPurchaseLead($input);
        
        return view('tenant.purchaselead.index', compact('followingLeads', 'mobileusers', 'users', 'statues', 'followUpLeadViewSetps', 'followUpLeadViewSetpsSlug', 'followingLeadsData', 'statusList', 'followUpLeadViewSetpsDropDown'));
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function indexList(Request $request) {
        $input = $request->all();

        $followingLeadsData = dataViewPurchaseLead($input);

        $followingLeads = getPurchaseLead($input);                

        $followUpLeadViewSetps = PurchaseLeadViewSetp::where('is_show', 1)->orderBy('order_no', 'asc')->get();

        $data = view('tenant.purchaselead.followingLeadsMain', compact('followingLeadsData', 'followUpLeadViewSetps', 'followingLeads', 'input'))->render();

        return response()->json(['success' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
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
    public function edit(PurchaseLead $purchase_lead) {
        $followup_lead = FollowingLead::where('lead_id','=',$purchase_lead->lead_id)->first();
        
        $users = User::orderby('status_id', 'desc')->where('status_id',1)->get();

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

        $statusList = FollowStatus::where('is_purchase','=',1)->latest()->get();

        $statues = Status::latest()->get();

        $data['status'] = Status::getList($param);
        $data['agent'] = User::getTenantUserList($param);

        $data['type'] = Type::whereIn('tenant_id', [$request['company_id']])->whereNull('deleted_at')->get();
        $data['lead'] = Lead::getById($id);

        updateCustomFiledPurchase($purchase_lead->id);

        updateCustomFiled($followup_lead->id);
        
        $userLeadAppointment = UserLeadAppointment::where('lead_id', '=', $purchase_lead->lead_id)
                ->orderBy('id', 'desc')
                ->first();
        
        return view('tenant.purchaselead.edit', compact('userLeadAppointment','purchase_lead','data', 'followup_lead', 'users', 'statusList', 'mobileusers', 'statues', 'followingCustomFields'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PurchaseLead $purchase_lead) {

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

        if ($request->follow_status != $purchase_lead->follow_status) {
            $followup_status = FollowStatus::where('id', '=', $request->follow_status)->first();
            if (isset($followup_status->id)) {
                $obj_lead_history = LeadHistory::create([
                            'lead_id' => $purchase_lead->lead_id,
                            'title' => $followup_status->title . ' status updated from Purchase Lead Management.',
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
                $followingCustomFields = PurchaseCustomFields::find($followingCustomField['id']);

                if (!is_null($followingCustomFields) && !empty($followingCustomField['value'])) {

                    $followingCustomFields->field_value = $followingCustomField['value'];
                    $followingCustomFields->save();
                }
            }
        }

        $purchase_lead->update($input);

        \Session::put('success', 'Purcahse lead updated successfully');
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
        $followingLead = PurchaseLead::find($request->pk);

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
                                'title' => $followup_status->title . ' status updated from Purchase Lead Management.',
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

        $followingCustomFields = PurchaseCustomFields::find($request->name);

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
                $followingLead = PurchaseLead::find($id);

                if (!is_null($followingLead)) {
                    $followingLead->delete();
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

        $followingLead = PurchaseLead::find($request->id);

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
        $followingLead = PurchaseLead::find($request->id);

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

                $marketingLead->update($input);
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
        $followingLead = PurchaseLead::find($request->id);

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
        $followUpLeadViewSetp = PurchaseLeadViewSetp::find($request->id);

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

        $followingLeads = getPurchaseLead($input, 1);
        
        $followingLeads = purchaseLeadDataSetup($followingLeads);
        
        $followingLead = PurchaseLeadResource::collection($followingLeads);                

        return Excel::download(new PurchaseLeadExport($followingLead), 'purchase-lead-' . date('m-d-Y') . '-' . time() . '.csv');
    }

    public function LeadsHistoryExport(Request $request) {
        $input = $request->all();

        $followingLeads = getPurchaseLeadAll($input);

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
        $result = LeadHistory::getList($param);
        foreach ($result as $row) {
            $followingLead = PurchaseLead::where('lead_id', $row->id)->first();
            $followingLead = purchaseLeadDataSetupOne($followingLead);
            $data[$count]['title'] = $row->title;
            $data[$count]['Address'] = $row->address . ' ' . $row->zip_code . ' ' . $row->city;
            $data[$count]['is_retired'] = $followingLead['is_retired'] == 0 ? 'Yes' : 'No';
            $data[$count]['lead_status'] = $row->lead_history_title;
            $leadUser = User::getById($followingLead['user_detail']);
            if (isset($row->status_id) AND $row->status_id == 0 AND $row->followup_status_id != 0) {
                $followStatus = FollowStatus::find($row->followup_status_id);
                $status_title = $followStatus->title;
            } elseif (isset($row->followup_status_id) AND $row->followup_status_id == 0 AND $row->status_id != 0) {
                $followStatus = Status::find($row->status_id);
                $status_title = $followStatus->title;
            } elseif (isset($row->status_id) AND $row->status_id == 0 AND isset($row->followup_status_id) AND $row->followup_status_id == 0) {
                $status_title = '';
            } else {
                $followStatus = FollowStatus::find($followingLead['follow_status']);
                $status_title = $followStatus->title;
            }
            $data[$count]['followStatus'] = $status_title;
            $data[$count]['lead'] = $leadUser->fullname ?? '';
            $investorUser = User::getById($followingLead['investor_id']);
            $data[$count]['investor'] = $investorUser->fullname ?? '';
            $data[$count]['date_to_follow_up'] = $followingLead['date_to_follow_up'] ?? '';
            $data[$count]['auction'] = $followingLead['auction'] ?? '';
            $data[$count]['purchase_date'] = $followingLead['purchase_date'] ?? '';
            $data[$count]['contract_date'] = $followingLead['contract_date'] ?? '';
            $user = User::getById($row->assign_id);
            $data[$count]['updated_by'] = "{$user->first_name} {$user->last_name}";
            $data[$count]['Who'] = $user->email;
            $data[$count]['updated_at'] = dynamicDateFormat(dateTimezoneChange($row->created_at), 3);
            $data[$count]['created_at'] = dynamicDateFormat(dateTimezoneChange($row->created_at), 3);
            $data[$count]['created_by'] = "{$user->first_name} {$user->last_name}";
            if (isset($row->leadType->title) AND $row->leadType->title != '') {
                $data[$count]['lead_type'] = $row->leadType->title;
            } else {
                $data[$count]['lead_type'] = '';
            }
            $data[$count]['lead_status_title'] = $row->status_title;
            $lead_queary_data_notes = LeadQuery::where('lead_id', '=', $row->id)
                    ->where('query_id', '=', 8)
                    ->orderBy('id', 'desc')
                    ->first();
            if (isset($lead_queary_data_notes->id) AND $lead_queary_data_notes->response != '') {
                $data[$count]['Notes'] = $lead_queary_data_notes->response;
            } else {
                $data[$count]['Notes'] = '';
            }
            $data[$count]['investor_notes'] = $followingLead['investor_notes'] ?? '';
            $data[$count]['auth_signed_date'] = $followingLead['auth_signed_date'] ?? '';
            $count++;
        }
        return Excel::download(new FollowingLeadHistoryExport($data), 'purchase-lead-history-' . date('m-d-Y') . '-' . time() . '.csv');
    }

}
