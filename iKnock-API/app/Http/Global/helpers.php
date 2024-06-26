<?php

use Carbon\Carbon;
use App\Models\FollowingCustomFields;
use App\Models\FollowUpLeadViewSetp;
use App\Models\FollowingLead;
use App\Models\LeadHistory;
use App\Models\Lead;
use App\Models\FollowStatus;
use App\Models\DealLead;
use App\Models\User;
use App\Models\Campaign;
use App\Models\MarketingCampaign;
use App\Models\Marketing;
use App\Models\CampaignUser;
use App\Models\SegmentUserStatus;
use App\Models\PurchaseLead;
use App\Models\PurchaseCustomFields;
use App\Models\UserLeadAppointment;
use App\Models\PurchaseLeadViewSetp;
use App\Models\DealLeadViewCustomFields;
use App\Models\DealLeadViewSetp;
use App\Models\Paginationlimit;
use App\Models\CampaignTag;

function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    // Radius of the Earth in meters
    $earthRadius = 6371000; // meters
    // Convert latitude and longitude from degrees to radians

    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);

    // Haversine formula
    $dLat = $lat2 - $lat1;
    $dLon = $lon2 - $lon1;
    $a = sin($dLat / 2) * sin($dLat / 2) + cos($lat1) * cos($lat2) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earthRadius * $c;

    return $distance;
}

/**
 * Write code for Session notification Msg
 *
 * @return response()
 */
function notificationMsg($type, $message) {
    \Session::put($type, $message);
}

/**
 * Write code on Method Date formate change Y-m-d to m/d/Y
 *
 * @return response()
 */
function dateFormatYMDtoMDY($date) {
    if (empty($date)) {
        return '';
    }

    return Carbon::createFromFormat('Y-m-d', $date)
                    ->format('m/d/Y');
}

function settingForRecordsDisplayedPerScreen() {
    $Paginationlimit = Paginationlimit::where('id', '=', 1)->first();

    return $Paginationlimit;
}

/**
 * Write code on Method Date formate change Y-m-d to m/d/Y
 *
 * @return response()
 */
function dateFormatMDYtoYMD($date) {
    if (empty($date)) {
        return '';
    }

    return Carbon::createFromFormat('m/d/Y', $date)
                    ->format('Y-m-d');
}

/**
 * Write code on Method
 *
 * @return response()
 */
function dateChangeDbFromate($date) {
    if (empty($date)) {
        return '';
    }

    // $date = date('j/n/Y', strtotime($date));

    return Carbon::parse($date)->format('Y-m-d');
}

/**
 * Write code on Method
 *
 * @return response()
 */
function updateCustomFiled($followUpLeadId) {
    $followUpLeadViewSetps = FollowUpLeadViewSetp::orderBy('order_no', 'asc')->get();

    if (!empty($followUpLeadViewSetps)) {
        foreach ($followUpLeadViewSetps as $key => $followUpLeadViewSetp) {
            $followingCustomField = FollowingCustomFields::where('followup_lead_id', $followUpLeadId)
                    ->where('followup_view_id', $followUpLeadViewSetp->id)
                    ->first();

            if (!isset($followingCustomField->id)) {
                $input['followup_lead_id'] = $followUpLeadId;
                $input['followup_view_id'] = $followUpLeadViewSetp->id;

                FollowingCustomFields::create($input);
            }
        }
    }

    return true;
}

function updateCustomFiledNew($followUpLeadId, $followUpLeadViewSetps) {
    if (!empty($followUpLeadViewSetps)) {
        foreach ($followUpLeadViewSetps as $key => $followUpLeadViewSetp) {
            $followingCustomField = FollowingCustomFields::where('followup_lead_id', $followUpLeadId)
                    ->where('followup_view_id', $followUpLeadViewSetp->id)
                    ->first();

            if (!isset($followingCustomField->id)) {
                $input['followup_lead_id'] = $followUpLeadId;
                $input['followup_view_id'] = $followUpLeadViewSetp->id;

                FollowingCustomFields::create($input);
            }
        }
    }

    return true;
}

function updateCustomFiledPurchase($followUpLeadId) {
    $followUpLeadViewSetps = PurchaseLeadViewSetp::orderBy('order_no', 'asc')->get();

    if (!empty($followUpLeadViewSetps)) {
        foreach ($followUpLeadViewSetps as $key => $followUpLeadViewSetp) {
            $followingCustomField = PurchaseCustomFields::where('followup_lead_id', $followUpLeadId)->where('followup_view_id', $followUpLeadViewSetp->id)->first();

            if (is_null($followingCustomField)) {
                $input['followup_lead_id'] = $followUpLeadId;
                $input['followup_view_id'] = $followUpLeadViewSetp->id;

                PurchaseCustomFields::create($input);
            }
        }
    }

    return true;
}

/**
 * Write code on Method
 *
 * @return response()
 */
function userArraySetJson($users) {
    $input = [];

    if (!empty($users)) {
        foreach ($users as $key => $user) {
            $input[$key]['text'] = $user->fullName;
            $input[$key]['value'] = $user->id;
        }
    }

    return json_encode($input);
}

/**
 * Write code on Method
 *
 * @return response()
 */
function dataArrayToJson($data) {
    $input = [];

    if (!empty($data)) {
        foreach ($data as $key => $value) {
            $input[] = ['text' => $value, 'value' => $key];
        }
    }

    return json_encode($input);
}

/**
 * Write code on Method
 *
 * @return response()
 */
function statusArraySetJson($statues) {
    $input = [];

    if (!empty($statues)) {
        foreach ($statues as $key => $status) {
            $input[$key]['text'] = $status->title;
            $input[$key]['value'] = $status->id;
        }
    }

    return json_encode($input);
}

/**
 * Write code on Method
 *
 * @return response()
 */
function followUpLeadDataSetup($followUpLeads) {

    $followUpLeadViewSetps = FollowUpLeadViewSetp::orderBy('order_no', 'asc')->get();

    $leads = [];
    if (!empty($followUpLeads)) {
        foreach ($followUpLeads as $key => $value) {
            
            
            if (!empty($value->id)) {
                $leads[$key]['id'] = $value->id;
                $leads[$key]['homeowner_name'] = $value->title;
                $leads[$key]['homeowner_address'] = $value->address;
                $leads[$key]['homeowner_city'] = $value->city;
                $leads[$key]['homeowner_state'] = $value->state;
                $leads[$key]['homeowner_county'] = $value->county;
                $leads[$key]['homeowner_zip_code'] = $value->zip_code;
                $leads[$key]['contract_date'] = $value->contract_date;
                $leads[$key]['status_update'] = $value->date_status_updated;
                $leads[$key]['investor_id'] = $value->investor_id;
                $leads[$key]['follow_status'] = $value->follow_status;
                $leads[$key]['user_detail'] = $value->user_detail;
                $leads[$key]['admin_notes'] = $value->admin_notes;
                $leads[$key]['auction'] = $value->auction;
                $leads[$key]['investor_notes'] = $value->investor_notes;
                $leads[$key]['is_retired'] = $value->is_retired;
                $leads[$key]['appointment_date'] = '';
                $leads[$key]['appointment_result'] = '';
                $leads[$key]['additional_note'] = '';
                $leads[$key]['additional_mobile'] = '';
                $leads[$key]['additional_email'] = '';
                $leads[$key]['additional_person'] = '';
                if (!is_null($value->Appointment)) {
                    $appointment_date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value->Appointment->appointment_date)->format('m/d/Y h:i A');
                    $leads[$key]['appointment_date'] = $appointment_date;
                    $leads[$key]['appointment_result'] = $value->Appointment->result;
                    $leads[$key]['additional_note'] = $value->Appointment->additional_notes;
                    $leads[$key]['additional_mobile'] = $value->Appointment->phone;
                    $leads[$key]['additional_email'] = $value->Appointment->email;
                    $leads[$key]['additional_person'] = $value->Appointment->person_meeting;
                }

//                $userLeadAppointment = UserLeadAppointment::where('lead_id', $value->lead_id)->first();                                
//                $leads[$key]['appointment_date'] = '';
//                $leads[$key]['appointment_result'] = '';
//                $leads[$key]['additional_note'] = '';
//                $leads[$key]['additional_mobile'] = '';
//                $leads[$key]['additional_email'] = '';
//                $leads[$key]['additional_person'] = '';
//                if (!is_null($userLeadAppointment)) {
//                    $appointment_date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $userLeadAppointment->appointment_date)->format('m/d/Y h:i A');
//                    $leads[$key]['appointment_date'] = $appointment_date;
//                    $leads[$key]['appointment_result'] = $userLeadAppointment->result;
//                    $leads[$key]['additional_note'] = $userLeadAppointment->additional_notes;
//                    $leads[$key]['additional_mobile'] = $userLeadAppointment->phone;
//                    $leads[$key]['additional_email'] = $userLeadAppointment->email;
//                    $leads[$key]['additional_person'] = $userLeadAppointment->person_meeting;
//                }
                if (count($followUpLeadViewSetps) != count($value->customFields)) {
                    updateCustomFiledNew($value->id, $followUpLeadViewSetps);
                }
//                $followingCustomFields = FollowingCustomFields::where('followup_lead_id', $value->id)->get();                                
                $customInput = [];

//                if (!empty($followingCustomFields)) {
                foreach ($value->customFields as $keynew => $followingCustomField) {
                    if (!empty($followingCustomField->followUpLeadViewSetp->title_slug) AND !isset($leads[$key][$followingCustomField->followUpLeadViewSetp->title_slug]) ) {
                        if ($followingCustomField->followUpLeadViewSetp->input_type == 4 && is_numeric($followingCustomField->field_value)) {
                            $leads[$key][$followingCustomField->followUpLeadViewSetp->title_slug] = '$' . $followingCustomField->field_value;
                        } else {
                            $leads[$key][$followingCustomField->followUpLeadViewSetp->title_slug] = $followingCustomField->field_value;
                        }
                    }
                }
//                }                                
            }

//            Illuminate\Support\Facades\Log::info('payoff_gd2_date:' . $leads[$key]['payoff_gd2_date'] . '**');
        }
    }

    return $leads;
}

function marketingLeadDataSetup($followUpLeads) {
    $leads = [];

    if (!empty($followUpLeads)) {

        foreach ($followUpLeads as $key => $value) {
            if (!empty($value->id)) {
                $leads[$key]['id'] = $value->id;
                $leads[$key]['homeowner_name'] = $value->title;
                $leads[$key]['homeowner_address'] = $value->address;
                $leads[$key]['lead'] = $value->lead_first_name . ' ' . $value->lead_last_name;
                $leads[$key]['investor'] = $value->in_first_name . ' ' . $value->in_first_name;
                $leads[$key]['admin_notes'] = $value->admin_notes;
                $leads[$key]['investore_note'] = $value->investore_note;
                $leads[$key]['appt_email'] = $value->appt_email;
                $leads[$key]['appt_phone'] = $value->appt_phone;
                $leads[$key]['marketing_mail'] = $value->marketing_mail;
                $leads[$key]['marketing_address'] = $value->marketing_address;

                $user_email = $value->marketing_mail;
                if ($user_email == '') {
                    $user_email = $value->appt_email;
                }

                $tags = CampaignTag::where('is_show_marketing', '=', 1)->get();
                foreach ($tags as $tag) {
                    $cuser = App\Models\CampaignUser::where('email_address', '=', $user_email)->first();
                    $user_data = json_decode($cuser->user_data);
                    $tags_data = [];
                    if (isset($user_data->tags)) {
                        foreach ($user_data->tags as $exiting_tag) {
                            $tags_data[] = $exiting_tag->id;
                        }
                    }
                    if (in_array($tag->tag_id, $tags_data)) {
                        $leads[$key][$tag->tag_name] = 'YES';
                    } else {
                        $leads[$key][$tag->tag_name] = 'NO';
                    }
                }
            }
        }
    }

    return $leads;
}

function purchaseLeadDataSetup($followUpLeads) {
    $leads = [];

    if (!empty($followUpLeads)) {

        foreach ($followUpLeads as $key => $value) {
            if (!empty($value->id)) {
                $leads[$key]['id'] = $value->id;
                $leads[$key]['homeowner_name'] = $value->title;
                $leads[$key]['homeowner_address'] = $value->address;
                $leads[$key]['homeowner_city'] = $value->city;
                $leads[$key]['homeowner_state'] = $value->state;
                $leads[$key]['homeowner_county'] = $value->county;
                $leads[$key]['homeowner_zip_code'] = $value->zip_code;
                $leads[$key]['contract_date'] = $value->contract_date;
                $leads[$key]['status_update'] = $value->date_status_updated;
                $leads[$key]['investor_id'] = $value->investor_id;
                $leads[$key]['follow_status'] = $value->follow_status;
                $leads[$key]['user_detail'] = $value->user_detail;
                $leads[$key]['admin_notes'] = $value->admin_notes;
                $leads[$key]['auction'] = $value->auction;
                $leads[$key]['investor_notes'] = $value->investor_notes;
                $leads[$key]['is_retired'] = $value->is_retired;

                updateCustomFiledPurchase($value->id);

                $followingCustomFields = PurchaseCustomFields::where('followup_lead_id', $value->id)->get();

                $customInput = [];

                if (!empty($followingCustomFields)) {

                    foreach ($followingCustomFields as $keynew => $followingCustomField) {
                        if (!empty($followingCustomField->PurchaseLeadViewSetp->title_slug) && !empty($followingCustomField->field_value)) {
                            if ($followingCustomField->PurchaseLeadViewSetp->input_type == 4 && is_numeric($followingCustomField->field_value)) {
                                $leads[$key][$followingCustomField->PurchaseLeadViewSetp->title_slug] = '$' . $followingCustomField->field_value;
                            } else {
                                $leads[$key][$followingCustomField->PurchaseLeadViewSetp->title_slug] = $followingCustomField->field_value;
                            }
                        }
                    }
                }
            }
        }
    }

    return $leads;
}

/**
 * Write code on Method
 *
 * @return response()
 */
function followUpLeadDataSetupOne($followUpLead) {
    $leads = [];

    if (!is_null($followUpLead)) {

        $leads['id'] = $followUpLead->id;
        $leads['homeowner_name'] = $followUpLead->title;
        $leads['homeowner_address'] = $followUpLead->address;
        $leads['homeowner_city'] = $followUpLead->city;
        $leads['homeowner_state'] = $followUpLead->state;
        $leads['homeowner_county'] = $followUpLead->county;
        $leads['homeowner_zip_code'] = $followUpLead->zip_code;
        $leads['contract_date'] = $followUpLead->contract_date;
        $leads['status_update'] = $followUpLead->date_status_updated;
        $leads['investor_id'] = $followUpLead->investor_id;
        $leads['follow_status'] = $followUpLead->follow_status;
        $leads['user_detail'] = $followUpLead->user_detail;
        $leads['admin_notes'] = $followUpLead->admin_notes;
        $leads['auction'] = $followUpLead->auction;
        $leads['investor_notes'] = $followUpLead->investor_notes;
        $leads['is_retired'] = $followUpLead->is_retired;

        updateCustomFiled($followUpLead->id);

        $followingCustomFields = FollowingCustomFields::where('followup_lead_id', $followUpLead->id)->get();

        $customInput = [];

        if (!empty($followingCustomFields)) {

            foreach ($followingCustomFields as $keynew => $followingCustomField) {
                if (!empty($followingCustomField->followUpLeadViewSetp->title_slug) && !empty($followingCustomField->field_value)) {
                    $leads[$followingCustomField->followUpLeadViewSetp->title_slug] = $followingCustomField->field_value;
                }
            }
        }
    }

    return $leads;
}

function followUpLeadDataSetupOneNew($followUpLead) {
    $leads = [];

    if (!is_null($followUpLead)) {

        $leads['id'] = $followUpLead->id;
        $leads['homeowner_name'] = $followUpLead->title;
        $leads['is_retired'] = $followUpLead->is_retired;

        $leads['homeowner_address'] = $followUpLead->address;
        $leads['homeowner_city'] = $followUpLead->city;
        $leads['homeowner_state'] = $followUpLead->state;
        $leads['homeowner_county'] = $followUpLead->county;
        $leads['homeowner_zip_code'] = $followUpLead->zip_code;
        $leads['contract_date'] = $followUpLead->contract_date;
        $leads['status_update'] = $followUpLead->date_status_updated;
        $leads['investor_id'] = $followUpLead->investor_id;
        $leads['follow_status'] = $followUpLead->follow_status;
        $leads['user_detail'] = $followUpLead->user_detail;
        $leads['admin_notes'] = $followUpLead->admin_notes;
        $leads['auction'] = $followUpLead->auction;
        $leads['investor_notes'] = $followUpLead->investor_notes;
        $leads['is_retired'] = $followUpLead->is_retired;
        $leads['userLead'] = $followUpLead->userLead;
        $leads['investor'] = $followUpLead->investor;
        $leads['leadFollowStatus'] = $followUpLead->leadFollowStatus;
        
        $leads['Appointment'] = $followUpLead->Appointment;
        $leads['latestNote'] = $followUpLead->latestNote;



        updateCustomFiled($followUpLead->id);

        $followingCustomFields = FollowingCustomFields::where('followup_lead_id', $followUpLead->id)
                ->whereIn('followup_view_id', [16, 29, 26, 30])
                ->get();

        $customInput = [];

        if (!empty($followingCustomFields)) {

            foreach ($followingCustomFields as $keynew => $followingCustomField) {
                if (!empty($followingCustomField->followUpLeadViewSetp->title_slug) && !empty($followingCustomField->field_value)) {
                    $leads[$followingCustomField->followUpLeadViewSetp->title_slug] = $followingCustomField->field_value;
                }
            }
        }
    }

    return $leads;
}

function purchaseLeadDataSetupOne($followUpLead) {
    $leads = [];

    if (!is_null($followUpLead)) {

        $leads['id'] = $followUpLead->id;
        $leads['homeowner_name'] = $followUpLead->title;
        $leads['homeowner_address'] = $followUpLead->address;
        $leads['homeowner_city'] = $followUpLead->city;
        $leads['homeowner_state'] = $followUpLead->state;
        $leads['homeowner_county'] = $followUpLead->county;
        $leads['homeowner_zip_code'] = $followUpLead->zip_code;
        $leads['contract_date'] = $followUpLead->contract_date;
        $leads['status_update'] = $followUpLead->date_status_updated;
        $leads['investor_id'] = $followUpLead->investor_id;
        $leads['follow_status'] = $followUpLead->follow_status;
        $leads['user_detail'] = $followUpLead->user_detail;
        $leads['admin_notes'] = $followUpLead->admin_notes;
        $leads['auction'] = $followUpLead->auction;
        $leads['investor_notes'] = $followUpLead->investor_notes;
        $leads['is_retired'] = $followUpLead->is_retired;

        updateCustomFiledPurchase($followUpLead->id);

        $followingCustomFields = PurchaseCustomFields::where('followup_lead_id', $followUpLead->id)->get();

        $customInput = [];

        if (!empty($followingCustomFields)) {

            foreach ($followingCustomFields as $keynew => $followingCustomField) {
                if (!empty($followingCustomField->followUpLeadViewSetp->title_slug) && !empty($followingCustomField->field_value)) {
                    $leads[$followingCustomField->followUpLeadViewSetp->title_slug] = $followingCustomField->field_value;
                }
            }
        }
    }

    return $leads;
}

/**
 * Write code on Method
 *
 * @return response()
 */
function getUser($id) {
    return User::find($id);
}

/**
 * Write code on Method
 *
 * @return response()
 */
function getFollowStatus($id) {
    return FollowStatus::find($id);
}

/**
 * Write code on Method
 *
 * @return response()
 */
function getCustomFiled($slug, $id) {
    $followupLeadViewSetp = FollowUpLeadViewSetp::where('title_slug', $slug)->first();

    $followupLeadViewSetp = FollowingCustomFields::where('followup_lead_id', $id)->where('followup_view_id', $followupLeadViewSetp->id)->first();

    return $followupLeadViewSetp;
}

function getCustomFiledPurchase($slug, $id) {
    $followupLeadViewSetp = PurchaseLeadViewSetp::where('title_slug', $slug)->first();

    $followupLeadViewSetp = PurchaseCustomFields::where('followup_lead_id', $id)->where('followup_view_id', $followupLeadViewSetp->id)->first();

    return $followupLeadViewSetp;
}

function getCustomFiledDeal($slug, $id) {
    $dealLeadViewSetp = DealLeadViewSetp::where('title_slug', $slug)->first();

    updateCustomFiledDeal($id);

    $dealLeadViewSetp = DealLeadViewCustomFields::where('deal_lead_id', $id)->where('deal_view_id', $dealLeadViewSetp->id)->first();

    return $dealLeadViewSetp;
}

function updateCustomFiledDeal($dealLeadId) {
    $dealLeadViewSetps = DealLeadViewSetp::orderBy('order', 'asc')->get();
    if (!empty($dealLeadViewSetps)) {
        foreach ($dealLeadViewSetps as $key => $dealLeadViewSetp) {
            $followingCustomField = DealLeadViewCustomFields::where('deal_lead_id', $dealLeadId)->where('deal_view_id', $dealLeadViewSetp->id)->first();

            if (is_null($followingCustomField)) {
                $input['deal_lead_id'] = $dealLeadId;
                $input['deal_view_id'] = $dealLeadViewSetp->id;

                DealLeadViewCustomFields::create($input);
            }
        }
    }

    return true;
}

/**
 * Write code on Method
 *
 * @return response()
 */
function dataViewFollowLead($input, $followingLeads, $followUpLeadViewSetps) {
    $users = User::orderby('status_id', 'desc')->where('status_id', 1)->get();
    $users = userArraySetJson($users);

    $mobileusers = User::latest()->where('status_id', 1)->where('user_group_id', 2)->get();
    $mobileusers = userArraySetJson($mobileusers);

    $statusList = FollowStatus::where('is_followup', '=', 1)->latest()->get();
    $statues = statusArraySetJson($statusList);

    $followingLeads = followUpLeadDataSetup($followingLeads);

    $followUpLeadViewSetpsSlug = FollowUpLeadViewSetp::where('is_show', 1)
            ->orderBy('order_no', 'asc')
            ->pluck('title_slug')
            ->toArray();

    $countSlug = count($followUpLeadViewSetpsSlug);

    $data = view('tenant.followinglead.tablelist', compact('followingLeads', 'users', 'mobileusers', 'followUpLeadViewSetps', 'followUpLeadViewSetpsSlug', 'countSlug', 'statues'))->render();

    return $data;
}

function dataViewFollowLeadNew($input, $followingLeads, $followUpLeadViewSetps) {
    $users = User::orderby('status_id', 'desc')
                    ->where('status_id', 1)->get();

    $mobileusers = User::latest()
            ->where('status_id', 1)
            ->where('user_group_id', 2)
            ->get();

    $statusList = FollowStatus::where('is_followup', '=', 1)
                    ->latest()->get();

    $users = userArraySetJson($users);
    $mobileusers = userArraySetJson($mobileusers);
    $statues = statusArraySetJson($statusList);

    $followingLeads = followUpLeadDataSetup($followingLeads);        

    $sortColumn = intval($input['sort_column']);

    if ($sortColumn != 0) {
        $followUpLeadViewSetps1 = FollowUpLeadViewSetp::where('id', $sortColumn)
                ->first();

        $keyToSortBy = $followUpLeadViewSetps1->title_slug;
        $sortType = $input['sort_type'];

//        $compareFunction = function($a, $b) use ($keyToSortBy) {
//            $aDate = $a[$keyToSortBy];
//            $bDate = $b[$keyToSortBy];
//
//            // Handle null values
//            if ($aDate === null && $bDate !== null) {
//                return 1;
//            } elseif ($aDate !== null && $bDate === null) {
//                return -1;
//            } elseif ($aDate === null && $bDate === null) {
//                return 0;
//            }
//            $aTimestamp = strtotime($aDate);
//            $bTimestamp = strtotime($bDate);
//
//            return $aTimestamp <=> $bTimestamp;
//        };
        
        $compareFunction = function($a, $b) use ($keyToSortBy, $sortType) {
            $aDate = $a[$keyToSortBy];
            $bDate = $b[$keyToSortBy];

            // Handle null values
            if ($aDate === null && $bDate !== null) {
                return 1;
            } elseif ($aDate !== null && $bDate === null) {
                return -1;
            } elseif ($aDate === null && $bDate === null) {
                return 0;
            }

            // Convert dates to timestamps
            $aTimestamp = $aDate;
            $bTimestamp = $bDate;

            // Sort in ascending or descending order based on $sortType
            if ($sortType === 'asc') {
                return $aTimestamp <=> $bTimestamp;
            } else {
                return $bTimestamp <=> $aTimestamp;
            }
        };

        usort($followingLeads, $compareFunction);
    }

    
    $followUpLeadViewSetpsSlug = FollowUpLeadViewSetp::where('is_show', 1)
            ->orderBy('order_no', 'asc')
            ->pluck('title_slug')
            ->toArray();

    $countSlug = count($followUpLeadViewSetpsSlug);

    $data = view('tenant.followinglead.tablelist', compact('followingLeads', 'users', 'mobileusers', 'followUpLeadViewSetps', 'followUpLeadViewSetpsSlug', 'countSlug', 'statues'))->render();

    return $data;
}

function dataViewPurchaseLead($input) {
    $users = User::orderby('status_id', 'desc')->where('status_id', 1)->get();

    $mobileusers = User::latest()->where('status_id', 1)->where('user_group_id', 2)->get();

    $statusList = FollowStatus::where('is_purchase', '=', 1)->latest()->get();

    $users = userArraySetJson($users);
    $mobileusers = userArraySetJson($mobileusers);
    $statues = statusArraySetJson($statusList);

    $followingLeads = getPurchaseLead($input);

    $followingLeads = purchaseLeadDataSetup($followingLeads);

    $followUpLeadViewSetps = PurchaseLeadViewSetp::where('is_show', 1)->orderBy('order_no', 'asc')->get();
    $followUpLeadViewSetpsSlug = PurchaseLeadViewSetp::where('is_show', 1)->orderBy('order_no', 'asc')->pluck('title_slug')->toArray();

    $countSlug = count($followUpLeadViewSetpsSlug);
    $data = view('tenant.purchaselead.tablelist', compact('followingLeads', 'users', 'mobileusers', 'followUpLeadViewSetps', 'followUpLeadViewSetpsSlug', 'countSlug', 'statues'))->render();

    return $data;
}

/**
 * Write code on Method
 *
 * @return response()
 */
function getFollowingLead($input, $isAll = null) {
//    $sortColumn = intval($input['sort_column']);
//    $followingLeads = new FollowingLead();
//    $followingLeads = $followingLeads->with('customFields');
//    $followingLeads = $followingLeads
//            ->join('following_custom_fields', function ($join) use ($sortColumn) {
//                $join->on('following_custom_fields.followup_lead_id', '=', 'following_leads.id')
//                ->where('following_custom_fields.followup_view_id', '=', $sortColumn);
//            })
//            ->orderBy('following_custom_fields.field_value', $input['sort_type'])
//            ->select('following_leads.*') 
//            ->paginate(10);
//
//    return $followingLeads;

    $followingLeads = new FollowingLead();
    $followingLeads = $followingLeads->with('customFields');

    if (!empty($input['search_text'])) {
        $followingLeads = $followingLeads->where(function($querysub) use($input) {
            $querysub->where('title', 'like', '%' . $input['search_text'] . '%')
                    ->orWhere('formatted_address', 'like', '%' . $input['search_text'] . '%')
                    ->orWhere('address', 'like', '%' . $input['search_text'] . '%')
                    ->orWhere('investor_notes', 'like', '%' . $input['search_text'] . '%')
                    ->orWhere('admin_notes', 'like', '%' . $input['search_text'] . '%');
        });
    }

    if (!empty($input['user_id_search'])) {
        $userIdSearch = is_array($input['user_id_search']) ? $input['user_id_search'] : explode(',', $input['user_id_search']);
        $followingLeads = $followingLeads->whereIn('investor_id', $userIdSearch)
                ->orWhereIn('user_detail', $userIdSearch);
    }

    if (!empty($input['leads_ids'])) {
        $followingLeads = $followingLeads->whereIn('id', $input['leads_ids']);
    }

    if (!empty($input['lead_status_id'])) {
        $leadStatusId = is_array($input['lead_status_id']) ? $input['lead_status_id'] : explode(',', $input['lead_status_id']);
        $followingLeads = $followingLeads->whereIn('follow_status', $leadStatusId);
    }

    $auctionDate = json_decode($input['auction_date'], true);
    if (!empty($auctionDate)) {
        $start = dateFormatYMDtoMDY($auctionDate['start']);
        $end = dateFormatYMDtoMDY($auctionDate['end']);
        $followingLeads = $followingLeads->whereBetween('auction', [$start, $end]);
    }

    $statusDateRange = json_decode($input['status_date_range'], true);
    if (!empty($statusDateRange)) {
        $followingLeads = $followingLeads->whereDate('date_status_updated', '>=', $statusDateRange['start'])
                ->whereDate('date_status_updated', '<=', $statusDateRange['end']);
    }

    $followingLeads->where('is_lead_up', '=', 0)
            ->where('is_deal', '=', 0)
            ->where('is_purchase', '=', 0)
            ->where('is_marketing', '=', 0);

    if (!empty($input['sort_column']) && !empty($input['sort_type'])) {
        switch ($input['sort_column']) {
            case 'homeowner_name':
                $followingLeads = $followingLeads->orderBy('title', $input['sort_type']);
                break;

            case 'homeowner_address':
                $followingLeads = $followingLeads->orderBy('address', $input['sort_type']);
                break;

            case 'notes_and_actions':
                $followingLeads = $followingLeads->orderBy('admin_notes', $input['sort_type']);
                break;

            case 'status_update':
                $followingLeads = $followingLeads->orderBy('date_status_updated', $input['sort_type']);
                break;

            case 'auction_date':
                $followingLeads = $followingLeads->orderBy('auction', $input['sort_type']);
                break;

            case 'contract_date':
                $followingLeads = $followingLeads->orderBy('contract_date', $input['sort_type']);
                break;

            case 'status':
                $followingLeads = $followingLeads->orderBy('follow_status', $input['sort_type']);
                break;

            case '17':
                $followingLeads = $followingLeads->has('userLead')
                        ->with(['userLead' => function ($query) use ($input) {
                        $query->orderBy('first_name', $input['sort_type']);
                    }]);
                $followingLeads = $followingLeads->join('user', 'user.id', '=', 'following_leads.user_detail')
                        ->orderBy('user.first_name', $input['sort_type']);
                break;

            case 'investor':
                $followingLeads = $followingLeads->join('user', 'user.id', '=', 'following_leads.investor_id')
                        ->orderBy('user.first_name', $input['sort_type']);
//                $followingLeads = $followingLeads->orderBy('investor_id', $input['sort_type']);
                break;

            case 'investor_notes':
                $followingLeads = $followingLeads->orderBy('investor_notes', $input['sort_type']);
                break;

            case 'is_retired':
                $followingLeads = $followingLeads->orderBy('is_retired', $input['sort_type']);
                break;

            default:
                $sortColumn = intval($input['sort_column']);

                $followingLeads = $followingLeads->join('following_custom_fields', function ($join) use ($sortColumn) {
                            $join->on('following_custom_fields.followup_lead_id', '=', 'following_leads.id')
                            ->where('following_custom_fields.followup_view_id', '=', $sortColumn);
                        })->orderBy('following_custom_fields.field_value', $input['sort_type'])
                        ->select('following_leads.*', 'following_custom_fields.field_value');
                break;
        }
    } else {
        $followingLeads = $followingLeads->latest();
    }

    if ($isAll === null) {
        $displayedPerScreen = settingForRecordsDisplayedPerScreen();
        $perPage = isset($displayedPerScreen->followup_lead_management) ? $displayedPerScreen->followup_lead_management : 10;
        $followingLeads = $followingLeads->paginate($perPage);
    } else {
        $followingLeads = $followingLeads->get();
    }

    return $followingLeads;
}

function getFollowingLeadOld($input, $isAll = null) {


    $followingLeads = new FollowingLead();
    $followingLeads = $followingLeads->with('customFields');
    if (!empty($input['search_text'])) {
        $followingLeads = $followingLeads->where(function($querysub) use($input) {
            $querysub->orwhere('title', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('formatted_address', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('address', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('investor_notes', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('admin_notes', 'like', '%' . $input['search_text'] . '%');
        });
    }

    if (!empty($input['user_id_search'])) {
        if (!is_array($input['user_id_search'])) {
            $input['user_id_search'] = explode(',', $input['user_id_search']);
        }
        $followingLeads = $followingLeads->orwhereIn('investor_id', $input['user_id_search'])
                ->orwhereIn('user_detail', $input['user_id_search']);
    }

    if (!empty($input['leads_ids'])) {
        $followingLeads = $followingLeads->whereIn('id', $input['leads_ids']);
    }

    if (!empty($input['lead_status_id'])) {
        if (!is_array($input['lead_status_id'])) {
            $input['lead_status_id'] = explode(',', $input['lead_status_id']);
        }
        $followingLeads = $followingLeads->whereIn('follow_status', $input['lead_status_id']);
    }

    $auctionDate = json_decode($input['auction_date']);

    if (!empty($auctionDate)) {
        $start = dateFormatYMDtoMDY($auctionDate->start);
        $end = dateFormatYMDtoMDY($auctionDate->end);
        $followingLeads = $followingLeads->whereBetween('auction', [$start, $end]);
    }

    $statusDateRange = json_decode($input['status_date_range']);

    if (!empty($statusDateRange)) {
        $followingLeads = $followingLeads->whereDate('date_status_updated', '>=', $statusDateRange->start)
                ->whereDate('date_status_updated', '<=', $statusDateRange->end);
    }

    $followingLeads->where('is_lead_up', '=', 0);
    $followingLeads->where('is_deal', '=', 0);
    $followingLeads->where('is_purchase', '=', 0);
    $followingLeads->where('is_marketing', '=', 0);


    if (!empty($input['sort_column']) && !empty($input['sort_type'])) {
        switch ($input['sort_column']) {
            case 'homeowner_name':
                $followingLeads = $followingLeads->orderby('title', $input['sort_type']);
                break;

            case 'homeowner_address':
                $followingLeads = $followingLeads->orderby('address', $input['sort_type']);
                break;

            case 'notes_and_actions':
                $followingLeads = $followingLeads->orderby('admin_notes', $input['sort_type']);
                break;

            case 'status_update':
                $followingLeads = $followingLeads->orderby('date_status_updated', $input['sort_type']);
                break;

            case 'auction_date':
                $followingLeads = $followingLeads->orderby('auction', $input['sort_type']);
                break;

            case 'contract_date':
                $followingLeads = $followingLeads->orderby('contract_date', $input['sort_type']);
                break;

            case 'status':
                $followingLeads = $followingLeads->orderby('follow_status', $input['sort_type']);
                break;

            case '17':
                $followingLeads = $followingLeads->has('userLead')->with(['userLead' => function ($query) use ($input) {
                        $query->orderBy('first_name', $input['sort_type']); // Order by created_at in userLead relation
                    }]);
                break;

            case 'investor':
                $followingLeads = $followingLeads->orderby('investor_id', $input['sort_type']);
                break;

            case 'investor_notes':
                $followingLeads = $followingLeads->orderby('investor_notes', $input['sort_type']);
                break;

            case 'is_retired':
                $followingLeads = $followingLeads->orderby('is_retired', $input['sort_type']);
                break;

            default:
                $sortColumn = intval($input['sort_column']);
                $followingLeads = $followingLeads->leftjoin("following_custom_fields", function($join) use ($sortColumn) {
                    $join->select('following_custom_fields.field_value as new_field_value')
                            ->on("following_leads.id", "=", "following_custom_fields.followup_lead_id")
                            ->where('following_custom_fields.followup_view_id', '=', $sortColumn);
                });
                $followingLeads = $followingLeads->orderByRaw('ISNULL(following_custom_fields.field_value), following_custom_fields.field_value ' . $input['sort_type']);
//                $followingLeads = $followingLeads->latest();
                break;
        }
    } else {
        $followingLeads = $followingLeads->latest();
    }

    if ($isAll == null) {
        $DisplayedPerScreen = settingForRecordsDisplayedPerScreen();
        if (isset($DisplayedPerScreen->followup_lead_management)) {
            $followingLeads = $followingLeads->paginate($DisplayedPerScreen->followup_lead_management);

            return $followingLeads;
        } else {
            $followingLeads = $followingLeads->paginate(10);
            return $followingLeads;
        }
    }
    return $followingLeads->get();
}

function getMarketingLead($input, $isAll = null) {
    $marketingLeads = new Marketing();

    $marketingLeads = $marketingLeads->select('marketings.*', 'user.first_name as in_first_name', 'user.last_name as in_last_name', 'user2.first_name as lead_first_name', 'user2.last_name as lead_last_name')
            ->leftjoin("user", "marketings.investore_id", "=", "user.id")
            ->leftjoin("user as user2", "marketings.user_detail", "=", "user2.id");
    if (!empty($input['search_text'])) {
        $marketingLeads = $marketingLeads->where(function($querysub) use($input) {
            $querysub->orwhere('title', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('address', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('admin_notes', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('investore_note', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('appt_email', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('appt_phone', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('marketing_mail', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('marketing_address', 'like', '%' . $input['search_text'] . '%');
        });
    }

    if (!empty($input['user_id_search'])) {
        $marketingLeads = $marketingLeads->where(function($querysub) use($input) {
            $querysub->orWhereIn('user_detail', $input['user_id_search'])
                    ->orWhereIn('investore_id', $input['user_id_search']);
        });
    }

    if (!empty($input['leads_ids'])) {
        $marketingLeads = $marketingLeads->whereIn('marketings.id', $input['leads_ids']);
    }

    if (!empty($input['lead_status_id'])) {
        $marketingLeads = $marketingLeads->whereIn('marketings.follow_status', $input['lead_status_id']);
    }


    if (!empty($input['sort_column']) && !empty($input['sort_type'])) {
        switch ($input['sort_column']) {
            case 'homeowner_name':
                $marketingLeads = $marketingLeads->orderby('marketings.title', $input['sort_type']);
                break;

            case 'address':
                $marketingLeads = $marketingLeads->orderby('marketings.formatted_address', $input['sort_type']);
                break;

            case 'appt_email':
                $marketingLeads = $marketingLeads->orderby('marketings.appt_email', $input['sort_type']);
                break;

            case 'appt_phone':
                $marketingLeads = $marketingLeads->orderby('marketings.appt_phone', $input['sort_type']);
                break;

            case 'marketing_email':
                $marketingLeads = $marketingLeads->orderby('marketings.marketing_mail', $input['sort_type']);
                break;

            case 'marketing_address':
                $marketingLeads = $marketingLeads->orderby('marketings.marketing_address', $input['sort_type']);
                break;

            case 'notes_and_actions':
                $marketingLeads = $marketingLeads->orderby('marketings.admin_notes', $input['sort_type']);
                break;

            case 'investor_notes':
                $marketingLeads = $marketingLeads->orderby('marketings.investore_note', $input['sort_type']);
                break;

            case 'auction_date':
                $marketingLeads = $marketingLeads->orderby('marketings.auction', $input['sort_type']);
                break;

            case 'lead':
                $marketingLeads = $marketingLeads->orderby('user2.first_name', $input['sort_type']);
                break;

            case 'investor':
                $marketingLeads = $marketingLeads->orderby('user.first_name', $input['sort_type']);
                break;

            default:
                break;
        }
    } else {
        $marketings = $marketings->latest();
    }

    $marketingLeads = $marketingLeads->where('is_followup', '=', 0);
    if ($isAll == null) {
        $DisplayedPerScreen = settingForRecordsDisplayedPerScreen();
        if (isset($DisplayedPerScreen->marketing_lead_management)) {
            return $marketingLeads->paginate($DisplayedPerScreen->marketing_lead_management);
        } else {
            return $marketingLeads->paginate(10);
        }
    }
    $marketingLeads = $marketingLeads->get();
    return $marketingLeads;
}

function getPurchaseLead($input, $isAll = null) {

    $followingLeads = new PurchaseLead();


    if (!empty($input['search_text'])) {
        $followingLeads = $followingLeads->where(function($querysub) use($input) {
            $querysub->orwhere('purchase_leads.title', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('purchase_leads.formatted_address', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('purchase_leads.investor_notes', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('purchase_leads.admin_notes', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('purchase_leads.address', 'like', '%' . $input['search_text'] . '%');
        });
    }

    if (!empty($input['user_id_search'])) {

        if (!is_array($input['user_id_search'])) {
            $input['user_id_search'] = explode(',', $input['user_id_search']);
        }

        $followingLeads = $followingLeads->orwhereIn('purchase_leads.investor_id', $input['user_id_search'])
                ->orwhereIn('purchase_leads.user_detail', $input['user_id_search']);
    }

    if (!empty($input['leads_ids'])) {
        $followingLeads = $followingLeads->whereIn('purchase_leads.id', $input['leads_ids']);
    }

    if (!empty($input['lead_status_id'])) {
        if (!is_array($input['lead_status_id'])) {
            $input['lead_status_id'] = explode(',', $input['lead_status_id']);
        }
        $followingLeads = $followingLeads->whereIn('purchase_leads.follow_status', $input['lead_status_id']);
    }

    $auctionDate = json_decode($input['auction_date']);

    if (!empty($auctionDate)) {
        $start = dateFormatYMDtoMDY($auctionDate->start);
        $end = dateFormatYMDtoMDY($auctionDate->end);

        $followingLeads = $followingLeads->whereBetween('purchase_leads.auction', [$start, $end]);
    }


    if (!empty($input['sort_column']) && !empty($input['sort_type'])) {
        switch ($input['sort_column']) {
            case 'homeowner_name':
                $followingLeads = $followingLeads->orderby('purchase_leads.title', $input['sort_type']);
                break;

            case 'homeowner_address':
                $followingLeads = $followingLeads->orderby('purchase_leads.formatted_address', $input['sort_type']);
                break;

            case 'notes_and_actions':
                $followingLeads = $followingLeads->orderby('purchase_leads.admin_notes', $input['sort_type']);

                break;

            case 'status_update':
                $followingLeads = $followingLeads->orderby('purchase_leads.date_status_updated', $input['sort_type']);

                break;

            case 'auction_date':
                $followingLeads = $followingLeads->orderby('purchase_leads.auction', $input['sort_type']);

                break;

            case 'contract_date':
                $followingLeads = $followingLeads->orderby('purchase_leads.contract_date', $input['sort_type']);

                break;

            case 'status':
                $followingLeads = $followingLeads->orderby('purchase_leads.follow_status', $input['sort_type']);

                break;

            case 'lead':
                $followingLeads = $followingLeads->orderby('purchase_leads.user_detail', $input['sort_type']);

            case 'investor':
                $followingLeads = $followingLeads->orderby('purchase_leads.investor_id', $input['sort_type']);

            case 'investor_notes':
                $followingLeads = $followingLeads->orderby('purchase_leads.investor_notes', $input['sort_type']);

                break;

            case 'is_retired':
                $followingLeads = $followingLeads->orderby('is_retired', $input['sort_type']);

                break;

            default:
                // code...
                break;
        }

        $PurchaseLeadViewSetp = PurchaseLeadViewSetp::where('title_slug', '=', $input['sort_column'])->first();

        if (isset($PurchaseLeadViewSetp->id)) {
            $followingLeads = $followingLeads->select('purchase_leads.*', 'purchase_custom_fields.field_value');
            $followingLeads = $followingLeads->join('purchase_custom_fields', 'purchase_leads.id', '=', 'purchase_custom_fields.followup_lead_id');


            if ($input['sort_column'] == 'contract_date' OR $input['sort_column'] == 'auction_date' OR $input['sort_column'] == 'date_to_follow_up' OR $input['sort_column'] == 'purchase_date') {
                $followingLeads = $followingLeads->where('purchase_custom_fields.followup_view_id', $PurchaseLeadViewSetp->id)
                        ->orderByRaw('DATE(purchase_custom_fields.field_value) ' . $input['sort_type']);
            } else {
                $followingLeads = $followingLeads->where('purchase_custom_fields.followup_view_id', $PurchaseLeadViewSetp->id)
                        ->orderby('purchase_custom_fields.field_value', $input['sort_type']);
            }
        }
    } else {
        $followingLeads = $followingLeads->latest();
    }

    $statusDateRange = json_decode($input['status_date_range']);

    if (!empty($statusDateRange)) {
        $followingLeads = $followingLeads->whereDate('purchase_leads.date_status_updated', '>=', $statusDateRange->start)
                ->whereDate('purchase_leads.date_status_updated', '<=', $statusDateRange->end);
    }
    $followingLeads->where('purchase_leads.is_lead_up', '=', 0);
    $followingLeads->where('purchase_leads.is_deal', '=', 0);
    $followingLeads->where('purchase_leads.is_followup', '=', 0);

    if ($isAll == null) {
        $DisplayedPerScreen = settingForRecordsDisplayedPerScreen();
        if (isset($DisplayedPerScreen->purchase_lead_management)) {
            return $followingLeads->groupBy('purchase_leads.id')->paginate($DisplayedPerScreen->purchase_lead_management);
        } else {
            return $followingLeads->groupBy('purchase_leads.id')->paginate(10);
        }
    }

    return $followingLeads->get();
}

function getFollowingLeadAll($input) {
    $followingLeads = new FollowingLead();

    if (!empty($input['search_text'])) {
        $followingLeads = $followingLeads->where(function($querysub) use($input) {
            $querysub->orwhere('title', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('formatted_address', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('investor_notes', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('admin_notes', 'like', '%' . $input['search_text'] . '%');
        });
    }

    if (!empty($input['user_id_search'])) {
        $followingLeads = $followingLeads->orwhereIn('investor_id', $input['user_id_search'])
                ->orwhereIn('user_detail', $input['user_id_search']);
    }

    if (!empty($input['lead_status_id'])) {
        $followingLeads = $followingLeads->whereIn('follow_status', $input['lead_status_id']);
    }

    if (!empty($input['leads_ids'])) {
        $followingLeads = $followingLeads->whereIn('id', $input['leads_ids']);
    }

    $auctionDate = json_decode($input['auction_date']);

    if (!empty($auctionDate)) {

        $start = dateFormatYMDtoMDY($auctionDate->start);
        $end = dateFormatYMDtoMDY($auctionDate->end);

        $followingLeads = $followingLeads->whereBetween('auction', [$start, $end]);
    }

    if (!empty($input['sort_column']) && !empty($input['sort_type'])) {
        switch ($input['sort_column']) {
            case 'homeowner_name':
                $followingLeads = $followingLeads->orderby('title', $input['sort_type']);
                break;

            case 'homeowner_address':
                $followingLeads = $followingLeads->orderby('formatted_address', $input['sort_type']);
                break;

            case 'notes_and_actions':
                $followingLeads = $followingLeads->orderby('admin_notes', $input['sort_type']);

                break;

            case 'status_update':
                $followingLeads = $followingLeads->orderby('date_status_updated', $input['sort_type']);

                break;

            case 'auction_date':
                $followingLeads = $followingLeads->orderby('auction', $input['sort_type']);

                break;

            case 'contract_date':
                $followingLeads = $followingLeads->orderby('contract_date', $input['sort_type']);

                break;

            case 'status':
                $followingLeads = $followingLeads->orderby('follow_status', $input['sort_type']);

                break;

            case 'lead':
                $followingLeads = $followingLeads->orderby('user_detail', $input['sort_type']);

            case 'investor':
                $followingLeads = $followingLeads->orderby('investor_id', $input['sort_type']);

            case 'investor_notes':
                $followingLeads = $followingLeads->orderby('investor_notes', $input['sort_type']);

                break;

            case 'is_retired':
                $followingLeads = $followingLeads->orderby('is_retired', $input['sort_type']);

                break;

            default:
                // code...
                break;
        }
    } else {
        $followingLeads = $followingLeads->latest();
    }

    $statusDateRange = json_decode($input['status_date_range']);

    if (!empty($statusDateRange)) {
        $followingLeads = $followingLeads->whereDate('date_status_updated', '>=', $statusDateRange->start)
                ->whereDate('date_status_updated', '<=', $statusDateRange->end);
    }
//    $followingLeads->where('is_lead_up', '=', 0);    
    return $followingLeads = $followingLeads->get();
}

function getFollowingLeadAllExport($input) {
    $followingLeads = new FollowingLead();

    if (!empty($input['search_text'])) {
        $followingLeads = $followingLeads->where(function($querysub) use($input) {
            $querysub->orwhere('title', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('formatted_address', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('investor_notes', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('admin_notes', 'like', '%' . $input['search_text'] . '%');
        });
    }

    if (!empty($input['user_id_search'])) {
        $followingLeads = $followingLeads->orwhereIn('investor_id', $input['user_id_search'])
                ->orwhereIn('user_detail', $input['user_id_search']);
    }

    if (!empty($input['lead_status_id'])) {
        $followingLeads = $followingLeads->whereIn('follow_status', $input['lead_status_id']);
    }

    if (!empty($input['leads_ids'])) {
        $followingLeads = $followingLeads->whereIn('id', $input['leads_ids']);
    }

    $auctionDate = json_decode($input['auction_date']);

    if (!empty($auctionDate)) {

        $start = dateFormatYMDtoMDY($auctionDate->start);
        $end = dateFormatYMDtoMDY($auctionDate->end);

        $followingLeads = $followingLeads->whereBetween('auction', [$start, $end]);
    }

    if (!empty($input['sort_column']) && !empty($input['sort_type'])) {
        switch ($input['sort_column']) {
            case 'homeowner_name':
                $followingLeads = $followingLeads->orderby('title', $input['sort_type']);
                break;

            case 'homeowner_address':
                $followingLeads = $followingLeads->orderby('formatted_address', $input['sort_type']);
                break;

            case 'notes_and_actions':
                $followingLeads = $followingLeads->orderby('admin_notes', $input['sort_type']);

                break;

            case 'status_update':
                $followingLeads = $followingLeads->orderby('date_status_updated', $input['sort_type']);

                break;

            case 'auction_date':
                $followingLeads = $followingLeads->orderby('auction', $input['sort_type']);

                break;

            case 'contract_date':
                $followingLeads = $followingLeads->orderby('contract_date', $input['sort_type']);

                break;

            case 'status':
                $followingLeads = $followingLeads->orderby('follow_status', $input['sort_type']);

                break;

            case 'lead':
                $followingLeads = $followingLeads->orderby('user_detail', $input['sort_type']);

            case 'investor':
                $followingLeads = $followingLeads->orderby('investor_id', $input['sort_type']);

            case 'investor_notes':
                $followingLeads = $followingLeads->orderby('investor_notes', $input['sort_type']);

                break;

            case 'is_retired':
                $followingLeads = $followingLeads->orderby('is_retired', $input['sort_type']);

                break;

            default:
                // code...
                break;
        }
    } else {
        $followingLeads = $followingLeads->latest();
    }

    $statusDateRange = json_decode($input['status_date_range']);

    if (!empty($statusDateRange)) {
        $followingLeads = $followingLeads->whereDate('date_status_updated', '>=', $statusDateRange->start)
                ->whereDate('date_status_updated', '<=', $statusDateRange->end);
    }
    $followingLeads->where('is_lead_up', '=', 0);
    $followingLeads->where('is_marketing', '=', 0);
    $followingLeads->where('is_deal', '=', 0);
    $followingLeads->where('is_purchase', '=', 0);
    return $followingLeads = $followingLeads->get();
}

function getPurchaseLeadAll($input) {
    $followingLeads = new PurchaseLead();

    if (!empty($input['search_text'])) {
        $followingLeads = $followingLeads->where(function($querysub) use($input) {
            $querysub->orwhere('title', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('formatted_address', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('investor_notes', 'like', '%' . $input['search_text'] . '%')
                    ->orwhere('admin_notes', 'like', '%' . $input['search_text'] . '%');
        });
    }

    if (!empty($input['user_id_search'])) {
        $followingLeads = $followingLeads->orwhereIn('investor_id', $input['user_id_search'])
                ->orwhereIn('user_detail', $input['user_id_search']);
    }

    if (!empty($input['lead_status_id'])) {
        $followingLeads = $followingLeads->whereIn('follow_status', $input['lead_status_id']);
    }

    if (!empty($input['leads_ids'])) {
        $followingLeads = $followingLeads->whereIn('id', $input['leads_ids']);
    }

    $auctionDate = json_decode($input['auction_date']);

    if (!empty($auctionDate)) {

        $start = dateFormatYMDtoMDY($auctionDate->start);
        $end = dateFormatYMDtoMDY($auctionDate->end);

        $followingLeads = $followingLeads->whereBetween('auction', [$start, $end]);
    }

    if (!empty($input['sort_column']) && !empty($input['sort_type'])) {
        switch ($input['sort_column']) {
            case 'homeowner_name':
                $followingLeads = $followingLeads->orderby('title', $input['sort_type']);
                break;

            case 'homeowner_address':
                $followingLeads = $followingLeads->orderby('formatted_address', $input['sort_type']);
                break;

            case 'notes_and_actions':
                $followingLeads = $followingLeads->orderby('admin_notes', $input['sort_type']);

                break;

            case 'status_update':
                $followingLeads = $followingLeads->orderby('date_status_updated', $input['sort_type']);

                break;

            case 'auction_date':
                $followingLeads = $followingLeads->orderby('auction', $input['sort_type']);

                break;

            case 'contract_date':
                $followingLeads = $followingLeads->orderby('contract_date', $input['sort_type']);

                break;

            case 'status':
                $followingLeads = $followingLeads->orderby('follow_status', $input['sort_type']);

                break;

            case 'lead':
                $followingLeads = $followingLeads->orderby('user_detail', $input['sort_type']);

            case 'investor':
                $followingLeads = $followingLeads->orderby('investor_id', $input['sort_type']);

            case 'investor_notes':
                $followingLeads = $followingLeads->orderby('investor_notes', $input['sort_type']);

                break;

            case 'is_retired':
                $followingLeads = $followingLeads->orderby('is_retired', $input['sort_type']);

                break;

            default:
                // code...
                break;
        }
    } else {
        $followingLeads = $followingLeads->latest();
    }

    $statusDateRange = json_decode($input['status_date_range']);

    if (!empty($statusDateRange)) {
        $followingLeads = $followingLeads->whereDate('date_status_updated', '>=', $statusDateRange->start)
                ->whereDate('date_status_updated', '<=', $statusDateRange->end);
    }
//    $followingLeads->where('is_lead_up', '=', 0);
    return $followingLeads = $followingLeads->get();
}

/**
 * Write code on Method
 *
 * @return response()
 */
function dateTimezoneChange($setDate) {
    if (empty($setDate)) {
        return '';
    }

    $check_daylight = date('d-M-Y');
    $date = new DateTime($check_daylight . ' America/Los_Angeles');
    $check_daylight_result = $date->format('I');

    $today = '';

    if ($check_daylight_result == 1) {
        $timestamp1 = date('Y-m-d H:i:s', strtotime($setDate));
        $today = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp1);
        $today->setTimezone('CST');
        $timestamp = strtotime($today) + 60 * 60;
        $today = date('Y-m-d H:i:s', $timestamp);
    } else {
        $timestamp1 = date('Y-m-d H:i:s', strtotime($setDate));
        $today = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp1);
        $today->setTimezone('CST');
        $timestamp = strtotime($today) + 60 * 60;
        $today = date('Y-m-d H:i:s', $timestamp);
    }

    return dateFormatYMDtoMDYTimezone($today);
}


function dateTimezoneChangeFullDateTimeReturn($setDate) {
    if (empty($setDate)) {
        return '';
    }

    $check_daylight = date('d-M-Y');
    $date = new DateTime($check_daylight . ' America/Los_Angeles');
    $check_daylight_result = $date->format('I');

    $today = '';

    if ($check_daylight_result == 1) {
        $timestamp1 = date('Y-m-d H:i:s', strtotime($setDate));
        $today = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp1);
        $today->setTimezone('CST');
        $timestamp = strtotime($today) + 60 * 60;
        $today = date('Y-m-d H:i:s', $timestamp);
    } else {
        $timestamp1 = date('Y-m-d H:i:s', strtotime($setDate));
        $today = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp1);
        $today->setTimezone('CST');
        $timestamp = strtotime($today) + 60 * 60;
        $today = date('Y-m-d H:i:s', $timestamp);
    }

    return dateFormatYMDtoMDYTimezoneFullDateTimeReturn($today);
}

function dateFormatYMDtoMDYTimezoneFullDateTimeReturn($date) {
    if (empty($date)) {
        return '';
    }

    return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('m/d/Y H:i:s');
}

/**
 * Write code on Method
 *
 * @return response()
 */
function dateFormatYMDtoMDYTimezone($date) {
    if (empty($date)) {
        return '';
    }

    return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('m/d/Y h:i A');
}

/**
 * Write code on Method
 *
 * @return response()
 */
function updateLeadAuctionDate() {
    $leads = Lead::latest()->get();

    if (!empty($leads)) {
        foreach ($leads as $key => $lead) {

            if (!empty($lead->auction)) {

                $auctionDate = '';

                $date = explode('/', $lead->auction);


                if (!empty($date) && strlen($date[1]) == 1) {
                    $month = $date[1];
                    $date[1] = '0' . $month;
                }

                if (!empty($date) && strlen($date[0]) == 1) {
                    $no = $date[0];
                    $date[0] = '0' . $no;
                }

                if (!empty($date[1]) && checkdate($date[0], $date[1], $date[2])) {
                    if (!isValidDate($lead->auction)) {
                        $lead->auction = Carbon::parse($lead->auction)->format('m/d/Y');
                        $lead->save();
                    }
                }
            }
        }
    }
}

function updateLeadAuctionDateNew() {
    $tenDaysAgo = Carbon::now()->subDays(7);

    $leads = Lead::where('created_at', '>=', $tenDaysAgo)
            ->orWhere('updated_at', '>=', $tenDaysAgo)
            ->get();

    if (!empty($leads)) {
        foreach ($leads as $key => $lead) {

            if (!empty($lead->auction)) {

                $auctionDate = '';
                $date = explode('/', $lead->auction);
                if (!empty($date) && strlen($date[1]) == 1) {
                    $month = $date[1];
                    $date[1] = '0' . $month;
                }
                if (!empty($date) && strlen($date[0]) == 1) {
                    $no = $date[0];
                    $date[0] = '0' . $no;
                }
                if (!empty($date[1]) && checkdate($date[0], $date[1], $date[2])) {
                    if (!isValidDate($lead->auction)) {
                        $lead->auction = Carbon::parse($lead->auction)->format('m/d/Y');
                        $lead->save();
                    }
                }

                $date = explode('/', $lead->auction);
                if (!empty($date) && strlen($date[1]) == 1) {
                    $month = $date[1];
                    $date[1] = '0' . $month;
                }
                if (!empty($date) && strlen($date[0]) == 1) {
                    $no = $date[0];
                    $date[0] = '0' . $no;
                }
                if (!empty($date[1]) && checkdate($date[0], $date[1], $date[2])) {
                    if (isValidDate($lead->auction)) {
                        if ($lead->auction_date != dateFormatMDYtoYMD($lead->auction)) {
                            $lead->auction_date = dateFormatMDYtoYMD($lead->auction);
                            $lead->save();
                        }
                    }
                }
            }
        }
    }
}

/**
 * Write code on Method
 *
 * @return response()
 */
function isValidDate(string $date, string $format = 'm/d/Y'): bool {
    $dateObj = Carbon::createFromFormat($format, $date);
    return $dateObj && $dateObj->format($format) == $date;
}

/**
 * Write code on Method
 *
 * @return response()
 */
function storeDateAuctionDateFormat() {
    $leads = Lead::latest()->get();

    if (!empty($leads)) {
        foreach ($leads as $key => $lead) {

            $date = explode('/', $lead->auction);

            if (!empty($date) && strlen($date[1]) == 1) {
                $month = $date[1];
                $date[1] = '0' . $month;
            }

            if (!empty($date) && strlen($date[0]) == 1) {
                $no = $date[0];
                $date[0] = '0' . $no;
            }

            if (!empty($date[1]) && checkdate($date[0], $date[1], $date[2])) {
                if (isValidDate($lead->auction)) {
                    if ($lead->auction_date != dateFormatMDYtoYMD($lead->auction)) {
                        $lead->auction_date = dateFormatMDYtoYMD($lead->auction);
                        $lead->save();
                    }
                }
            }
        }
    }
}

function getMonthList($dates = '') {
    $months = [];

    if (!empty($dateinput)) {

        $start = new DateTime($dateinput->start);
        $start->modify('first day of this month');
        $end = new DateTime($dateinput->start);
        $end->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($start, $interval, $end);

        foreach ($period as $dt) {
            array_push($months, $dt->format("Y-m"));
        }
    } else {

        $months = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];
    }

    return $months;
}

/**
 * Write code for dynamic Date fromate change
 *
 * @return response()
 */
function dynamicDateFormat($date, $toType) {
    if (empty($date)) {
        return '-';
    }

    return Carbon::parse($date)
                    ->format(getDateFormat($toType));
}

/**
 * Write code for get Date Format
 *
 * @return response()
 */
function getDateFormat($type) {
    $dateFormat = [
        1 => 'Y-m-d H:i:s',
        2 => 'Y-m-d',
        3 => 'm/d/Y - H:i A',
        4 => 'd/m/Y - H:i A',
        5 => 'm/d/Y',
        6 => 'd/m/Y',
        7 => 'h:i A',
        8 => 'h:i:s',
        9 => 'H:i:s',
    ];

    return $dateFormat[$type];
}

/**
 * Write code on Method
 *
 * @return response()
 */
function createLeadHistory($leadId, $is_retired, $user) {
    $lead = Lead::find($leadId);

    if (!is_null($lead)) {
        $input['lead_id'] = $lead->id;
        $input['assign_id'] = $user->id ?? '';
        $input['status_id'] = 0;
        $input['title'] = 'Lead Unretired.';
        $input['followup_status_id'] = 0;

        if ($is_retired == 1) {
            $input['title'] = 'Lead Is Retired.';
        }

        LeadHistory::create($input);
    }

    return true;
}

/**
 * Write code on Method
 *
 * @return response()
 */
function createLeadHistoryTitle($leadId, $title) {
    $lead = Lead::find($leadId);

    if (!is_null($lead)) {
        $input['lead_id'] = $lead->id;
        $input['assign_id'] = session()->get('user')->id ?? '';
        $input['status_id'] = 0;
        $input['title'] = $title;

        LeadHistory::create($input);
    }

    return true;
}

/**
 * write code for.
 *
 * @param  \Illuminate\Http\Request  
 * @return \Illuminate\Http\Response
 * @author <>
 */
function createMarketingCampaign($id) {
    $campaigns = Campaign::latest()
            ->get();

    if (!empty($campaigns)) {
        foreach ($campaigns as $key => $campaign) {
            $input['campaign_id'] = $campaign->campaign_id;
            $input['campaign_db_id'] = $campaign->id;
            $input['marketing_id'] = $id;

            $marketingCampaign = MarketingCampaign::where('campaign_id', $campaign->campaign_id)
                    ->where('marketing_id', $input['marketing_id'])
                    ->first();

            if (is_null($marketingCampaign)) {
                MarketingCampaign::create($input);
            }
        }
    }

    return true;
}

/**
 * write code for.
 *
 * @param  \Illuminate\Http\Request  
 * @return \Illuminate\Http\Response
 * @author <>
 */
function marketingCampaignObj() {
    $mailchimpMarketing = new \MailchimpMarketing\ApiClient();

    $mailchimpMarketing = $mailchimpMarketing->setConfig([
        'apiKey' => env('MAILCHIMP_API_NO_SERVER_KEY'),
        'server' => env('MAILCHIMP_SERVER_PREFIX'),
    ]);

    return $mailchimpMarketing;
}

function membersUpdateMailChimp($body, $href) {

    $listId = env('MAILCHIMP_LIST_ID');

    $mailchimp_key = env('MAILCHIMP_API_KEY');

    $body = json_encode($body);

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $href,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PATCH',
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Basic ' . $mailchimp_key
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    return true;
}

function membersUpdateTagMailChimp($body, $href) {

    $listId = env('MAILCHIMP_LIST_ID');

    $mailchimp_key = env('MAILCHIMP_API_KEY');

    $body = json_encode($body);

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $href,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PATCH',
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Basic ' . $mailchimp_key
        ),
    ));

    $response = curl_exec($curl);

    dd($response);

    curl_close($curl);
    echo $response;
}

function membersRemoveMailChimp($href) {

    $listId = env('MAILCHIMP_LIST_ID');

    $mailchimp_key = env('MAILCHIMP_API_KEY');

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $href,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'DELETE',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Basic ' . $mailchimp_key
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return true;
}

function createOrUpdateCampaign() {
    $listId = env('MAILCHIMP_LIST_ID');
    $mailchimp_key = env('MAILCHIMP_API_KEY');

    if (!empty($mailchimp_key)) {
        $marketingCampaignObj = marketingCampaignObj();

        $campaignList = $marketingCampaignObj->campaigns->list(null, null, 100);

        if ($campaignList->total_items != 0) {
            foreach ($campaignList->campaigns as $key => $campaign) {
                $input['campaign_id'] = $campaign->id;
                $input['title'] = $campaign->settings->title;
                $input['type'] = $campaign->type ?? '';
                $input['status'] = $campaign->status;
                $input['updated_at'] = $campaign->create_time;
                $input['created_at'] = $campaign->create_time;
                $input['campaign_data'] = json_encode($campaign, true);

                $campaign = Campaign::where('campaign_id', $input['campaign_id'])
                        ->first();

                if (is_null($campaign)) {
                    Campaign::create($input);
                } else {
                    $campaign->update($input);
                }
            }
        }

        $mailchimpMarketing = marketingCampaignObj();

        $userList = $mailchimpMarketing->lists->getListMembersInfo($listId);

        if (isset($userList->total_items) && $userList->total_items != 0) {

            foreach ($userList->members as $key => $user) {

                $inputUser['campaign_id'] = $user->id;
                $inputUser['name'] = $user->full_name;
                $inputUser['email_address'] = $user->email_address;
                $inputUser['member_rating'] = $user->member_rating;
                $inputUser['user_data'] = json_encode($user, true);

                $campaignuser = CampaignUser::where('campaign_id', $inputUser['campaign_id'])
                        ->first();

                if (is_null($campaignuser)) {
                    CampaignUser::create($inputUser);
                } else {
                    $campaignuser->update($inputUser);
                }
            }
        }
    }

    return true;
}

function createOrUpdateCampaignMain() {
    $listId = env('MAILCHIMP_LIST_ID');
    $mailchimp_key = env('MAILCHIMP_API_KEY');

    if (!empty($mailchimp_key)) {
        $marketingCampaignObj = marketingCampaignObj();

        $campaignList = $marketingCampaignObj->campaigns->list(null, null, 100);

        if ($campaignList->total_items != 0) {
            foreach ($campaignList->campaigns as $key => $campaign) {
                $input['campaign_id'] = $campaign->id;
                $input['title'] = $campaign->settings->title;
                $input['type'] = $campaign->type ?? '';
                $input['status'] = $campaign->status;
                $input['updated_at'] = $campaign->create_time;
                $input['created_at'] = $campaign->create_time;
                $input['campaign_data'] = json_encode($campaign, true);

                $campaign = Campaign::where('campaign_id', $input['campaign_id'])
                        ->first();

                if (is_null($campaign)) {
                    Campaign::create($input);
                } else {
                    $campaign->update($input);
                }
            }
        }

        $mailchimpMarketing = marketingCampaignObj();

        $userList = $mailchimpMarketing->lists->getListMembersInfo($listId);

        if (isset($userList->total_items) && $userList->total_items != 0) {

            foreach ($userList->members as $key => $user) {

                $inputUser['campaign_id'] = $user->id;
                $inputUser['name'] = $user->full_name;
                $inputUser['email_address'] = $user->email_address;
                $inputUser['member_rating'] = $user->member_rating;
                $inputUser['user_data'] = json_encode($user, true);

                $campaignuser = CampaignUser::where('campaign_id', $inputUser['campaign_id'])
                        ->first();

                if (is_null($campaignuser)) {
                    CampaignUser::create($inputUser);
                } else {
                    $campaignuser->update($inputUser);
                }
            }
        }
    }

    return true;
}

/**
 * write code for.
 *
 * @param  \Illuminate\Http\Request  
 * @return \Illuminate\Http\Response
 * @author <>
 */
function updateCampaignContent($id, $champ_id) {

    $mailchimpMarketing = marketingCampaignObj();

    $marketingIds = MarketingCampaign::where('status', 1)->where('campaign_id', $champ_id)->pluck('marketing_id')->toArray();

    $marketings = Marketing::select('marketings.*', 'user.first_name as in_first_name', 'user.last_name as in_last_name', 'user2.first_name as lead_first_name', 'user2.last_name as lead_last_name')
                    ->leftjoin("user", "marketings.investore_id", "=", "user.id")
                    ->leftjoin("user as user2", "marketings.investore_id", "=", "user2.id")
                    ->whereIn('marketings.id', $marketingIds)
                    ->latest()->first();


    $segment_id = $marketings->segment_id;


    if (!empty($marketingIds)) {
        if ($segment_id == '') {

            $marketing_email = $marketings->marketing_mail;
            if (empty($marketing_email)) {
                $marketing_email = $marketings->appt_email;
            }

            $listId = env('MAILCHIMP_LIST_ID');
            $email = $marketing_email;

            $segmentData = [
                'name' => 'Contact Segment For ' . $marketings->title,
                'static_segment' => [$email]
            ];

            $segment = $mailchimpMarketing->lists->createSegment($listId, $segmentData);

            if (isset($segment->id)) {
                $marketings->segment_id = $segment->id;
                $marketings->update();


                $segmentId = $segment->id; // Replace with your actual segment ID
                $campaignId = $champ_id; // Replace with your actual campaign ID

                $segmentOpts = [
                    'saved_segment_id' => $segmentId
                ];

                $campaignUpdateData = [
                    'recipients' => [
                        'segment_opts' => $segmentOpts
                    ]
                ];

                try {
                    $response = $mailchimpMarketing->campaigns->update($campaignId, $campaignUpdateData);
                } catch (Exception $e) {
                    
                }
            } else {
                info('Segment creation failed');
            }
        } else {
            $marketing_email = $marketings->marketing_mail;
            if (empty($marketing_email)) {
                $marketing_email = $marketings->appt_email;
            }

            $listId = env('MAILCHIMP_LIST_ID');
            $email = $marketing_email;

            $segmentData = [
                'static_segment' => [$email]
            ];

            $segment = $mailchimpMarketing->lists->updateSegment($listId, $segment_id, $segmentData);
        }
    }



    $marketing_address = $marketings->marketing_address;
    if ($marketing_address == '') {
        $marketing_address = $marketings->formatted_address;
    }

    $campaign = $mailchimpMarketing->campaigns->getContent($champ_id);

    if (isset($campaign->html)) {
        $CampaignData = Campaign::where('campaign_id', '=', $champ_id)->first();

        if ($CampaignData->template_html == '') {
            $CampaignData->template_html = $campaign->html;
            $CampaignData->update();
        }




        if (strpos($html, '*|FNAME|*') !== false) {
            $html = str_replace('*|FNAME|*', $marketings->title, $html);
            $html = str_replace('*|ADDRESS|*', $marketing_address, $html);
        } else {
            $html = $CampaignData->template_html;
            $html = str_replace('*|FNAME|*', $marketings->title, $html);
            $html = str_replace('*|ADDRESS|*', $marketing_address, $html);
        }

        $response = $mailchimpMarketing->campaigns->setContent($champ_id, [
            'html' => $html,
        ]);
    } else {

        $CampaignData = Campaign::where('campaign_id', '=', $champ_id)->first();

        if ($CampaignData->template_html == '') {
            $CampaignData->template_html = $campaign->plain_text;
        }

        $html = $campaign->plain_text;

        if (strpos($html, '*|FNAME|*') !== false) {
            $html = str_replace('*|FNAME|*', $marketings->title, $html);
            $html = str_replace('*|ADDRESS|*', $marketing_address, $html);
        } else {
            $html = $CampaignData->template_html;
            $html = str_replace('*|FNAME|*', $marketings->title, $html);
            $html = str_replace('*|ADDRESS|*', $marketing_address, $html);
        }

        $response = $mailchimpMarketing->campaigns->setContent($champ_id, [
            'plain_text' => $html,
        ]);


//        $html = $campaign->plain_text;
//
//        $html = str_replace('{{table_data}}', $marketingListMail, $html);
//
//        $response = $mailchimpMarketing->campaigns->setContent($champ_id, [
//            'plain_text' => $html,
//        ]);
    }


//    $response = $mailchimpMarketing->campaigns->setContent($id, ['text' => $marketingListMail]);

    return true;
}

/**
 * write code for.
 *
 * @param  \Illuminate\Http\Request  
 * @return \Illuminate\Http\Response
 * @author <>
 */
function getSegmentStatus($segemntId, $userId) {
    $segmentUserStatus = SegmentUserStatus::where('segment_id', $segemntId)
            ->where('user_id', $userId)
            ->first();
    if (is_null($segmentUserStatus)) {
        return 0;
    }

    return $segmentUserStatus->status;
}

/**
 * write code for.
 *
 * @param  \Illuminate\Http\Request  
 * @return \Illuminate\Http\Response
 * @author <>
 */
function getUserNameAudit($user) {
    if ($user == '') {
        return '-';
    }

    $user = json_decode($user);

    return $user->first_name . ' ' . $user->last_name;
}

/**
 * write code for.
 *
 * @param  \Illuminate\Http\Request  
 * @return \Illuminate\Http\Response
 * @author <>
 */
function getDayLightTimeZone($sendDate) {
    $check_daylight = date('d-M-Y');
    $date = new DateTime($check_daylight . 'America/Chicago');
    $check_daylight_result = $date->format('I');
    if ($check_daylight_result == 1) {
        $timestamp1 = date('Y-m-d H:i:s', strtotime($sendDate));
        $getDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $timestamp1);
        $getDate->setTimezone('CST');
        $timestamp = strtotime($getDate) + 60 * 60;
        $getDate = date('Y-m-d H:i A', $timestamp);
    } else {
        $timestamp1 = date('Y-m-d H:i:s', strtotime($sendDate));
        $getDate = Carbon::createFromFormat('Y-m-d H:i A', $timestamp1);
        $getDate->setTimezone('CST');
    }

    return $getDate;
}

/**
 * write code for.
 *
 * @param  \Illuminate\Http\Request  
 * @return \Illuminate\Http\Response
 * @author <>
 */
function getDayLightTimeZoneDB($sendDate) {
    $check_daylight = date('d-M-Y');
    $date = new DateTime($check_daylight . 'America/Chicago');
    $check_daylight_result = $date->format('I');
    if ($check_daylight_result == 1) {
        $timestamp1 = date('Y-m-d H:i:s', strtotime($sendDate));
        $getDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $timestamp1);
        $getDate->setTimezone('CST');
        $timestamp = strtotime($getDate) + 60 * 60;
        $getDate = date('Y-m-d H:i:s', $timestamp);
    } else {
        $timestamp1 = date('Y-m-d H:i:s', strtotime($sendDate));
        $getDate = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp1);
        $getDate->setTimezone('CST');
    }

    return $getDate;
}

/**
 * write code for.
 *
 * @param  \Illuminate\Http\Request  
 * @return \Illuminate\Http\Response
 * @author <>
 */
function getPriceInt($price) {
    $price = str_replace("$", "", $price);
    $price = str_replace(",", "", $price);
    $price = preg_replace('/[^0-9.]/', '', $price);

    if (empty($price)) {
        return null;
    }

    return $price;
}

/**
 * write code for.
 *
 * @param  \Illuminate\Http\Request  
 * @return \Illuminate\Http\Response
 * @author <>
 */
function numberDollor($price) {
    if (empty($price)) {
        return '-';
    }

    return '$' . $price;
}

/**
 * write code for.
 *
 * @param  \Illuminate\Http\Request  
 * @return \Illuminate\Http\Response
 * @author <>
 */
function userUpdateLastActivity($token, $userId = null) {

    if (empty($token)) {
        return true;
    }

    $user = User::where('token', $token)->where('status_id', 1)->first();

    if (!is_null($user)) {
        $user->last_app_activity = getDayLightTimeZoneDB(Carbon::now());
        $user->save();
    }

    return true;
}

/**
 * write code for.
 *
 * @param  \Illuminate\Http\Request  
 * @return \Illuminate\Http\Response
 * @author <>
 */
function dateTimezoneChangeNew($setDate) {
    if (empty($setDate)) {
        return '';
    }

    $check_daylight = date('d-M-Y');
    $date = new DateTime($check_daylight . ' America/Los_Angeles');
    $check_daylight_result = $date->format('I');
    $today = '';

    if ($check_daylight_result == 1) {
        $timestamp1 = date('Y-m-d H:i:s', strtotime($setDate));
        $today = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp1);
        $today->setTimezone('CST');
        $timestamp = strtotime($today) + 60 * 60;
        $today = date('Y-m-d H:i:s', $timestamp);
    } else {
        $timestamp1 = date('Y-m-d H:i:s', strtotime($setDate));
        $today = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp1);
        $today->setTimezone('CST');
        $timestamp = strtotime($today);
        $today = date('Y-m-d H:i:s', $timestamp);
    }

    $setDate = new Carbon($setDate);

    $today = new Carbon($today);
    $Diff = $setDate->diff($today)->format('+ %H Hours %I Minutes %S Seconds');
    $today = date('Y-m-d H:i:s', strtotime($setDate . $Diff));

    return $today;
}

function dateTimezoneChangeForGraph($setDate) {
    if (empty($setDate)) {
        return '';
    }

    $check_daylight = date('d-M-Y');
    $date = new DateTime($check_daylight . ' America/Los_Angeles');
    $check_daylight_result = $date->format('I');
    $today = '';

    if ($check_daylight_result == 1) {
        $timestamp1 = date('Y-m-d H:i:s', strtotime($setDate));
        $today = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp1);
        $today->setTimezone('CST');
        $timestamp = strtotime($today) + 60 * 60;
        $today = date('Y-m-d H:i:s', $timestamp);
    } else {
        $timestamp1 = date('Y-m-d H:i:s', strtotime($setDate));
        $today = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp1);
        $today->setTimezone('CST');
        $timestamp = strtotime($today);
        $today = date('Y-m-d H:i:s', $timestamp);
    }

    $setDate = new Carbon($setDate);

    $today = new Carbon($today);
    $Diff = $setDate->diff($today)->format('- %H Hours %I Minutes %S Seconds');
    $today = date('Y/m/d H:i', strtotime($setDate . $Diff));

    return $today;
}

function dateTimezoneChangeNewMinus($setDate) {
    if (empty($setDate)) {
        return '';
    }

    $check_daylight = date('d-M-Y');
    $date = new DateTime($check_daylight . ' America/Los_Angeles');
    $check_daylight_result = $date->format('I');
    $today = '';

    if ($check_daylight_result == 1) {
        $timestamp1 = date('Y-m-d H:i:s', strtotime($setDate));
        $today = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp1);
        $today->setTimezone('CST');
        $timestamp = strtotime($today) + 60 * 60;
        $today = date('Y-m-d H:i:s', $timestamp);
    } else {
        $timestamp1 = date('Y-m-d H:i:s', strtotime($setDate));
        $today = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp1);
        $today->setTimezone('CST');
        $timestamp = strtotime($today);
        $today = date('Y-m-d H:i:s', $timestamp);
    }

    $setDate = new Carbon($setDate);

    $today = new Carbon($today);
    $Diff = $setDate->diff($today)->format('- %H Hours %I Minutes %S Seconds');
    $today = date('Y-m-d H:i:s', strtotime($setDate . $Diff));

    return $today;
}

/**
 * write code for.
 *
 * @param  \Illuminate\Http\Request  
 * @return \Illuminate\Http\Response
 * @author <>
 */
function getDealScreenData($input, $dataFormate = null) {

    $dealLeads = DealLead::latest();

    if (!empty($input['search'])) {
        $dealLeads = $dealLeads->where('title', 'like', '%' . $input['search'] . '%')
                ->orWhere('address', 'like', '%' . $input['search'] . '%')
                ->orWhere('county', 'like', '%' . $input['search'] . '%')
                ->orWhere('state', 'like', '%' . $input['search'] . '%')
                ->orWhere('zip_code', 'like', '%' . $input['search'] . '%')
                ->orWhere('formatted_address', 'like', '%' . $input['search'] . '%')
                ->orWhere('sq_ft', 'like', '%' . $input['search'] . '%')
                ->orWhere('yr_blt', 'like', '%' . $input['search'] . '%')
                ->orWhere('city', 'like', '%' . $input['search'] . '%');
    }

    if (!empty($input['investor_id'])) {
        $dealLeads = $dealLeads->where('investor_id', $input['investor_id']);
    }

    if (!empty($input['closer_id'])) {
        $dealLeads = $dealLeads->where('closer_id', $input['closer_id']);
    }


    if (!empty($input['sort_column']) && !empty($input['sort_type'])) {
        switch ($input['sort_column']) {
            case 'homeowner_name':
                $dealLeads = $dealLeads->orderby('title', $input['sort_type']);
                break;

            case 'address':
                $dealLeads = $dealLeads->orderby('address', $input['sort_type']);
                break;

            case 'city':
                $dealLeads = $dealLeads->orderby('city', $input['sort_type']);
                break;

            case 'state':
                $dealLeads = $dealLeads->orderby('state', $input['sort_type']);
                break;

            case 'county':
                $dealLeads = $dealLeads->orderby('county', $input['sort_type']);
                break;

            case 'zip_code':
                $dealLeads = $dealLeads->orderby('zip_code', $input['sort_type']);
                break;

            case 'sq_ft':
                $dealLeads = $dealLeads->orderby('sq_ft', $input['sort_type']);
                break;

            case 'yr_blt':
                $dealLeads = $dealLeads->orderby('yr_blt', $input['sort_type']);
                break;

            case 'deal_status':
                $dealLeads = $dealLeads->orderby('deal_status', $input['sort_type']);
                break;


            case 'investor':
                $dealLeads = $dealLeads->orderby('investor_id', $input['sort_type']);
                break;

            case 'closer':
                $dealLeads = $dealLeads->orderby('closer_id', $input['sort_type']);
                break;

            case 'purchase_finance':
                $dealLeads = $dealLeads->orderby('purchase_finance', $input['sort_type']);
                break;

            case 'ownership':
                $dealLeads = $dealLeads->orderby('ownership', $input['sort_type']);
                break;

            case 'purchase_date':
                $dealLeads = $dealLeads->orderby('purchase_date', $input['sort_type']);
                break;

            case 'sell_date':
                $dealLeads = $dealLeads->orderby('sell_date', $input['sort_type']);
                break;

            case 'purchase_price':
                $dealLeads = $dealLeads->orderby('purchase_price', $input['sort_type']);
                break;

            case 'purchase_closing_costs':
                $dealLeads = $dealLeads->orderby('purchase_closing_costs', $input['sort_type']);
                break;

            case 'cash_in_at_purchase':
                $dealLeads = $dealLeads->orderby('cash_in_at_purchase', $input['sort_type']);
                break;

            case 'rehab_and_other_costs':
                $dealLeads = $dealLeads->orderby('rehab_and_other_costs', $input['sort_type']);
                break;

            case 'total_cash_in':
                $dealLeads = $dealLeads->orderby('total_cash_in', $input['sort_type']);
                break;

            case 'investor_commission':
                $dealLeads = $dealLeads->orderby('Investor_commission', $input['sort_type']);
                break;

            case 'total_cost':
                $dealLeads = $dealLeads->orderby('total_cost', $input['sort_type']);
                break;

            case 'sales_value':
                $dealLeads = $dealLeads->orderby('sales_value', $input['sort_type']);
                break;

            case 'sales_cash_proceeds':
                $dealLeads = $dealLeads->orderby('sales_cash_proceeds', $input['sort_type']);
                break;

            case 'lh_profit_after_sharing':
                $dealLeads = $dealLeads->orderby('lh_profit_after_sharing', $input['sort_type']);
                break;

            case 'notes':
                $dealLeads = $dealLeads->orderby('notes', $input['sort_type']);
                break;

            case 'deal_type':
                $dealLeads = $dealLeads->orderby('deal_type', $input['sort_type']);
                break;

            case 'deal_no':
                $dealLeads = $dealLeads->orderby('deal_no', $input['sort_type']);
                break;

            default:
                // code...
                break;
        }
    }

    if (!empty($input['deal_status'])) {
        $dealLeads = $dealLeads->where('deal_status', $input['deal_status']);
    }

    if (!empty($input['deal_type'])) {
        $dealLeads = $dealLeads->where('deal_type', $input['deal_type']);
    }

    if (!empty($input['purchase_finance'])) {
        $dealLeads = $dealLeads->where('purchase_finance', $input['purchase_finance']);
    }

    if (!empty($input['ownership'])) {
        $dealLeads = $dealLeads->where('ownership', $input['ownership']);
    }

    if (!empty($input['purchase_date'])) {
        $purchaseDate = json_decode($input['purchase_date']);
        $dealLeads = $dealLeads->whereBetween('purchase_date', [$purchaseDate->start, $purchaseDate->end]);
    }

    if (!empty($input['sell_date'])) {
        $sellDate = json_decode($input['sell_date']);
        $dealLeads = $dealLeads->whereBetween('sell_date', [$sellDate->start, $sellDate->end]);
    }

    if (is_null($dataFormate)) {
        $DisplayedPerScreen = settingForRecordsDisplayedPerScreen();
        if (isset($DisplayedPerScreen->deal_management)) {
            return $dealLeads->paginate($DisplayedPerScreen->deal_management);
        } else {
            return $dealLeads->paginate(15);
        }
    }

    return $dealLeads->get();
}

/**
 * write code for.
 *
 * @param  \Illuminate\Http\Request  
 * @return \Illuminate\Http\Response
 * @author <>
 */
function getDealScreenDataExport($input, $dataFormate = null) {

    $dealLeads = new DealLead();

    if (!empty($input['search'])) {
        $dealLeads = $dealLeads->where('title', 'like', '%' . $input['search'] . '%')
                ->orWhere('address', 'like', '%' . $input['search'] . '%')
                ->orWhere('county', 'like', '%' . $input['search'] . '%')
                ->orWhere('state', 'like', '%' . $input['search'] . '%')
                ->orWhere('zip_code', 'like', '%' . $input['search'] . '%')
                ->orWhere('formatted_address', 'like', '%' . $input['search'] . '%')
                ->orWhere('sq_ft', 'like', '%' . $input['search'] . '%')
                ->orWhere('yr_blt', 'like', '%' . $input['search'] . '%')
                ->orWhere('city', 'like', '%' . $input['search'] . '%');
    }

    if (!empty($input['leads_ids'])) {
        $dealLeads = $dealLeads->whereIn('deal_leads.id', $input['leads_ids']);
    }

    if (!empty($input['investor_id'])) {
        $dealLeads = $dealLeads->where('investor_id', $input['investor_id']);
    }

    if (!empty($input['closer_id'])) {
        $dealLeads = $dealLeads->where('closer_id', $input['closer_id']);
    }


    if (!empty($input['sort_column']) && !empty($input['sort_type'])) {
        switch ($input['sort_column']) {
            case 'homeowner_name':
                $dealLeads = $dealLeads->orderby('title', $input['sort_type']);
                break;

            case 'address':
                $dealLeads = $dealLeads->orderby('address', $input['sort_type']);
                break;

            case 'city':
                $dealLeads = $dealLeads->orderby('city', $input['sort_type']);
                break;

            case 'state':
                $dealLeads = $dealLeads->orderby('state', $input['sort_type']);
                break;

            case 'county':
                $dealLeads = $dealLeads->orderby('county', $input['sort_type']);
                break;

            case 'zip_code':
                $dealLeads = $dealLeads->orderby('zip_code', $input['sort_type']);
                break;

            case 'sq_ft':
                $dealLeads = $dealLeads->orderby('sq_ft', $input['sort_type']);
                break;

            case 'yr_blt':
                $dealLeads = $dealLeads->orderby('yr_blt', $input['sort_type']);
                break;

            case 'deal_status':
                $dealLeads = $dealLeads->orderby('deal_status', $input['sort_type']);
                break;


            case 'investor':
                $dealLeads = $dealLeads->orderby('investor_id', $input['sort_type']);
                break;

            case 'closer':
                $dealLeads = $dealLeads->orderby('closer_id', $input['sort_type']);
                break;

            case 'purchase_finance':
                $dealLeads = $dealLeads->orderby('purchase_finance', $input['sort_type']);
                break;

            case 'ownership':
                $dealLeads = $dealLeads->orderby('ownership', $input['sort_type']);
                break;

            case 'purchase_date':
                $dealLeads = $dealLeads->orderby('purchase_date', $input['sort_type']);
                break;

            case 'sell_date':
                $dealLeads = $dealLeads->orderby('sell_date', $input['sort_type']);
                break;

            case 'purchase_price':
                $dealLeads = $dealLeads->orderby('purchase_price', $input['sort_type']);
                break;

            case 'purchase_closing_costs':
                $dealLeads = $dealLeads->orderby('purchase_closing_costs', $input['sort_type']);
                break;

            case 'cash_in_at_purchase':
                $dealLeads = $dealLeads->orderby('cash_in_at_purchase', $input['sort_type']);
                break;

            case 'rehab_and_other_costs':
                $dealLeads = $dealLeads->orderby('rehab_and_other_costs', $input['sort_type']);
                break;

            case 'total_cash_in':
                $dealLeads = $dealLeads->orderby('total_cash_in', $input['sort_type']);
                break;

            case 'investor_commission':
                $dealLeads = $dealLeads->orderby('Investor_commission', $input['sort_type']);
                break;

            case 'total_cost':
                $dealLeads = $dealLeads->orderby('total_cost', $input['sort_type']);
                break;

            case 'sales_value':
                $dealLeads = $dealLeads->orderby('sales_value', $input['sort_type']);
                break;

            case 'sales_cash_proceeds':
                $dealLeads = $dealLeads->orderby('sales_cash_proceeds', $input['sort_type']);
                break;

            case 'lh_profit_after_sharing':
                $dealLeads = $dealLeads->orderby('lh_profit_after_sharing', $input['sort_type']);
                break;

            case 'notes':
                $dealLeads = $dealLeads->orderby('notes', $input['sort_type']);
                break;

            case 'deal_type':
                $dealLeads = $dealLeads->orderby('deal_type', $input['sort_type']);
                break;

            case 'deal_no':
                $dealLeads = $dealLeads->orderby('deal_no', $input['sort_type']);
                break;

            default:
                // code...
                break;
        }
    }

    if (!empty($input['deal_status'])) {
        $dealLeads = $dealLeads->where('deal_status', $input['deal_status']);
    }

    if (!empty($input['deal_type'])) {
        $dealLeads = $dealLeads->where('deal_type', $input['deal_type']);
    }

    if (!empty($input['purchase_finance'])) {
        $dealLeads = $dealLeads->where('purchase_finance', $input['purchase_finance']);
    }

    if (!empty($input['ownership'])) {
        $dealLeads = $dealLeads->where('ownership', $input['ownership']);
    }

    if (!empty($input['purchase_date'])) {
        $purchaseDate = json_decode($input['purchase_date']);
        $dealLeads = $dealLeads->whereBetween('purchase_date', [$purchaseDate->start, $purchaseDate->end]);
    }

    if (!empty($input['sell_date'])) {
        $sellDate = json_decode($input['sell_date']);
        $dealLeads = $dealLeads->whereBetween('sell_date', [$sellDate->start, $sellDate->end]);
    }

    if (is_null($dataFormate)) {
        $DisplayedPerScreen = settingForRecordsDisplayedPerScreen();
        if (isset($DisplayedPerScreen->deal_management)) {
            dd($DisplayedPerScreen->deal_management);
            return $dealLeads->paginate($DisplayedPerScreen->deal_management);
        } else {
            return $dealLeads->get();
        }
    }

    return $dealLeads->get();
}

/**
 * write code for.
 *
 * @param  \Illuminate\Http\Request  
 * @return \Illuminate\Http\Response
 * @author <>
 */
function setupExportDealView($dealLeads, $dealLeadViewSetp) {
    $data = [];

    if (!empty($dealLeadViewSetp)) {
        foreach ($dealLeads as $dealLeadkey => $dealLead) {
            foreach ($dealLeadViewSetp as $key => $dealLeadView) {
                if ($dealLeadView->title_slug == 'id') {
                    $data[$dealLeadkey]['id'] = $dealLead->deal_no;
                } elseif ($dealLeadView->title_slug == 'homeowner_name') {
                    $data[$dealLeadkey]['homeowner_name'] = $dealLead->title;
                } elseif ($dealLeadView->title_slug == 'street_address') {
                    $data[$dealLeadkey]['street_address'] = $dealLead->address;
                } elseif ($dealLeadView->title_slug == 'city') {
                    $data[$dealLeadkey]['city'] = $dealLead->city;
                } elseif ($dealLeadView->title_slug == 'county') {
                    $data[$dealLeadkey]['county'] = $dealLead->county;
                } elseif ($dealLeadView->title_slug == 'state') {
                    $data[$dealLeadkey]['state'] = $dealLead->state;
                } elseif ($dealLeadView->title_slug == 'zip') {
                    $data[$dealLeadkey]['zip'] = $dealLead->zip_code;
                } elseif ($dealLeadView->title_slug == 'sq_ft') {
                    $data[$dealLeadkey]['sq_ft'] = $dealLead->sq_ft;
                } elseif ($dealLeadView->title_slug == 'year_built') {
                    $data[$dealLeadkey]['year_built'] = $dealLead->yr_blt;
                } elseif ($dealLeadView->title_slug == 'investor') {
                    $data[$dealLeadkey]['investor'] = getUser($dealLead->investor_id)->fullName ?? '';
                } elseif ($dealLeadView->title_slug == 'closer') {
                    $data[$dealLeadkey]['closer'] = getUser($dealLead->closer_id)->fullName ?? '';
                } elseif ($dealLeadView->title_slug == 'deal_status') {
                    $data[$dealLeadkey]['deal_status'] = $dealLead->dealStatusLabel;
                } elseif ($dealLeadView->title_slug == 'purchase_finance') {
                    $data[$dealLeadkey]['purchase_finance'] = $dealLead->purchaseFinanceLabel;
                } elseif ($dealLeadView->title_slug == 'ownership') {
                    $data[$dealLeadkey]['ownership'] = $dealLead->ownershipLabel;
                } elseif ($dealLeadView->title_slug == 'purchase_date') {
                    $data[$dealLeadkey]['purchase_date'] = dynamicDateFormat($dealLead->purchase_date, 5);
                } elseif ($dealLeadView->title_slug == 'sell_date') {
                    $data[$dealLeadkey]['sell_date'] = dynamicDateFormat($dealLead->sell_date, 5);
                } elseif ($dealLeadView->title_slug == 'purchase_price') {
                    $data[$dealLeadkey]['purchase_price'] = numberDollor($dealLead->purchase_price);
                } elseif ($dealLeadView->title_slug == 'purchase_closing_costs') {
                    $data[$dealLeadkey]['purchase_closing_costs'] = numberDollor($dealLead->purchase_closing_costs);
                } elseif ($dealLeadView->title_slug == 'cash_in_at_purchase') {
                    $data[$dealLeadkey]['cash_in_at_purchase'] = numberDollor($dealLead->cash_in_at_purchase);
                } elseif ($dealLeadView->title_slug == 'rehab_and_other_costs') {
                    $data[$dealLeadkey]['rehab_and_other_costs'] = numberDollor($dealLead->rehab_and_other_costs);
                } elseif ($dealLeadView->title_slug == 'total_cash_in') {
                    $data[$dealLeadkey]['total_cash_in'] = numberDollor($dealLead->total_cash_in);
                } elseif ($dealLeadView->title_slug == 'investor_commission') {
                    $data[$dealLeadkey]['investor_commission'] = numberDollor($dealLead->investor_commission);
                } elseif ($dealLeadView->title_slug == 'total_cost') {
                    $data[$dealLeadkey]['total_cost'] = numberDollor($dealLead->total_cost);
                } elseif ($dealLeadView->title_slug == 'sales_value') {
                    $data[$dealLeadkey]['sales_value'] = numberDollor($dealLead->sales_value);
                } elseif ($dealLeadView->title_slug == 'sales_cash_proceeds') {
                    $data[$dealLeadkey]['sales_cash_proceeds'] = numberDollor($dealLead->sales_cash_proceeds);
                } elseif ($dealLeadView->title_slug == 'lh_profit_after_sharing') {
                    $data[$dealLeadkey]['lh_profit_after_sharing'] = numberDollor($dealLead->lh_profit_after_sharing);
                } elseif ($dealLeadView->title_slug == 'notes') {
                    $data[$dealLeadkey]['notes'] = $dealLead->notes;
                }
            }
            $dealLeadsCustomFields = DealLeadViewCustomFields::where('deal_lead_id', $dealLead->id)->get();

            $customInput = [];

            if (!empty($dealLeadsCustomFields)) {

                foreach ($dealLeadsCustomFields as $keynew => $dealLeadsCustomField) {
                    if (!empty($dealLeadsCustomField->dealLeadViewSetp->title_slug) && !empty($dealLeadsCustomField->field_value)) {
                        if ($dealLeadsCustomField->dealLeadViewSetp->input_type == 4 && is_numeric($dealLeadsCustomField->field_value)) {
                            $data[$dealLeadkey][$dealLeadsCustomField->dealLeadViewSetp->title_slug] = '$' . number_format($dealLeadsCustomField->field_value, 0);
                        } else {
                            $data[$dealLeadkey][$dealLeadsCustomField->dealLeadViewSetp->title_slug] = $dealLeadsCustomField->field_value;
                        }
                    }
                }
            }
        }
    }

    $lead = [];

    if (!empty($dealLeadViewSetp)) {
        foreach ($data as $leadNo => $input) {
            foreach ($dealLeadViewSetp as $key => $dealLeadView) {
                if (!empty($input[$dealLeadView->title_slug])) {
                    $lead[$leadNo][$dealLeadView->title_slug] = $input[$dealLeadView->title_slug];
                } else {
                    $lead[$leadNo][$dealLeadView->title_slug] = $input[$dealLeadView->title_slug] ?? '';
                }
            }
        }
    }

    return $lead;
}

/**
 * write code for.
 *
 * @param  \Illuminate\Http\Request  
 * @return \Illuminate\Http\Response
 * @author <>
 */
function setupFollowupAddressUpdate() {
    $followingLeads = FollowingLead::whereNull('address')->get();

    if (!empty($followingLeads)) {
        foreach ($followingLeads as $key => $followingLead) {
            $lead = Lead::find($followingLead->lead_id);

            $followingLead->address = $lead->address ?? null;
            $followingLead->save();

            $leads = Lead::where('address', $followingLead->address)->get();

            if (!empty($leads)) {
                foreach ($leads as $key => $value) {
                    $value->is_follow_up = 1;
                    $value->save();
                }
            }
        }
    }

    return true;
}

function setupExportUserView($dealLeads, $dealLeadViewSetp) {
    $data = [];

    if (!empty($dealLeadViewSetp)) {
        foreach ($dealLeads as $dealLeadkey => $dealLead) {
            foreach ($dealLeadViewSetp as $key => $dealLeadView) {
                if ($dealLeadView == 'id') {
                    $data[$dealLeadkey]['id'] = $dealLead->id;
                } elseif ($dealLeadView == 'code') {
                    $data[$dealLeadkey]['code'] = 'IN-004-0' . $dealLead->id;
                } elseif ($dealLeadView == 'first_name') {
                    $data[$dealLeadkey]['first_name'] = $dealLead->first_name;
                } elseif ($dealLeadView == 'last_name') {
                    $data[$dealLeadkey]['last_name'] = $dealLead->last_name;
                } elseif ($dealLeadView == 'email') {
                    $data[$dealLeadkey]['email'] = $dealLead->email;
                } elseif ($dealLeadView == 'mobile_no') {
                    $data[$dealLeadkey]['mobile_no'] = $dealLead->mobile_no;
                } elseif ($dealLeadView == 'date_of_join') {
                    $data[$dealLeadkey]['date_of_join'] = $dealLead->date_of_join;
                } elseif ($dealLeadView == 'status') {
                    if ($dealLead->status_id == 1) {
                        $data[$dealLeadkey]['status'] = 'Active';
                    } else {
                        $data[$dealLeadkey]['status'] = 'Inactive';
                    }
                } elseif ($dealLeadView == 'startup_paid') {
                    if ($dealLead->startup_paid == 1) {
                        $data[$dealLeadkey]['startup_paid'] = 'Yes';
                    } else {
                        $data[$dealLeadkey]['startup_paid'] = 'No';
                    }
                } elseif ($dealLeadView == 'startup_reimbursed') {
                    if ($dealLead->startup_reimbursed == 1) {
                        $data[$dealLeadkey]['startup_reimbursed'] = 'Yes';
                    } else {
                        $data[$dealLeadkey]['startup_reimbursed'] = 'No';
                    }
                } elseif ($dealLeadView == 'user_group_id') {
                    if ($dealLead->user_group_id == 2) {
                        $data[$dealLeadkey]['user_group_id'] = 'Mobile User';
                    } elseif ($dealLead->user_group_id == 3) {
                        $data[$dealLeadkey]['user_group_id'] = 'Sub Admin';
                    } else {
                        $data[$dealLeadkey]['user_group_id'] = '';
                    }
                } elseif ($dealLeadView == 'last_app_activity') {
                    if ($dealLead->last_app_activity != '') {
                        $data[$dealLeadkey]['last_app_activity'] = date('m/d/Y h:i A', strtotime(dateTimezoneChange($dealLead->last_app_activity)));
                    } else {
                        $data[$dealLeadkey]['last_app_activity'] = '';
                    }
                } elseif ($dealLeadView == 'created_at') {
                    $data[$dealLeadkey]['created_at'] = date('m/d/Y h:i A', strtotime(dateTimezoneChange($dealLead->created_at)));
                } elseif ($dealLeadView == 'updated_at') {
                    $data[$dealLeadkey]['updated_at'] = date('m/d/Y h:i A', strtotime(dateTimezoneChange($dealLead->updated_at)));
                }
            }
        }
    }

    return $data;
}
