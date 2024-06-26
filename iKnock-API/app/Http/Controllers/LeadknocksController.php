<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Status;
use App\Models\Lead;
use App\Models\LeadHistory;
use App\Models\UserLeadKnocks;
use App\Exports\LeadknocksExport;
use App\Imports\UserLeadKnocksImport;
use App\Http\Resources\UserLeadKnocksResource;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\LeadknocksResource;
use Carbon\Carbon;
use App\Models\FollowStatus;
use Illuminate\Support\Facades\DB;

class LeadknocksController extends Controller {

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index(Request $request) {
        $users = User::whereNull('deleted_at')
                        ->where('user.user_group_id', '!=', 4)->latest()->get();

        $startDate = Carbon::today()->toDateString();
        $endDate = Carbon::today()->toDateString();

        $userLeadKnocks = UserLeadKnocks::select('user_lead_knocks.*', 'lead_detail.title', 'lead_detail.formatted_address', 'user.first_name', 'user.last_name')
                ->latest()
                ->join('user', 'user_lead_knocks.user_id', 'user.id')
                ->join('lead_detail', 'user_lead_knocks.lead_id', 'lead_detail.id')
                ->where('user_lead_knocks.status_id', '!=', 77)
                ->where('user.user_group_id', '!=', 3);
        if (!empty($request->search)) {
            $userLeadKnocks = $userLeadKnocks->where('user_lead_knocks.id', 'like', '%' . $request->search . '%')
                    ->orwhere('lead_detail.title', 'like', '%' . $request->search . '%')
                    ->orwhere('lead_detail.formatted_address', 'like', '%' . $request->search . '%')
                    ->orwhere('user.first_name', 'like', '%' . $request->search . '%')
                    ->orwhere('user.last_name', 'like', '%' . $request->search . '%');
        }
        if (!empty($request->date)) {
            $date = json_decode($request->date);
            $startDate = Carbon::createFromFormat('Y-m-d', $date->start)->format('Y/m/d');
            $endDate = Carbon::createFromFormat('Y-m-d', $date->end)->format('Y/m/d');
            $startDate = dateTimezoneChangeNew($startDate . ' 00:00:00');
            $endDate = dateTimezoneChangeNew($endDate . ' 23:59:59');
            $startDate = date('Y-m-d H:i:s', strtotime($startDate));
            $endDate = date('Y-m-d H:i:s', strtotime($endDate));
            $userLeadKnocks = $userLeadKnocks->whereBetween('user_lead_knocks.created_at', [$startDate, $endDate]);
        }

        if (!empty($request->userId)) {
            $userLeadKnocks = $userLeadKnocks->where('user_lead_knocks.user_id', $request->userId);
        }
        if ($request->select_all == 1) {
            $userLeadKnocks = $userLeadKnocks->get();
        } else {
            $DisplayedPerScreen = settingForRecordsDisplayedPerScreen();
            if (isset($DisplayedPerScreen->knock_list)) {
                $userLeadKnocks = $userLeadKnocks->paginate($DisplayedPerScreen->knock_list);
            } else {
                $userLeadKnocks = $userLeadKnocks->paginate(10);
            }
        }


        return view('tenant.leadknocks.indexUser', compact('users', 'userLeadKnocks'));
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function indexknocks(Request $request) {
        $userLeadKnocks = UserLeadKnocks::where('user_id', $request->id)->latest()->get();

        return view('tenant.leadknocks.indexKnocks', compact('userLeadKnocks'));
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function editknock(Request $request) {
        $knock = UserLeadKnocks::find($request->id);

        $status_id = $knock->status_id;

        $lead = Lead::find($knock->lead_id);

        $status = Status::where('tenant_id', $request->company_id)->whereNull('deleted_at')->pluck('title', 'id')->toArray();

        return view('tenant.leadknocks.editKnocks', compact('lead', 'knock', 'status', 'status_id'));
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function destroyknock(Request $request) {
        $userLeadKnocks = UserLeadKnocks::find($request->id);

        $leadHistory = LeadHistory::find($userLeadKnocks->lead_history_id);

        if (!is_null($leadHistory)) {
            $leadHistory->delete();
        }

        $userLeadKnocks->delete();

        return response()->json(['success' => 'User Lead Knock', $userLeadKnocks, 'code' => 200, 'message' => 'Knock delete successfully']);
    }

    public function destroyHistory(Request $request) {
        $LeadHistory = LeadHistory::find($request->id);

        $UserLeadKnocks = UserLeadKnocks::where('lead_history_id', '=', $LeadHistory->id)->first();

        if (!is_null($UserLeadKnocks)) {
            $UserLeadKnocks->delete();
        }

        $LeadHistory->delete();

        return response()->json(['success' => 'Lead History', $userLeadKnocks, 'code' => 200, 'message' => 'Lead History deleted successfully']);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function updateknock(Request $request) {

        $userLeadKnocks = UserLeadKnocks::find($request->id);

        $userLeadKnocks->update(['status_id' => $request->status_id]);

        $leadHistory = LeadHistory::find($userLeadKnocks->lead_history_id);

        // $userLeadKnocksOld = UserLeadKnocks::where('lead_id',$userLeadKnocks->lead_id)->latest()->first();
        // if($userLeadKnocks->id == $userLeadKnocksOld->id){
        // }

        $lead = Lead::find($userLeadKnocks->lead_id);

        $lead->status_id = $request->status_id;

        $lead->save();

        if (!empty($leadHistory)) {
            $leadHistory->update(['status_id' => $request->status_id]);
        }

        return back();
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function export(Request $request) {

        $users = User::where('company_id', $request->company_id)->whereNull('deleted_at')->latest()->get();
        $startDate = Carbon::today()->toDateString();
        $endDate = Carbon::today()->toDateString();

        $userLeadKnocks = UserLeadKnocks::select('user_lead_knocks.*', 'lead_detail.title', 'lead_detail.formatted_address', 'user.first_name', 'user.last_name')
                ->latest()
                ->join('user', 'user_lead_knocks.user_id', 'user.id')
                ->join('lead_detail', 'user_lead_knocks.lead_id', 'lead_detail.id')
                ->where('user_lead_knocks.status_id', '!=', 77)
                ->where('user.user_group_id', '!=', 3);

        if (!empty($request->search)) {
            $userLeadKnocks = $userLeadKnocks->where('user_lead_knocks.id', 'like', '%' . $request->search . '%')
                    ->orwhere('lead_detail.title', 'like', '%' . $request->search . '%')
                    ->orwhere('lead_detail.formatted_address', 'like', '%' . $request->search . '%')
                    ->orwhere('user.first_name', 'like', '%' . $request->search . '%')
                    ->orwhere('user.last_name', 'like', '%' . $request->search . '%');
        }
        if (!empty($request->idlist)) {

            $idlist = explode(",", $request->idlist);

            $userLeadKnocks = $userLeadKnocks->whereIn('user_lead_knocks.id', $idlist);
        }


        if (!empty($request->userId)) {
            $userLeadKnocks = $userLeadKnocks->where('user_lead_knocks.user_id', $request->userId);
        }

        if (!empty($request->date)) {
            $date = json_decode($request->date);
            $startDate = dateTimezoneChangeNew($date->start . ' 00:00:00');
            $endDate = dateTimezoneChangeNew($date->end . ' 23:59:59');
            $startDate = date('Y-m-d H:i:s', strtotime($startDate));
            $endDate = date('Y-m-d H:i:s', strtotime($endDate));
            $userLeadKnocks = $userLeadKnocks->whereBetween('user_lead_knocks.created_at', [$startDate, $endDate]);
        }

        $userLeadKnocks = $userLeadKnocks->get();

        $leadknocks = UserLeadKnocksResource::collection($userLeadKnocks);

        return Excel::download(new LeadknocksExport($leadknocks), 'leadknocks-' . date('m-d-Y') . '.csv');
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function deleteBulk(Request $request) {
        if (!empty($request->idsList)) {
            foreach ($request->idsList as $key => $idKnocks) {

                $userLeadKnocks = UserLeadKnocks::find($idKnocks);

                if (!is_null($userLeadKnocks)) {
                    $userLeadKnocks->delete();
                }
            }
        }


        return response()->json(['success' => 1]);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function isVerified(Request $request) {
        $userLeadKnocks = UserLeadKnocks::find($request->id);

        $userLeadKnocks->update(['is_verified' => $request->is_verified]);
        return response()->json(['success']);
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function indexImport() {
        return view('tenant.leadknocks.import');
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function storeImport(Request $request) {
        $request->validate([
            'knock_file' => 'required',
        ]);

        Excel::import(new UserLeadKnocksImport, request()->file('knock_file'));

        notificationMsg('success', 'User Knocks import successfully.');
        return redirect('/tenant/lead/knocks/user/list');
    }

    public function purchaseListExport(Request $request) {
        $time_clause = '';
        $time_clause1 = '';

        $time_clause = '';
        $time_clause1 = '';

        if (!empty($request->date)) {
            $date = json_decode($request->date);
            $startDate = Carbon::createFromFormat('Y-m-d', $date->start)->format('Y/m/d');
            $endDate = Carbon::createFromFormat('Y-m-d', $date->end)->format('Y/m/d');
            $startDate = dateTimezoneChangeNew($startDate . ' 00:00:00');
            $endDate = dateTimezoneChangeNew($endDate . ' 23:59:59');
            $startDate = date('Y-m-d H:i:s', strtotime($startDate));
            $endDate = date('Y-m-d H:i:s', strtotime($endDate));
            $time_clause = "lead_history.created_at between '" . $startDate . "' and '" . $endDate . "' ";
            $time_clause1 = "following_leads.contract_date BETWEEN '" . $startDate . "' and '" . $endDate . "' ";
        }

        $modal_user_clause = '';
        if (!empty($request->filter_user_id) AND $request->filter_user_id != 'null' AND $request->filter_user_id != '[object Object]') {
            $modal_user_clause = '  lead_history.assign_id IN (' . $request->filter_user_id . ')';
            $db_user_clause = 'AND  lead_history.assign_id IN (' . $request->filter_user_id . ')';
            $db_user_clause_for_knock = ' user_lead_knocks.user_id IN (' . $request->filter_user_id . ')';
        }

        $followingLeads = getFollowingLeadAll($new_input);
        $new_input_id = [];
        foreach ($followingLeads as $lead) {
            $new_input_id['lead_ids'][] = $lead->lead_id;
        }

        $followStatusApptNotKept = FollowStatus::where('title', 'Purchased')->first();

        $DisplayedPerScreen = settingForRecordsDisplayedPerScreen();

        $PurchaseStatusleadIds = DB::table('lead_history')
                        ->select('lead_history.lead_id')
                        ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
                        ->whereIn('lead_history.lead_id', $new_input_id['lead_ids'])
                        ->where('lead_history.followup_status_id', '!=', 0)
                        ->where('lead_history.status_id', 0)
                        ->where('lead_history.followup_status_id', $followStatusApptNotKept->id)
                        ->when($month_clause, function ($query, $month_clause) {
                            return $query->whereRaw($month_clause);
                        })
                        ->when($time_clause, function ($query, $time_clause) {
                            return $query->whereRaw($time_clause);
                        })
//                        ->when($modal_user_clause, function ($query, $modal_user_clause) {
//                            return $query->whereRaw($modal_user_clause);
//                        })
                        ->orderBy('lead_history.ID', 'ASC')
                        ->pluck('lead_history.lead_id')->toArray();

        $time_clause = '';
        $time_clause1 = '';

        if (!empty($request->date)) {
            $date = json_decode($request->date);
            $startDate = Carbon::createFromFormat('Y-m-d', $date->start)->format('Y/m/d');
            $endDate = Carbon::createFromFormat('Y-m-d', $date->end)->format('Y/m/d');
            $startDate = dateTimezoneChangeNew($startDate . ' 00:00:00');
            $endDate = dateTimezoneChangeNew($endDate . ' 23:59:59');
            $startDate = date('Y-m-d H:i:s', strtotime($startDate));
            $endDate = date('Y-m-d H:i:s', strtotime($endDate));
            $time_clause1 = "(
                STR_TO_DATE(following_custom_fields.field_value, '%m/%d/%Y') BETWEEN '" . $startDate . "' AND '" . $endDate . "'
                OR
                STR_TO_DATE(purchase_custom_fields.field_value, '%m/%d/%Y') BETWEEN '" . $startDate . "' AND '" . $endDate . "'
            )";
        }

        $followingLeads = DB::table('following_leads')
                ->select('following_leads.lead_id')
                ->join('following_custom_fields', 'following_leads.id', 'following_custom_fields.followup_lead_id')
                ->where('following_custom_fields.followup_view_id', '=', 29)
                ->where('following_custom_fields.field_value', '!=', null)
                ->whereRaw("STR_TO_DATE(following_custom_fields.field_value, '%m/%d/%Y') BETWEEN '$startDate' AND '$endDate'")
                ->orderBy('following_leads.lead_id', 'desc');
        $followingLeads = $followingLeads->get();

        $leadIds = $followingLeads->pluck('lead_id')->toArray();

        $purchaseLeads = DB::table('purchase_leads')
                ->select('purchase_leads.lead_id')
                ->join('purchase_custom_fields', 'purchase_leads.id', 'purchase_custom_fields.followup_lead_id')
                ->where('purchase_custom_fields.followup_view_id', '=', 29)
                ->where('purchase_custom_fields.field_value', '!=', null)
                ->whereRaw("STR_TO_DATE(purchase_custom_fields.field_value, '%m/%d/%Y') BETWEEN '$startDate' AND '$endDate'")
                ->orderBy('purchase_leads.lead_id', 'desc');
        $purchaseLeads = $purchaseLeads->get();

        $pleadIds = $purchaseLeads->pluck('lead_id')->toArray();

        $leadIds = array_merge($leadIds, $PurchaseStatusleadIds);

        $mergedArray = array_merge($leadIds, $pleadIds);

        $uniqueValues = array_values(array_unique($mergedArray));

        $modal_user_clause = '';

//        $dashboardData = $dashboardData = DB::table('lead_history')
//                ->select('lead_history.*', 'following_custom_fields.field_value as purchase_date',
//                        'purchase_custom_fields.field_value as p_purchase_date',
//                        'lead_history.followup_status_id as his_status_id',
//                        'lead_detail.title as lead_title',
//                        'lead_detail.formatted_address as lead_formatted_address')
//                ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
//                ->join('following_leads', 'lead_history.lead_id', 'following_leads.lead_id')
//                ->join('following_custom_fields', 'following_leads.id', 'following_custom_fields.followup_lead_id')
//                ->where('following_custom_fields.followup_view_id', '=', 29)
//                ->join('purchase_leads', 'lead_history.lead_id', 'purchase_leads.lead_id')
//                ->join('purchase_custom_fields', 'purchase_leads.id', 'purchase_custom_fields.followup_lead_id')
//                ->where('purchase_custom_fields.followup_view_id', '=', 29)
//                ->whereIn('lead_history.lead_id', $uniqueValues)
//                ->when($modal_user_clause, function ($query, $modal_user_clause) {
//                    return $query->whereRaw($modal_user_clause);
//                })
//                ->when($time_clause1, function ($query, $time_clause1) {
//                    return $query->whereRaw($time_clause1);
//                })
//                ->orderBy('purchase_custom_fields.field_value', 'DESC')
//                ->orderBy('following_custom_fields.field_value', 'DESC')
//                ->groupBy('lead_id')
//                ->get();

        $followStatusApptNotKept = FollowStatus::where('title', 'Purchased')->first();
        $followupStatus = [];
        $followupStatus[] = $followStatusApptNotKept->id;
        $start_date = $startDate;
        $end_date = $endDate;

        $dashboardData = DB::table('lead_history as lh')
                ->leftJoin('purchase_leads as pl', 'lh.lead_id', '=', 'pl.lead_id')
                ->leftJoin('following_leads as fl', 'lh.lead_id', '=', 'fl.lead_id')
                ->join('lead_detail as ld', 'lh.lead_id', '=', 'ld.id')
                ->leftJoin('following_custom_fields', function ($join) {
                    $join->on('fl.id', '=', 'following_custom_fields.followup_lead_id')
                    ->where('following_custom_fields.followup_view_id', 29);
                })
                ->leftJoin('purchase_custom_fields', function ($join) {
                    $join->on('pl.id', '=', 'purchase_custom_fields.followup_lead_id')
                    ->where('purchase_custom_fields.followup_view_id', 29);
                })
                ->select('lh.*', 'fl.contract_date as contract_date', 'pl.contract_date as p_contract_date',
                        'fl.id as fl_id',
                        'ld.title as lead_title',
                        'ld.formatted_address as lead_formatted_address',
                        'ld.address as lead_address',
                        'lh.followup_status_id as his_status_id',
                        'purchase_custom_fields.field_value as pl_purchase_date',
                        'following_custom_fields.field_value as fl_purchase_date'
                )
                ->where(function ($query) use ($start_date, $end_date, $followupStatus) {
                    $query->where(function ($subquery) use ($start_date, $end_date, $followupStatus) {
                        $subquery->whereIn('lh.followup_status_id', $followupStatus)
                        ->whereBetween(DB::raw('DATE(lh.created_at)'), [$start_date, $end_date]);
                    })->where(function ($subquery) use ($start_date, $end_date) {
                        $subquery->where(function ($subquery) use ($start_date, $end_date) {
                            $subquery->whereNotNull('following_custom_fields.field_value')
                            ->whereBetween(
                                    DB::raw("STR_TO_DATE(following_custom_fields.field_value, '%m/%d/%Y')"),
                                    [date('Y-m-d', strtotime($start_date)), date('Y-m-d', strtotime($end_date))]
                            );
                        })->orWhere(function ($subquery) use ($start_date, $end_date) {
                            $subquery->whereNotNull('purchase_custom_fields.field_value')
                            ->whereBetween(
                                    DB::raw("STR_TO_DATE(purchase_custom_fields.field_value, '%m/%d/%Y')"),
                                    [date('Y-m-d', strtotime($start_date)), date('Y-m-d', strtotime($end_date))]
                            );
                        });
                    });
                })
                ->groupBy('lh.lead_id')
                ->get();

        $data = [];
        foreach ($dashboardData as $key => $value) {
            $data[$key]['Sr No'] = $key + 1;
            $data[$key]['Lead Id'] = $value->lead_id;
            $data[$key]['Homeowner Name'] = $value->lead_title;
            if ($value->lead_formatted_address != '') {
                $data[$key]['Homeowner Address'] = $value->lead_formatted_address;
            } else {
                $data[$key]['Homeowner Address'] = $value->lead_address;
            }


            $purchaeData = \App\Models\PurchaseLead::where('lead_id', '=', $value->lead_id)
                    ->join('purchase_custom_fields', 'purchase_leads.id', 'purchase_custom_fields.followup_lead_id')
                    ->where('purchase_custom_fields.followup_view_id', '=', 29)
                    ->first();
            if (!empty($purchaeData->field_value)) {
                $data[$key]['Purchase Date'] = $purchaeData->field_value;
            } else {
                $FollowingLead = \App\Models\FollowingLead::where('lead_id', '=', $value->lead_id)
                        ->join('following_custom_fields', 'following_leads.id', 'following_custom_fields.followup_lead_id')
                        ->where('following_custom_fields.followup_view_id', '=', 29)
                        ->first();
                if (!empty($FollowingLead->field_value)) {
                    $data[$key]['Purchase Date'] = $FollowingLead->field_value;
                } else {
                    $data[$key]['Purchase Date'] = date('m/d/Y', strtotime($value->created_at));
                }
            }
        }

        return Excel::download(new \App\Exports\PurchaseLeadHisotryExport($data), 'Purchase_Conversion_Rate_' . date('m-d-Y') . '.csv');
    }

    public function purchaseList(Request $request) {

        $time_clause = '';
        $time_clause1 = '';

        if (!empty($request->date)) {
            $date = json_decode($request->date);
            $startDate = Carbon::createFromFormat('Y-m-d', $date->start)->format('Y/m/d');
            $endDate = Carbon::createFromFormat('Y-m-d', $date->end)->format('Y/m/d');
            $startDate = dateTimezoneChangeNew($startDate . ' 00:00:00');
            $endDate = dateTimezoneChangeNew($endDate . ' 23:59:59');
            $startDate = date('Y-m-d H:i:s', strtotime($startDate));
            $endDate = date('Y-m-d H:i:s', strtotime($endDate));
            $time_clause = "lead_history.created_at between '" . $startDate . "' and '" . $endDate . "' ";
            $time_clause1 = "following_leads.contract_date BETWEEN '" . $startDate . "' and '" . $endDate . "' ";
        }

//        $modal_user_clause = '';
//        if (!empty($request->filter_user_id) AND $request->filter_user_id != 'null' AND $request->filter_user_id != '[object Object]') {
//            $modal_user_clause = '  lead_history.assign_id IN (' . $request->filter_user_id . ')';
//            $db_user_clause = 'AND  lead_history.assign_id IN (' . $request->filter_user_id . ')';
//            $db_user_clause_for_knock = ' user_lead_knocks.user_id IN (' . $request->filter_user_id . ')';
//        }
//
//        $followingLeads = getFollowingLeadAll($new_input);
//        $new_input_id = [];
//        foreach ($followingLeads as $lead) {
//            $new_input_id['lead_ids'][] = $lead->lead_id;
//        }
//
//        $followStatusApptNotKept = FollowStatus::where('title', 'Purchased')->first();
//
//        $DisplayedPerScreen = settingForRecordsDisplayedPerScreen();
//
//        $PurchaseStatusleadIds = DB::table('lead_history')
//                        ->select('lead_history.lead_id')
//                        ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
//                        ->whereIn('lead_history.lead_id', $new_input_id['lead_ids'])
//                        ->where('lead_history.followup_status_id', '!=', 0)
//                        ->where('lead_history.status_id', 0)
//                        ->where('lead_history.followup_status_id', $followStatusApptNotKept->id)
//                        ->when($month_clause, function ($query, $month_clause) {
//                            return $query->whereRaw($month_clause);
//                        })
//                        ->when($time_clause, function ($query, $time_clause) {
//                            return $query->whereRaw($time_clause);
//                        })
//                        ->when($modal_user_clause, function ($query, $modal_user_clause) {
//                            return $query->whereRaw($modal_user_clause);
//                        })
//                        ->orderBy('lead_history.ID', 'ASC')
//                        ->pluck('lead_history.lead_id')->toArray();
//
//        $time_clause = '';
//        $time_clause1 = '';
//
//        if (!empty($request->date)) {
//            $date = json_decode($request->date);
//            $startDate = Carbon::createFromFormat('Y-m-d', $date->start)->format('Y/m/d');
//            $endDate = Carbon::createFromFormat('Y-m-d', $date->end)->format('Y/m/d');
//            $startDate = dateTimezoneChangeNew($startDate . ' 00:00:00');
//            $endDate = dateTimezoneChangeNew($endDate . ' 23:59:59');
//            $startDate = date('Y-m-d H:i:s', strtotime($startDate));
//            $endDate = date('Y-m-d H:i:s', strtotime($endDate));
//            $time_clause1 = "(
//                STR_TO_DATE(following_custom_fields.field_value, '%m/%d/%Y') BETWEEN '" . $startDate . "' AND '" . $endDate . "'
//                OR
//                STR_TO_DATE(purchase_custom_fields.field_value, '%m/%d/%Y') BETWEEN '" . $startDate . "' AND '" . $endDate . "'
//            )";
//        }
//
//        $followingLeads = DB::table('following_leads')
//                ->select('following_leads.lead_id')
//                ->join('following_custom_fields', 'following_leads.id', 'following_custom_fields.followup_lead_id')
//                ->where('following_custom_fields.followup_view_id', '=', 29)
//                ->where('following_custom_fields.field_value', '!=', null)
//                ->whereRaw("STR_TO_DATE(following_custom_fields.field_value, '%m/%d/%Y') BETWEEN '$startDate' AND '$endDate'")
//                ->orderBy('following_leads.lead_id', 'desc');
//        $followingLeads = $followingLeads->get();
//
//        $leadIds = $followingLeads->pluck('lead_id')->toArray();
//
//
//
//        $purchaseLeads = DB::table('purchase_leads')
//                ->select('purchase_leads.lead_id')
//                ->join('purchase_custom_fields', 'purchase_leads.id', 'purchase_custom_fields.followup_lead_id')
//                ->where('purchase_custom_fields.followup_view_id', '=', 29)
//                ->where('purchase_custom_fields.field_value', '!=', null)
//                ->whereRaw("STR_TO_DATE(purchase_custom_fields.field_value, '%m/%d/%Y') BETWEEN '$startDate' AND '$endDate'")
//                ->orderBy('purchase_leads.lead_id', 'desc');
//        $purchaseLeads = $purchaseLeads->get();
//
//        $pleadIds = $purchaseLeads->pluck('lead_id')->toArray();
//
//        $leadIds = array_merge($leadIds, $PurchaseStatusleadIds);
//
//        $mergedArray = array_merge($leadIds, $pleadIds);
//
//        $uniqueValues = array_values(array_unique($mergedArray));
//
//        $DisplayedPerScreen = settingForRecordsDisplayedPerScreen();
//
//        if (isset($DisplayedPerScreen->purchase_conversion_rate)) {
////            $dashboardData = $dashboardData = DB::table('lead_history')
////                    ->select('lead_history.*', 'following_custom_fields.field_value as purchase_date',
////                            'purchase_custom_fields.field_value as p_purchase_date',
////                            'lead_history.followup_status_id as his_status_id',
////                            'lead_detail.title as lead_title',
////                            'lead_detail.formatted_address as lead_formatted_address')
////                    ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
////                    ->join('following_leads', 'lead_history.lead_id', 'following_leads.lead_id')
////                    ->join('following_custom_fields', 'following_leads.id', 'following_custom_fields.followup_lead_id')
//////                    ->where('following_custom_fields.followup_view_id', '=', 29)
////                    ->join('purchase_leads', 'lead_history.lead_id', 'purchase_leads.lead_id')
////                    ->join('purchase_custom_fields', 'purchase_leads.id', 'purchase_custom_fields.followup_lead_id')
//////                    ->where('purchase_custom_fields.followup_view_id', '=', 29)
////                    ->whereIn('lead_history.lead_id', $uniqueValues)
////                    ->when($time_clause1, function ($query, $time_clause1) {
////                        return $query->whereRaw($time_clause1);
////                    })
////                    ->orderBy('purchase_custom_fields.field_value', 'DESC')
////                    ->orderBy('following_custom_fields.field_value', 'DESC')
////                    ->groupBy('lead_id')
////                    ->paginate($DisplayedPerScreen->purchase_conversion_rate);
//
//            $dashboardData = $dashboardData = DB::table('lead_history')
//                    ->select('lead_history.*',
//                            'lead_history.followup_status_id as his_status_id',
//                            'lead_detail.title as lead_title',
//                            'lead_detail.formatted_address as lead_formatted_address',
//                            'lead_detail.address as lead_address')
//                    ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
//                    ->whereIn('lead_history.lead_id', $uniqueValues)
//                    ->orderByDesc('id')
//                    ->groupBy('lead_id')
//                    ->paginate($DisplayedPerScreen->purchase_conversion_rate);
//        } else {
//
//            $dashboardData = $dashboardData = DB::table('lead_history')
//                    ->select('lead_history.*',
//                            'lead_history.followup_status_id as his_status_id',
//                            'lead_detail.title as lead_title',
//                            'lead_detail.formatted_address as lead_formatted_address',
//                            'lead_detail.address as lead_address')
//                    ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
//                    ->whereIn('lead_history.lead_id', $uniqueValues)
//                    ->orderByDesc('id')
//                    ->groupBy('lead_id')
//                    ->paginate(10);
//        }

        $followStatusApptNotKept = FollowStatus::where('title', 'Purchased')->first();
        $followupStatus = [];
        $followupStatus[] = $followStatusApptNotKept->id;
        $start_date = $startDate;
        $end_date = $endDate;

        $dashboardData = DB::table('lead_history as lh')
                ->leftJoin('purchase_leads as pl', 'lh.lead_id', '=', 'pl.lead_id')
                ->leftJoin('following_leads as fl', 'lh.lead_id', '=', 'fl.lead_id')
                ->join('lead_detail as ld', 'lh.lead_id', '=', 'ld.id')
                ->leftJoin('following_custom_fields', function ($join) {
                    $join->on('fl.id', '=', 'following_custom_fields.followup_lead_id')
                    ->where('following_custom_fields.followup_view_id', 29);
                })
                ->leftJoin('purchase_custom_fields', function ($join) {
                    $join->on('pl.id', '=', 'purchase_custom_fields.followup_lead_id')
                    ->where('purchase_custom_fields.followup_view_id', 29);
                })
                ->select('lh.*', 'fl.contract_date as contract_date', 'pl.contract_date as p_contract_date',
                        'fl.id as fl_id',
                        'ld.title as lead_title',
                        'ld.formatted_address as lead_formatted_address',
                        'ld.address as lead_address',
                        'lh.followup_status_id as his_status_id',
                        'purchase_custom_fields.field_value as pl_purchase_date',
                        'following_custom_fields.field_value as fl_purchase_date'
                )
                ->where(function ($query) use ($start_date, $end_date, $followupStatus) {
                    $query->where(function ($subquery) use ($start_date, $end_date, $followupStatus) {
                        $subquery->whereIn('lh.followup_status_id', $followupStatus)
                        ->whereBetween(DB::raw('DATE(lh.created_at)'), [$start_date, $end_date]);
                    })->where(function ($subquery) use ($start_date, $end_date) {
                        $subquery->where(function ($subquery) use ($start_date, $end_date) {
                            $subquery->whereNotNull('following_custom_fields.field_value')
                            ->whereBetween(
                                    DB::raw("STR_TO_DATE(following_custom_fields.field_value, '%m/%d/%Y')"),
                                    [date('Y-m-d', strtotime($start_date)), date('Y-m-d', strtotime($end_date))]
                            );
                        })->orWhere(function ($subquery) use ($start_date, $end_date) {
                            $subquery->whereNotNull('purchase_custom_fields.field_value')
                            ->whereBetween(
                                    DB::raw("STR_TO_DATE(purchase_custom_fields.field_value, '%m/%d/%Y')"),
                                    [date('Y-m-d', strtotime($start_date)), date('Y-m-d', strtotime($end_date))]
                            );
                        });
                    });
                })
                ->groupBy('lh.lead_id')
                ->paginate(10);

        $agents = User::getTenantUserListSub($param);

        return view('tenant.leadknocks.DashboardPurchase', compact('dashboardData', 'agents'));
    }

    public function contractListExport(Request $request) {
        $time_clause = '';
        $time_clause1 = '';

        if (!empty($request->date)) {
            $date = json_decode($request->date);
            $startDate = Carbon::createFromFormat('Y-m-d', $date->start)->format('Y/m/d');
            $endDate = Carbon::createFromFormat('Y-m-d', $date->end)->format('Y/m/d');
            $startDate = dateTimezoneChangeNew($startDate . ' 00:00:00');
            $endDate = dateTimezoneChangeNew($endDate . ' 23:59:59');
            $startDate = date('Y-m-d H:i:s', strtotime($startDate));
            $endDate = date('Y-m-d H:i:s', strtotime($endDate));
            $time_clause = "lead_history.created_at between '" . $startDate . "' and '" . $endDate . "' ";
            $time_clause1 = "following_leads.contract_date BETWEEN '" . $startDate . "' and '" . $endDate . "' ";
        }

        $followStatusPurchased = FollowStatus::where('title', 'Purchased')->first();

        $followStatusContract = FollowStatus::where('title', 'Contract')->first();

        $start_date = date('Y-m-d', strtotime($startDate));
        $end_date = date('Y-m-d', strtotime($endDate));

        $followupStatus = [];

        $followupStatus[] = $followStatusPurchased->id;

        $followupStatus[] = $followStatusContract->id;

        $start_date = date('Y-m-d', strtotime($startDate));
        $end_date = date('Y-m-d', strtotime($endDate));

        $followupStatus = [];
        $followupStatus[] = $followStatusPurchased->id;
        $followupStatus[] = $followStatusContract->id;

        $PurchasedId = $followStatusPurchased->id;
        $ContractId = $followStatusContract->id;

        $dashboardData = DB::table('lead_history as lh')
                ->leftJoin('purchase_leads as pl', 'lh.lead_id', '=', 'pl.lead_id')
                ->leftJoin('following_leads as fl', 'lh.lead_id', '=', 'fl.lead_id')
                ->join('lead_detail as ld', 'lh.lead_id', '=', 'ld.id')
                ->join('user', 'fl.investor_id', 'user.id')
                ->leftJoin('following_custom_fields', function ($join) {
                    $join->on('fl.id', '=', 'following_custom_fields.followup_lead_id')
                    ->where('following_custom_fields.followup_view_id', 29);
                })
                ->leftJoin('purchase_custom_fields', function ($join) {
                    $join->on('pl.id', '=', 'purchase_custom_fields.followup_lead_id')
                    ->where('purchase_custom_fields.followup_view_id', 29);
                })
                ->select('lh.*',
                        'fl.contract_date as contract_date', 'pl.contract_date as p_contract_date',
                        'fl.id as fl_id',
                        'ld.title as lead_title',
                        'ld.formatted_address as lead_formatted_address',
                        'ld.address as lead_address',
                        'lh.followup_status_id as his_status_id',
                        'purchase_custom_fields.field_value as pl_purchase_date',
                        'following_custom_fields.field_value as fl_purchase_date',
                        'user.first_name as invester_first_name',
                        'user.last_name as invester_last_name'
                )
                ->where(function ($query) use ($start_date, $end_date, $PurchasedId) {
                    $query->where(function ($subquery) use ($start_date, $end_date, $PurchasedId) {
                        $subquery->where('lh.followup_status_id', '=', $PurchasedId)
                        ->whereBetween(DB::raw('DATE(lh.created_at)'), [$start_date, $end_date]);
                    })->where(function ($subquery) use ($start_date, $end_date) {
                        $subquery->where(function ($subquery) use ($start_date, $end_date) {
                            $subquery->whereNotNull('following_custom_fields.field_value')
                            ->whereBetween(
                                    DB::raw("STR_TO_DATE(following_custom_fields.field_value, '%m/%d/%Y')"),
                                    [date('Y-m-d', strtotime($start_date)), date('Y-m-d', strtotime($end_date))]
                            );
                        })->orWhere(function ($subquery) use ($start_date, $end_date) {
                            $subquery->whereNotNull('purchase_custom_fields.field_value')
                            ->whereBetween(
                                    DB::raw("STR_TO_DATE(purchase_custom_fields.field_value, '%m/%d/%Y')"),
                                    [date('Y-m-d', strtotime($start_date)), date('Y-m-d', strtotime($end_date))]
                            );
                        });
                    });
                })
                ->orWhere(function ($query) use ($start_date, $end_date, $ContractId) {
                    $query->where(function ($subquery) use ($start_date, $end_date, $ContractId) {
                        $subquery->where('lh.followup_status_id', $ContractId)
                        ->whereBetween(DB::raw('DATE(lh.created_at)'), [$start_date, $end_date]);
                    })->where(function ($subquery) use ($start_date, $end_date) {
                        $subquery->where(function ($subquery) use ($start_date, $end_date) {
                            $subquery->whereNotNull('pl.contract_date')
                            ->whereBetween('pl.contract_date', [$start_date, $end_date]);
                        })->orWhere(function ($subquery) use ($start_date, $end_date) {
                            $subquery->whereNotNull('fl.contract_date')
                            ->whereBetween('fl.contract_date', [$start_date, $end_date]);
                        });
                    });
                })
                ->orderBy('pl.contract_date', 'DESC')
                ->orderBy('fl.contract_date', 'DESC')
                ->groupBy('lh.lead_id')
                ->get();

//        $dashboardData = DB::table('lead_history')
//                ->select('lead_history.*', 'following_leads.contract_date as contract_date', 'purchase_leads.contract_date as p_contract_date', 'following_leads.id as fl_id', 'lead_detail.title as lead_title', 'lead_detail.formatted_address as lead_formatted_address', 'lead_history.followup_status_id as his_status_id')
//                ->leftJoin('following_leads', 'lead_history.lead_id', '=', 'following_leads.lead_id')
//                ->leftJoin('purchase_leads', 'purchase_leads.lead_id', '=', 'following_leads.lead_id')
//                ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
//                ->whereIn('lead_history.lead_id', $uniqueIds)
//                ->where('following_leads.is_lead_up', 0)
//                ->when($month_clause, function ($query, $month_clause) {
//                    return $query->whereRaw($month_clause);
//                })
//                ->when($time_clause, function ($query, $time_clause) {
//                    return $query->whereRaw($time_clause);
//                })
//                ->when($time_clause1, function ($query, $time_clause1) {
//                    return $query->whereRaw($time_clause1);
//                })
//                ->when($modal_user_clause, function ($query, $modal_user_clause) {
//                    return $query->whereRaw($modal_user_clause);
//                })
//                ->orderBy('purchase_leads.contract_date', 'DESC')
//                ->orderBy('following_leads.contract_date', 'DESC')
//                ->groupBy('lead_id')
//                ->get();


        $data = [];
        foreach ($dashboardData as $key => $value) {
            $data[$key]['Sr No'] = $key + 1;
            $data[$key]['Lead Id'] = $value->lead_id;
            $data[$key]['Homeowner Name'] = $value->lead_title;

            if ($value->lead_formatted_address != '') {
                $data[$key]['Homeowner Address'] = $value->lead_formatted_address;
            } else {
                $data[$key]['Homeowner Address'] = $value->lead_address;
            }
            if (isset($value->p_contract_date) AND $value->p_contract_date != null) {
                $data[$key]['Contract date'] = date('m/d/Y', strtotime($value->p_contract_date));
            } else {
                $data[$key]['Contract date'] = date('m/d/Y', strtotime($value->contract_date));
            }
            if (isset($value->pl_purchase_date) AND $value->pl_purchase_date != null) {
                $data[$key]['Purchase Date'] = $value->pl_purchase_date;
            } else {
                $data[$key]['Purchase Date'] = $value->fl_purchase_date;
            }
            if ($value->followup_status_id == 11 OR $value->followup_status_id == 1) {
                $data[$key]['Lead History Created Date'] = date('m/d/Y', strtotime($value->created_at));
            } else {
                $data[$key]['Lead History Created Date'] = '';
            }
            if ($value->followup_status_id == 11) {
                $data[$key]['Status Name'] = 'Purchased';
            } elseif ($value->followup_status_id == 1) {
                $data[$key]['Status Name'] = 'Contract';
            } else {
                $data[$key]['Status Name'] = '';
            }
        }

        return Excel::download(new \App\Exports\ContractLeadHisotryExport($data), 'Contract_Conversion_Rate_' . date('m-d-Y') . '.csv');
    }

    public function contractList(Request $request) {

        $time_clause = '';
        $time_clause1 = '';

        if (!empty($request->date)) {
            $date = json_decode($request->date);
            $startDate = Carbon::createFromFormat('Y-m-d', $date->start)->format('Y/m/d');
            $endDate = Carbon::createFromFormat('Y-m-d', $date->end)->format('Y/m/d');
            $startDate = dateTimezoneChangeNew($startDate . ' 00:00:00');
            $endDate = dateTimezoneChangeNew($endDate . ' 23:59:59');
            $startDate = date('Y-m-d H:i:s', strtotime($startDate));
            $endDate = date('Y-m-d H:i:s', strtotime($endDate));
            $time_clause = "lead_history.created_at between '" . $startDate . "' and '" . $endDate . "' ";
            $time_clause1 = "following_leads.contract_date BETWEEN '" . $startDate . "' and '" . $endDate . "' ";
        }

        $modal_user_clause = '';
        if (!empty($request->filter_user_id) AND $request->filter_user_id != 'null' AND $request->filter_user_id != '[object Object]') {
            $modal_user_clause = '  lead_history.assign_id IN (' . $request->filter_user_id . ')';
            $db_user_clause = 'AND  lead_history.assign_id IN (' . $request->filter_user_id . ')';
            $db_user_clause_for_knock = ' user_lead_knocks.user_id IN (' . $request->filter_user_id . ')';
        }

        $followingLeads = getFollowingLeadAll($new_input);
        $new_input_id = [];
        foreach ($followingLeads as $lead) {
            $new_input_id['lead_ids'][] = $lead->lead_id;
        }

        $followStatusPurchased = FollowStatus::where('title', 'Purchased')->first();

        $PurchaseStatusleadIds = DB::table('lead_history')
                        ->select('lead_history.lead_id')
                        ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
                        ->whereIn('lead_history.lead_id', $new_input_id['lead_ids'])
                        ->where('lead_history.followup_status_id', '!=', 0)
                        ->where('lead_history.status_id', 0)
                        ->where('lead_history.followup_status_id', $followStatusPurchased->id)
                        ->when($month_clause, function ($query, $month_clause) {
                            return $query->whereRaw($month_clause);
                        })
                        ->when($time_clause, function ($query, $time_clause) {
                            return $query->whereRaw($time_clause);
                        })
                        ->when($modal_user_clause, function ($query, $modal_user_clause) {
                            return $query->whereRaw($modal_user_clause);
                        })
                        ->orderBy('lead_history.ID', 'ASC')
                        ->pluck('lead_history.lead_id')->toArray();

        if (!empty($request->date)) {
            $time_clause1 = "(
                STR_TO_DATE(following_custom_fields.field_value, '%m/%d/%Y') BETWEEN '" . $startDate . "' AND '" . $endDate . "'
                OR
                STR_TO_DATE(purchase_custom_fields.field_value, '%m/%d/%Y') BETWEEN '" . $startDate . "' AND '" . $endDate . "'
            )";
        }

        $followingLeads = DB::table('following_leads')
                ->select('following_leads.lead_id')
                ->join('following_custom_fields', 'following_leads.id', 'following_custom_fields.followup_lead_id')
                ->where('following_custom_fields.followup_view_id', '=', 29)
                ->where('following_custom_fields.field_value', '!=', null)
                ->whereRaw("STR_TO_DATE(following_custom_fields.field_value, '%m/%d/%Y') BETWEEN '$startDate' AND '$endDate'")
                ->orderBy('following_leads.lead_id', 'desc');
        $followingLeads = $followingLeads->get();

        $followingLeadIds = $followingLeads->pluck('lead_id')->toArray();

        $purchaseLeads = DB::table('purchase_leads')
                ->select('purchase_leads.lead_id')
                ->join('purchase_custom_fields', 'purchase_leads.id', 'purchase_custom_fields.followup_lead_id')
                ->where('purchase_custom_fields.followup_view_id', '=', 29)
                ->where('purchase_custom_fields.field_value', '!=', null)
                ->whereRaw("STR_TO_DATE(purchase_custom_fields.field_value, '%m/%d/%Y') BETWEEN '$startDate' AND '$endDate'")
                ->orderBy('purchase_leads.lead_id', 'desc');
        $purchaseLeads = $purchaseLeads->get();

        $purchaseLeadIds = $purchaseLeads->pluck('lead_id')->toArray();

        $followingLeadIds = array_merge($followingLeadIds, $PurchaseStatusleadIds);

        $mergedArrayPurchaseIds = array_merge($followingLeadIds, $purchaseLeadIds);

        $uniqueValuesPurchaseIds = array_values(array_unique($mergedArrayPurchaseIds));

        ////// contract calculation code started

        $followStatusContract = FollowStatus::where('title', 'Contract')->first();

        $contractStatusleadIds = DB::table('lead_history')
                        ->select('lead_history.lead_id')
                        ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
                        ->whereIn('lead_history.lead_id', $new_input_id['lead_ids'])
                        ->where('lead_history.followup_status_id', '!=', 0)
                        ->where('lead_history.status_id', 0)
                        ->where('lead_history.followup_status_id', $followStatusContract->id)
                        ->when($month_clause, function ($query, $month_clause) {
                            return $query->whereRaw($month_clause);
                        })
                        ->when($time_clause, function ($query, $time_clause) {
                            return $query->whereRaw($time_clause);
                        })
                        ->when($modal_user_clause, function ($query, $modal_user_clause) {
                            return $query->whereRaw($modal_user_clause);
                        })
                        ->orderBy('lead_history.ID', 'ASC')
                        ->pluck('lead_history.lead_id')->toArray();

        $time_clause = '';
        $time_clause1 = '';

        $followingLeads = DB::table('following_leads')
                ->select('following_leads.lead_id')
                ->join('following_custom_fields', 'following_leads.id', 'following_custom_fields.followup_lead_id')
                ->where('following_leads.contract_date', '!=', null)
                ->where('following_leads.is_expired', '=', 0)
                ->whereRaw("STR_TO_DATE(following_custom_fields.field_value, '%m/%d/%Y') BETWEEN '$startDate' AND '$endDate'")
                ->orderBy('following_leads.lead_id', 'desc');
        $followingLeads = $followingLeads->get();

        $leadIds = $followingLeads->pluck('lead_id')->toArray();

        $purchaseLeads = DB::table('purchase_leads')
                ->join('purchase_custom_fields', 'purchase_leads.id', 'purchase_custom_fields.followup_lead_id')
                ->select('purchase_leads.lead_id')
                ->where('purchase_leads.contract_date', '!=', null)
                ->where('purchase_leads.is_expired', '=', 0)
                ->whereRaw("STR_TO_DATE(purchase_custom_fields.field_value, '%m/%d/%Y') BETWEEN '$startDate' AND '$endDate'")
                ->orderBy('purchase_leads.lead_id', 'desc');

        $purchaseLeads = $purchaseLeads->get();

        $pleadIds = $purchaseLeads->pluck('lead_id')->toArray();

        $leadIds = array_merge($leadIds, $contractStatusleadIds);

        $mergedArray = array_merge($leadIds, $pleadIds);

        $mergedArray = array_merge($mergedArray, $uniqueValuesPurchaseIds);

        $uniqueIds = array_unique($mergedArray);

        if (!empty($request->date)) {
            $date = json_decode($request->date);
            $startDate = Carbon::createFromFormat('Y-m-d', $date->start)->format('Y/m/d');
            $endDate = Carbon::createFromFormat('Y-m-d', $date->end)->format('Y/m/d');
            $startDate = dateTimezoneChangeNew($startDate . ' 00:00:00');
            $endDate = dateTimezoneChangeNew($endDate . ' 23:59:59');
            $startDate = date('Y-m-d H:i:s', strtotime($startDate));
            $endDate = date('Y-m-d H:i:s', strtotime($endDate));
            $time_clause1 = "(
                        following_leads.contract_date BETWEEN '" . $startDate . "' AND '" . $endDate . "'
                        OR
                        purchase_leads.contract_date BETWEEN '" . $startDate . "' AND '" . $endDate . "'
                    )";
        }


        $DisplayedPerScreen = settingForRecordsDisplayedPerScreen();
        if (isset($DisplayedPerScreen->contract_conversion_rate)) {
            $dashboardData = DB::table('lead_history')
                    ->select('lead_history.*', 'following_leads.contract_date as contract_date',
                            'purchase_leads.contract_date as p_contract_date',
                            'following_leads.id as fl_id', 'lead_detail.title as lead_title', 'lead_detail.formatted_address as lead_formatted_address', 'following_leads.contract_date as contract_date', 'lead_history.followup_status_id as his_status_id')
                    ->leftJoin('following_leads', 'lead_history.lead_id', '=', 'following_leads.lead_id')
                    ->leftJoin('purchase_leads', 'purchase_leads.lead_id', '=', 'following_leads.lead_id')
                    ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
                    ->where('following_leads.is_lead_up', 0)
                    ->whereIn('lead_history.lead_id', $uniqueIds)
//                    ->whereNotIn('lead_history.lead_id', $uniqueValuesPurchaseIds)
                    ->orderBy('purchase_leads.contract_date', 'DESC')
                    ->orderBy('following_leads.contract_date', 'DESC')
                    ->groupBy('lead_id')
                    ->paginate($DisplayedPerScreen->contract_conversion_rate);
        } else {

//            $dashboardData = DB::table('lead_history as lh')
//                    ->leftJoin('purchase_leads as pl', 'lh.lead_id', '=', 'pl.lead_id')
//                    ->leftJoin('following_leads as fl', 'lh.lead_id', '=', 'fl.lead_id')
//                    ->join('lead_detail as ld', 'lh.lead_id', '=', 'ld.id')
//                    ->leftJoin('following_custom_fields', function($join) {
//                        $join->on('fl.id', '=', 'following_custom_fields.followup_lead_id')
//                        ->where('following_custom_fields.followup_view_id', 29);
//                    })
//                    ->leftJoin('purchase_custom_fields', function($join) {
//                        $join->on('pl.id', '=', 'purchase_custom_fields.followup_lead_id')
//                        ->where('purchase_custom_fields.followup_view_id', 29);
//                    })
//                    ->select('lh.*', 'fl.contract_date as contract_date', 'pl.contract_date as p_contract_date',
//                            'fl.id as fl_id',
//                            'ld.title as lead_title',
//                            'ld.formatted_address as lead_formatted_address',
//                            'ld.address as lead_address',
//                            'lh.followup_status_id as his_status_id',
//                            'purchase_custom_fields.field_value as pl_purchase_date',
//                            'following_custom_fields.field_value as fl_purchase_date'
//                    )
//                    ->where(function ($query) use ($start_date, $end_date, $followupStatus) {
//                        $query->where(function ($subquery) use ($start_date, $end_date) {
//                            $subquery->whereNotNull('pl.contract_date')
//                            ->whereBetween(DB::raw('DATE(pl.contract_date)'), [$start_date, $end_date]);
//                        })->orWhere(function ($subquery) use ($start_date, $end_date) {
//                            $subquery->whereNotNull('fl.contract_date')
//                            ->whereBetween(DB::raw('DATE(fl.contract_date)'), [$start_date, $end_date]);
//                        })->orWhere(function ($subquery) use ($start_date, $end_date, $followupStatus) {
//                            $subquery->whereIn('lh.followup_status_id', $followupStatus)
//                            ->whereBetween(DB::raw('DATE(lh.created_at)'), [$start_date, $end_date]);
//                        })->orWhere(function ($subquery) use ($start_date, $end_date) {
//                            $subquery->whereNotNull('following_custom_fields.field_value')
//                            ->whereBetween(DB::raw('DATE(following_custom_fields.field_value)'), [$start_date, $end_date]);
//                        })->orWhere(function ($subquery) use ($start_date, $end_date) {
//                            $subquery->whereNotNull('purchase_custom_fields.field_value')
//                            ->whereBetween(DB::raw('DATE(purchase_custom_fields.field_value)'), [$start_date, $end_date]);
//                        });
//                    })
//                    ->groupBy('lh.lead_id')
//                    ->paginate(100);

            $start_date = date('Y-m-d', strtotime($startDate));
            $end_date = date('Y-m-d', strtotime($endDate));

            $followupStatus = [];
            $followupStatus[] = $followStatusPurchased->id;
            $followupStatus[] = $followStatusContract->id;

            $PurchasedId = $followStatusPurchased->id;
            $ContractId = $followStatusContract->id;

            $dashboardData = DB::table('lead_history as lh')
                    ->leftJoin('purchase_leads as pl', 'lh.lead_id', '=', 'pl.lead_id')
                    ->leftJoin('following_leads as fl', 'lh.lead_id', '=', 'fl.lead_id')
                    ->join('lead_detail as ld', 'lh.lead_id', '=', 'ld.id')
                    ->join('user', 'fl.investor_id', 'user.id')
                    ->leftJoin('following_custom_fields', function ($join) {
                        $join->on('fl.id', '=', 'following_custom_fields.followup_lead_id')
                        ->where('following_custom_fields.followup_view_id', 29);
                    })
                    ->leftJoin('purchase_custom_fields', function ($join) {
                        $join->on('pl.id', '=', 'purchase_custom_fields.followup_lead_id')
                        ->where('purchase_custom_fields.followup_view_id', 29);
                    })
                    ->select('lh.*',
                            'fl.contract_date as contract_date', 'pl.contract_date as p_contract_date',
                            'fl.id as fl_id',
                            'ld.title as lead_title',
                            'ld.formatted_address as lead_formatted_address',
                            'ld.address as lead_address',
                            'lh.followup_status_id as his_status_id',
                            'purchase_custom_fields.field_value as pl_purchase_date',
                            'following_custom_fields.field_value as fl_purchase_date',
                            'user.first_name as invester_first_name',
                            'user.last_name as invester_last_name'
                    )
                    ->where(function ($query) use ($start_date, $end_date, $ContractId) {
                        $query->where(function ($subquery) use ($start_date, $end_date, $ContractId) {
                            $subquery->where('lh.followup_status_id', $ContractId)
                            ->whereBetween(DB::raw('DATE(lh.created_at)'), [$start_date, $end_date]);
                        })->where(function ($subquery) use ($start_date, $end_date) {
                            $subquery->where(function ($subquery) use ($start_date, $end_date) {
                                $subquery->whereNotNull('pl.contract_date')
                                ->whereBetween('pl.contract_date', [$start_date, $end_date]);
                            })->orWhere(function ($subquery) use ($start_date, $end_date) {
                                $subquery->whereNotNull('fl.contract_date')
                                ->whereBetween('fl.contract_date', [$start_date, $end_date]);
                            });
                        });
                    })
                    ->orWhere(function ($query) use ($start_date, $end_date, $PurchasedId) {
                        $query->where(function ($subquery) use ($start_date, $end_date, $PurchasedId) {
                            $subquery->where('lh.followup_status_id', '=', $PurchasedId)
                            ->whereBetween(DB::raw('DATE(lh.created_at)'), [$start_date, $end_date])
                            ->where(function ($subquery) use ($start_date, $end_date, $ContractId) {
                                $subquery->where('lh.followup_status_id', $ContractId)
                                ->whereBetween(DB::raw('DATE(lh.created_at)'), [$start_date, $end_date]);
                            })->where(function ($subquery) use ($start_date, $end_date) {
                                $subquery->where(function ($subquery) use ($start_date, $end_date) {
                                    $subquery->whereNotNull('pl.contract_date')
                                    ->whereBetween('pl.contract_date', [$start_date, $end_date]);
                                })->orWhere(function ($subquery) use ($start_date, $end_date) {
                                    $subquery->whereNotNull('fl.contract_date')
                                    ->whereBetween('fl.contract_date', [$start_date, $end_date]);
                                });
                            });
                        })->where(function ($subquery) use ($start_date, $end_date) {
                            $subquery->where(function ($subquery) use ($start_date, $end_date) {
                                $subquery->whereNotNull('following_custom_fields.field_value')
                                ->whereBetween(
                                        DB::raw("STR_TO_DATE(following_custom_fields.field_value, '%m/%d/%Y')"),
                                        [date('Y-m-d', strtotime($start_date)), date('Y-m-d', strtotime($end_date))]
                                );
                            })->orWhere(function ($subquery) use ($start_date, $end_date) {
                                $subquery->whereNotNull('purchase_custom_fields.field_value')
                                ->whereBetween(
                                        DB::raw("STR_TO_DATE(purchase_custom_fields.field_value, '%m/%d/%Y')"),
                                        [date('Y-m-d', strtotime($start_date)), date('Y-m-d', strtotime($end_date))]
                                );
                            });
                        });
                    })
                    ->orderBy('pl.contract_date', 'DESC')
                    ->orderBy('fl.contract_date', 'DESC')
                    ->groupBy('lh.lead_id')
                    ->paginate(10);

//            $dashboardData = DB::table('lead_history')
//                    ->select('lead_history.*', 
//                    'following_leads.contract_date as contract_date',
//                    'purchase_leads.contract_date as p_contract_date', 
//                    'following_leads.id as fl_id', 
//                    'lead_detail.title as lead_title',
//                     'lead_detail.formatted_address as lead_formatted_address', 
//                     'lead_history.followup_status_id as his_status_id'
//                     )
//                    ->leftJoin('following_leads', 'lead_history.lead_id', '=', 'following_leads.lead_id')
//                    ->leftJoin('purchase_leads', 'purchase_leads.lead_id', '=', 'following_leads.lead_id')
//                    ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
//                    ->whereIn('lead_history.lead_id', $uniqueIds)
//                    ->when($time_clause, function ($query, $time_clause) {
//                        return $query->whereRaw($time_clause);
//                    })
////                    ->where('purchase_leads.contract_date', '!=', null)
////                    ->where('following_leads.contract_date', '!=', null)
////                    ->whereNotIn('lead_history.lead_id', $uniqueValuesPurchaseIds)                    
//                    ->orderBy('purchase_leads.contract_date', 'DESC')
//                    ->orderBy('following_leads.contract_date', 'DESC')
//                    ->groupBy('lead_id')
//                    ->paginate(100);
        }

//        $followStatusPurchased = FollowStatus::where('title', 'Purchased')->first();
//
//        $followStatusContract = FollowStatus::where('title', 'Contract')->first();
//
//        $followupStatus = [];
//
//        $followupStatus[] = $followStatusPurchased->id;
//
//        $followupStatus[] = $followStatusContract->id;
//
//        $time_clause = '';
//        $time_clause1 = '';
//
//        if (!empty($request->date)) {
//            $date = json_decode($request->date);
//            $startDate = Carbon::createFromFormat('Y-m-d', $date->start)->format('Y/m/d');
//            $endDate = Carbon::createFromFormat('Y-m-d', $date->end)->format('Y/m/d');
//            $startDate = dateTimezoneChangeNew($startDate . ' 00:00:00');
//            $endDate = dateTimezoneChangeNew($endDate . ' 23:59:59');
//            $startDate = date('Y-m-d H:i:s', strtotime($startDate));
//            $endDate = date('Y-m-d H:i:s', strtotime($endDate));
//            $time_clause = "lead_history.created_at between '" . $startDate . "' and '" . $endDate . "' ";
//            $time_clause1 = "following_leads.contract_date BETWEEN '" . $startDate . "' and '" . $endDate . "' ";
//        }
//
//        $dashboardData = DB::table('lead_history')
//                ->select('lead_history.*',
//                        'following_leads.contract_date as contract_date',
//                        'purchase_leads.contract_date as p_contract_date',
//                        'following_leads.id as fl_id',
//                        'lead_detail.title as lead_title',
//                        'lead_detail.formatted_address as lead_formatted_address',
//                        'lead_history.followup_status_id as his_status_id'
//                )
//                ->leftJoin('following_leads', 'lead_history.lead_id', '=', 'following_leads.lead_id')
//                ->leftJoin('purchase_leads', 'purchase_leads.lead_id', '=', 'following_leads.lead_id')
//                ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
//                ->whereIn('lead_history.followup_status_id', $followupStatus)
//                ->when($time_clause, function ($query, $time_clause) {
//                    return $query->whereRaw($time_clause);
//                })
////                ->where('purchase_leads.contract_date', '!=', null)
////                ->where('following_leads.contract_date', '!=', null)
////                    ->whereNotIn('lead_history.lead_id', $uniqueValuesPurchaseIds)                    
//                ->orderBy('purchase_leads.contract_date', 'DESC')
//                ->orderBy('following_leads.contract_date', 'DESC')
//                ->groupBy('lead_id')
//                ->paginate(100);


        $agents = User::getTenantUserListSub($param);

        return view('tenant.leadknocks.DashboardContract', compact('dashboardData', 'agents'));
    }

    public function appointmentsKeptListExport(Request $request) {
        $time_clause = '';
        $time_clause1 = '';

        if (!empty($request->date)) {
            $date = json_decode($request->date);
            $startDate = Carbon::createFromFormat('Y-m-d', $date->start)->format('Y/m/d');
            $endDate = Carbon::createFromFormat('Y-m-d', $date->end)->format('Y/m/d');
            $startDate = dateTimezoneChangeNew($startDate . ' 00:00:00');
            $endDate = dateTimezoneChangeNew($endDate . ' 23:59:59');
            $startDate = date('Y-m-d H:i:s', strtotime($startDate));
            $endDate = date('Y-m-d H:i:s', strtotime($endDate));
            $time_clause = "lead_history.created_at between '" . $startDate . "' and '" . $endDate . "' ";
            $time_clause1 = "following_leads.contract_date BETWEEN '" . $startDate . "' and '" . $endDate . "' ";
        }

        $modal_user_clause = '';
        if (!empty($request->filter_user_id) AND $request->filter_user_id != 'null') {
            $modal_user_clause = '  lead_history.assign_id IN (' . $request->filter_user_id . ')';
            $db_user_clause = 'AND  lead_history.assign_id IN (' . $request->filter_user_id . ')';
            $db_user_clause_for_knock = ' user_lead_knocks.user_id IN (' . $request->filter_user_id . ')';
        }

        $followingLeads = getFollowingLeadAll($new_input);
        $new_input_id = [];
        foreach ($followingLeads as $lead) {
            $new_input_id['lead_ids'][] = $lead->lead_id;
        }

        $followStatusApptNotKept = FollowStatus::where('title', 'Appt Kept')->first();

        $dashboardData = DB::table('lead_history')
                ->select('lead_history.*', 'lead_history.followup_status_id as his_status_id', 'lead_detail.title as lead_title', 'lead_detail.formatted_address as lead_formatted_address')
                ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
                ->whereIn('lead_history.lead_id', $new_input_id['lead_ids'])
                ->where('lead_history.followup_status_id', '!=', 0)
                ->where('lead_history.status_id', 0)
                ->where('followup_status_id', $followStatusApptNotKept->id)
                ->when($month_clause, function ($query, $month_clause) {
                    return $query->whereRaw($month_clause);
                })
                ->when($time_clause, function ($query, $time_clause) {
                    return $query->whereRaw($time_clause);
                })
                ->when($modal_user_clause, function ($query, $modal_user_clause) {
                    return $query->whereRaw($modal_user_clause);
                })
                ->orderBy('ID', 'ASC')
                ->get();

        $data = [];
        foreach ($dashboardData as $key => $value) {
            $data[$key]['Sr No'] = $key + 1;
            $data[$key]['Id'] = $value->id;
            $data[$key]['Lead Id'] = $value->lead_id;
            $data[$key]['Homeowner Name'] = $value->lead_title;
            $data[$key]['Homeowner Address'] = $value->lead_formatted_address;
            $data[$key]['Created on'] = dateTimezoneChange($value->created_at);
        }

        return Excel::download(new \App\Exports\LeadHisotryExport($data), 'Appointments_KEPT_Conversion_Rate_' . date('m-d-Y') . '.csv');
    }

    public function appointmentsKeptList(Request $request) {
        $time_clause = '';
        $time_clause1 = '';

        if (!empty($request->date)) {
            $date = json_decode($request->date);
            $startDate = Carbon::createFromFormat('Y-m-d', $date->start)->format('Y/m/d');
            $endDate = Carbon::createFromFormat('Y-m-d', $date->end)->format('Y/m/d');
            $startDate = dateTimezoneChangeNew($startDate . ' 00:00:00');
            $endDate = dateTimezoneChangeNew($endDate . ' 23:59:59');
            $startDate = date('Y-m-d H:i:s', strtotime($startDate));
            $endDate = date('Y-m-d H:i:s', strtotime($endDate));
            $time_clause = "lead_history.created_at between '" . $startDate . "' and '" . $endDate . "' ";
            $time_clause1 = "following_leads.contract_date BETWEEN '" . $startDate . "' and '" . $endDate . "' ";
        }

        $modal_user_clause = '';
        if (!empty($request->filter_user_id) AND $request->filter_user_id != 'null') {
            $modal_user_clause = '  lead_history.assign_id IN (' . $request->filter_user_id . ')';
            $db_user_clause = 'AND  lead_history.assign_id IN (' . $request->filter_user_id . ')';
            $db_user_clause_for_knock = ' user_lead_knocks.user_id IN (' . $request->filter_user_id . ')';
        }

        $followingLeads = getFollowingLeadAll($new_input);
        $new_input_id = [];
        foreach ($followingLeads as $lead) {
            $new_input_id['lead_ids'][] = $lead->lead_id;
        }

        $followStatusApptNotKept = FollowStatus::where('title', 'Appt Kept')->first();

        $DisplayedPerScreen = settingForRecordsDisplayedPerScreen();
        if (isset($DisplayedPerScreen->appointments_kept_conversion_rate)) {
            $dashboardData = DB::table('lead_history')
                    ->select('lead_history.*', 'lead_history.followup_status_id as his_status_id', 'lead_detail.title as lead_title', 'lead_detail.formatted_address as lead_formatted_address')
                    ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
                    ->whereIn('lead_history.lead_id', $new_input_id['lead_ids'])
                    ->where('lead_history.followup_status_id', '!=', 0)
                    ->where('lead_history.status_id', 0)
                    ->where('followup_status_id', $followStatusApptNotKept->id)
                    ->when($month_clause, function ($query, $month_clause) {
                        return $query->whereRaw($month_clause);
                    })
                    ->when($time_clause, function ($query, $time_clause) {
                        return $query->whereRaw($time_clause);
                    })
                    ->when($modal_user_clause, function ($query, $modal_user_clause) {
                        return $query->whereRaw($modal_user_clause);
                    })
//                ->groupBy('lead_history.lead_id')
                    ->orderBy('ID', 'ASC')
                    ->paginate($DisplayedPerScreen->appointments_kept_conversion_rate);
        } else {
            $dashboardData = DB::table('lead_history')
                    ->select('lead_history.*', 'lead_history.followup_status_id as his_status_id', 'lead_detail.title as lead_title', 'lead_detail.formatted_address as lead_formatted_address')
                    ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
                    ->whereIn('lead_history.lead_id', $new_input_id['lead_ids'])
                    ->where('lead_history.followup_status_id', '!=', 0)
                    ->where('lead_history.status_id', 0)
                    ->where('followup_status_id', $followStatusApptNotKept->id)
                    ->when($month_clause, function ($query, $month_clause) {
                        return $query->whereRaw($month_clause);
                    })
                    ->when($time_clause, function ($query, $time_clause) {
                        return $query->whereRaw($time_clause);
                    })
                    ->when($modal_user_clause, function ($query, $modal_user_clause) {
                        return $query->whereRaw($modal_user_clause);
                    })
//                ->groupBy('lead_history.lead_id')
                    ->orderBy('ID', 'ASC')
                    ->paginate(10);
        }


        $agents = User::getTenantUserListSub($param);

        return view('tenant.leadknocks.DashboardAppointmentKept', compact('dashboardData', 'agents'));
    }

    public function appointmentsRequestedListExport(Request $request) {
        $time_clause = '';
        $time_clause1 = '';
        if (!empty($request->date)) {
            $date = json_decode($request->date);
            $startDate = Carbon::createFromFormat('Y-m-d', $date->start)->format('Y/m/d');
            $endDate = Carbon::createFromFormat('Y-m-d', $date->end)->format('Y/m/d');
            $startDate = dateTimezoneChangeNew($startDate . ' 00:00:00');
            $endDate = dateTimezoneChangeNew($endDate . ' 23:59:59');
            $startDate = date('Y-m-d H:i:s', strtotime($startDate));
            $endDate = date('Y-m-d H:i:s', strtotime($endDate));
            $time_clause = "lead_history.created_at between '" . $startDate . "' and '" . $endDate . "' ";
            $time_clause1 = "following_leads.contract_date BETWEEN '" . $startDate . "' and '" . $endDate . "' ";
        }

        $modal_user_clause = '';
        if (!empty($request->filter_user_id) AND $request->filter_user_id != 'null') {
            $modal_user_clause = '  lead_history.assign_id IN (' . $request->filter_user_id . ')';
            $db_user_clause = 'AND  lead_history.assign_id IN (' . $request->filter_user_id . ')';
            $db_user_clause_for_knock = ' user_lead_knocks.user_id IN (' . $request->filter_user_id . ')';
        }

        $followingLeads = getFollowingLeadAll($new_input);
        $new_input_id = [];
        foreach ($followingLeads as $lead) {
            $new_input_id['lead_ids'][] = $lead->lead_id;
        }
        $followStatusApptRequest = FollowStatus::where('title', 'Appt Request')->first();
        $leadStatusApptRequest = Status::where('title', 'Appt Request')
                ->orderBy('id', 'desc')
                ->first();

        $dashboardData = DB::table('lead_history')->select(
                        'lead_history.*', 'lead_history.followup_status_id as his_status_id', 'lead_detail.title as lead_title', 'lead_detail.formatted_address as lead_formatted_address'
                )
                ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
                ->whereIn('lead_history.lead_id', $new_input_id['lead_ids'])
                ->where(function ($query) use ($leadStatusApptRequest, $followStatusApptRequest) {
                    $query->where('lead_history.followup_status_id', $followStatusApptRequest->id)
                    ->orWhere('lead_history.status_id', $leadStatusApptRequest->id);
                })
                ->when($month_clause, function ($query, $month_clause) {
                    return $query->whereRaw($month_clause);
                })
                ->when($time_clause, function ($query, $time_clause) {
                    return $query->whereRaw($time_clause);
                })
                ->when($modal_user_clause, function ($query, $modal_user_clause) {
                    return $query->whereRaw($modal_user_clause);
                })
                ->orderBy('id', 'DESC')
                ->get();

        $data = [];
        foreach ($dashboardData as $key => $value) {
            $data[$key]['Sr No'] = $key + 1;
            $data[$key]['Id'] = $value->id;
            $data[$key]['Lead Id'] = $value->lead_id;
            $data[$key]['Homeowner Name'] = $value->lead_title;
            $data[$key]['Homeowner Address'] = $value->lead_formatted_address;
            $data[$key]['Created on'] = dateTimezoneChange($value->created_at);
        }

        return Excel::download(new \App\Exports\LeadHisotryExport($data), 'Appointments_Requested_Conversion_Rate_' . date('m-d-Y') . '.csv');
    }

    public function appointmentsRequestedList(Request $request) {
        $time_clause = '';
        $time_clause1 = '';

        if (!empty($request->date)) {
            $date = json_decode($request->date);
            $startDate = Carbon::createFromFormat('Y-m-d', $date->start)->format('Y/m/d');
            $endDate = Carbon::createFromFormat('Y-m-d', $date->end)->format('Y/m/d');
            $startDate = dateTimezoneChangeNew($startDate . ' 00:00:00');
            $endDate = dateTimezoneChangeNew($endDate . ' 23:59:59');
            $startDate = date('Y-m-d H:i:s', strtotime($startDate));
            $endDate = date('Y-m-d H:i:s', strtotime($endDate));
            $time_clause = "lead_history.created_at between '" . $startDate . "' and '" . $endDate . "' ";
            $time_clause1 = "following_leads.contract_date BETWEEN '" . $startDate . "' and '" . $endDate . "' ";
        }

        $modal_user_clause = '';
        if (!empty($request->filter_user_id) AND $request->filter_user_id != 'null') {
            $modal_user_clause = '  lead_history.assign_id IN (' . $request->filter_user_id . ')';
            $db_user_clause = 'AND  lead_history.assign_id IN (' . $request->filter_user_id . ')';
            $db_user_clause_for_knock = ' user_lead_knocks.user_id IN (' . $request->filter_user_id . ')';
        }

        $followingLeads = getFollowingLeadAll($new_input);
        $new_input_id = [];
        foreach ($followingLeads as $lead) {
            $new_input_id['lead_ids'][] = $lead->lead_id;
        }

        $followStatusApptRequest = FollowStatus::where('title', 'Appt Request')->first();
//        25

        $leadStatusApptRequest = Status::where('title', 'Appt Request')
                ->orderBy('id', 'desc')
                ->first();
//        46 and 95


        $DisplayedPerScreen = settingForRecordsDisplayedPerScreen();
        if (isset($DisplayedPerScreen->appointments_requested_conversion_rate)) {
            $dashboardData = DB::table('lead_history')->select(
                            'lead_history.*', 'lead_history.followup_status_id as his_status_id', 'lead_detail.title as lead_title', 'lead_detail.formatted_address as lead_formatted_address'
                    )
                    ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
                    ->join('user', 'lead_history.assign_id', 'user.id')
                    ->where('user.user_group_id', "!=", 3)
                    ->where(function ($query) use ($leadStatusApptRequest, $followStatusApptRequest) {
                        $query->where('lead_history.followup_status_id', $followStatusApptRequest->id)
                        ->orWhere('lead_history.status_id', $leadStatusApptRequest->id);
                    })
                    ->when($month_clause, function ($query, $month_clause) {
                        return $query->whereRaw($month_clause);
                    })
                    ->when($time_clause, function ($query, $time_clause) {
                        return $query->whereRaw($time_clause);
                    })
                    ->when($modal_user_clause, function ($query, $modal_user_clause) {
                        return $query->whereRaw($modal_user_clause);
                    })
                    ->orderBy('id', 'DESC')
                    ->paginate($DisplayedPerScreen->appointments_requested_conversion_rate);
        } else {
            $dashboardData = DB::table('lead_history')->select(
                            'lead_history.*', 'lead_history.followup_status_id as his_status_id', 'lead_detail.title as lead_title', 'lead_detail.formatted_address as lead_formatted_address'
                    )
                    ->join('lead_detail', 'lead_history.lead_id', 'lead_detail.id')
                    ->join('user', 'lead_history.assign_id', 'user.id')
                    ->where('user.user_group_id', "!=", 3)
                    ->where(function ($query) use ($leadStatusApptRequest, $followStatusApptRequest) {
                        $query->where('lead_history.followup_status_id', $followStatusApptRequest->id)
                        ->orWhere('lead_history.status_id', $leadStatusApptRequest->id);
                    })
                    ->when($month_clause, function ($query, $month_clause) {
                        return $query->whereRaw($month_clause);
                    })
                    ->when($time_clause, function ($query, $time_clause) {
                        return $query->whereRaw($time_clause);
                    })
                    ->when($modal_user_clause, function ($query, $modal_user_clause) {
                        return $query->whereRaw($modal_user_clause);
                    })
                    ->orderBy('id', 'DESC')
                    ->paginate(10);
        }


        $agents = User::getTenantUserListSub($param);

        return view('tenant.leadknocks.DashboardAppointmentRequested', compact('dashboardData', 'agents'));
    }

    public function editHistory(Request $request) {
        $history = LeadHistory::find($request->id);

        $status_id = $history->followup_status_id;

        if ($status_id == 0) {
            $status_id = $history->status_id;

            $status = Status::whereNull('deleted_at')->pluck('title', 'id')->toArray();
        } else {
            $status = FollowStatus::whereNull('deleted_at')->pluck('title', 'id')->toArray();
        }

        $lead = Lead::find($history->lead_id);

        return view('tenant.leadknocks.editHistory', compact('lead', 'history', 'status', 'status_id'));
    }

    public function updateHistory(Request $request) {

        $history = LeadHistory::find($request->id);

        if ($history->followup_status_id == 0) {
            $followup_status = Status::where('id', '=', $request->status_id)->first();
            $updated_data = [];
            $updated_data['followup_status_id'] = 0;
            $updated_data['title'] = $followup_status->title . ' status updated.';
            $updated_data['status_id'] = $request->status_id;
            $history->update($updated_data);

            $userLeadKnocks = UserLeadKnocks::where('lead_history_id', '=', $request->id)->first();

            if (!empty($userLeadKnocks)) {
                $updated_knock_data = [];
                $updated_knock_data['status_id'] = $request->status_id;
                $updated_knock_data['updated_at'] = NOW();
                $userLeadKnocks->update($updated_knock_data);
            }
        } else {
            $followup_status = FollowStatus::where('id', '=', $request->status_id)->first();
            $updated_data = [];
            $updated_data['followup_status_id'] = $request->status_id;
            $updated_data['title'] = $followup_status->title . ' status updated from Follow Up Lead Management.';
            $updated_data['status_id'] = 0;
            $history->update($updated_data);
        }
        return back();
    }

}
