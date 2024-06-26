<?php

namespace App\Http\Controllers;

use App\Http\Middleware\LoginAuth;
use App\Libraries\History;
use App\Models\Lead;
use App\Models\LeadCustomField;
use App\Models\Media;
use App\Models\UserCommission;
use App\Models\FollowingLead;
use App\Models\FollowUpLeadViewSetp;
use App\Models\LeadQuery;
use App\Models\Status;
use App\Models\FollowStatus;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UserCommissionReportExport;
use Carbon\Carbon;
use App\Models\TemplateFields;
use App\Models\TenantCustomField;
use App\Models\TenantQuery;
use App\Models\LeadHistory;
use App\Models\TenantTemplate;
use App\Models\Type;
use App\Exports\TeamPerformanceExport;
use App\Models\User;
use App\Models\UserLeadAppointment;
use App\Models\UserLeadKnocks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Facades\Mail;
use Twilio;
use Twilio\Rest\Client as TwilioClient;
use App\Models\Alerts;
use App\Models\LeadType;
use App\Http\Resources\TeamPerformanceResource;
use Validator;
use DateTime;
use App\Models\PurchaseLead;
use App\Exports\LeadExport;
use App\Http\Resources\LeadExportResource;

class LeadController extends Controller {

    /**
     * Write code on Method
     *
     * @return response()
     */
    function __construct(Request $request) {

        parent::__construct();
        $this->middleware(LoginAuth::class, ['only' => ['index', 'indexNew', 'indexAPI', 'newIndexAPI', 'store', 'update', 'edit', 'show', 'userAssignLead', 'userList'
                , 'history', 'updateQuery', 'createAppointment', 'createOutBoundAppointment', 'leadReport', 'indexView', 'addView', 'listView'
                , 'uploadMedia', 'leadStatsReport', 'bulkUpdate', 'leadUserReport', 'uploadLeads', 'dashboard', 'templateList', 'templateShow'
                , 'templateDestroy', 'statusListView', 'alertsListView', 'templateFieldList', 'addTemplate', 'updateTemplateFieldIndex', 'executeAppointment'
                , 'deleteTemplate', 'historyExport', 'leadStatusUserReport', 'destroy', 'leadsHistoryExport'
        ]]);

        userUpdateLastActivity(\Request::header('user-token'), $request->user_login_id);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $param_rules['search'] = 'sometimes';

        $time_slot_map['today'] = 'INTERVAL 1 MONTH';
        $time_slot_map['yesterday'] = 'INTERVAL 1 MONTH';
        $time_slot_map['week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['last_year'] = 'INTERVAL 1 YEAR';

        $param['search'] = isset($request['search']) ? $request['search'] : '';
        $param['latitude'] = isset($request['latitude']) ? $request['latitude'] : '';
        $param['longitude'] = isset($request['longitude']) ? $request['longitude'] : '';
        $param['lead_type_id'] = isset($request['lead_type_id']) ? $request['lead_type_id'] : '';
        $param['user_ids'] = isset($request['target_user_id']) ? trim($request['target_user_id']) : '';
        $param['status_ids'] = isset($request['status_id']) ? trim($request['status_id']) : '';
        $param['radius'] = isset($request['radius']) ? $request['radius'] : 500;
        $param['time_slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $time_slot_map[$request['time_slot']] : '' : '';
        $param['slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $request['time_slot'] : '' : '';

        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];

        $response = Lead::getListIndex($param);

        return $this->__sendResponse('Lead', $response, 200, 'Lead list retrieved successfully.');
    }

    public function indexNew(Request $request) {
        $param_rules['search'] = 'sometimes';
        $time_slot_map['today'] = 'INTERVAL 1 MONTH';
        $time_slot_map['yesterday'] = 'INTERVAL 1 MONTH';
        $time_slot_map['week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['last_year'] = 'INTERVAL 1 YEAR';
        $param['search'] = isset($request['search']) ? $request['search'] : '';
        $param['latitude'] = isset($request['latitude']) ? $request['latitude'] : '';
        $param['longitude'] = isset($request['longitude']) ? $request['longitude'] : '';
        $param['lead_type_id'] = isset($request['lead_type_id']) ? $request['lead_type_id'] : '';
        $param['user_ids'] = isset($request['target_user_id']) ? trim($request['target_user_id']) : '';
        $param['status_ids'] = isset($request['status_id']) ? trim($request['status_id']) : '';
        $param['radius'] = isset($request['radius']) ? $request['radius'] : 500;
        $param['time_slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $time_slot_map[$request['time_slot']] : '' : '';
        $param['slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $request['time_slot'] : '' : '';
        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];

        $response = Lead::getListIndexNew($param);

        return $this->__sendResponse('LeadList', $response, 200, 'Lead list retrieved successfully.');
    }

    public function indexAPI(Request $request) {
        $param_rules['search'] = 'sometimes';
        $time_slot_map['today'] = 'INTERVAL 1 MONTH';
        $time_slot_map['yesterday'] = 'INTERVAL 1 MONTH';
        $time_slot_map['week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['last_year'] = 'INTERVAL 1 YEAR';
        $param['search'] = isset($request['search']) ? $request['search'] : '';
        $param['latitude'] = isset($request['latitude']) ? $request['latitude'] : '';
        $param['longitude'] = isset($request['longitude']) ? $request['longitude'] : '';
        $param['lead_type_id'] = isset($request['lead_type_id']) ? $request['lead_type_id'] : '';
        $param['user_ids'] = isset($request['target_user_id']) ? trim($request['target_user_id']) : '';
        $param['status_ids'] = isset($request['status_id']) ? trim($request['status_id']) : '';
        $param['radius'] = isset($request['radius']) ? $request['radius'] : 500;
        $param['time_slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $time_slot_map[$request['time_slot']] : '' : '';
        $param['slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $request['time_slot'] : '' : '';
        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);
        if ($this->__is_error == true)
            return $response;

        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];

        // userUpdateLastActivity($request->user_id);

        $response = Lead::getListApiIndex($param);

        return $this->__sendResponse('Lead', $response, 200, 'Lead list retrieved successfully.');
    }

    public function newIndexAPI(Request $request) {
        $param_rules['search'] = 'sometimes';
        $time_slot_map['today'] = 'INTERVAL 1 MONTH';
        $time_slot_map['yesterday'] = 'INTERVAL 1 MONTH';
        $time_slot_map['week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['last_year'] = 'INTERVAL 1 YEAR';
        $param['search'] = isset($request['search']) ? $request['search'] : '';
        $param['latitude'] = isset($request['latitude']) ? $request['latitude'] : '';
        $param['longitude'] = isset($request['longitude']) ? $request['longitude'] : '';
        $param['lead_type_id'] = isset($request['lead_type_id']) ? $request['lead_type_id'] : '';
        $param['user_ids'] = isset($request['target_user_id']) ? trim($request['target_user_id']) : '';
        $param['status_ids'] = isset($request['status_id']) ? trim($request['status_id']) : '';
        $param['radius'] = isset($request['radius']) ? $request['radius'] : 500;
        $param['time_slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $time_slot_map[$request['time_slot']] : '' : '';
        $param['slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $request['time_slot'] : '' : '';
        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);
        if ($this->__is_error == true)
            return $response;

        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];

        // userUpdateLastActivity($request->user_id);

        $response = Lead::getListApiIndexNew($param);

        return $this->__sendResponse('LeadMap', $response, 200, 'Lead list retrieved successfully.');
    }

    public function leadsMap(Request $request) {

        return view('tenant.lead.leads_map');
    }

    public function dashboard(Request $request) {

        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];
        $param['is_paginate'] = false;

        $response['status'] = Status::getList($param);
        $response['agent'] = User::getTenantUserListSub($param);
        $response['type'] = Type::whereIn('tenant_id', [$request['company_id']])->whereNull('deleted_at')->get();

        $this->__view = 'tenant.dashboard.home';

        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('Lead', $response, 200, 'Lead has been retrieved successfully.');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexView(Request $request) {

        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];
        $param['is_paginate'] = false;


        $param['search'] = isset($request['search']) ? $request['search'] : '';
        $param['latitude'] = isset($request['latitude']) ? $request['latitude'] : '';
        $param['longitude'] = isset($request['longitude']) ? $request['longitude'] : '';
        $param['radius'] = isset($request['radius']) ? $request['radius'] : 500;
        $param['user_ids'] = isset($request['user_ids']) ? trim($request['user_ids']) : '';
        $param['status_ids'] = isset($request['status_ids']) ? trim($request['status_ids']) : '';
        $param['start_date'] = isset($request['start_date']) ? $request['start_date'] : '';
        $param['end_date'] = isset($request['end_date']) ? $request['end_date'] : '';
        $param['order_by'] = isset($request['order_by']) ? $request['order_by'] : 'id';
        $param['order_type'] = isset($request['order_type']) ? $request['order_type'] : 'desc';
        $param['is_status_group_by'] = 1;
        $param['is_web'] = 1;

        $param['export'] = isset($request['export']) ? $request['export'] : FALSE;
        // status list , with count of leads, percentage of total leads
        // search on created at date range

        $lead_response = Lead::getList($param);

        info($lead_response);

        $lead_status_count = [];
        foreach ($lead_response as $lead) {
            $lead_status_count[$lead['status_id']] = $lead['lead_count'];
        }

        $response['status'] = Status::getList($param);

        $status_count = 0;
        $status_total = 0;
        foreach ($response['status'] as $key => $status) {
            $status['lead_count'] = (isset($lead_status_count[$status->id])) ? $lead_status_count[$status->id] : 0;
            $status_count++;
            $status_total += $status['lead_count'];
        }

        if ($status_total) {
            foreach ($response['status'] as $key => $status) {
                $response['status'][$key]['lead_percentage'] = round((($status['lead_count'] / $status_total) * 100), 1);
            }
        }

        $response['agent'] = User::getTenantUserList($param);
        $response['type'] = Type::whereIn('tenant_id', [$request['company_id']])->whereNull('deleted_at')->orderBy('title')->get();
        $response['templates'] = Lead::getTemplate($request['company_id']);

        // , 'owner','first_name', 'last_name' // removing owner into first and last name
        // $response['columns'] = Config::get('constants.LEAD_DEFAULT_COLUMNS');
        // $default_columns = [];
        $result_default_columns = TenantCustomField::getTenantDefaultFields($request['company_id']);

//       $tenantQuery = TenantQuery::where('tenant_id', $request['company_id'])->where('type', 'summary')->pluck('query')->toArray();
        $tenantQuery = TenantQuery::where('tenant_id', $request['company_id'])->where('type', 'summary')->pluck('query', 'id')->toArray();

        foreach ($result_default_columns as $result_default_column) {
            if ($result_default_column['key'] == 'lead_name') {
                // $result_default_column['key_mask'] = 'title';
                // $result_default_column['key'] = 'title';
            }
            // info($result_default_column['key']);
            // info($result_default_column['key_mask']);

            $default_columns[] = str_replace(Config::get('constants.SPECIAL_CHARACTERS.IGNORE'), Config::get('constants.SPECIAL_CHARACTERS.REPLACE'), $result_default_column['key']);
            // $default_columns_title[] = str_replace(Config::get('constants.SPECIAL_CHARACTERS.IGNORE'), Config::get('constants.SPECIAL_CHARACTERS.REPLACE'),$result_default_column['key_mask']);
            $default_columns_title[] = empty($result_default_column['key_mask']) ? '' : $result_default_column['key_mask'];
        }

        $response['columns'] = (!is_null($default_columns) && count($default_columns)) ? $default_columns : Config::get('constants.LEAD_DEFAULT_COLUMNS');
        $response['columns_title'] = (!is_null($default_columns) && count($default_columns_title)) ? $default_columns_title : Config::get('constants.LEAD_DEFAULT_COLUMNS');

        if (!empty($tenantQuery)) {

            $tenantQueryNew = [];

            foreach ($tenantQuery as $key => $value) {
                if ($key == 8) {
//                if ($value == 'Notes (Add to Top, Include Date, Your Name and Notes)' AND $value == 'Notes (Add to Top, Include Date and Your Name)') {
                    $tenantQueryNew[$key] = preg_replace('/[^A-Za-z0-9. -]/', '', $value);
//                }
                }
            }

            $response['columns'] = array_merge($response['columns'], $tenantQueryNew);
            $response['columns_title'] = array_merge($response['columns_title'], $tenantQueryNew);
        }

        $response['orderable_columns'] = Config::get('constants.LEAD_DEFAULT_COLUMNS');
        $response['orderable_columns'][] = 'title';
        $response['orderable_columns'][] = 'City';
        $response['orderable_columns'][] = 'State';
        $response['orderable_columns'][] = 'County';
        $response['orderable_columns'][] = 'Address';
        $response['orderable_columns'][] = 'Homeowner Name';
        $response['orderable_columns'][] = 'Admin Notes';
        $response['orderable_columns'][] = 'Lead Value';
        $response['orderable_columns'][] = 'Original Loan';
        $response['orderable_columns'][] = 'Loan Date';
        $response['orderable_columns'][] = 'Sq Ft';
        $response['orderable_columns'][] = 'Yr Blt';
        $response['orderable_columns'][] = 'EQ';
        $response['orderable_columns'][] = 'Auction';
        $response['orderable_columns'][] = 'Lead Status';
        $response['orderable_columns'][] = 'Lead Type';
        $response['orderable_columns'][] = 'Assigned To';
        $response['orderable_columns'][] = 'Zip';

        $response['orderable_columns'][] = 'Mortgagee';
        $response['orderable_columns'][] = 'Loan Type';
        $response['orderable_columns'][] = 'Trustee';
        $response['orderable_columns'][] = 'Source';
        $response['orderable_columns'][] = 'Owner Address - If Not Owner Occupied';
        $response['orderable_columns'][] = 'Loan Mod';
        $response['orderable_columns'][] = 'Notes';

        // $response['column_ids'] = ['title','lead_type','address', 'city', 'zip_code'];
        /* $custom_fields =TenantCustomField::getList($param);
          foreach($custom_fields as $field)
          $response['columns'][] = str_replace(["'"],['&#039;'],$field['key']); */

        $this->__view = 'tenant.lead.lead_mgmt';

        $this->__is_paginate = false;
        $this->__collection = false;
        $this->__is_collection = false;

        return $this->__sendResponse('lead', $response, 200, 'Lead list retrieved successfully.');
    }

    public function listView(Request $request) {
        $all_leads = Lead::get();

        ini_set('max_execution_time', '300');
        ini_set('memory_limit', '-1');
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

        $param['export'] = isset($request['export']) ? $request['export'] : FALSE;

        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);
        if ($this->__is_error == true)
            return $response;
        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];

        $columns = [];
        if ($param['export'] === 'true') {
            $request['lead_ids'] = isset($request['lead_ids']) ? empty($request['lead_ids']) ? '' : $request['lead_ids'] : '';
            $is_download = isset($request['is_download']) ? $request['is_download'] : 1;
            $param['lead_ids'] = explode(',', $request['lead_ids']);

            $response_data = Lead::getLeadWCustomField($param);

            $leadData = LeadExportResource::collection($response_data);

            $export = Excel::download(new LeadExport($leadData), 'lead' . date('m-d-Y') . '.csv');

            if (!$is_download) {
                $this->__collection = false;
                $this->__is_paginate = false;
                return $this->__sendResponse('Lead', [], 200, 'Caching lead list export successfully.');
            }
            return $export;
        } else {
            $param['is_web'] = 1;
            if ((strtolower($this->call_mode) == 'api')) {
                $param['is_web'] = 0;
            }

            $response = \App\Http\Resources\LeadIndex::collection(Lead::getListIndex($param));
        }
        $this->__collection = false;

        return $this->__sendResponse('Lead', $response, 200, 'Lead list retrieved successfully.');
    }

    function custom_array_merge(&$array1, &$array2) {
        $result = Array();
        foreach ($array1 as $key_1 => &$value_1) {
            foreach ($array2 as $key_1 => $value_2) {
                if ($value_1['id'] == $value_2['lead_id']) {
                    $result[] = array_merge($value_1, $value_2);
                }
            }
        }
        return $result;
    }

    public function export($columns = [], $data, $filename = "", $ignoreCols = [], $is_download = 1, $ignoe_column_map = 0) {
        $filename = (empty($filename)) ? 'temp2.csv' : $filename;

        if (isset($this->__params['user_id'])) {
            $tmp_file_name = "{$this->__params['user_id']}_$filename";
        } else {
            $tmp_file_name = "$filename";
        }

        if (!isset($this->__params['page']))
            $this->__params['page'] = 1;

        $file_mode = 'a+';
        if ($this->__params['page'] == 1)
            $file_mode = 'w';

        $file = fopen(public_path($tmp_file_name), $file_mode);
        $columns = array_diff($columns, $ignoreCols);

        $new_columns = [];
        $new_columns = $columns;

        foreach ($new_columns as $key => $column) {
            if ($column == 'zip_code') {
                $new_columns[$key] = 'Zip';
            } else if ($column == 'is_follow_up') {
                $new_columns[$key] = 'Is Follow Up';
            } else if ($column == 'is_expired') {
                $new_columns[$key] = 'Is Retired';
            } else {
                $new_columns[$key] = str_replace('_', ' ', $column);
                $new_columns[$key] = ucwords($new_columns[$key]);
            }
        }

        if ($this->__params['page'] == 1)
            fputcsv($file, $new_columns);
        foreach ($data as $dataRow) {
            if ($ignoe_column_map) {
                fputcsv($file, $dataRow);
                continue;
            }

            $csvRow = [];

            foreach ($columns as $column) {

                if ($column == 'Notes Add to Top Include Date Your Name and Notes') {

                    $csvRow['Notes Add to Top Include Date Your Name and Notes'] = $dataRow['Notes_Add_to_Top_Include_Date_Your_Name_and_Notes'];
                } else if ($column == 'Loan Mod') {
                    $csvRow['Loan Mod'] = $dataRow['Loan Mod'];
                } else if ($column == 'State') {
                    $csvRow['State'] = $dataRow['state'];
                } else if ($column == 'Lead Type') {
                    $csvRow['Lead Type'] = $dataRow['lead_type'];
                } else if ($column == 'Lead Status') {
                    $csvRow['Lead Status'] = $dataRow['lead_status'];
                } else if ($column == 'Address') {
                    $csvRow['Address'] = $dataRow['address'];
                } else if ($column == 'County') {
                    $csvRow['County'] = $dataRow['county'];
                } else if ($column == 'City') {
                    $csvRow['City'] = $dataRow['city'];
                } else if ($column == 'Auction') {
                    $csvRow['Auction'] = $dataRow['auction'];
                } else if ($column == 'Lead Value') {
                    $csvRow['Lead Value'] = $dataRow['lead_value'];
                } else if ($column == 'Original Loan') {
                    $csvRow['Original Loan'] = $dataRow['original_loan'];
                } else if ($column == 'Loan Date') {
                    $csvRow['Loan Date'] = $dataRow['loan_date'];
                } else if ($column == 'Sq Ft') {
                    $csvRow['Sq Ft'] = $dataRow['sq_ft'];
                } else if ($column == 'Yr Blt') {
                    $csvRow['Yr Blt'] = $dataRow['yr_blt'];
                } else if ($column == 'EQ') {
                    $csvRow['EQ'] = $dataRow['eq'];
                } else if ($column == 'Mortgagee') {
                    $csvRow['Mortgagee'] = $dataRow['mortgagee'];
                } else if ($column == 'Owner Address - If Not Owner Occupied') {
                    $csvRow['Owner Address - If Not Owner Occupied'] = $dataRow['owner_address'];
                } else if ($column == 'Loan Type') {
                    $csvRow['loan_type'] = $dataRow['loan_type'];
                } else if ($column == 'Trustee') {
                    $csvRow['Trustee'] = $dataRow['trustee'];
                } else if ($column == 'Source') {
                    $csvRow['Source'] = $dataRow['source'];
                } else if ($column == 'Zip') {
                    $csvRow['Zip'] = $dataRow['zip_code'];
                } else if ($column == 'Admin Notes') {
                    $csvRow['Admin Notes'] = $dataRow['admin_notes'];
                } else if ($column == 'Is Retired') {
                    if ($dataRow['is_expired'] == 1) {
                        $csvRow['Is Retired'] = 'Yes';
                    } else {
                        $csvRow['Is Retired'] = 'No';
                    }
                } else {
                    if (!in_array($column, $ignoreCols)) {

                        if ($column == Config::get('constants.LEAD_TITLE_DISPLAY')) {
                            $column = 'title';
                        }

                        $csvRow[] = $dataRow[$column];
                    }
                }

                if ($column == 'Loan Mod') {
                    $csvRow['Loan Mod'] = $dataRow['loan_mod'];
                }
            }

            fputcsv($file, $csvRow);
        }

        fclose($file);
        if (!$is_download)
            return false;

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=file.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        return response()->download(public_path($tmp_file_name), $filename, $headers);
    }

    public function statusListView(Request $request) {
        $param_rules['search'] = 'sometimes';

        $param['search'] = isset($request['search']) ? $request['search'] : '';
        $param['latitude'] = isset($request['latitude']) ? $request['latitude'] : '';
        $param['longitude'] = isset($request['longitude']) ? $request['longitude'] : '';
        $param['radius'] = isset($request['radius']) ? $request['radius'] : 500;
        $param['user_ids'] = isset($request['user_ids']) ? trim($request['user_ids']) : '';
        $param['status_ids'] = isset($request['status_ids']) ? trim($request['status_ids']) : '';
        $param['start_date'] = isset($request['start_date']) ? $request['start_date'] : '';
        $param['end_date'] = isset($request['end_date']) ? $request['end_date'] : '';
        $param['order_by'] = isset($request['order_by']) ? $request['order_by'] : 'id';
        $param['order_type'] = isset($request['order_type']) ? $request['order_type'] : 'desc';
        $param['company_id'] = $request['company_id'];

        $param['export'] = isset($request['export']) ? $request['export'] : FALSE;
        $param['is_status_group_by'] = 1;

        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        $lead_response = Lead::getList($param);

        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];
        $param['user_ids'] = empty($param['user_ids']) ? [] : explode(',', $param['user_ids']);
        $param['status_ids'] = empty($param['status_ids']) ? [] : explode(',', $param['status_ids']);

        $response = \App\Http\Resources\Status::collection(Status::getList($param));

        $lead_status_count = [];
        foreach ($lead_response as $lead) {
            $lead_status_count[$lead['status_id']] = $lead['lead_count'];
        }

        $status_count = 0;
        $status_total = 0;

        foreach ($response as $key => $status) {
            if (!in_array($status['id'], $param['status_ids']) && !empty($param['status_ids'])) {
                $response[$key]['lead_count'] = 0;
                continue;
            }

            $status_count++;
            $status['lead_count'] = (isset($lead_status_count[$status['id']])) ? $lead_status_count[$status['id']] : 0;
            $status_total += $status['lead_count'];
        }

        if ($status_total) {
            foreach ($response as $key => $status) {
                if (!in_array($status['id'], $param['status_ids']) && !empty($param['status_ids'])) {
                    $response[$key]['lead_percentage'] = 0;
                    continue;
                }
                $response[$key]['lead_percentage'] = round((($status['lead_count'] / $status_total) * 100), 1);
            }
        }
        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('Lead', $response, 200, 'Lead list retrieved successfully.');
    }

    public function alertsListView(Request $request) {
        $param_rules['search'] = 'sometimes';
        $param['search'] = isset($request['search']) ? $request['search'] : '';
        $param['latitude'] = isset($request['latitude']) ? $request['latitude'] : '';
        $param['longitude'] = isset($request['longitude']) ? $request['longitude'] : '';
        $param['radius'] = isset($request['radius']) ? $request['radius'] : 500;
        $param['user_ids'] = isset($request['user_ids']) ? trim($request['user_ids']) : '';
        $param['status_ids'] = isset($request['status_ids']) ? trim($request['status_ids']) : '';
        $param['start_date'] = isset($request['start_date']) ? $request['start_date'] : '';
        $param['end_date'] = isset($request['end_date']) ? $request['end_date'] : '';
        $param['order_by'] = isset($request['order_by']) ? $request['order_by'] : 'id';
        $param['order_type'] = isset($request['order_type']) ? $request['order_type'] : 'desc';
        $param['company_id'] = $request['company_id'];
        $param['export'] = isset($request['export']) ? $request['export'] : FALSE;
        $param['is_status_group_by'] = 1;

        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        $response = \App\Http\Resources\Alerts::collection(Alerts::getList($param));

        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('Lead', $response, 200, 'Alerts list retrieved successfully.');
    }

    public function addView(Request $request) {
        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];
        $param['is_paginate'] = false;

        $response['agent'] = User::getTenantUserList($param);
        $response['status'] = Status::whereIn('tenant_id', [$request['company_id']])->whereNull('deleted_at')->orderBy('order_by')->get();
        $response['type'] = Type::whereIn('tenant_id', [$request['company_id']])->orderBy('title')->whereNull('deleted_at')->get();
        $response['custom_fields'] = TenantCustomField::getCustomList($param);

        $this->__view = 'tenant.lead.add_lead';
        $this->__is_paginate = false;
        $this->__is_collection = false;

        return $this->__sendResponse('Lead', $response, 200, 'Lead list retrieved successfully.');
    }

    public function userList(Request $request) {
        $param_rules['search'] = 'sometimes';

        $time_slot_map['today'] = 'INTERVAL 1 MONTH';
        $time_slot_map['yesterday'] = 'INTERVAL 1 MONTH';
        $time_slot_map['week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['last_year'] = 'INTERVAL 1 YEAR';

        $param['search'] = isset($request['search']) ? $request['search'] : '';
        $param['latitude'] = isset($request['latitude']) ? $request['latitude'] : '';
        $param['longitude'] = isset($request['longitude']) ? $request['longitude'] : '';
        $param['lead_type_id'] = isset($request['lead_type_id']) ? $request['lead_type_id'] : '';
        $param['user_ids'] = isset($request['target_user_id']) ? trim($request['target_user_id']) : '';
        $param['status_ids'] = isset($request['status_id']) ? trim($request['status_id']) : '';
        $param['radius'] = isset($request['radius']) ? $request['radius'] : 500;
        $param['time_slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $time_slot_map[$request['time_slot']] : '' : '';
        $param['slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $request['time_slot'] : '' : '';


        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];

        $response = Lead::getUserList($param);

        return $this->__sendResponse('Lead', $response, 200, 'Assigned lead list retrieved successfully.');
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
        $param_rules['user_id'] = 'required';
        $param_rules['title'] = 'required';
        $param_rules['address'] = 'required';
        $param_rules['city'] = 'required'; //'|regex:/^[a-zA-Z]+$/';
        $param_rules['county'] = 'required';
        $param_rules['state'] = 'required';
        $param_rules['zip_code'] = 'required';
        $param_rules['type_id'] = 'required';
        $param_rules['status_id'] = 'required';
        $param_rules['image_url'] = 'nullable';
        $param_rules['image_url.*'] = 'image|mimes:jpeg,png,jpg,gif,svg|max:2048';

        //$param_rules['latitude']  = 'required';
        //$param_rules['longitude']  = 'required';
        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules, ['title.required' => 'The ' . Config::get('constants.LEAD_TITLE_DISPLAY') . ' field is required']);

        if ($this->__is_error == true)
            return $response;

        $system_image_url = [];
        if ($request->hasFile('image_url')) {
            foreach ($request->image_url as $image_url) {
                // $obj is model
                $system_image_url[] = $this->__moveUploadFile(
                        $image_url,
                        md5($request->title . time() . rand(10, 99)),
                        Config::get('constants.MEDIA_IMAGE_PATH')
                );
            }
        }
        // .',' . $request->zip_code //remove from the end
        $lat_long_response = $this->getLatLongFromAddress($request->address . ',' . $request->city);

        $obj = new Lead();

        $obj->creator_id = $request->user_id;
        $obj->company_id = $request->company_id;
        $obj->title = $request->title;
        $obj->foreclosure_date = (isset($request->foreclosure_date)) ? $request->foreclosure_date : '';
        $obj->admin_notes = (isset($request->admin_notes)) ? $request->admin_notes : '';
        $obj->owner = $request->first_name . ' ' . $request->last_name;
        $obj->address = $request->address;

        // sorting_filed_step_6

        $obj->type_id = $request->type_id;
        $obj->status_id = $request->status_id;
        $obj->city = $request->city;
        $obj->state = $request->state;
        $obj->county = $request->county;
        $obj->zip_code = $request->zip_code;
        $obj->auction = $request->auction;
        $obj->lead_value = $request->lead_value;
        $obj->admin_notes = $request->admin_notes;
        $obj->original_loan = $request->original_loan;
        $obj->loan_date = $request->loan_date;
        $obj->sq_ft = $request->sq_ft;
        $obj->assignee_id = $request->target_id;
        $obj->yr_blt = $request->yr_blt;
        $obj->eq = $request->eq;

        $obj->mortgagee = $request->mortgagee;
        $obj->loan_type = $request->loan_type;
        $obj->loan_mod = $request->loan_mod;
        $obj->trustee = $request->trustee;
        $obj->owner_address = $request->owner_address;
        $obj->source = $request->source;

        $obj->latitude = $lat_long_response['lat'];
        $obj->longitude = $lat_long_response['long'];
        $obj->formatted_address = $lat_long_response['formatted_address'];
        $objArray = [];
        $objArray['creator_id'] = $request->user_id;
        $objArray['company_id'] = $request->company_id;
        $objArray['title'] = $request->title;
        $objArray['foreclosure_date'] = (isset($request->foreclosure_date)) ? $request->foreclosure_date : '';
        $objArray['admin_notes'] = (isset($request->admin_notes)) ? $request->admin_notes : '';
        $objArray['owner'] = $request->first_name . ' ' . $request->last_name;
        $objArray['address'] = $request->address;

        $objArray['type_id'] = $request->type_id;
        $objArray['status_id'] = $request->status_id;
        $objArray['city'] = $request->city;
        $objArray['state'] = $request->state;
        $objArray['county'] = $request->county;
        $objArray['zip_code'] = $request->zip_code;
        $objArray['auction'] = $request->auction;
        $objArray['lead_value'] = $request->lead_value;
        $objArray['admin_notes'] = $request->admin_notes;
        $objArray['original_loan'] = $request->original_loan;
        $objArray['loan_date'] = $request->loan_date;
        $objArray['sq_ft'] = $request->sq_ft;
        $objArray['assignee_id'] = $request->target_id;
        $objArray['yr_blt'] = $request->yr_blt;
        $objArray['eq'] = $request->eq;

        $objArray['mortgagee'] = $request->mortgagee;
        $objArray['loan_type'] = $request->loan_type;
        $objArray['loan_mod'] = $request->loan_mod;
        $objArray['trustee'] = $request->trustee;
        $objArray['owner_address'] = $request->owner_address;
        $objArray['source'] = $request->source;

        $objArray['latitude'] = $lat_long_response['lat'];
        $objArray['longitude'] = $lat_long_response['long'];
        $objArray['formatted_address'] = $lat_long_response['formatted_address'];

        $user_data = User::where('id', $request['user_id'])
                ->first();

        if (isset($user_data->first_name) AND $user_data->first_name != '') {
            $obj->updated_by = $user_data->first_name . ' ' . $user_data->last_name;
            $obj->created_by = $user_data->first_name . ' ' . $user_data->last_name;

            $objArray['updated_by'] = $user_data->first_name . ' ' . $user_data->last_name;
            $objArray['created_by'] = $user_data->first_name . ' ' . $user_data->last_name;
        }

        $lead_exits = Lead::where('address', '=', $obj->address)->first();
        if (isset($lead_exits->id) AND $request->address != '') {
            $objArray['is_expired'] = 0;
            Lead::where('id', '=', $lead_exits->id)->update($objArray);
            $obj = Lead::where('id', '=', $lead_exits->id)->first();
        } else {
            $obj->save();
        }

        if (count($system_image_url))
            Media::createBulk($obj->id, 'lead', 'image', $system_image_url);

        LeadQuery::insertBulk($obj->id, $request->company_id);
        $status_id = $request->status_id;
        Status::incrementLeadCount($status_id);

        $obj_lead_history = LeadHistory::create([
                    'lead_id' => $obj->id,
                    'title' => 'Lead created',
                    'assign_id' => $request['user_id'],
                    'status_id' => 0
        ]);

        $obj_lead_type_history = LeadType::create([
                    'lead_id' => $obj->id,
                    'title' => 'Lead Type History created from form.',
                    'assign_id' => $request['user_id'],
                    'type_id' => $request->type_id
        ]);

        $obj_lead_history = LeadHistory::create([
                    'lead_id' => $obj->id,
                    'title' => '',
                    'lead_status_title' => 'Lead status initialized.',
                    'assign_id' => $request['user_id'],
                    'status_id' => $status_id
        ]);

        $ignore_fields = ['_token', 'first_name', 'last_name', 'user_id', 'company_id', 'title', 'foreclosure_date', 'admin_notes', 'address', 'type_id', 'status_id', 'image_url'];
        $custom_field = $request->custom_field;
        $custom_field['company_id'] = $request['company_id'];
        LeadCustomField::insert($obj->id, $ignore_fields, $custom_field);

        $this->__is_paginate = false;
        $this->__is_collection = false;
        return $this->__sendResponse('Lead', Lead::getById($obj->id), 200, 'Your lead has been added successfully.');
    }

    public function wizardView(Request $request) {

        $response['template'] = Lead::getTemplate($request['company_id']);
        $response['lead_types'] = Type::whereIn('tenant_id', [$request['company_id']])->orderBy('title')->whereNull('deleted_at')->get();
        $response['lead_status'] = Status::whereIn('tenant_id', [$request['company_id']])->orderBy('order_by')->whereNull('deleted_at')->get();

        $response['fields'] = TenantCustomField::getList(['company_id' => $request['company_id']]);


        $this->__view = 'tenant.lead.wizard';
        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('Wizard', $response, 200, 'Your leads file has been added in process.');
    }

    public function uploadLeads(Request $request) {
        $param_rules['user_id'] = 'required';
        $param_rules['file'] = 'required|min:0.15,max:5120';
        $param_rules['extension'] = 'required|in:xls,xlsx,csv';

        $this->__is_ajax = true;
        $request['extension'] = strtolower($request->file->getClientOriginalExtension());
        $response = $this->__validateRequestParams($request->all(), $param_rules, [
            'file.min' => 'The file must be at least 150 bytes.'
        ]);

        if ($this->__is_error == true)
            return $response;

        $system_image_url = [];
        if ($request->hasFile('file')) {
            $system_file_url = $this->__moveUploadFile(
                    $request->file,
                    md5($request['company_id']),
                    Config::get('constants.MEDIA_FILE_PATH'),
                    false
            );
        }

        $param['tenant_id'] = $request['company_id'];
        $param['media_url'] = $system_file_url;
        Lead::saveTempFile($param);
        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('Lead', [], 200, 'Your leads file has been added in process.');
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public static function upload($path, $image) {
        $imageName = 'NoImage.png';

        if (!empty($image)) {
            $file = $image->getClientOriginalName();
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $filename = str_replace(' ', '-', $filename);
            $extension = $image->extension();
            $imageName = time() . '-' . $file;
            $image->storeAs($path, $imageName);
        }

        return $imageName;
    }

    public function uploadMedia(Request $request, $lead_id) {

        $param_rules['user_id'] = 'required';
        $param_rules['lead_id'] = 'required|exists:lead_detail,id';
        $param_rules['image_url'] = 'required';
        $param_rules['user_id'] = 'required';
        $request['lead_id'] = $lead_id;
        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true) {
            return $response;
        }

        // userUpdateLastActivity($request->user_id);

        $system_image_url = [];
        if (!empty($request->image_url)) {
            $files = $request->file('image_url');
            foreach ($request->image_url as $key => $image) {
                $file = $files[$key];
                $system_image_url[] = $this->__moveUploadFile(
                        $file,
                        md5($lead_id . time() . rand(10, 99)),
                        Config::get('constants.MEDIA_IMAGE_PATH')
                );
            }
        }

        Media::deleteBySourceId($lead_id);
        Media::createBulk($lead_id, 'lead', 'image', $system_image_url);

        $this->__is_paginate = false;
        $this->__is_collection = false;
        return $this->__sendResponse('Lead', Lead::getById($lead_id), 200, 'Your lead has been updated successfully');
    }

    public function updateTemplateFieldIndex(Request $request) {
        $param_rules['template_id'] = 'required';
        $param_rules['field_id'] = 'required|exists:tenant_custom_field,NULL,deleted_at';
        $param_rules['field_id'] = 'required';

        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true) {
            return $response;
        }

        TemplateFields::where('field', $request['field_id'])
                ->where('template_id', $request['template_id'])
                ->Update(['index' => $request['indexs']]);

        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('Template', [], 200, 'Your lead template field index has been updated successfully.');
    }

    public function updateTemplateFieldIndexClear(Request $request) {
        $param_rules['template_id'] = 'required';

        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        TemplateFields::where('template_id', $request['template_id'])
                ->Update(['index' => '', 'index_map' => '']);

        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('Template', [], 200, 'Your lead template field index has been updated successfully.');
    }

    public function addTemplate(Request $request) {
        $param_rules['user_id'] = 'required';
        $param_rules['title'] = 'required|string|max:100|regex:/(?!^\d+$)^.+$/|unique:tenant_template,NULL,deleted_at,id,tenant_id,' . $request['company_id'];
        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        $param['tenant_id'] = $request['company_id'];
        $param['title'] = $request['title'];
        $param['description'] = $request['title'];
        $template_id = Lead::saveTemplate($param);

        TemplateFields::where('template_id', '=', $template_id)->delete();

        $template_fields = Config::get('constants.LEAD_DEFAULT_COLUMNS');

        $template_fields_total = Lead::getTemplateFields($template_id);
        $tmpOrder = 0;
        $data = [];
        foreach ($template_fields as $template_field) {
            $data[$tmpOrder]['index'] = 0;
            $data[$tmpOrder]['index_map'] = str_replace('_', ' ', $template_field);
            $data[$tmpOrder]['field'] = $template_field;
            $data[$tmpOrder]['order_by'] = $tmpOrder;

            $count++;
            $tmpOrder++;
        }

        Lead::saveTemplateField($template_id, $data);

        $list = TenantCustomField::Select('id', 'tenant_id', \DB::raw('`key` as query'), \DB::raw("'lead_detail' as type"))
                ->where('tenant_id', $request['company_id'])
                ->whereNull('deleted_at')
                ->orderBy('order_by')
                ->pluck('query')
                ->toArray();

        if (!empty($list)) {
            $d = [];
            foreach ($list as $key => $value) {
                if (!empty($value) && $value !== 'Zip' && $value !== 'updated_by' && $value !== 'Created_at' && $value !== 'updated_at' && $value !== 'created_by' && $value !== 'created_at' && $value !== 'Homeowner Name') {
                    $input['query'] = $value;
                    $input['index'] = $value;
                    $input['user_id'] = $request['user_id'];
                    $input['template_id'] = $template_id;
                    $input['company_id'] = $request['company_id'];
                    $input['session_user_group_id'] = $request['session_user_group_id'];
                    $input['call_mode'] = $request['call_mode'];
                    $this->storeTemplateFieldMe($input);
                }
            }
            if (!empty($d)) {
                Lead::saveTemplateFields($template_id, $d);
            }
        }
        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('Template', [], 200, 'Your lead template has been created successfully.');
    }

    public function CopyTemplate(Request $request) {
        $old_template_data = TenantTemplate::where('id', '=', $request->id)->first();
        $param['title'] = $request->template_name;
        $param['tenant_id'] = $old_template_data->tenant_id;
        $param['description'] = $old_template_data->description;
        $param['created_at'] = NOW();
        $param['updated_at'] = NOW();
        $template_id = Lead::saveTemplate($param);

        TemplateFields::where('template_id', '=', $template_id)->delete();

        $response = Lead::getFieldsTemplateById($request->id, $params);

        foreach ($response as $template_field) {

            $data[$tmpOrder]['template_id'] = $template_id;
            $data[$tmpOrder]['index'] = $template_field->index;
            $data[$tmpOrder]['index_map'] = $template_field->index_map;
            $data[$tmpOrder]['field'] = $template_field->field;
            $data[$tmpOrder]['order_by'] = $template_field->order_by;
            $data[$tmpOrder]['is_active'] = $template_field->is_active;

            $count++;
            $tmpOrder++;
        }

        Lead::saveTemplateField($template_id, $data);

        return redirect('tenant/template');
    }

    public function storeTemplateFieldMe($input) {
        $params['company_id'] = $input['company_id'];
        $tenant_custom = [];
        $tenant_custom = TenantCustomField::getByKey($input['query'], $params);
        $id = 0;
        if (isset($tenant_custom['id'])) {
            $id = $tenant_custom->id;
        } else {
            $tenant_custom = TenantCustomField::getTenantDefaultFields($input['company_id']);
            $obj = new TenantCustomField();
            $obj->tenant_id = $input['company_id'];
            $obj->key = $input['query'];
            $obj->key_mask = $input['query'];
            $obj->rule = '';
            $obj->order_by = count($tenant_custom) + 1;
            $obj->save();
            $id = $obj->id;
        }

        $template_fields = Lead::getTemplateFields($input['template_id']);
        $tenantCustomField = null;

        $tenantCustomField = TemplateFields::where('index_map', $input['index'])->where('template_id', $input['template_id'])->first();

        if (is_null($tenantCustomField)) {
            $detailinput[0]['index'] = 0;
            $detailinput[0]['index_map'] = $input['index'];
            $detailinput[0]['field'] = $id;
            $detailinput[0]['order_by'] = count($template_fields) + 1;
            Lead::saveTemplateFieldCustom($input['template_id'], $detailinput);
        }
        return true;
    }

    public function wizardTemplate(Request $request) {
        $param_rules['user_id'] = 'required';

        $this->__is_ajax = true;

        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        if (empty($request['template_id']) && empty($request['template'])) {
            $errors['template'] = 'Template is required';
            return $this->__sendError('Validation Error.', $errors);
        }
        $response = [];
        $response['template_id'] = 0;
        if (isset($request['template_id']) && !empty($request['template_id']))
            $response['template_id'] = $request['template_id'];

        if (isset($request['template']) && !empty(trim($request['template']))) {
            $param['tenant_id'] = $request['company_id'];
            $param['title'] = $request['template'];
            $param['description'] = $request['template'];
            $response['template_id'] = Lead::saveTemplate($param);
        }

        $response['fields'] = TenantCustomField::getList(['company_id' => $request['company_id'], 'template_id' => $response['template_id']]);


        $temp_file = Lead::getTempfile($request['company_id']);
        $file_leads = $response['file_header'] = $this->__getFileContent(storage_path(Config::get('constants.MEDIA_FILE_PATH') . $temp_file->media_url), 1);

        $response['template_fields'] = Lead::getTemplateById($response['template_id']);

        $response['custom_fields'] = Lead::getTemplateById($response['template_id'], 'INNER');
        foreach ($response['template_fields'] as $key => $template_fields) {
            if (empty($template_fields->index)) {
                $case_1 = strtolower($template_fields->index_map);
                $case_2 = ucwords($template_fields->index_map);
                $response['template_fields'][$key]->index = false;
                if (!empty($file_leads)) {
                    $response['template_fields'][$key]->index = ($this->__array_search([$case_1, $case_2], $file_leads));
                }
            }
        }

        foreach ($response['custom_fields'] as $key => $template_fields) {
            if (empty($template_fields->index)) {
                $template_fields->index = false;
                if (!empty($file_leads)) {
                    $response['custom_fields'][$key]->index = (array_search($template_fields->index_map, $file_leads));
                }
            }
        }

        $response['fixed_fields'][0]['template_id'] = $response['template_id'];
        $response['fixed_fields'][0]['field'] = 'lead_name';
        $response['fixed_fields'][0]['index'] = $this->__array_search(['Homeowner Name', 'lead_name', 'name', 'title', 'First Name', 'Last Name', 'Homeowner Name'], $response['file_header']);

        $response['fixed_fields'][1]['template_id'] = $response['template_id'];
        $response['fixed_fields'][1]['field'] = 'address';
        $response['fixed_fields'][1]['index'] = $this->__array_search(['Address', 'Street Address'], $response['file_header']);

        $response['fixed_fields'][2]['template_id'] = $response['template_id'];
        $response['fixed_fields'][2]['field'] = 'city';
        $response['fixed_fields'][2]['index'] = $this->__array_search(['City'], $response['file_header']);

        $response['fixed_fields'][3]['template_id'] = $response['template_id'];
        $response['fixed_fields'][3]['field'] = 'zip_code';
        $response['fixed_fields'][3]['index'] = $this->__array_search(['Zip Code', 'zip_code', 'zip'], $response['file_header']);

        $response['fixed_fields'][5]['template_id'] = $response['template_id'];
        $response['fixed_fields'][5]['field'] = 'first_name';
        $response['fixed_fields'][5]['index'] = $this->__array_search(['Mortgagor First Name', 'owner', 'First Name'], $response['file_header']);

        $response['fixed_fields'][6]['template_id'] = $response['template_id'];
        $response['fixed_fields'][6]['field'] = 'last_name';
        $response['fixed_fields'][6]['index'] = $this->__array_search(['Mortgagor Last Name', 'owner', 'Last Name'], $response['file_header']);

        $response['fixed_fields'][7]['template_id'] = $response['template_id'];
        $response['fixed_fields'][7]['field'] = 'county';
        $response['fixed_fields'][7]['index'] = $this->__array_search(['County', 'county'], $response['file_header']);

        $response['fixed_fields'][8]['template_id'] = $response['template_id'];
        $response['fixed_fields'][8]['field'] = 'state';
        $response['fixed_fields'][8]['index'] = $this->__array_search(['state'], $response['file_header']);

        $response['fixed_fields'][9]['template_id'] = $response['template_id'];
        $response['fixed_fields'][9]['field'] = 'lead_status';
        $response['fixed_fields'][9]['index'] = $this->__array_search(['Lead Status', 'lead_status', 'status'], $response['file_header']);

        $response['fixed_fields'][10]['template_id'] = $response['template_id'];
        $response['fixed_fields'][10]['field'] = 'admin_notes';
        $response['fixed_fields'][10]['index'] = $this->__array_search(['admin_notes', 'Admin Notes'], $response['file_header']);

        $response['fixed_fields'][11]['template_id'] = $response['template_id'];
        $response['fixed_fields'][11]['field'] = 'foreclosure_date';
        $response['fixed_fields'][11]['index'] = $this->__array_search(['foreclosure_date', 'Foreclosure Date'], $response['file_header']);

        $response['fixed_fields'][12]['template_id'] = $response['template_id'];
        $response['fixed_fields'][12]['field'] = 'lead_name';
        $response['fixed_fields'][12]['index'] = [$this->__array_search(['First Name'], $response['file_header']), $this->__array_search(['Last Name'], $response['file_header'])];

        $response['fixed_fields'][4]['template_id'] = $response['template_id'];
        $response['fixed_fields'][4]['field'] = 'lead_type';
        $response['fixed_fields'][4]['index'] = $this->__array_search(['Lead Type', 'lead_type'], $response['file_header']);

        $response['fixed_fields'][13]['template_id'] = $response['template_id'];
        $response['fixed_fields'][13]['field'] = 'auction';
        $response['fixed_fields'][13]['index'] = $this->__array_search(['Auction', 'auction'], $response['file_header']);

        $response['fixed_fields'][14]['template_id'] = $response['template_id'];
        $response['fixed_fields'][14]['field'] = 'eq';
        $response['fixed_fields'][14]['index'] = $this->__array_search(['eq', 'EQ'], $response['file_header']);

        $response['fixed_fields'][15]['template_id'] = $response['template_id'];
        $response['fixed_fields'][15]['field'] = 'sq_ft';
        $response['fixed_fields'][15]['index'] = $this->__array_search(['Sq Ft', 'sq ft'], $response['file_header']);

        $response['fixed_fields'][16]['template_id'] = $response['template_id'];
        $response['fixed_fields'][16]['field'] = 'loan_date';
        $response['fixed_fields'][16]['index'] = $this->__array_search(['Loan Date', 'loan date'], $response['file_header']);

        $response['fixed_fields'][17]['template_id'] = $response['template_id'];
        $response['fixed_fields'][17]['field'] = 'yr_blt';
        $response['fixed_fields'][17]['index'] = $this->__array_search(['Yr Blt', 'yr blt'], $response['file_header']);

        $response['fixed_fields'][18]['template_id'] = $response['template_id'];
        $response['fixed_fields'][18]['field'] = 'lead_value';
        $response['fixed_fields'][18]['index'] = $this->__array_search(['Lead Value', 'lead value'], $response['file_header']);

        $response['fixed_fields'][19]['template_id'] = $response['template_id'];
        $response['fixed_fields'][19]['field'] = 'mortgagee';
        $response['fixed_fields'][19]['index'] = $this->__array_search(['Mortgagee', 'mortgagee'], $response['file_header']);

        $response['fixed_fields'][25]['template_id'] = $response['template_id'];
        $response['fixed_fields'][25]['field'] = 'loan_type';
        $response['fixed_fields'][25]['index'] = $this->__array_search(['Loan Type', 'loan type'], $response['file_header']);

        $response['fixed_fields'][20]['template_id'] = $response['template_id'];
        $response['fixed_fields'][20]['field'] = 'loan_mod';
        $response['fixed_fields'][20]['index'] = $this->__array_search(['Loan Mod', 'loan mod'], $response['file_header']);

        $response['fixed_fields'][21]['template_id'] = $response['trustee'];
        $response['fixed_fields'][21]['field'] = 'trustee';
        $response['fixed_fields'][21]['index'] = $this->__array_search(['Trustee', 'trustee'], $response['file_header']);

        $response['fixed_fields'][22]['template_id'] = $response['template_id'];
        $response['fixed_fields'][22]['field'] = 'owner_address';
        $response['fixed_fields'][22]['index'] = $this->__array_search(['Owner Address', 'owner address', 'Owner Address - If Not Owner Occupied'], $response['file_header']);

        $response['fixed_fields'][23]['template_id'] = $response['template_id'];
        $response['fixed_fields'][23]['field'] = 'source';
        $response['fixed_fields'][23]['index'] = $this->__array_search(['Source', 'source'], $response['file_header']);

        $response['fixed_fields'][24]['template_id'] = $response['template_id'];
        $response['fixed_fields'][24]['field'] = 'original_loan';
        $response['fixed_fields'][24]['index'] = $this->__array_search(['Original Loan', 'original loan'], $response['file_header']);

        $params['is_all'] = (isset($request['is_all'])) ? $request['is_all'] : 2;
        $params['company_id'] = $request['company_id'];

        $this->__is_paginate = false;
        $this->__collection = false;

        return $this->__sendResponse('Template', $response, 200, 'Your lead template has been created successfully.');
    }

    public function wizardFields(Request $request) {
        $param_rules['user_id'] = 'required';
        $param_rules['template_id'] = 'required';
        $param_rules['lead_name'] = 'required';
        $param_rules['address'] = 'required';
        $param_rules['city'] = 'required';
        $param_rules['zip_code'] = 'required';

        $this->__is_ajax = true;

        $response = $this->__validateRequestParams($request->all(), $param_rules,
                ['lead_name.required' => 'The ' . Config::get('constants.LEAD_TITLE_DISPLAY') . ' field is required',
                    'lead_type_id.required' => 'The Lead Type field is required',
                    'lead_status_id.required' => 'The Lead Status field is required'
                ]
        );

        if ($this->__is_error == true)
            return $response;

        $temp_file = Lead::getTempfile($request['company_id']);
        $file_leads = $this->__getFileContent(storage_path(Config::get('constants.MEDIA_FILE_PATH') . $temp_file->media_url));

        $status_id = $request['lead_status_id'];
        if (empty($status_id)) {
            $errors['code'] = 'Default status is not defined.';
        }

        $lead_statuses = [];
        $lead_status_result = [];
        foreach ($lead_status_result as $lead_status) {
            $lead_statuses[strtolower($lead_status->title)] = $lead_status->id;
        }
        $lead_types = [];
        $users = User::getTenantUserList(['company_id' => $request['company_id']]);
        $user_codes = [];
        foreach ($users as $user)
            $user_codes['IN-' . str_pad($user->company_id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($user->id, 4, '0', STR_PAD_LEFT)] = $user->id;

        $lead_type_id = $request['lead_type_id'];
        $count = 0;

        $temp_fields = [];
        $file_header_index[] = $file_leads[0];
        $user_code_index = ($this->__array_search(['user_code'], $file_leads[0]));
        for ($i = 1; $i <= count($file_leads); $i++) {
            $user_code = '';

            if (!empty($user_code_index)) {
                $user_code = $file_leads[$i][$user_code_index];
            }

            if (!empty($request['address']) && !isset($file_leads[$i][$request['address']]) || $file_leads[$i][$request['address']] == '1970-01-01' || $file_leads[$i][$request['address']] == '1970-01-01 00:00:00' || empty($file_leads[$i][$request['address']]) || !isset($file_leads[$i]))
                continue;

            $address = null;

            if (!empty($request['address']) && !empty($request['city']) && !empty($request['zip_code'])) {
                $address = $file_leads[$i][$request['address']] . ',' . $file_leads[$i][$request['city']] . ',' . $file_leads[$i][$request['state']] . ',' . $file_leads[$i][$request['zip_code']];
            }

            $lat_long_response = $this->getLatLongFromAddress($address);

            $obj = new \stdClass();
            $obj->creator_id = $request->user_id;
            $obj->company_id = $request->company_id;
            $obj->assignee_id = (isset($user_codes[$file_leads[$i][$user_code_index]])) ? $user_codes[$file_leads[$i][$user_code_index]] : '';

            $lead_title = '';
            foreach ($request['lead_name'] as $title_index)
                $lead_title .= $file_leads[$i][$title_index] . ' ';

            $lead_owner = '';
            if (!empty($request['owner'])) {
                foreach ($request['owner'] as $title_index)
                    $lead_owner .= $file_leads[$i][$title_index] . ' ';
            }
            $owner_name = '';
            if (!empty($request['first_name'])) {
                $owner_name = $file_leads[$i][$request['first_name']];
            }

            if (!empty($request['last_name'])) {
                $owner_name = (!empty($owner_name)) ? $owner_name . ' ' . $file_leads[$i][$request['last_name']] : $file_leads[$i][$request['last_name']];
            }

            $county = '';
            if (!empty($request['county'])) {
                $county = $file_leads[$i][$request['county']];
            }

            $state = '';
            if (!empty($request['state'])) {
                $state = $file_leads[$i][$request['state']];
            }

            $foreclosure_date = '';
            if (!empty($request['foreclosure_date'])) {
                $foreclosure_date = $file_leads[$i][$request['foreclosure_date']];
            }

            $admin_notes = '';
            if (!empty($request['admin_notes'])) {
                $admin_notes = $file_leads[$i][$request['admin_notes']];
            }

            $auction = '';
            if (!empty($request['auction'])) {
                $auction = $file_leads[$i][$request['auction']];
            }

            $lead_value = '';
            if (!empty($request['lead_value'])) {
                $lead_value = $file_leads[$i][$request['lead_value']];
            }

            $loan_date = '';
            if (!empty($request['loan_date'])) {
                $loan_date = $file_leads[$i][$request['loan_date']];
            }

            $yr_blt = '';
            if (!empty($request['yr_blt'])) {
                $yr_blt = $file_leads[$i][$request['yr_blt']];
            }

            $sq_ft = '';
            if (!empty($request['sq_ft'])) {
                $sq_ft = $file_leads[$i][$request['sq_ft']];
            }

            $eq = '';
            if (!empty($request['eq'])) {
                $eq = $file_leads[$i][$request['eq']];
            }

            $eq = '';

            if (!empty($request['eq']) && !empty($file_leads[$i][$request['eq']])) {
                $eq = $file_leads[$i][$request['eq']];
            }

            $mortgagee = '';
            if (!empty($request['mortgagee'])) {
                $eq = $file_leads[$i][$request['mortgagee']];
            }

            $loan_type = '';
            if (!empty($request['loan_type'])) {
                $eq = $file_leads[$i][$request['loan_type']];
            }

            $admin_notes = '';
            if (!empty($request['admin_notes'])) {
                $admin_notes = $file_leads[$i][$request['admin_notes']];
            }

            $loan_mod = '';
            if (!empty($request['loan_mod'])) {
                $eq = $file_leads[$i][$request['loan_mod']];
            }

            $trustee = '';
            if (!empty($request['trustee'])) {
                $eq = $file_leads[$i][$request['trustee']];
            }

            $owner_address = '';
            if (!empty($request['owner_address'])) {
                $eq = $file_leads[$i][$request['owner_address']];
            }

            $lead_owner = trim($owner_name);
            $obj->title = trim($lead_title);
            $obj->owner = trim($lead_owner);
            $obj->county = $county;
            $obj->state = $state;
            $obj->foreclosure_date = $foreclosure_date;
            $obj->admin_notes = $admin_notes;
            $obj->auction = $auction;
            $obj->lead_value = $lead_value;
            $obj->loan_date = $loan_date;
            $obj->yr_blt = $yr_blt;
            $obj->sq_ft = $sq_ft;
            $obj->eq = null;

            if (!empty($eq)) {
                $obj->eq = $eq;
            }

            $obj->mortgagee = $mortgagee;
            $obj->loan_type = $loan_type;
            $obj->admin_notes = $admin_notes;
            $obj->loan_mod = $loan_mod;
            $obj->trustee = $trustee;
            $obj->owner_address = $owner_address;
            $obj->source = $source;
            $obj->address = $file_leads[$i][$request['address']];

            if (!empty($request['lead_type'])) {
                $file_leads[$i][$request['lead_type']] = strtolower($file_leads[$i][$request['lead_type']]);
            }

            $keyLead = array_search('Lead Type', $file_leads[0]);

            $typeDetail = [];
            $type = [$request['company_id']];
            if ($this->call_mode != 'api')
                $type = [$request['company_id']];

            $typeSheet = ltrim($file_leads[$i][$keyLead], ' ');
            $typeSheet = str_replace("  ", ' ', $file_leads[$i][$keyLead]);

            $typeDetail = null;

            if (!empty($typeSheet)) {
                $typeDetail = Type::where('title', $typeSheet)
                        ->whereIn('tenant_id', $type)
                        ->whereNull('deleted_at')
                        ->first();
            }

            if (is_null($typeDetail)) {
                return $this->__sendError('Validation Error.', ['lead_status' => 'Please choose a lead type that is already in the system and retry your upload.']);
            }

            $keyLeadStatus = array_search('Lead Status', $file_leads[0]);
            $keyAssignedTo = array_search('Assigned To', $file_leads[0]);

            $statusSheet = ltrim($file_leads[$i][$keyLeadStatus], ' ');
            $statusSheet = str_replace("  ", ' ', $statusSheet);

            $statusData = Status::whereIn('tenant_id', [$request['company_id']])
                    ->whereNull('deleted_at')
                    ->where('title', $statusSheet)
                    ->first();

            $keyAssignedTo_array = explode(' ', $file_leads[$i][$keyAssignedTo]);
            $assign_to_first_name = $keyAssignedTo_array[0];
            $assign_to_last_name = $keyAssignedTo_array[1];

            $userAssignedTo = null;

            if (isset($keyAssignedTo_array[0]) AND $keyAssignedTo_array[1] AND $keyAssignedTo_array[0] != '' AND $keyAssignedTo_array[1] != '') {

                $userAssignedTo = User::where('first_name', 'like', $keyAssignedTo_array[0])
                        ->where('last_name', 'like', $keyAssignedTo_array[1])
                        ->first();

                if (is_null($userAssignedTo)) {
                    return $this->__sendError('Validation Error.', ['lead_status' => 'There was an error with this upload. Please check your data and try again.']);
                }
            }

            if (is_null($statusData)) {

                $statusData = FollowStatus::whereNull('deleted_at')
                        ->where('title', $statusSheet)
                        ->first();

                if (is_null($statusData)) {
                    return $this->__sendError('Validation Error.', ['lead_status' => 'Please choose a lead status that is already in the system and retry your upload.']);
                }
            }

            $obj->type_id = $typeDetail->id ?? 0;
            $obj->status_id = $statusData->id ?? 0;
            $obj->assignee_id = $userAssignedTo->id ?? null;

            $original_loan = $file_leads[$i][$request['original_loan']];

            if (is_numeric($file_leads[$i][$request['original_loan']])) {
                $original_loan = '$' . number_format($file_leads[$i][$request['original_loan']]);
            }

            $obj->latitude = $lat_long_response['lat'];
            $obj->longitude = $lat_long_response['long'];
            $obj->formatted_address = $lat_long_response['formatted_address'];
            $obj->city = (!empty($file_leads[$i][$request['city']])) ? $file_leads[$i][$request['city']] : $lat_long_response['city'];
            $obj->county = (!empty($file_leads[$i][$request['county']])) ? $file_leads[$i][$request['county']] : $lat_long_response['county'];
            $obj->zip_code = (!empty($file_leads[$i][$request['zip_code']])) ? $file_leads[$i][$request['zip_code']] : $lat_long_response['zip_code'];
            $obj->auction = (!empty($file_leads[$i][$request['auction']])) ? $file_leads[$i][$request['auction']] : $lat_long_response['auction'];
            $obj->lead_value = (!empty($file_leads[$i][$request['lead_value']])) ? str_replace(',', '', str_replace('$', '', $file_leads[$i][$request['lead_value']])) : str_replace(',', '', str_replace('$', '', $lat_long_response['lead_value']));
            $obj->original_loan = (!empty($file_leads[$i][$request['original_loan']])) ? $original_loan : '$0';
            $obj->loan_date = (!empty($file_leads[$i][$request['loan_date']])) ? $file_leads[$i][$request['loan_date']] : $lat_long_response['loan_date'];
            $obj->yr_blt = (!empty($file_leads[$i][$request['yr_blt']])) ? $file_leads[$i][$request['yr_blt']] : $lat_long_response['yr_blt'];
            $obj->sq_ft = (!empty($file_leads[$i][$request['sq_ft']])) ? $file_leads[$i][$request['sq_ft']] : $lat_long_response['sq_ft'];
            $obj->deleted_at = null;
            $eq = $file_leads[$i][$request['eq']];

            $eq = null;
            if (!empty($file_leads[$i][$request['eq']]) && is_numeric($file_leads[$i][$request['eq']])) {

                $eq = $file_leads[$i][$request['eq']];

                if ($eq < 0) {
                    $eq = $eq * 100;
                }

                $eq = number_format($eq, 0) . '%';
            }

            $obj->eq = $eq;

            $obj->owner_address = !empty($file_leads[$i][$request['owner_address']]) ? $file_leads[$i][$request['owner_address']] : null;

            $lead_exits = Lead::where('address', '=', $obj->address)->first();

            $lead_exits = Lead::where('address', '=', $obj->address)->where('is_follow_up', 0)->first();

            $following_lead_exits = FollowingLead::where('address', '=', $obj->address)
                            ->where('is_lead_up', '=', 0)->where('is_purchase', '=', 0)->first();

            $purchase_lead_exits = PurchaseLead::where('address', '=', $obj->address)
                            ->where('is_followup', '=', 0)->first();

            $new_lead = false;
            $is_followup = false;
            $is_purchase = false;
            if (isset($following_lead_exits->id)) {
                $statusData = FollowStatus::whereNull('deleted_at')
                        ->where('title', $statusSheet)
                        ->first();
                if (isset($statusData->id) AND $statusData->id != '') {
                    $obj->status_id = $statusData->id;
                } else {
                    $obj->status_id = 0;
                }
                $new_lead = false;
                $is_followup = true;
                $is_purchase = false;
            } elseif (isset($purchase_lead_exits->id)) {
                $statusData = FollowStatus::whereNull('deleted_at')
                        ->where('title', $statusSheet)
                        ->first();
                if (isset($statusData->id) AND $statusData->id != '') {
                    $obj->status_id = $statusData->id;
                } else {
                    $obj->status_id = 0;
                }
                $new_lead = false;
                $is_followup = false;
                $is_purchase = true;
            } else {
                if ($lead_exits->is_follow_up == 0 && isset($lead_exits->id) AND $obj->address != '') {
                    $new_lead = false;
                } else {
                    $new_lead = true;
                }
            }

            $obj->id = Lead::saveLead($obj);

            $leadDetail = Lead::find($obj->id);
            if (isset($leadDetail->id)) {
                $leadDetail->mortgagee = !empty($file_leads[$i][$request['mortgagee']]) ? $file_leads[$i][$request['mortgagee']] : null;
                $leadDetail->loan_type = !empty($file_leads[$i][$request['loan_type']]) ? $file_leads[$i][$request['loan_type']] : null;
                $leadDetail->admin_notes = !empty($file_leads[$i][$request['admin_notes']]) ? $file_leads[$i][$request['admin_notes']] : null;
                $leadDetail->loan_mod = !empty($file_leads[$i][$request['loan_mod']]) ? $file_leads[$i][$request['loan_mod']] : null;
                $leadDetail->trustee = !empty($file_leads[$i][$request['trustee']]) ? $file_leads[$i][$request['trustee']] : null;
                $leadDetail->source = !empty($file_leads[$i][$request['source']]) ? $file_leads[$i][$request['source']] : null;
                $leadDetail->original_loan_2 = !empty($file_leads[$i][$request['original_loan']]) ? (int) filter_var($file_leads[$i][$request['original_loan']], FILTER_SANITIZE_NUMBER_INT) : 0;
                $leadDetail->sq_ft_2 = !empty($file_leads[$i][$request['sq_ft']]) ? (int) filter_var($file_leads[$i][$request['sq_ft']], FILTER_SANITIZE_NUMBER_INT) : 0;
                $user_data = User::where('id', $request->user_id)
                        ->first();
                $leadDetail->created_by = !empty($user_data->first_name) ? $user_data->first_name . ' ' . $user_data->last_name : null;
                $leadDetail->updated_by = !empty($user_data->first_name) ? $user_data->first_name . ' ' . $user_data->last_name : null;
                $leadDetail->save();
            }
            $temp_fields['lead_name']['index'] = implode(',', $request['lead_name']);
            $temp_fields['lead_name']['field'] = 'lead_name';
            $tmp_lead_name_index = [];
            foreach ($request['lead_name'] as $lead_name_index) {
                $tmp_lead_name_index[] = $file_leads[0][$lead_name_index];
            }

            $temp_fields['lead_name']['index_map'] = implode(',', $tmp_lead_name_index);


            if (!empty($request['first_name'])) {
                $temp_fields['first_name']['index'] = $request['first_name'];
                $temp_fields['first_name']['field'] = 'first_name';
                $temp_fields['first_name']['index_map'] = $file_leads[0][$request['first_name']];
            }

            if (!empty($request['last_name'])) {
                $temp_fields['last_name']['index'] = $request['last_name'];
                $temp_fields['last_name']['field'] = 'last_name';
                $temp_fields['last_name']['index_map'] = $file_leads[0][$request['last_name']];
            }

            if (!empty($request['county'])) {
                $temp_fields['county']['index'] = $request['county'];
                $temp_fields['county']['field'] = 'county';
                $temp_fields['county']['index_map'] = $file_leads[0][$request['county']];
            }

            $temp_fields['address']['index'] = $request['address'];
            $temp_fields['address']['field'] = 'address';
            $temp_fields['address']['index_map'] = $file_leads[0][$request['address']];

            $temp_fields['foreclosure_date']['index'] = $request['foreclosure_date'];
            $temp_fields['foreclosure_date']['field'] = 'foreclosure_date';
            $temp_fields['foreclosure_date']['index_map'] = $file_leads[0][$request['foreclosure_date']];


            $temp_fields['admin_notes']['index'] = $request['admin_notes'];
            $temp_fields['admin_notes']['field'] = 'admin_notes';
            $temp_fields['admin_notes']['index_map'] = $file_leads[0][$request['admin_notes']];


            $temp_fields['auction']['index'] = $request['auction'];
            $temp_fields['auction']['field'] = 'auction';
            $temp_fields['auction']['index_map'] = $file_leads[0][$request['auction']];


            $temp_fields['lead_value']['index'] = $request['lead_value'];
            $temp_fields['lead_value']['field'] = 'lead_value';
            $temp_fields['lead_value']['index_map'] = $file_leads[0][$request['lead_value']];


            $temp_fields['loan_date']['index'] = $request['loan_date'];
            $temp_fields['loan_date']['field'] = 'loan_date';
            $temp_fields['loan_date']['index_map'] = $file_leads[0][$request['loan_date']];


            $temp_fields['sq_ft']['index'] = $request['sq_ft'];
            $temp_fields['sq_ft']['field'] = 'sq_ft';
            $temp_fields['sq_ft']['index_map'] = $file_leads[0][$request['sq_ft']];


            $temp_fields['yr_blt']['index'] = $request['yr_blt'];
            $temp_fields['yr_blt']['field'] = 'yr_blt';
            $temp_fields['yr_blt']['index_map'] = $file_leads[0][$request['yr_blt']];

            $temp_fields['admin_notes']['index'] = $request['admin_notes'];
            $temp_fields['admin_notes']['field'] = 'admin_notes';
            $temp_fields['admin_notes']['index_map'] = $file_leads[0][$request['admin_notes']];

            $temp_fields['eq']['index'] = $request['eq'];
            $temp_fields['eq']['field'] = 'eq';
            $temp_fields['eq']['index_map'] = $file_leads[0][$request['eq']];

            $temp_fields['mortgagee']['index'] = $request['mortgagee'];
            $temp_fields['mortgagee']['field'] = 'mortgagee';
            $temp_fields['mortgagee']['index_map'] = $file_leads[0][$request['mortgagee']];

            $temp_fields['loan_type']['index'] = $request['loan_type'];
            $temp_fields['loan_type']['field'] = 'loan_type';
            $temp_fields['loan_type']['index_map'] = $file_leads[0][$request['loan_type']];

            $temp_fields['loan_mod']['index'] = $request['loan_mod'];
            $temp_fields['loan_mod']['field'] = 'loan_mod';
            $temp_fields['loan_mod']['index_map'] = $file_leads[0][$request['loan_mod']];

            $temp_fields['trustee']['index'] = $request['trustee'];
            $temp_fields['trustee']['field'] = 'trustee';
            $temp_fields['trustee']['index_map'] = $file_leads[0][$request['trustee']];

            $temp_fields['owner_address']['index'] = $request['owner_address'];
            $temp_fields['owner_address']['field'] = 'eq';
            $temp_fields['owner_address']['index_map'] = $file_leads[0][$request['owner_address']];

            $temp_fields['source']['index'] = $request['source'];
            $temp_fields['source']['field'] = 'eq';
            $temp_fields['source']['index_map'] = $file_leads[0][$request['source']];

            $temp_fields['county'] = [];

            if (!is_null($request['county'])) {

                $temp_fields['county']['index'] = $request['county'];
                $temp_fields['county']['field'] = 'county';
                $temp_fields['county']['index_map'] = $file_leads[0][$request['county']];
            }

            $temp_fields['state'] = [];
            if (!empty($request['state']) && !is_null($request['state'])) {

                $temp_fields['state']['index'] = $request['state'];
                $temp_fields['state']['field'] = 'state';
                $temp_fields['state']['index_map'] = $file_leads[0][$request['state']];
            }

            $temp_fields['lead_type']['index'] = $request['lead_type'];
            $temp_fields['lead_type']['field'] = 'lead_type';
            $temp_fields['lead_type']['index_map'] = !empty($request['lead_type']) ? $file_leads[0][$request['lead_type']] : null;

            $temp_fields['lead_status']['index'] = $request['lead_status'];
            $temp_fields['lead_status']['field'] = 'lead_status';
            $temp_fields['lead_status']['index_map'] = !empty($request['lead_status']) ? $file_leads[0][$request['lead_status']] : null;

            $temp_fields['city']['index'] = $request['city'];
            $temp_fields['city']['field'] = 'city';
            $temp_fields['city']['index_map'] = $file_leads[0][$request['city']];

            $temp_fields['zip_code']['index'] = $request['zip_code'];
            $temp_fields['zip_code']['field'] = 'zip_code';
            $temp_fields['zip_code']['index_map'] = $file_leads[0][$request['zip_code']];

            LeadQuery::insertBulk($obj->id, $request->company_id);

            if ($new_lead == true) {
                Status::incrementLeadCount($status_id);
                $obj_lead_history = LeadHistory::create([
                            'lead_id' => $obj->id,
                            'title' => 'Lead created',
                            'assign_id' => $request['user_id'],
                            'status_id' => 0
                ]);
            }

            if ($is_followup == false) {
                $obj_lead_type_history = LeadType::create([
                            'lead_id' => $obj->id,
                            'title' => 'Lead Type History created from import.',
                            'assign_id' => $request['user_id'],
                            'type_id' => $leadDetail->type_id
                ]);
            }

            $assign_id = (isset($user_codes[$file_leads[$i][$user_code_index]])) ? $user_codes[$file_leads[$i][$user_code_index]] : $request['user_id'];


            if (!empty($statusData) AND $new_lead == true) {
                $obj_lead_history = LeadHistory::create([
                            'lead_id' => $obj->id,
                            'title' => '',
                            'lead_status_title' => 'Lead status initialized.',
                            'assign_id' => $assign_id,
                            'status_id' => $statusData->id
                ]);
            }

            // insert lead custom fields
            $custom_fields = [];
            $ignore_fields = ['company_id'];
            $custom_fields['company_id'] = $request->company_id;
            if (isset($request['custom_field'])) {
                foreach ($request['custom_field'] as $key => $value) {
                    //$multi_key = [];
                    $multi_value = [];
                    $multi_index_map = [];
                    foreach ($value as $each_value) {
                        if (!empty($file_leads[$i][$each_value]) &&
                                (strtolower($file_leads[$i][$each_value]) != 'n/a' && strtolower($file_leads[$i][$each_value]) && '1970-01-01 00:00:00' && strtolower($file_leads[$i][$each_value]) != '1970-01-01')) {
                            $multi_value[] = $file_leads[$i][$each_value];
                            $multi_index_map[] = $file_leads[0][$each_value];
                        }
                    }
                    $multi_value_c = implode(',', $multi_value);
                    $value_c = implode(',', $value);
                    if (!empty($multi_value_c) && strtolower($value_c) != 'n/a') {
                        $custom_fields[$key] = $multi_value_c; //$file_leads[$i][$value];
                        $temp_fields[$key]['index'] = $value_c;
                        $temp_fields[$key]['field'] = $key;
                        $temp_fields[$key]['index_map'] = implode(',', $multi_index_map); //$file_leads[0][$value];
                        // $temp_field_index_map[$key] = $file_leads[0][$value];
                    }
                }

                LeadCustomField::insert($obj->id, $ignore_fields, $custom_fields);
            }
        }

        if (count($temp_fields)) {
            Lead::saveTemplateField($request['template_id'], $temp_fields);
        }

        updateLeadAuctionDate();
        storeDateAuctionDateFormat();

        \Artisan::call('cache:clear');

        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('Lead', [], 200, 'Your lead bulk has been added successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $param_rules['id'] = 'required|exists:lead_detail,id';
        $this->__is_ajax = true;
        $response = $this->__validateRequestParams(['id' => $id], $param_rules);

        if ($this->__is_error == true)
            return $response;
        $lead = Lead::find($id);

        if (!is_null($lead)) {
            $lead->last_see_at = getDayLightTimeZoneDB(Carbon::now());
            $lead->save();
        }

        $this->__is_paginate = false;
        $this->__is_collection = false;
        return $this->__sendResponse('Lead', Lead::getById($id), 200, 'Lead has been retrieved successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id) {
        $param_rules['id'] = 'required|exists:lead_detail,id';
        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];
        $param['is_paginate'] = false;

        $response = $this->__validateRequestParams(['id' => $id], $param_rules);

        if ($this->__is_error == true)
            return $response;

        $response['status'] = Status::getList($param);
        $response['agent'] = User::getTenantUserList($param);

        $response['type'] = Type::whereIn('tenant_id', [$request['company_id']])->whereNull('deleted_at')->get();
        $response['lead'] = Lead::getById($id);
        // info($response['lead']);

        $this->__view = 'tenant.lead.lead_detail';

        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('Lead', $response, 200, 'Lead has been retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {

        $request['id'] = $id;
        $param_rules['id'] = 'required|exists:lead_detail,id';
        $param_rules['target_id'] = 'required|exists:user,id';
        $param_rules['status_id'] = 'required|exists:status,id';
        $param_rules['is_expired'] = 'required';
        $param_rules['title'] = 'required';
        $param_rules['type_id'] = 'required';
        $param_rules['address'] = 'required';
        $param_rules['city'] = 'required';
        $param_rules['state'] = 'required';

        $this->__is_ajax = true;

        $request['target_id'] = (!empty($request['target_id'])) ? $request['target_id'] : $request['user_id'];
        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        $address = explode(',', $request->address);
        $address = (isset($address[0])) ? $address[0] : $address;

        $owner = (isset($request['owner'])) ? $request['owner'] : '';
        $owner = (isset($request['first_name'])) ? $request['first_name'] : $owner;
        $owner = (isset($request['last_name'])) ? trim($owner . ' ' . $request['last_name']) : $owner;

        $lead_old_data = [];
        $obj_lead = Lead::find($id);
        $lead_old_data = $obj_lead->toArray();

        $obj_lead->address = $request->address;
        $obj_lead->title = $request['title'];
        $obj_lead->foreclosure_date = (isset($request['foreclosure_date'])) ? $request['foreclosure_date'] : 'test';
        $obj_lead->admin_notes = (isset($request['admin_notes'])) ? $request['admin_notes'] : null;
        $obj_lead->owner = $owner;
        $obj_lead->assignee_id = ($request['target_id'] == $request['user_id']) ? 0 : $request['target_id'];
        $obj_lead->status_id = $request['status_id'];
        $obj_lead->type_id = $request['type_id'];
        $obj_lead->is_expired = (empty($request['is_expired'])) ? 0 : 1;
        $obj_lead->city = $request['city'];
        $obj_lead->county = $request['county'];
        $obj_lead->state = $request['state'];
        $obj_lead->zip_code = $request['zip_code'];
        $obj_lead->admin_notes = $request['admin_notes'];
        $obj_lead->auction = $request['auction'];
        $obj_lead->lead_value = str_replace('$', '', $request['lead_value']);
        $obj_lead->original_loan = $request['original_loan'];
        $obj_lead->original_loan_2 = (int) filter_var($request['original_loan'], FILTER_SANITIZE_NUMBER_INT);
        $obj_lead->loan_date = $request['loan_date'];
        $obj_lead->sq_ft = $request['sq_ft'];
        $obj_lead->sq_ft_2 = (int) filter_var($request['sq_ft'], FILTER_SANITIZE_NUMBER_INT);
        $obj_lead->yr_blt = $request['yr_blt'];
        $obj_lead->eq = $request['eq'];

        $obj_lead->mortgagee = $request['mortgagee'];
        $obj_lead->loan_type = $request['loan_type'];
        $obj_lead->loan_mod = $request['loan_mod'];
        $obj_lead->trustee = $request['trustee'];
        $obj_lead->owner_address = $request['owner_address'];
        $obj_lead->source = $request['source'];

        if (!empty($request->knocks_target_id)) {
            $request['user_id'] = $request->knocks_target_id;
        }

        $user_data = User::where('id', $request['user_id'])
                ->first();

        // userUpdateLastActivity($user_data->id);

        $inputUserLeadKnocks = [];

        if (!empty($request->knocks_status_id) && !empty($request->knocks_target_id)) {

            $obj_lead->assignee_id = $request->knocks_target_id;
            $obj_lead->status_id = $request->knocks_status_id;

            $leadHistoryInput['lead_id'] = $obj_lead->id;
            $leadHistoryInput['assign_id'] = $request->knocks_target_id;
            $leadHistoryInput['status_id'] = $request->knocks_status_id;

            $status = Status::find($request->knocks_status_id);

            $leadHistoryInput['title'] = 'Admin Edited Status to ' . $status->title;
            $leadHistoryInput['followup_status_id'] = 0;

            $leadHistory = LeadHistory::create($leadHistoryInput);

            $inputUserLeadKnocks['user_id'] = $request->knocks_target_id;
            $inputUserLeadKnocks['lead_id'] = $obj_lead->id;
            $inputUserLeadKnocks['status_id'] = $request->knocks_status_id;
            $inputUserLeadKnocks['lead_history_id'] = $leadHistory->id;

            UserLeadKnocks::create($inputUserLeadKnocks);
        }

        // if($request->has('knocks_target_id') && !empty($request->knocks_target_id)){
        //     $leadHistoryInput['lead_id'] = $obj_lead->id;
        //     $leadHistoryInput['assign_id'] = $request['user_id'];
        //     $leadHistoryInput['status_id'] = 0;
        //     $user = User::find($request->knocks_target_id);
        //     $leadHistoryInput['title'] =  'Admin Edit Assigned to '.$user->first_name.''.$user->last_name;
        //     $leadHistoryInput['followup_status_id'] = 0;
        //     $leadHistory = LeadHistory::create($leadHistoryInput);
        //     $inputUserLeadKnocks['user_id'] = $request['user_id'];
        //     $inputUserLeadKnocks['lead_id'] = $obj_lead->id;
        //     $inputUserLeadKnocks['status_id'] = $obj_lead->status_id;
        //     $inputUserLeadKnocks['lead_history_id'] = $leadHistory->id;
        //     UserLeadKnocks::create($inputUserLeadKnocks);
        // }

        if (isset($user_data->first_name) AND $user_data->first_name != '') {
            $obj_lead->updated_by = $user_data->first_name . ' ' . $user_data->last_name;
        }


        if ($address != $lead_old_data['address']) {
            $lat_long_response = $this->getLatLongFromAddress($request->address);

            $obj_lead->latitude = $lat_long_response['lat'];
            $obj_lead->longitude = $lat_long_response['long'];
            $obj_lead->formatted_address = $lat_long_response['formatted_address'];
            $obj_lead->zip_code = $lat_long_response['zip_code'];
        }

        $obj_lead->save();

        $obj_history = new History();

        $obj_history->history_trigger_prefx = 'lead';
        $obj_history->history_trigger_map = ['address', 'title', 'assignee', 'type', 'expired', 'status'];

        if (empty($request->knocks_status_id) && empty($request->knocks_target_id)) {
            // $obj_history->history_trigger_map = ['address', 'title','type', 'expired'];
            $obj_history->initiate($lead_old_data, $request->all());
        }


        if ($lead_old_data['type_id'] != $request['type_id']) {
            $obj_lead_type_history = LeadType::create([
                        'lead_id' => $id,
                        'title' => 'Lead Type Hisotry created from edit form.',
                        'assign_id' => $request['target_id'],
                        'type_id' => $request['type_id']
            ]);
        }


        if ($lead_old_data['status_id'] != $request['status_id'] && empty($request->knocks_status_id) && empty($request->knocks_target_id)) {
            Status::incrementLeadCount($obj_lead->status_id);
            Status::decrementLeadCount($lead_old_data['status_id']);
        }

        if (!empty($request->customData)) {

            foreach ($request->customData as $key => $value) {
                $leadCustomField = LeadCustomField::find($value['id']);

                if (!empty($leadCustomField)) {

                    $leadCustomField->value = $value['value'];
                    $leadCustomField->save();
                }
            }
        }

        $this->__is_paginate = false;
        $this->__is_collection = false;
        return $this->__sendResponse('Lead', Lead::getById($id), 200, 'Lead has been retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function bulkUpdate(Request $request) {
        $param_rules['assign_id'] = 'nullable|exists:user,id';
        $param_rules['status_id'] = 'nullable|exists:status,id';
        $param_rules['is_expired'] = 'nullable';
        $param_rules['type_id'] = 'nullable|exists:type,id';
        $param_rules['action'] = 'required|in:delete,update';
        $param_rules['lead_ids'] = 'required';

        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        $params['assign_id'] = (isset($request['assign_id'])) ? $request['assign_id'] : '';
        $params['status_id'] = (isset($request['status_id'])) ? $request['status_id'] : '';
        $params['type_id'] = (isset($request['type_id'])) ? $request['type_id'] : '';
        $params['is_expired'] = (isset($request['is_expired'])) ? $request['is_expired'] : '';
        $params['action'] = $request['action'];
        $params['lead_ids'] = $request['lead_ids'];
        $params['company_id'] = $request['company_id'];
        $params['target_user_id'] = (!empty($request['assign_id'])) ? $request['assign_id'] : $request['user_id'];
        $params['user_id'] = $request['user_id'];

        Lead::bulkUpdate($params);

        \Artisan::call('cache:clear');

        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('Lead', [], 200, 'Lead has been updated successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateQuery(Request $request, $id) {

        $request['id'] = $id;
        $param_rules['id'] = 'required|exists:lead_detail,id';
        $param_rules['status_id'] = 'required|exists:status,id';
        $param_rules['query'] = 'required';

        if (isset($request['user_login_id'])) {
            $user_data = User::find($request['user_login_id']);
            if (isset($user_data->first_name) AND $user_data->last_name AND $user_data->last_name != '' AND $user_data->first_name != '') {
                $userName = $user_data->first_name . ' ' . $user_data->last_name;
                $lead = Lead::find($id);
                $lead->updated_by = $userName ?? '';
                $lead->save();
            }
            if (isset($user_data->first_name) AND $user_data->first_name != '') {
                $userName = $user_data->first_name . ' ' . $user_data->last_name;
                $lead = Lead::find($id);
                $lead->updated_by = $userName;
                $lead->save();
            }
            $request['user_id'] = $request['user_login_id'];
        }

        $response = $this->__validateRequestParams($request->all(), $param_rules);
        if ($this->__is_error == true)
            return $response;

        $obj_lead = Lead::find($id);
        $obj_lead->updated_at = Carbon::now();

        if (!empty($request['user_login_id'])) {
            $user = User::find($request['user_login_id']);
            $userName = $user->first_name;
            if (!empty($user->last_name)) {
                $userName = $userName . ' ' . $user->last_name;
            }
            $obj_lead->updated_by = $userName;
        }

        $status_id = $obj_lead->status_id;
        $obj_lead->status_id = $request['status_id'];
        if (isset($request['is_verified'])) {
            $request['is_verified'] = (empty($request['is_verified'])) ? 0 : 1;
            $obj_lead->is_verified = $request['is_verified'];
        } else {
            $request['is_verified'] = 0;
        }
        $obj_lead->save();

        if ($request['user_id'] == 4) {
            $request['user_id'] = $request['user_login_id'];
        }

        $leadQuery = LeadQuery::where('lead_id', $request->id)
                ->where('query_id', 8)
                ->latest()
                ->first();


        $leadQueryCollect = collect(json_decode($request['query']));

        $leadQueryCollect = $leadQueryCollect->where('query_id', 8)->first();

        if ($request['is_status_update'] == 1) {
            $statusDetail = Status::find($request['status_id']);
            $obj_lead_history = LeadHistory::create([
                        'lead_id' => $id,
                        'title' => '',
                        'assign_id' => $request['user_id'],
                        'status_id' => $request['status_id'], //$obj_lead->status_id
                        'key_history' => 'Status Update', //$obj_lead->status_id
                        'value_history' => $statusDetail->title ?? '', //$obj_lead->status_id
                        'latest_status_id' => $request['status_id'], //$obj_lead->status_id
                        'created_at' => Now()
            ]);

            $request['lead_history_id'] = $obj_lead_history;

            Status::incrementLeadCount($obj_lead->status_id);
            Status::decrementLeadCount($status_id);
            $request['lead_id'] = $id;
            UserLeadKnocks::insertLeadKnocks($request->all());
        }

        $querys = json_decode($request['query'], true);

        $request['user_id'] = $obj_lead->assignee_id;


        $request['user_id'] = $request['user_login_id'];


        // if (!empty($request['query'])) {
        //     foreach ($querys as $key => $value) {
        //         if (!empty($value['query_id']) && $value['query_id'] == 8) {
        //             $leadQuery = LeadQuery::where('response', $value['response'])->find($value['id']);
        //             if (is_null($leadQuery)) {
        //                 $input['lead_id'] = $id;
        //                 $input['assign_id'] = $request['user_id'];
        //                 $input['status_id'] = 133;
        //                 $status = Status::where('code', 'NU')->first();
        //                 $input['title'] = $status->id ?? 0;
        //                 LeadHistory::create($input);
        //             }
        //         }
        //     }
        // }


        if (!empty($request['query'])) {

            $input['latest_status_id'] = $lead->status_id;
            $input['key_history'] = null;
            $input['value_history'] = null;
            $input['status_id'] = 0;
            $input['title'] = '';

            foreach ($querys as $key => $value) {
                if (!empty($value['query_id'])) {
                    $leadQuery = LeadQuery::find($value['id']);
                    if ($leadQuery->response != $value['response']) {
                        $input['lead_id'] = $id;

                        $lead = Lead::find($id);

                        $input['assign_id'] = $request['user_id'];
                        $title = $value['query'];

//                        $status_data = Status::where('title','=',$value['query'])->first();
//                        if(isset($status_data->id)){
//                            $input['status_id'] = $status_data->id;
//                        }else{
//                        }
                        $input['title'] = $input['title'] . $value['query'] . ': ' . $value['response'] . '
';
                    }
                }
            }

            LeadHistory::createNew($input);
        }

        LeadQuery::updateQuery($id, $querys);

        $this->__is_paginate = false;
        $this->__is_collection = false;
        return $this->__sendResponse('Lead', Lead::getById($id), 200, 'Lead has been retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function userAssignLead(Request $request, $lead_id) {
        $param_rules['id'] = 'required|exists:lead_detail,id';
        $param_rules['target_id'] = 'required|exists:user,id';
        $request['id'] = $lead_id;

        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if (isset($request['user_login_id'])) {
            $user_data = User::find($request['user_login_id']);
            // userUpdateLastActivity($user_data->id);

            if (isset($user_data->first_name) AND $user_data->last_name AND $user_data->last_name != '' AND $user_data->first_name != '') {
                $userName = $user_data->first_name . ' ' . $user_data->last_name;

                $updated_by = [];
                $updated_by['updated_by'] = $userName;
                Lead::where('id', '=', $id)->update($updated_by);
            }
            if (isset($user_data->first_name) AND $user_data->first_name != '') {
                $userName = $user_data->first_name . ' ' . $user_data->last_name;
                $updated_by = [];
                $updated_by['updated_by'] = $userName;
                Lead::where('id', '=', $id)->update($updated_by);
            }
        }


        if ($this->__is_error == true)
            return $response;

        $assignee_id = isset($request['target_id']) ? $request['target_id'] : $request['user_id'];


        $obj_lead = Lead::find($lead_id);

        // if($obj_lead->assignee_id != $assignee_id){
        //     $input['user_id'] = $request->user_id;
        //     $input['lead_id'] = $request->id;
        //     $input['status_id'] = $request->status_id;
        //     UserLeadKnocks::create($input);
        // }

        $lead_old_data = $obj_lead->toArray();
//        if($request['user_id'] == 4){
//            $request['user_id'] = $request['user_login_id'];
//        }
//        $obj_lead->assignee_id = isset($request['user_login_id']) ? $request['user_login_id'] : $request['user_id'];
        $obj_lead->assignee_id = $assignee_id;

//        $obj_lead->assignee_id = isset($request['user_login_id']) ? $request['user_login_id'] : $request['user_login_id'];
        $obj_lead->save();

        /* $obj_lead_history = LeadHistory::create([
          'lead_id' => $lead_id,
          'title' => '',
          'assign_id' => $request['user_id'],
          'status_id' => $obj_lead->status_id
          ]);
         */

//        $obj_lead_type_history = LeadType::create([
//                            'lead_id' => $lead_id,
//                            'title' => 'Lead Type History created from user assign.',
//                            'assign_id' => $obj_lead->assignee_id,
//                            'type_id' => $obj_lead->type_id
//                ]);

        $obj_history = new History();

        $obj_history->history_trigger_prefx = 'lead';
        $obj_history->history_trigger_map = ['assignee'];
        $request['assignee_id'] = $obj_lead->assignee_id;

        $obj_history->initiate($lead_old_data, $request->all());

        $this->__is_paginate = false;
        $this->__is_collection = false;
        return $this->__sendResponse('Lead', Lead::getById($lead_id), 200, 'Lead has been retrieved successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function history(Request $request) {

        $param_rules['search'] = 'sometimes';
        $param_rules['lead_id'] = 'sometimes';

        $param['search'] = isset($request['search']) ? $request['search'] : '';
        $param['lead_id'] = isset($request['lead_id']) ? $request['lead_id'] : '';
        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];

        $response = LeadHistory::getListWeb($param);

        return $this->__sendResponse('LeadHistory', $response, 200, 'Lead history list retrieved successfully.');
    }

    public function historyExport(Request $request, $lead_id) {

        $param_rules['search'] = 'sometimes';
        $param_rules['lead_id'] = 'required';

        $request['lead_id'] = $lead_id;
        $param['search'] = isset($request['search']) ? $request['search'] : '';
        $param['lead_id'] = isset($request['lead_id']) ? $request['lead_id'] : $lead_id;

        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];

        $data = [];
        $count = 0;
        $result = LeadHistory::getList($param);
        foreach ($result as $row) {
            $data[$count] = new \stdClass();
            $data[$count]->title = $row->title;
            $data[$count]->lead_op = $row->lead_history_title;
            $user = User::getById($row->assign_id);
            $data[$count]->updated_by = "{$user->first_name} {$user->last_name}";
            $data[$count]->who = $user->email;
            $data[$count]->updated_at = dynamicDateFormat(dateTimezoneChange($row->created_at), 3);
            $count++;
        }

        return $this->__exportCSV(['title', 'lead_op', 'updated_by', 'who', 'updated_at'], $data);
    }

    public function leadsHistoryExport(Request $request) {
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
        $param['lead_ids'] = isset($request['lead_ids']) ? (!empty($request['lead_ids'])) ? explode(',', $request['lead_ids']) : [] : [];
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
            $lead_response = Lead::getListIndexExportIds($param)->pluck('id');
            $param['lead_ids'] = $lead_response->toArray();
        }

        $data = [];
        $count = 0;

        $query = LeadHistory::select('status.title as status_title',
                        'lead_history.id as lead_history_id',
                        'lead_detail.id as lead_id',
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
                        'lead_detail.status_id',
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
                        'lead_detail.created_at as lead_created_at',
                        'lead_detail.updated_at as lead_updated_at',
                        'lead_status.title as lead_status_title',
                        'lead_user.first_name as lead_assignee_first_name',
                        'lead_user.last_name as lead_assignee_last_name',
                        'user.first_name as assign_id_first_name',
                        'user.last_name as assign_id_last_name',
                        'user.email as assign_id_email',
                        'lead_history.assign_id'
                        , DB::raw("concat(user.first_name,' ', user.last_name) as name")
                        , DB::raw("(IF(lead_history.status_id = 0, lead_history.title, concat(status.title, ' status updated'))) as lead_history_title")
                        , 'lead_history.created_at', 'lead_history.followup_status_id', 'lead_history.key_history',
                        'lead_history.value_history', 'lead_history.latest_status_id');        
        $query->with('latestnotes');
        $query->with('leadStatus');
        $query->with('leadType');
        $query->with('leadMedia');
        $query->leftJoin('lead_detail', 'lead_detail.id', 'lead_history.lead_id');
        $query->leftJoin('status', 'status.id', 'lead_history.status_id');

        $query->leftJoin('user', 'user.id', 'lead_history.assign_id');
        $query->leftJoin('type', 'lead_detail.type_id', '=', 'type.id');
        $query->leftJoin('user as lead_user', 'lead_detail.assignee_id', '=', 'lead_user.id');
        $query->leftJoin('status as lead_status', 'lead_detail.status_id', '=', 'lead_status.id');


        if (isset($param['lead_id']) && !empty($param['lead_id']))
            $query->where('lead_history.lead_id', $param['lead_id']);

        if (isset($param['lead_ids']) && !empty($param['lead_ids']))
            $query->whereIn('lead_history.lead_id', $param['lead_ids']);

        if (isset($param['search']) && !empty($param['search']))
            $query->whereRaw("lead_detail.title like '%{$param['search']}%'");

        if (isset($param['is_lead_export']) && $param['is_lead_export'] === 'true') {
            $query->where('lead_history.assign_id', '!=', 0);
            $query->where('lead_history.assign_id', '!=', null);
            $query->whereNotNull('lead_history.lead_id');
            $query->orderBy('lead_detail.title');
        }
        $query->groupBy('lead_history.id');
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
                $data = new \stdClass();
                $data->title = $row->title;
                $data->Address = $row->address . ' ' . $row->zip_code . ' ' . $row->city;
                $data->lead_status = $row->lead_history_title;
                $data->updated_by = $row->updated_by;
                $data->Who = $row->assign_id_email;
                $data->CreatedDate = dynamicDateFormat(dateTimezoneChangeFullDateTimeReturn($row->created_at), 5);
                $data->CreatedTime = dynamicDateFormat(dateTimezoneChangeFullDateTimeReturn($row->created_at), 9);
                $data->created_by = $row->created_by;
                if (isset($row->leadType->title) && $row->leadType->title != '') {
                    $data->lead_type = $row->leadType->title;
                } else {
                    $data->lead_type = '';
                }
                $data->lead_status_title = $row->status_title;

                if (isset($row->latestnotes->response) && $row->latestnotes->response != '') {
                    $data->Notes = $row->latestnotes->response;
                } else {
                    $data->Notes = '';
                }
                if ($row->is_expired == 0) {
                    $data->IsRetired = 'No';
                } else {
                    $data->IsRetired = 'Yes';
                }
                if ($row->is_verified == 1) {
                    $data->is_verified = 'YES';
                } else {
                    $data->is_verified = 'NO';
                }
                $data->AssignedTo = "{$row->lead_assignee_first_name} {$row->lead_assignee_last_name}";
                $data->LeadStatus = $row->lead_status_title;
                if (isset($row->leadType->title) && $row->leadType->title != '') {
                    $data->LeadType = $row->leadType->title;
                } else {
                    $data->LeadType = '';
                }
                $data->Auction = $row->auction;
                $data->City = $row->city;
                $data->State = $row->state;
                $data->Zip = $row->zip_code;
                $data->County = $row->county;
                $data->AdminNotes = $row->admin_notes;
                $data->LeadValue = $row->lead_value;
                $data->Mortgagee = $row->mortgagee;
                $data->OriginalLoan = $row->original_loan;
                $data->LoanDate = $row->loan_date;
                $data->LoanMod = $row->loan_mod;
                $data->Trustee = $row->trustee;
                $data->SqFt = $row->sq_ft;
                $data->YrBlt = $row->yr_blt;
                $data->OwnerAddress = $row->owner_address;
                $data->EQ = $row->eq;
                $data->Source = $row->source;
                $data->CreatedBy = $row->created_by;
                $data->UpdatedBy = $row->updated_by;
                $data->CreatedOnDate = dynamicDateFormat(dateTimezoneChange($row->lead_created_at), 5);
                $data->UpdatedOnDate = dynamicDateFormat(dateTimezoneChange($row->lead_updated_at), 5);
                $data->CreatedOnTime = dynamicDateFormat(dateTimezoneChangeFullDateTimeReturn($row->lead_created_at), 9);
                $data->UpdatedOnTime = dynamicDateFormat(dateTimezoneChangeFullDateTimeReturn($row->lead_updated_at), 9);                
                $allRecords[] = $data;
            }
        });

        return $this->__exportCSV(['Homeowner Name', 'Address', 'Information Updated', 'Updated By', 'Who', 'Created Date', 'Created Time', 'Created By', 'Lead Type', 'Lead Status', 'Notes', 'Is Retired', 'is_verified', 'Assigned To', 'Lead Status', 'Lead Type', 'Auction', 'City', 'State', 'Zip', 'County', 'Admin Notes', 'Lead Value', 'Mortgagee', 'Original Loan', 'Loan Date', 'Loan Mod', 'Trustee', 'Sq Ft', 'Yr Blt', 'Owner Address', 'EQ', 'Source', 'Created By', 'Updated By', 'Lead Created Date', 'Lead Updated Date', 'Lead Created Time', 'Lead Updated Time'], $allRecords, '', [], 'leads_status_history.csv');
        $result = LeadHistory::getListHistory($param);

        foreach ($allRecords as $row) {
            $data[$count] = new \stdClass();
            $data[$count]->title = $row->title;
            $data[$count]->Address = $row->address . ' ' . $row->zip_code . ' ' . $row->city;
            $data[$count]->lead_status = $row->lead_history_title;
            $data[$count]->updated_by = $row->updated_by;
            $data[$count]->Who = $row->assign_id_email;
            $data[$count]->CreatedDate = dynamicDateFormat(dateTimezoneChange($row->created_at), 5);
            $data[$count]->CreatedTime = dynamicDateFormat(dateTimezoneChange($row->created_at), 9);
            $data[$count]->created_by = $row->created_by;
            if (isset($row->leadType->title) AND $row->leadType->title != '') {
                $data[$count]->lead_type = $row->leadType->title;
            } else {
                $data[$count]->lead_type = '';
            }
            $data[$count]->lead_status_title = $row->status_title;

            if (isset($row->new_lead_query_response) AND $row->new_lead_query_response != '') {
                $data[$count]->Notes = $row->new_lead_query_response;
            } else {
                $data[$count]->Notes = '';
            }
            if ($row->is_expired == 0) {
                $data[$count]->IsRetired = 'No';
            } else {
                $data[$count]->IsRetired = 'Yes';
            }
            if ($row->is_verified == 1) {
                $data[$count]->is_verified = 'YES';
            } else {
                $data[$count]->is_verified = 'NO';
            }
            $data[$count]->AssignedTo = "{$row->lead_assignee_first_name} {$row->lead_assignee_last_name}";
            $data[$count]->LeadStatus = $row->lead_status_title;
            if (isset($row->leadType->title) AND $row->leadType->title != '') {
                $data[$count]->LeadType = $row->leadType->title;
            } else {
                $data[$count]->LeadType = '';
            }
            $data[$count]->Auction = $row->auction;
            $data[$count]->City = $row->city;
            $data[$count]->State = $row->state;
            $data[$count]->Zip = $row->zip_code;
            $data[$count]->County = $row->county;
            $data[$count]->AdminNotes = $row->admin_notes;
            $data[$count]->LeadValue = $row->lead_value;
            $data[$count]->Mortgagee = $row->mortgagee;
            $data[$count]->OriginalLoan = $row->original_loan;
            $data[$count]->LoanDate = $row->loan_date;
            $data[$count]->LoanMod = $row->loan_mod;
            $data[$count]->Trustee = $row->trustee;
            $data[$count]->SqFt = $row->sq_ft;
            $data[$count]->YrBlt = $row->yr_blt;
            $data[$count]->OwnerAddress = $row->owner_address;
            $data[$count]->EQ = $row->eq;
            $data[$count]->Source = $row->source;
            $data[$count]->CreatedBy = $row->created_by;
            $data[$count]->UpdatedBy = $row->updated_by;
            $data[$count]->CreatedOnDate = dynamicDateFormat(dateTimezoneChange($row->lead_created_at), 5);
            $data[$count]->UpdatedOnDate = dynamicDateFormat(dateTimezoneChange($row->lead_updated_at), 5);
            $data[$count]->CreatedOnTime = dynamicDateFormat(dateTimezoneChange($row->lead_created_at), 9);
            $data[$count]->UpdatedOnTime = dynamicDateFormat(dateTimezoneChange($row->lead_updated_at), 9);
            $count++;
        }



        return $this->__exportCSV(['Homeowner Name', 'Address', 'Information Updated', 'Updated By', 'Who', 'Created Date', 'Created Time', 'Created By', 'Lead Type', 'Lead Status', 'Notes', 'Is Retired', 'is_verified', 'Assigned To', 'Lead Status', 'Lead Type', 'Auction', 'City', 'State', 'Zip', 'County', 'Admin Notes', 'Lead Value', 'Mortgagee', 'Original Loan', 'Loan Date', 'Loan Mod', 'Trustee', 'Sq Ft', 'Yr Blt', 'Owner Address', 'EQ', 'Source', 'Created By', 'Updated By', 'Lead Created Date', 'Lead Updated Date', 'Lead Created Time', 'Lead Updated Time'], $data, '', [], 'leads_status_history.csv');
    }

    public function checkServiceAccountCredentialsFile() {
        $application_creds = storage_path('app/service-account-credentials.json');

        return file_exists($application_creds) ? $application_creds : false;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function createAppointment(Request $request) {

        $param_rules['lead_id'] = 'required|exists:lead_detail,id';
        $param_rules['query'] = 'required';
        $param_rules['appointment_date'] = 'required|date_format:"n-j-Y G:i"|after_or_equal:' . date("n-j-Y G:i");

        $appointment_date = explode(':', $request['appointment_date']);
        $appointment_date_min = (isset($appointment_date[1])) ? ((strlen($appointment_date[1]) > 1) ? $appointment_date[1] : "0{$appointment_date[1]}") : '00';
        $request['appointment_date'] = "{$appointment_date[0]}:$appointment_date_min";

        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        $parse_date = explode(' ', $request['appointment_date']);
        $parse_time = $parse_date[1];
        $parse_date = explode('-', $parse_date[0]);
        $parse_month = $parse_date[0];
        $parse_day = $parse_date[1];
        $parse_year = $parse_date[2];
        $request['appointment_date'] = "$parse_year-$parse_month-$parse_day $parse_time";
        $request['appointment_date'] = date('Y-m-d H:i', strtotime($request['appointment_date']));

        $start_date = date('Y-m-d', strtotime($request['appointment_date']));
        $start_time = date('H:i:s', strtotime('-1 hours', strtotime($request['appointment_date'])));
        $end_date = date('Y-m-d', strtotime($request['appointment_date']));
        $end_time = date('H:i:s', strtotime('1 hours', strtotime($request['appointment_date'])));

        $check_daylight = date('d-M-Y', strtotime($request['appointment_date']));
        $date = new DateTime($check_daylight . ' America/Los_Angeles');
        $check_daylight_result = $date->format('I');
        if ($check_daylight_result == 1) {
            $final_start_date = $start_date . 'T' . $start_time . '-06:00';
            $final_end_date = $end_date . 'T' . $end_time . '-06:00';
        } else {
            $final_start_date = $start_date . 'T' . $start_time . '-07:00';
            $final_end_date = $end_date . 'T' . $end_time . '-07:00';
        }

        $appointments = UserLeadAppointment::whereRaw("('{$request['appointment_date']}:00'" . ' between `appointment_date` and `appointment_end_date`)')
                ->whereRaw("(user_id = {$request['user_id']} OR lead_id = {$request['lead_id']})")
                ->whereNull('deleted_at')
                ->orderBy('id', 'desc')
                ->first();

        // userUpdateLastActivity($request['user_id']);

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

        $obj_appointment = new UserLeadAppointment();
        $obj_appointment->lead_id = $request['lead_id'];

        $queryData = json_decode($request['query'], true);

        $lead = Lead::find($request['lead_id']);

        $user = User::find($request['user_id']);

        $title = $lead->title ? $lead->title : $user->name;
        $address = $lead->formatted_address ?? $lead->address;

        $person_meeting = null;
        $emailUser = null;
        $phoneUser = null;
        $additionalNotes = null;

        if (!empty($queryData)) {

            foreach ($queryData as $key => $value) {
                switch ($value['query']) {
                    case 'Person With Whom You Are Meeting':
                        $person_meeting = $value['response'];
                        break;

                    case 'Email':
                        $emailUser = $value['response'];
                        break;

                    case 'Phone':
                        $phoneUser = $value['response'];
                        break;

                    case 'Additional Notes':
                        $additionalNotes = $value['response'];
                        break;
                }
            }
        }

        $obj_appointment->user_id = $request['user_id'];
        $obj_appointment->appointment_date = $request['appointment_date'];
        $obj_appointment->appointment_end_date = $request['appointment_date'];
        $obj_appointment->is_out_bound = 0;

        $obj_appointment->person_meeting = $person_meeting;
        $obj_appointment->phone = $phoneUser;
        $obj_appointment->email = $emailUser;
        $obj_appointment->additional_notes = $additionalNotes;
        $obj_appointment->result = 'Home Owner Name: ' . $title . '
Address: ' . $address . ', 
Person With Whom You Are Meeting: ' . $obj_appointment->person_meeting . ',
Phone Number: ' . $obj_appointment->phone . ', 
E-mail: ' . $obj_appointment->email . ', 
Additional Notes: ' . $obj_appointment->additional_notes . ', 
lead ID: ' . $lead->id;



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


            if (isset($request['user_id'])) {
                $user = User::getById($request['user_id']);
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
                    $obj_appointment->result .= ",User who scheduled the appointment: " . $scheduled_user;
                }
            }

            $client->setApplicationName("Client_Library_Examples");
            $client->setScopes(['https://www.googleapis.com/auth/calendar']);
            $user_to_impersonate = env('GOOGLE_CALENDER_EMAIL', 'investors@letsgetpaid.com');
            $client->setSubject($user_to_impersonate);
            $service = new Calendar($client);
            $calendarList = $service->calendarList->listCalendarList();
            $event = new Event(array(
                'summary' => $obj_appointment->result,
                'location' => '',
                'description' => $obj_appointment->result,
                'start' => array(
                    'dateTime' => $final_start_date,
                    'timeZone' => 'UTC',
                ),
                'end' => array(
                    'dateTime' => $final_end_date,
                    'timeZone' => 'UTC',
                ),
            ));
            $calendarId = env('GOOGLE_CALENDAR_ID', 'c_6f9bf513c524a8b73219803a02364d3367065fd0ab70932f9e0756cd0f02b1cf@group.calendar.google.com');
            $event = $service->events->insert($calendarId, $event);

            if (isset($event->id)) {
                $obj_appointment->calendar_event_id = $event->id;
            }
        }
        $obj_appointment->save();


        $user = User::getById($request['user_id']);

        $scheduled_user = '';
        if (isset($user->id)) {
            if (isset($user->first_name) AND $user->first_name != '') {
                $scheduled_user .= $user->first_name . ' ';
            }
            if (isset($user->last_name) AND $user->last_name != '') {
                $scheduled_user .= $user->last_name;
            }
        }


        if (isset($request['lead_id']) AND $request['lead_id'] != '') {
            $lead = Lead::find($request['lead_id']);
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



        $data['start'] = $request['appointment_date'];
        $data['scheduled_user'] = $scheduled_user;
        $data['homeowner_name'] = $title;
        $data['address'] = $address;
        $data['person_meeting'] = $obj_appointment->person_meeting;
        $data['phone'] = $obj_appointment->phone;
        $data['email'] = $obj_appointment->email;
        $data['additional_notes'] = $obj_appointment->additional_notes;


        $mails = Alerts::where('type', '=', 1)
                ->whereNull('deleted_at')
                ->get();
        if (isset($mails[0])) {
            foreach ($mails as $mail) {
                $data['name'] = $mail->value;
                $subject = 'iKnock New Appointment Scheduled ' . date('m-d-Y g:i a', strtotime($request['appointment_date']));
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

//                try {
//                    Mail::send('emails.scheduling', $data, function($message) use ($to_email) {
//                        $message->from(env('MAIL_FROM_ADDRESS'), 'iKnock');
//                        $message->to($to_email);
//                        $message->subject('New appoiment Scheduled');
//                    });
//                } catch (\Swift_TransportException $e) {
//                    \Illuminate\Support\Facades\Log::error('Mail not sended please check SMTP details!');
//                }
            }
        }


        $message = '';
        $message .= "iKnock New Appointment Scheduled. A new appointment has been scheduled in iKnock "
                . date('m-d-Y g:i a', strtotime($request['appointment_date'])) . ". ";
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
//            $message = "Iknock New appoiment <br> Notes : " . $request['note'] . " Start time : " . date('Y-m-d H:i A', strtotime($final_start_date)) . " End Time : " . date('Y-m-d H:i A', strtotime($final_end_date));
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




        $inputLeadHistory['lead_id'] = $request['lead_id'];
        $inputLeadHistory['assign_id'] = $request['user_id'];

        $obj_lead = Lead::find($request['lead_id']);
        $obj_lead->assignee_id = $request['user_id'];
        $obj_lead->appointment_date = $request['appointment_date'];
        $obj_lead->save();

        LeadQuery::updateQuery($request['lead_id'], json_decode($request['query'], true));

        $inputLeadHistory['title'] = 'Appointment Created';

        if (!empty($request->update_appointment) && $request->update_appointment == 1) {
            $inputLeadHistory['title'] = 'Appointment Updated';
        }

        $inputLeadHistory['status_id'] = 0;

        LeadHistory::create($inputLeadHistory);

        $this->__is_paginate = false;
        $this->__is_collection = false;
        return $this->__sendResponse('Lead', Lead::getById($request['lead_id']), 200, 'Appointment Lead has been created successfully.');
    }

    public function createOutBoundAppointment(Request $request) {
        $param_rules['start_date'] = 'required|date_format:"n-j-Y G:i"|after_or_equal:' . date("n-j-Y G:i");
        $param_rules['end_date'] = 'required|date_format:"n-j-Y G:i"|after_or_equal:' . $request['start_date']; //date("Y-n-j G:i");
        $param_rules['result'] = 'nullable';

        $appointment_date = explode(':', $request['start_date']);
        $appointment_date_min = (isset($appointment_date[1])) ? ((strlen($appointment_date[1]) > 1) ? $appointment_date[1] : "0{$appointment_date[1]}") : '00';
        $request['start_date'] = "{$appointment_date[0]}:$appointment_date_min";

        $appointment_date = explode(':', $request['end_date']);
        $appointment_date_min = (isset($appointment_date[1])) ? ((strlen($appointment_date[1]) > 1) ? $appointment_date[1] : "0{$appointment_date[1]}") : '00';
        $request['end_date'] = "{$appointment_date[0]}:$appointment_date_min";

        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        // userUpdateLastActivity($request->user_id);

        $parse_date = explode(' ', $request['start_date']);
        $parse_time = $parse_date[1];
        $parse_date = explode('-', $parse_date[0]);
        $parse_month = $parse_date[0];
        $parse_day = $parse_date[1];
        $parse_year = $parse_date[2];
        $request['start_date'] = "$parse_year-$parse_month-$parse_day $parse_time";
        $request['start_date'] = date('Y-m-d h:i', strtotime($request['start_date']));

        $parse_date = explode(' ', $request['end_date']);
        $parse_time = $parse_date[1];
        $parse_date = explode('-', $parse_date[0]);
        $parse_month = $parse_date[0];
        $parse_day = $parse_date[1];
        $parse_year = $parse_date[2];
        $request['end_date'] = "$parse_year-$parse_month-$parse_day $parse_time";
        $request['end_date'] = date('Y-m-d h:i', strtotime($request['end_date']));

        $obj_appointment = new UserLeadAppointment();
        $obj_appointment->lead_id = 0;
        $obj_appointment->user_id = $request['user_id'];
        $obj_appointment->appointment_date = $request['start_date'];
        $obj_appointment->appointment_end_date = $request['end_date'];
        $obj_appointment->result = isset($request['result']) ? $request['result'] : '';
        $obj_appointment->is_out_bound = 1;
        $obj_appointment->type = 'lead';
        $obj_appointment->save();

        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('appointment', [], 200, 'Outbound appointment has been created successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function executeAppointment(Request $request) {
        $param_rules['lead_id'] = 'required|exists:lead_detail,id';
        $param_rules['appointment_id'] = 'required|exists:user_lead_appointment,id';
        $param_rules['result'] = 'required';

        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;

        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];
        $obj_lead = Lead::find($request['lead_id']);

//        if($obj_lead->assignee_id != $request['user_id']){
//
//            $user = User::find($request['user_id']);
//
//            LeadHistory::create([
//                        'lead_id' => $obj_lead->id,
//                        'title' => 'Agent "'.$user->fullname.'" updated.',
//                        'assign_id' => $request['user_id'],
//                        'status_id' => 0
//            ]);
//        }
//        $obj_lead->assignee_id = $request['user_id'];
        $obj_lead->appointment_result = $request['result'];
        $obj_lead->save();

        $userLeadAppointment = UserLeadAppointment::find($request['appointment_id']);
        $userLeadAppointment->result = $request['result'];
        $userLeadAppointment->save();

        $this->__is_paginate = false;
        $this->__is_collection = false;
        return $this->__sendResponse('Lead', Lead::getById($request['lead_id']), 200, 'Lead has been retrieved successfully.');
    }

    public function viewLeadReport(Request $request) {

        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];
        $param['is_paginate'] = false;


        $response['status'] = Status::getList($param);
        $response['agent'] = User::getTenantUserListSub($param);

        $response['type'] = Type::whereIn('tenant_id', [$request['company_id']])->whereNull('deleted_at')->get();

        $this->__view = 'tenant.team-performance.team-report';

        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('Lead', $response, 200, 'Lead has been retrieved successfully.');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function leadReport(Request $request) {
        $time_slot_map['today'] = 'INTERVAL 1 MONTH';
        $time_slot_map['yesterday'] = 'INTERVAL 1 MONTH';
        $time_slot_map['week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_month'] = 'INTERVAL 1 MONTH';
        //$time_slot_map['bi_month'] = 'INTERVAL 15 DAY';
        //$time_slot_map['bi_year'] = 'INTERVAL 6 MONTH';
        $time_slot_map['year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['last_year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['all_time'] = '';

        $param['is_web'] = (strtolower($this->call_mode) == 'api') ? 0 : 1;
        $default_time_slot = ($param['is_web']) ? 'year' : 'all_time';

        $param['company_id'] = $request['company_id'];
        $param['time_slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $time_slot_map[$request['time_slot']] : $time_slot_map['month'] : $time_slot_map['month'];
        $param['slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $request['time_slot'] : $default_time_slot : $default_time_slot;
        $param['user_id'] = isset($request['target_user_id']) ? trim($request['target_user_id'], ' ,') : '';
        $param['status_id'] = isset($request['status_id']) ? trim($request['status_id'], ' ,') : '';
        $param['lead_type_id'] = isset($request['lead_type_id']) ? trim($request['lead_type_id'], ' ,') : '';
        $param['lead_type_id'] = isset($request['type_id']) ? trim($request['type_id'], ' ,') : $param['lead_type_id'];


        $this->__is_ajax = true;
        $list = Lead::getStatusReport($param);

        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('UserCommission', $list, 200, 'User commission list retrieved successfully.');
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function leadUserReportExport(Request $request) {
        $time_slot_map['today'] = 'INTERVAL 1 MONTH';
        $time_slot_map['yesterday'] = 'INTERVAL 1 MONTH';
        $time_slot_map['week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['all_time'] = '';
        //$time_slot_map['bi_month'] = 'INTERVAL 15 DAY';
        //$time_slot_map['bi_year'] = 'INTERVAL 6 MONTH';
        $time_slot_map['year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['last_year'] = 'INTERVAL 1 YEAR';

        $param['is_web'] = (strtolower($this->call_mode) == 'api') ? 0 : 1;

//         $default_time_slot = ($param['is_web']) ? 'all_time' : 'year';

        $param['company_id'] = $request['company_id'];
        $param['time_slot'] = $request['time_slot'];
        $param['slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $request['time_slot'] : $default_time_slot : $default_time_slot;
        $param['user_id'] = isset($request['target_user_id']) ? trim($request['target_user_id'], ' ,') : '';
        $param['status_id'] = isset($request['status_id']) ? trim($request['status_id'], ' ,') : '';
        $param['lead_type_id'] = isset($request['lead_type_id']) ? trim($request['lead_type_id'], ' ,') : '';
        $param['lead_type_id'] = isset($request['type_id']) ? trim($request['type_id'], ' ,') : $param['lead_type_id'];

        $param['start_date'] = isset($request['start_date']) ? trim($request['start_date']) : '';
        $param['end_date'] = isset($request['end_date']) ? trim($request['end_date']) : '';

        if ($param['status_id'] == 'null') {
            $param['status_id'] = '';
        }

        if ($param['lead_type_id'] == 'null') {
            $param['lead_type_id'] = '';
        }

        if ($param['user_id'] == 'null') {
            $param['user_id'] = '';
        }

        $list = Lead::getUserStatusReport($param);

        $data = [];

        if (!is_null($list['result'])) {
            $data = TeamPerformanceResource::collection($list['result']);
        }

        if (empty($data)) {
            notificationMsg('error', 'Data Not Found!');
            return back();
        }

        return Excel::download(new TeamPerformanceExport($data), 'team-performance-report' . date('m-d-Y') . '-' . now() . '.csv');
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function leadUserReport(Request $request) {
        $time_slot_map['today'] = 'INTERVAL 1 MONTH';
        $time_slot_map['yesterday'] = 'INTERVAL 1 MONTH';
        $time_slot_map['week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['all_time'] = '';
        $time_slot_map['year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['last_year'] = 'INTERVAL 1 YEAR';
        $param['is_web'] = (strtolower($this->call_mode) == 'api') ? 0 : 1;
        $param['company_id'] = $request['company_id'];
        $param['time_slot'] = $request['time_slot'];
        $param['slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $request['time_slot'] : $default_time_slot : $default_time_slot;
        $param['user_id'] = isset($request['target_user_id']) ? trim($request['target_user_id'], ' ,') : '';
        $param['status_id'] = isset($request['status_id']) ? trim($request['status_id'], ' ,') : '';
        $param['lead_type_id'] = isset($request['lead_type_id']) ? trim($request['lead_type_id'], ' ,') : '';
        $param['lead_type_id'] = isset($request['type_id']) ? trim($request['type_id'], ' ,') : $param['lead_type_id'];
        $param['start_date'] = isset($request['start_date']) ? trim($request['start_date']) : '';
        $param['end_date'] = isset($request['end_date']) ? trim($request['end_date']) : '';

        $this->__is_ajax = true;
        $list = Lead::getUserStatusReport($param);

        if (!$param['is_web'])
            $list = $list['result'];

        if (isset($request['export']) && $request['export'] == true) {
            $list = $list['result'];
            $ignoreCols = [];
            $columns = ['lead_count', 'appointment_count', 'commission_count', 'commission_profit_count', 'commission_contract_count', 'agent_name'];
            $columns = ['agent_name', 'commission_count'];
            return $this->export($columns, $list, 'commission.csv', $ignoreCols);
        }
        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('UserCommission', $list, 200, 'User commission list retrieved successfully.', true);
    }

    public function leadStatusUserReport(Request $request) {
        $param['company_id'] = $request['company_id'];
        $param['month'] = (!isset($request['month'])) ? '' : $request['month'];
        $param['start_date'] = (!isset($request['start_date'])) ? '' : $request['start_date'];
        $param['end_date'] = (!isset($request['end_date'])) ? '' : $request['end_date'];
        $param['target_user_id'] = (!isset($request['target_user_id'])) ? '' : $request['target_user_id'];
        $param['status_id'] = (!isset($request['status_id'])) ? '' : $request['status_id'];
        $param['type_id'] = (!isset($request['type_id'])) ? '' : $request['type_id'];
        $param['export'] = isset($request['export']) ? $request['export'] : FALSE;

        $this->__is_ajax = true;

        $list = Lead::leadStatusUserReport($param);
        $this->__is_paginate = false;
        $this->__collection = false;

        if (isset($request['export']) && $request['export'] == true) {
            $ignoreCols = [];
            $columns = ['S.No', 'Status'];
            $columns = array_merge($columns, $list['user_names']);
            return $this->export($columns, $list['export'], 'lead_status_report.csv', $ignoreCols, 1, 1);
        }
        return $this->__sendResponse('UserLeadStatus', $list, 200, 'User lead status list retrieved successfully.', true);
    }

    public function leadTypeUserReport(Request $request) {
        $param['company_id'] = $request['company_id'];
        $param['time_slot'] = (!isset($request['time_slot'])) ? '' : $request['time_slot'];
        $param['month'] = (!isset($request['month'])) ? '' : $request['month'];
        $param['start_date'] = (!isset($request['start_date'])) ? '' : $request['start_date'];
        $param['end_date'] = (!isset($request['end_date'])) ? '' : $request['end_date'];
        $param['target_user_id'] = (!isset($request['target_user_id'])) ? '' : $request['target_user_id'];
        $param['status_id'] = (!isset($request['status_id'])) ? '' : $request['status_id'];
        $param['type_id'] = (!isset($request['type_id'])) ? '' : $request['type_id'];
        $param['type'] = (!isset($request['type'])) ? '' : $request['type'];
        $param['datetype'] = (!isset($request['datetype'])) ? 'year' : $request['datetype'];
        $param['export'] = isset($request['export']) ? $request['export'] : FALSE;

        $this->__is_ajax = true;

        $list = Lead::leadTypeUserReport($param);
        $this->__is_paginate = false;
        $this->__collection = false;

        if (isset($request['export']) && $request['export'] == true) {
            $ignoreCols = [];
            $columns = ['S.No', 'Status'];
            $columns = array_merge($columns, $list['months']);
            return $this->export($columns, $list['export'], 'lead_type_report.csv', $ignoreCols, 1, 1);
        }
        return $this->__sendResponse('UserLeadStatus', $list, 200, 'User lead status list retrieved successfully.', true);
    }

    public function leadStatusUserReportNew(Request $request) {
        $param['company_id'] = $request['company_id'];
        $param['time_slot'] = (!isset($request['time_slot'])) ? '' : $request['time_slot'];
        $param['month'] = (!isset($request['month'])) ? '' : $request['month'];
        $param['start_date'] = (!isset($request['start_date'])) ? '' : $request['start_date'];
        $param['end_date'] = (!isset($request['end_date'])) ? '' : $request['end_date'];
        $param['target_user_id'] = (!isset($request['target_user_id'])) ? '' : $request['target_user_id'];
        $param['status_id'] = (!isset($request['status_id'])) ? '' : $request['status_id'];
        $param['type_id'] = (!isset($request['type_id'])) ? '' : $request['type_id'];
        $param['type'] = (!isset($request['type'])) ? '' : $request['type'];
        $param['datetype'] = (!isset($request['datetype'])) ? 'year' : $request['datetype'];
        $param['export'] = isset($request['export']) ? $request['export'] : FALSE;

        $this->__is_ajax = true;

        $list = Lead::leadTypeUserReportNew($param);
        $this->__is_paginate = false;
        $this->__collection = false;

        if (isset($request['export']) && $request['export'] == true) {
            $ignoreCols = [];
            $columns = ['S.No', 'Status'];
            $columns = array_merge($columns, $list['months']);
            return $this->export($columns, $list['export'], 'lead_type_report.csv', $ignoreCols, 1, 1);
        }
        return $this->__sendResponse('UserLeadStatus', $list, 200, 'User lead status list retrieved successfully.', true);
    }

    public function leadStatusUserReportFollowUp(Request $request) {
        $param['company_id'] = $request['company_id'];
        $param['time_slot'] = (!isset($request['time_slot'])) ? '' : $request['time_slot'];
        $param['month'] = (!isset($request['month'])) ? '' : $request['month'];
        $param['start_date'] = (!isset($request['start_date'])) ? '' : $request['start_date'];
        $param['end_date'] = (!isset($request['end_date'])) ? '' : $request['end_date'];
        $param['target_user_id'] = (!isset($request['target_user_id'])) ? '' : $request['target_user_id'];
        $param['status_id'] = (!isset($request['status_id'])) ? '' : $request['status_id'];
        $param['type_id'] = (!isset($request['type_id'])) ? '' : $request['type_id'];
        $param['type'] = (!isset($request['type'])) ? '' : $request['type'];
        $param['datetype'] = (!isset($request['datetype'])) ? 'year' : $request['datetype'];
        $param['export'] = isset($request['export']) ? $request['export'] : FALSE;

        $this->__is_ajax = true;

        $list = Lead::leadTypeUserReportFollowUp($param);
        $this->__is_paginate = false;
        $this->__collection = false;

        if (isset($request['export']) && $request['export'] == true) {
            $ignoreCols = [];
            $columns = ['S.No', 'Status'];
            $columns = array_merge($columns, $list['months']);
            return $this->export($columns, $list['export'], 'followup_lead_report.csv', $ignoreCols, 1, 1);
        }
        return $this->__sendResponse('UserLeadStatus', $list, 200, 'User lead status list retrieved successfully.', true);
    }

    public function userReportKnock(Request $request) {
        $param['company_id'] = $request['company_id'];
        $param['time_slot'] = (!isset($request['time_slot'])) ? '' : $request['time_slot'];
        $param['month'] = (!isset($request['month'])) ? '' : $request['month'];
        $param['start_date'] = (!isset($request['start_date'])) ? '' : $request['start_date'];
        $param['end_date'] = (!isset($request['end_date'])) ? '' : $request['end_date'];
        $param['target_user_id'] = (!isset($request['target_user_id'])) ? '' : $request['target_user_id'];
        $param['status_id'] = (!isset($request['status_id'])) ? '' : $request['status_id'];
        $param['type_id'] = (!isset($request['type_id'])) ? '' : $request['type_id'];
        $param['type'] = (!isset($request['type'])) ? '' : $request['type'];
        $param['datetype'] = (!isset($request['datetype'])) ? 'year' : $request['datetype'];
        $param['export'] = isset($request['export']) ? $request['export'] : FALSE;

        $this->__is_ajax = true;

        $list = Lead::userKnockReports($param);
        $this->__is_paginate = false;
        $this->__collection = false;

        if (isset($request['export']) && $request['export'] == true) {
            $ignoreCols = [];
            $columns = ['S.No', 'Status'];
            $columns = array_merge($columns, $list['months']);
            return $this->export($columns, $list['export'], 'user_report_knock.csv', $ignoreCols, 1, 1);
        }
        return $this->__sendResponse('UserLeadStatus', $list, 200, 'User lead status list retrieved successfully.', true);
    }

    public function userReportKnockWithColour(Request $request) {
        $param['company_id'] = $request['company_id'];
        $param['time_slot'] = (!isset($request['time_slot'])) ? '' : $request['time_slot'];
        $param['month'] = (!isset($request['month'])) ? '' : $request['month'];
        $param['start_date'] = (!isset($request['start_date'])) ? '' : $request['start_date'];
        $param['end_date'] = (!isset($request['end_date'])) ? '' : $request['end_date'];
        $param['target_user_id'] = (!isset($request['target_user_id'])) ? '' : $request['target_user_id'];
        $param['status_id'] = (!isset($request['status_id'])) ? '' : $request['status_id'];
        $param['type_id'] = (!isset($request['type_id'])) ? '' : $request['type_id'];
        $param['type'] = (!isset($request['type'])) ? '' : $request['type'];
        $param['datetype'] = (!isset($request['datetype'])) ? 'year' : $request['datetype'];
        $param['export'] = isset($request['export']) ? $request['export'] : FALSE;
        $this->__is_ajax = true;

        $list = Lead::userKnockReportsColour($param);

        if ($list === false) {
            $errors['error_message'] = "You can't select more that 8 days.";
            return $this->__sendError('Validation Error.', $errors);
        }

        $this->__is_paginate = false;
        $this->__collection = false;

        if (isset($request['export']) && $request['export'] == true) {
            $ignoreCols = [];
            $columns = ['S.No', 'Status'];
            $columns = array_merge($columns, $list['months']);
            return $this->export($columns, $list['export'], 'user_report_knock_with_all_status.csv', $ignoreCols, 1, 1);
        }

        if (isset($request['export_new']) && $request['export_new'] == true) {
            $ignoreCols = [];
            $columns = ['S.No', 'Status'];
            $columns = array_merge($columns, $list['months']);
            return $this->export($columns, $list['export_new'], 'user_report_knock_with_ans_and_noans.csv', $ignoreCols, 1, 1);
        }

        return $this->__sendResponse('UserLeadStatus', $list, 200, 'User lead status list retrieved successfully.', true);
    }

    public function userReportKnockNotContracted(Request $request) {
        $param['company_id'] = $request['company_id'];
        $param['time_slot'] = (!isset($request['time_slot'])) ? '' : $request['time_slot'];
        $param['month'] = (!isset($request['month'])) ? '' : $request['month'];
        $param['start_date'] = (!isset($request['start_date'])) ? '' : $request['start_date'];
        $param['end_date'] = (!isset($request['end_date'])) ? '' : $request['end_date'];
        $param['target_user_id'] = (!isset($request['target_user_id'])) ? '' : $request['target_user_id'];
        $param['status_id'] = (!isset($request['status_id'])) ? '' : $request['status_id'];
        $param['type_id'] = (!isset($request['type_id'])) ? '' : $request['type_id'];
        $param['type_id'] = 77;
        $param['type'] = (!isset($request['type'])) ? '' : $request['type'];
        $param['datetype'] = (!isset($request['datetype'])) ? 'year' : $request['datetype'];
        $param['export'] = isset($request['export']) ? $request['export'] : FALSE;
        $this->__is_ajax = true;
        $list = Lead::userReportKnockNotContracted($param);
        $this->__is_paginate = false;
        $this->__collection = false;

        if (isset($request['export']) && $request['export'] == true) {
            $ignoreCols = [];
            $columns = ['S.No', 'Status'];
            $columns = array_merge($columns, $list['months']);
            return $this->export($columns, $list['export'], 'user_report_knock_not_contracted.csv', $ignoreCols, 1, 1);
        }
        return $this->__sendResponse('UserLeadStatus', $list, 200, 'User lead status list retrieved successfully.', true);
    }

    public function userReportKnockDayReport(Request $request) {
        $param['company_id'] = $request['company_id'];
        $param['time_slot'] = (!isset($request['time_slot'])) ? '' : $request['time_slot'];
        $param['month'] = (!isset($request['month'])) ? '' : $request['month'];
        $param['start_date'] = (!isset($request['start_date'])) ? '' : $request['start_date'];
        $param['end_date'] = (!isset($request['end_date'])) ? '' : $request['end_date'];
        $param['target_user_id'] = (!isset($request['target_user_id'])) ? '' : $request['target_user_id'];
        $param['status_id'] = (!isset($request['status_id'])) ? '' : $request['status_id'];
        $param['type_id'] = (!isset($request['type_id'])) ? '' : $request['type_id'];
        $param['type_id'] = 77;
        $param['type'] = (!isset($request['type'])) ? '' : $request['type'];
        $param['datetype'] = (!isset($request['datetype'])) ? 'year' : $request['datetype'];
        $param['export'] = isset($request['export']) ? $request['export'] : FALSE;

        $this->__is_ajax = true;

        $list = Lead::userReportKnockDayReport($param);
        $this->__is_paginate = false;
        $this->__collection = false;

        if (isset($request['export']) && $request['export'] == true) {
            $ignoreCols = [];
            $columns = ['S.No', 'Status'];
            $columns = array_merge($columns, $list['months']);
            return $this->export($columns, $list['export'], 'user_report_knock_not_contracted.csv', $ignoreCols, 1, 1);
        }
        return $this->__sendResponse('UserLeadStatus', $list, 200, 'User lead status list retrieved successfully.', true);
    }

    public function leadStatusUserReportDashboardKnocksStatistics(Request $request) {
        $param['company_id'] = $request['company_id'];
        $param['time_slot'] = (!isset($request['time_slot'])) ? '' : $request['time_slot'];
        $param['month'] = (!isset($request['month'])) ? '' : $request['month'];
        $param['start_date'] = (!isset($request['start_date'])) ? '' : $request['start_date'];
        $param['end_date'] = (!isset($request['end_date'])) ? '' : $request['end_date'];
        $param['target_user_id'] = (!isset($request['target_user_id'])) ? '' : $request['target_user_id'];
        $param['status_id'] = (!isset($request['status_id'])) ? '' : $request['status_id'];
        $param['type_id'] = (!isset($request['type_id'])) ? '' : $request['type_id'];
        $param['type'] = (!isset($request['type'])) ? '' : $request['type'];
        $param['datetype'] = (!isset($request['datetype'])) ? 'year' : $request['datetype'];
        $param['export'] = isset($request['export']) ? $request['export'] : FALSE;

        $this->__is_ajax = true;

        $list = Lead::leadTypeUserReportDashboardKnocksStatistics($param);
        $this->__is_paginate = false;
        $this->__collection = false;

        if (isset($request['export']) && $request['export'] == true) {
            $ignoreCols = [];
            $columns = ['S.No', 'Status'];
            $columns = array_merge($columns, $list['months']);
            return $this->export($columns, $list['export'], 'followup_lead_report.csv', $ignoreCols, 1, 1);
        }
        return $this->__sendResponse('UserLeadStatus', $list, 200, 'User lead status list retrieved successfully.', true);
    }

    public function leadStatusUserReportCurrentNew(Request $request) {
        $param['company_id'] = $request['company_id'];
        $param['time_slot'] = (!isset($request['time_slot'])) ? '' : $request['time_slot'];
        $param['month'] = (!isset($request['month'])) ? '' : $request['month'];
        $param['start_date'] = (!isset($request['start_date'])) ? '' : $request['start_date'];
        $param['end_date'] = (!isset($request['end_date'])) ? '' : $request['end_date'];
        $param['target_user_id'] = (!isset($request['target_user_id'])) ? '' : $request['target_user_id'];
        $param['status_id'] = (!isset($request['status_id'])) ? '' : $request['status_id'];
        $param['type_id'] = (!isset($request['type_id'])) ? '' : $request['type_id'];
        $param['type'] = (!isset($request['type'])) ? '' : $request['type'];
        $param['datetype'] = (!isset($request['datetype'])) ? 'year' : $request['datetype'];
        $param['export'] = isset($request['export']) ? $request['export'] : FALSE;
        $this->__is_ajax = true;
        $list = Lead::leadTypeUserReportCurrentNew($param);
        $this->__is_paginate = false;
        $this->__collection = false;
        if (isset($request['export']) && $request['export'] == true) {
            $ignoreCols = [];
            $columns = ['S.No', 'Status'];
            $columns = array_merge($columns, $list['months']);
            return $this->export($columns, $list['export'], 'lead_type_report.csv', $ignoreCols, 1, 1);
        }
        return $this->__sendResponse('UserLeadStatus', $list, 200, 'User lead status list retrieved successfully.', true);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function leadStatsReport(Request $request) {
        $time_slot_map['today'] = 'INTERVAL 1 MONTH';
        $time_slot_map['yesterday'] = 'INTERVAL 1 MONTH';
        $time_slot_map['week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_month'] = 'INTERVAL 1 MONTH';
        //$time_slot_map['bi_month'] = 'INTERVAL 15 DAY';
        //$time_slot_map['bi_year'] = 'INTERVAL 6 MONTH';
        $time_slot_map['year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['last_year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['all_time'] = '';

        $param['is_web'] = (strtolower($this->call_mode) == 'api') ? 0 : 1;
        $default_time_slot = ($param['is_web']) ? 'year' : 'all_time';

        $param['company_id'] = $request['company_id'];
        $param['time_slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $time_slot_map[$request['time_slot']] : $time_slot_map['month'] : $time_slot_map['month'];
        $param['slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $request['time_slot'] : $default_time_slot : $default_time_slot;
        $param['user_id'] = isset($request['target_user_id']) ? trim($request['target_user_id'], ' ,') : '';
        $param['status_id'] = isset($request['status_id']) ? trim($request['status_id'], ' ,') : '';
        $param['lead_type_id'] = isset($request['lead_type_id']) ? trim($request['lead_type_id'], ' ,') : '';
        $param['lead_type_id'] = isset($request['type_id']) ? trim($request['type_id'], ' ,') : $param['lead_type_id'];


        $this->__is_ajax = true;
        $list = Lead::getStatsReport($param);

        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('UserCommission', $list, 200, 'User commission list retrieved successfully.');
    }

    public function leadStatusStatsReport(Request $request) {
        $time_slot_map['today'] = 'INTERVAL 1 MONTH';
        $time_slot_map['yesterday'] = 'INTERVAL 1 MONTH';
        $time_slot_map['week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_month'] = 'INTERVAL 1 MONTH';
        //$time_slot_map['bi_month'] = 'INTERVAL 15 DAY';
        //$time_slot_map['bi_year'] = 'INTERVAL 6 MONTH';
        $time_slot_map['year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['last_year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['all_time'] = '';

        $param['is_web'] = (strtolower($this->call_mode) == 'api') ? 0 : 1;
        $default_time_slot = ($param['is_web']) ? 'year' : 'all_time';

        $param['company_id'] = $request['company_id'];
        $param['time_slot'] = $request['time_slot'];
        $param['slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $request['time_slot'] : $default_time_slot : $default_time_slot;
        $param['user_id'] = isset($request['target_user_id']) ? trim($request['target_user_id'], ' ,') : '';
        $param['status_id'] = isset($request['status_id']) ? trim($request['status_id'], ' ,') : '';
        $param['lead_type_id'] = isset($request['lead_type_id']) ? trim($request['lead_type_id'], ' ,') : '';
        $param['lead_type_id'] = isset($request['type_id']) ? trim($request['type_id'], ' ,') : $param['lead_type_id'];
        $param['type'] = isset($request['type']) ? $request['type'] : 'percentage';


        if ($param['slot'] == 'all_time') {
            $param['slot'] = 'last_year';
        }
        $this->__is_ajax = true;
        $list = Lead::getStatusStatsReport($param);
        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('UserCommission', $list, 200, 'User commission list retrieved successfully.');
    }

    public function leadTypesStatsReport(Request $request) {
        $time_slot_map['today'] = 'INTERVAL 1 MONTH';
        $time_slot_map['yesterday'] = 'INTERVAL 1 MONTH';
        $time_slot_map['week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_month'] = 'INTERVAL 1 MONTH';
        //$time_slot_map['bi_month'] = 'INTERVAL 15 DAY';
        //$time_slot_map['bi_year'] = 'INTERVAL 6 MONTH';
        $time_slot_map['year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['last_year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['all_time'] = '';

        $param['is_web'] = (strtolower($this->call_mode) == 'api') ? 0 : 1;
        $default_time_slot = ($param['is_web']) ? 'year' : 'all_time';

        $param['company_id'] = $request['company_id'];
        $param['time_slot'] = $request['time_slot'];

        $param['start_date'] = $request['start_date'];
        $param['end_date'] = $request['end_date'];

        $param['slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $request['time_slot'] : $default_time_slot : $default_time_slot;
        $param['user_id'] = isset($request['target_user_id']) ? trim($request['target_user_id'], ' ,') : '';
        $param['status_id'] = isset($request['status_id']) ? trim($request['status_id'], ' ,') : '';
        $param['lead_type_id'] = isset($request['lead_type_id']) ? trim($request['lead_type_id'], ' ,') : '';
        $param['lead_type_id'] = isset($request['type_id']) ? trim($request['type_id'], ' ,') : $param['lead_type_id'];
        $param['type'] = isset($request['type']) ? $request['type'] : 'percentage';


        if ($param['slot'] == 'all_time') {
            $param['slot'] = 'last_year';
        }
        $this->__is_ajax = true;
        $list = Lead::getTypesStatsReport($param);
        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('UserCommission', $list, 200, 'User commission list retrieved successfully.');
    }

    public function leadTypesStatsReportPie(Request $request) {
        $time_slot_map['today'] = 'INTERVAL 1 MONTH';
        $time_slot_map['yesterday'] = 'INTERVAL 1 MONTH';
        $time_slot_map['week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_month'] = 'INTERVAL 1 MONTH';
        //$time_slot_map['bi_month'] = 'INTERVAL 15 DAY';
        //$time_slot_map['bi_year'] = 'INTERVAL 6 MONTH';
        $time_slot_map['year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['last_year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['all_time'] = '';

        $param['is_web'] = (strtolower($this->call_mode) == 'api') ? 0 : 1;
        $default_time_slot = ($param['is_web']) ? 'year' : 'all_time';

        $param['company_id'] = $request['company_id'];
        $param['time_slot'] = $request['time_slot'];

        $param['start_date'] = $request['start_date'];
        $param['end_date'] = $request['end_date'];

        $param['slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $request['time_slot'] : $default_time_slot : $default_time_slot;
        $param['user_id'] = isset($request['target_user_id']) ? trim($request['target_user_id'], ' ,') : '';
        $param['status_id'] = isset($request['status_id']) ? trim($request['status_id'], ' ,') : '';
        $param['lead_type_id'] = isset($request['lead_type_id']) ? trim($request['lead_type_id'], ' ,') : '';
        $param['lead_type_id'] = isset($request['type_id']) ? trim($request['type_id'], ' ,') : $param['lead_type_id'];
        $param['type'] = isset($request['type']) ? $request['type'] : 'percentage';


        if ($param['slot'] == 'all_time') {
            $param['slot'] = 'last_year';
        }
        $this->__is_ajax = true;
        $list = Lead::leadTypesStatsReportPie($param);
        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('UserCommission', $list, 200, 'User commission list retrieved successfully.');
    }

    public function leadTypesStatsReportFollowupPie(Request $request) {
        $time_slot_map['today'] = 'INTERVAL 1 MONTH';
        $time_slot_map['yesterday'] = 'INTERVAL 1 MONTH';
        $time_slot_map['week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_month'] = 'INTERVAL 1 MONTH';
        //$time_slot_map['bi_month'] = 'INTERVAL 15 DAY';
        //$time_slot_map['bi_year'] = 'INTERVAL 6 MONTH';
        $time_slot_map['year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['last_year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['all_time'] = '';

        $param['is_web'] = (strtolower($this->call_mode) == 'api') ? 0 : 1;
        $default_time_slot = ($param['is_web']) ? 'year' : 'all_time';

        $param['company_id'] = $request['company_id'];
        $param['time_slot'] = $request['time_slot'];

        $param['start_date'] = $request['start_date'];
        $param['end_date'] = $request['end_date'];

        $param['slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $request['time_slot'] : $default_time_slot : $default_time_slot;
        $param['user_id'] = isset($request['target_user_id']) ? trim($request['target_user_id'], ' ,') : '';
        $param['status_id'] = isset($request['status_id']) ? trim($request['status_id'], ' ,') : '';
        $param['lead_type_id'] = isset($request['lead_type_id']) ? trim($request['lead_type_id'], ' ,') : '';
        $param['lead_type_id'] = isset($request['type_id']) ? trim($request['type_id'], ' ,') : $param['lead_type_id'];
        $param['type'] = isset($request['type']) ? $request['type'] : 'percentage';


        if ($param['slot'] == 'all_time') {
            $param['slot'] = 'last_year';
        }
        $this->__is_ajax = true;
        $list = Lead::leadTypesStatsReportFollowUpPie($param);
        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('UserCommission', $list, 200, 'User commission list retrieved successfully.');
    }

    public function KnockReportPie(Request $request) {
        $time_slot_map['today'] = 'INTERVAL 1 MONTH';
        $time_slot_map['yesterday'] = 'INTERVAL 1 MONTH';
        $time_slot_map['week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_month'] = 'INTERVAL 1 MONTH';
        //$time_slot_map['bi_month'] = 'INTERVAL 15 DAY';
        //$time_slot_map['bi_year'] = 'INTERVAL 6 MONTH';
        $time_slot_map['year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['last_year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['all_time'] = '';

        $param['is_web'] = (strtolower($this->call_mode) == 'api') ? 0 : 1;
        $default_time_slot = ($param['is_web']) ? 'year' : 'all_time';

        $param['company_id'] = $request['company_id'];
        $param['time_slot'] = $request['time_slot'];

        $param['start_date'] = $request['start_date'];
        $param['end_date'] = $request['end_date'];

        $param['slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $request['time_slot'] : $default_time_slot : $default_time_slot;
        $param['user_id'] = isset($request['target_user_id']) ? trim($request['target_user_id'], ' ,') : '';
        $param['status_id'] = isset($request['status_id']) ? trim($request['status_id'], ' ,') : '';
        $param['lead_type_id'] = isset($request['lead_type_id']) ? trim($request['lead_type_id'], ' ,') : '';
        $param['lead_type_id'] = isset($request['type_id']) ? trim($request['type_id'], ' ,') : $param['lead_type_id'];
        $param['type'] = isset($request['type']) ? $request['type'] : 'percentage';


        if ($param['slot'] == 'all_time') {
            $param['slot'] = 'last_year';
        }
        $this->__is_ajax = true;
        $list = Lead::getKnockReportPie($param);
        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('UserCommission', $list, 200, 'User commission list retrieved successfully.');
    }

    public function leadTypesStatsReportCurrentPie(Request $request) {
        $time_slot_map['today'] = 'INTERVAL 1 MONTH';
        $time_slot_map['yesterday'] = 'INTERVAL 1 MONTH';
        $time_slot_map['week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_month'] = 'INTERVAL 1 MONTH';
        //$time_slot_map['bi_month'] = 'INTERVAL 15 DAY';
        //$time_slot_map['bi_year'] = 'INTERVAL 6 MONTH';
        $time_slot_map['year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['last_year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['all_time'] = '';

        $param['is_web'] = (strtolower($this->call_mode) == 'api') ? 0 : 1;
        $default_time_slot = ($param['is_web']) ? 'year' : 'all_time';

        $param['company_id'] = $request['company_id'];
        $param['time_slot'] = $request['time_slot'];

        $param['start_date'] = $request['start_date'];
        $param['end_date'] = $request['end_date'];

        $param['slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $request['time_slot'] : $default_time_slot : $default_time_slot;
        $param['user_id'] = isset($request['target_user_id']) ? trim($request['target_user_id'], ' ,') : '';
        $param['status_id'] = isset($request['status_id']) ? trim($request['status_id'], ' ,') : '';
        $param['lead_type_id'] = isset($request['lead_type_id']) ? trim($request['lead_type_id'], ' ,') : '';
        $param['lead_type_id'] = isset($request['type_id']) ? trim($request['type_id'], ' ,') : $param['lead_type_id'];
        $param['type'] = isset($request['type']) ? $request['type'] : 'percentage';


        if ($param['slot'] == 'all_time') {
            $param['slot'] = 'last_year';
        }
        $this->__is_ajax = true;
        $list = Lead::leadTypesStatsReportCurrentPie($param);
        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('UserCommission', $list, 200, 'User commission list retrieved successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id) {
        $param_rules['id'] = 'required|exists:lead_detail,id,company_id,' . $request['company_id'];

        $this->__is_ajax = true;
        $response = $this->__validateRequestParams(['id' => $id], $param_rules);

        if ($this->__is_error == true)
            return $response;

        $obj_lead = Lead::find($id);
        Status::decrementLeadCount($obj_lead->status_id);
        Lead::destroy($id);
        UserLeadAppointment::destroyByLeadId($id);

        $this->__is_paginate = false;
        $this->__is_collection = false;
        $this->__collection = false;
        return $this->__sendResponse('Lead', [], 200, 'Lead has been deleted successfully.');
    }

    public function templateList(Request $request) {
        $param_rules['user_id'] = 'required';

        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);


        if ($this->__is_error == true)
            return $response;

        $param['tenant_id'] = $request['company_id'];
        $response = Lead::getTemplate($request['company_id']);
        //$response = Type::getList($request['company_id']);


        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('Template', $response, 200, 'Your lead template has been retrieved successfully.');
    }

    public function deleteTemplate(Request $request) {
        $param_rules['template_id'] = 'required';
        $param_rules['user_id'] = 'required';
        $param_rules['company_id'] = 'required';

        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);


        if ($this->__is_error == true)
            return $response;

        $param['tenant_id'] = $request['company_id'];
        $param['template_id'] = $request['template_id'];
        Lead::deleteTemplate($param);

        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('Template', [], 200, 'Your lead template has been retrieved successfully.');
    }

    public function templateFieldList(Request $request) {
        $param_rules['user_id'] = 'required';
        $param_rules['template_id'] = 'required';

        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);


        if ($this->__is_error == true)
            return $response;

        $params['is_all'] = (isset($request['is_all'])) ? $request['is_all'] : '';
        $params['company_id'] = $request['company_id'];
        $response = Lead::getFieldsTemplateById($request['template_id'], $params);
        $ignore_columns = [];

        if (isset($request['is_all']) && $request['is_all'] == 2)
            $ignore_columns = Config::get('constants.TEMPLATE_SHOW_LEAD_IGNORE_COLUMNS');
        $response_data = [];
        foreach ($response as $key => $row) {
            $response[$key]->key_map = $response[$key]->key;
            if (in_array($row->field, $ignore_columns)) {
                unset($response[$key]);
                continue;
            }

            if ($row->field == 'lead_name' || $row->field == 'title') {
                $response[$key]->key_map = 'title';
                $response[$key]->key = Config::get('constants.LEAD_TITLE_DISPLAY');
            }
            if (empty($row->key)) {
                $response[$key]->key = $row->field;
                $response[$key]->key_map = $row->field;
            }
            if (empty($row->index_map)) {
                $response[$key]->index_map = (ctype_digit($row->field) ? '' : $row->field);
            }
            $response_data[] = $response[$key];
        }

        //$response['orderable_columns'] = Config::get('constants.LEAD_DEFAULT_COLUMNS');
        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('Template', $response_data, 200, 'Your lead template has been retrieved successfully.');
    }

    public function defaultFieldList(Request $request) {
        $param_rules['user_id'] = 'required';

        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);


        if ($this->__is_error == true)
            return $response;

        $params['is_all'] = (isset($request['is_all'])) ? $request['is_all'] : '';
        $params['tenant_id'] = $request['company_id'];
        $response = Lead::getFieldsDefault($params);

        // info($response);

        foreach ($response as $key => $row) {
            // print_r($row->key);
            if ($row->key == 'title' || $row->key == 'lead_name') {
                $response[$key]->key = Config::get('constants.LEAD_TITLE_DISPLAY');
            }
            //exit;
        }//exit;
        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('Template', $response, 200, 'Your lead default has been retrieved successfully.');
    }

    public function templateShow(Request $request, $id) {
        $param_rules['id'] = 'required|exists:tenant_template,id';
        $this->__is_ajax = true;
        $response = $this->__validateRequestParams(['id' => $id], $param_rules);

        if ($this->__is_error == true)
            return $response;

        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('Lead', Lead::getTemplateDetailById($request['company_id'], $id), 200, 'Lead template has been retrieved successfully.');
    }

    public function templateDestroy(Request $request, $id) {
        $param_rules['id'] = 'required|exists:tenant_template,id,tenant_id,' . $request['company_id'];

        $this->__is_ajax = true;
        $response = $this->__validateRequestParams(['id' => $id], $param_rules);

        if ($this->__is_error == true)
            return $response;

        TenantTemplate::destroy($id);

        TemplateFields::where('template_id', '=', $id)->delete();

        $this->__is_paginate = false;
        $this->__is_collection = false;
        $this->__collection = false;
        return $this->__sendResponse('Tempalte', [], 200, 'Lead has been deleted successfully.');
    }

    public function templateUpdate(Request $request, $id) {
        $request['id'] = $id;
        $param_rules['id'] = 'required|exists:tenant_template,id,tenant_id,' . $request['company_id'];
        $param_rules['template_title'] = 'required';

        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);

        if ($this->__is_error == true)
            return $response;


        $obj_lead = TenantTemplate::find($id);
        $obj_lead->title = $request['template_title'];
        $obj_lead->save();

        $this->__is_paginate = false;
        $this->__collection = false;
        return $this->__sendResponse('Lead', Lead::getTemplateDetailById($request['company_id'], $id), 200, 'Lead template has been retrieved successfully.');
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function tenantCustomField(Request $request) {

        $tenantCustomField = TenantCustomField::find($request->id);

        $templateFields = TemplateFields::where('field', $tenantCustomField->id)->where('index_map', $tenantCustomField->key)->first();

        $tenantCustomField->is_active = $request->value;

        $tenantCustomField->save();

        // info($templateFields);

        if (!is_null($templateFields)) {
            TemplateFields::where('field', $tenantCustomField->id)->where('index_map', $tenantCustomField->key)->update(['is_active' => $request->value]);
        }

        return response()->json(['success' => '1']);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function editableField(Request $request) {

        $tenantCustomField = TenantCustomField::find($request->pk);

        $tenantCustomField->key_mask = $request->value;

        $tenantCustomField->save();

        return response()->json(['success']);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function editableFieldHistory(Request $request) {
        $leadHistoryField = LeadHistory::find($request->pk);

        if (!empty($request->value)) {
            $date = dynamicDateFormat($request->value, 1);

            $validator = Validator::make($request->all(), [
                        'value' => 'required|before:' . now(),
                            ], [
                        'value.before' => 'The history date must be a date before ' . dynamicDateFormat(now(), 3) . '.'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()]);
            }


            $leadHistoryField->created_at = $date;
            $leadHistoryField->save();

            $userLeadKnocks = UserLeadKnocks::where('lead_history_id', $leadHistoryField->id)->first();

            if (!is_null($userLeadKnocks)) {
                $userLeadKnocks->created_at = $date;
                $userLeadKnocks->save();
            }
        }

        return response()->json(['success']);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function isExpriredUpdate(Request $request) {
        $lead = Lead::find($request->lead_id);

        $lead->is_expired = $request->value;

        $lead->save();

        return response()->json(['success' => 1]);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function isfollowup(Request $request) {
        $lead = Lead::find($request->lead_id);

        $lead->is_follow_up = $request->value;

        $lead->save();

        $followingLead = FollowingLead::where('lead_id', $lead->id)->first();

        if (!isset($followingLead->id)) {
            $input['lead_id'] = $lead->id;
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

            if (!is_null($status)) {
                $followStatus = FollowStatus::where('title', $status->title)->first();
                if (!is_null($followStatus)) {
                    $input['follow_status'] = $followStatus->id;

                    LeadHistory::create([
                        'lead_id' => $lead->id,
                        'title' => $followStatus->title . ' status updated where lead move in to Follow Up Lead Management.',
                        'assign_id' => $request['user_id'],
                        'status_id' => 0,
                        'followup_status_id' => 0
                    ]);
                }
            }

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

            $leadQuery = LeadQuery::where('query_id', 8)->where('lead_id', $lead->id)->latest()->first();

            $input['investor_notes'] = $leadQuery->response ?? null;

            $leadCustomField = LeadCustomField::where('lead_id', $lead->id)->get();

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

        $obj_lead_history = LeadHistory::create([
                    'lead_id' => $lead->id,
                    'title' => 'Lead Moved into Follow Up Lead Management.',
                    'assign_id' => $request['user_id'],
                    'status_id' => 0
        ]);


        return response()->json(['success' => 1]);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function isleadup(Request $request) {
        $lead = FollowingLead::find($request->lead_id);

        $lead->is_lead_up = $request->value;

        $lead->save();

        $followingLead = Lead::where('id', $lead->lead_id)->first();

        if (isset($followingLead->id)) {

            $status = Status::where('title', '=', 'MVDFLUP')->first();
            if (isset($status->id)) {
                $followingLead->status_id = $status->id;

                if (isset($status->id)) {
                    $obj_lead_history = LeadHistory::create([
                                'lead_id' => $lead->lead_id,
                                'title' => $status->title . ' status updated from Follow Up Lead Management.',
                                'assign_id' => $request['user_id'],
                                'status_id' => 0
                    ]);
                }
            }

            $followingLead->is_follow_up = 0;
            $followingLead->auction = $lead->auction;
            $followingLead->is_expired = 1;
            $followingLead->assignee_id = 4;
            $followingLead->admin_notes = $lead->admin_notes;
            $followingLead->address = $lead->formatted_address;
            $followingLead->title = $lead->title;

            if ($lead->investor_notes != '') {
                $update_data = [];
                $update_data['response'] = $lead->investor_notes;
                $lead_queary_data_notes = LeadQuery::where('lead_id', '=', $lead->lead_id)
                        ->where('query_id', '=', 8)
                        ->orderBy('id', 'desc')
                        ->update($update_data);
            }


            $followingLead->save();
        }

        return response()->json(['success' => 1]);
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function commissionReportExport(Request $request) {


        $userCommission = UserCommission::select('user_commission.id', 'user_commission.lead_id', 'user_commission.user_id', 'user_commission.commission', 'user_commission.commission_event', 'user_commission.created_at', 'lead_detail.type_id', 'lead_detail.title', 'lead_detail.address', 'lead_detail.city', 'lead_detail.state', 'lead_detail.zip_code');


        if ($request->has('target_user_id') && !empty($request->target_user_id)) {
            $usersList = explode(',', $request->target_user_id);
            $userCommission = $userCommission->whereIn('user_id', $usersList);
        }

        $userCommission = $userCommission->join('lead_detail', 'user_commission.lead_id', 'lead_detail.id');

        if ($request->has('type_id') && !empty($request->type_id)) {
            $typeList = explode(',', $request->type_id);
            $userCommission = $userCommission->whereIn('type_id', $typeList);
        }

        if ($request->has('time_slot') && !empty($request->time_slot)) {
            switch ($request->time_slot) {
                case 'today':
                    $userCommission = $userCommission->whereBetween('user_commission.created_at', [dateTimezoneChangeNew(date('d.m.Y') . ' 00:00:00'), dateTimezoneChangeNew(Carbon::now())]);
                    break;

                case 'yesterday':
                    $userCommission = $userCommission->whereBetween('user_commission.created_at', [dateTimezoneChangeNew(date('d.m.Y', strtotime("-1 days")) . ' 00:00:00'), dateTimezoneChangeNew(date('d.m.Y', strtotime("-1 days")) . ' 23:59:00')]);
                    break;

                case 'week':
                    $userCommission = $userCommission->whereBetween('user_commission.created_at', [dateTimezoneChangeNew(date('Y-m-d', strtotime(date('Y-m-d') . ' -6 day')) . ' 00:00:00'), dateTimezoneChangeNew(date('Y-m-d') . ' 23:59:59')]);
                    break;

                case 'last_week':
                    $userCommission = $userCommission->whereBetween('user_commission.created_at', [dateTimezoneChangeNew(Carbon::now()->subWeek()->startOfWeek()), dateTimezoneChangeNew(Carbon::now()->subWeek()->endOfWeek())]);
                    break;

                case 'month':
                    $userCommission = $userCommission->whereBetween('user_commission.created_at', [dateTimezoneChangeNew(Carbon::now()->startOfMonth()), dateTimezoneChangeNew(Carbon::now())]);
                    break;

                case 'last_month':
                    $userCommission = $userCommission->whereBetween('user_commission.created_at', [dateTimezoneChangeNew(Carbon::now()->subMonth()->startOfMonth()), dateTimezoneChangeNew(Carbon::now()->subMonth()->endOfMonth())]);
                    break;

                case 'year':
                    $userCommission = $userCommission->whereBetween('user_commission.created_at', [dateTimezoneChangeNew(Carbon::now()->startOfYear()), dateTimezoneChangeNew(Carbon::now())]);
                    break;

                case 'last_year':
                    $userCommission = $userCommission->whereBetween('user_commission.created_at', [dateTimezoneChangeNew(Carbon::now()->subYear()->startOfYear()), dateTimezoneChangeNew(Carbon::now()->subYear()->endOfYear())]);
                    break;

                default:
                    // code...
                    break;
            }
        }

        $userCommissions = $userCommission->get();

        $userCommissionList = [];

        if (!empty($userCommissions)) {
            foreach ($userCommissions as $key => $userCommission) {
                $userCommissionList[$key]['name'] = $userCommission->user->first_name . ' ' . $userCommission->user->last_name;
                $userCommissionList[$key]['commission'] = '$' . number_format($userCommission->commission, '2');
                $userCommissionList[$key]['commission_event'] = $userCommission->commission_event;
                $userCommissionList[$key]['title'] = $userCommission->title;
                $userCommissionList[$key]['address'] = $userCommission->address . ', ' . $userCommission->city . ', ' . $userCommission->state . ', ' . $userCommission->zip_code;
                $userCommissionList[$key]['created_at'] = dynamicDateFormat(dateTimezoneChange($userCommission->created_at), 3);
            }
        }

        return Excel::download(new UserCommissionReportExport($userCommissionList), 'Users-Commission-Report-' . Carbon::now() . '.csv');
    }

    public function v2IndexMap(Request $request) {
        $param_rules['search'] = 'sometimes';
        $time_slot_map['today'] = 'INTERVAL 1 MONTH';
        $time_slot_map['yesterday'] = 'INTERVAL 1 MONTH';
        $time_slot_map['week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['last_year'] = 'INTERVAL 1 YEAR';
        $param['search'] = isset($request['search']) ? $request['search'] : '';
        $param['latitude'] = isset($request['latitude']) ? $request['latitude'] : '';
        $param['longitude'] = isset($request['longitude']) ? $request['longitude'] : '';
        $param['lead_type_id'] = isset($request['lead_type_id']) ? $request['lead_type_id'] : '';
        $param['user_ids'] = isset($request['target_user_id']) ? trim($request['target_user_id']) : '';
        $param['status_ids'] = isset($request['status_id']) ? trim($request['status_id']) : '';
        $param['radius'] = isset($request['radius']) ? $request['radius'] : 500;
        $param['time_slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $time_slot_map[$request['time_slot']] : '' : '';
        $param['slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $request['time_slot'] : '' : '';
        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);
        if ($this->__is_error == true)
            return $response;

        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];
        $response = Lead::getListMapIndexV2($param);
        return $this->__sendResponse('LeadMap', $response, 200, 'Lead list retrieved successfully.');
    }

    public function v2Index(Request $request) {
        $param_rules['search'] = 'sometimes';
        $time_slot_map['today'] = 'INTERVAL 1 MONTH';
        $time_slot_map['yesterday'] = 'INTERVAL 1 MONTH';
        $time_slot_map['week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_week'] = 'INTERVAL 1 MONTH';
        $time_slot_map['month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['last_month'] = 'INTERVAL 1 MONTH';
        $time_slot_map['year'] = 'INTERVAL 1 YEAR';
        $time_slot_map['last_year'] = 'INTERVAL 1 YEAR';
        $param['search'] = isset($request['search']) ? $request['search'] : '';
        $param['latitude'] = isset($request['latitude']) ? $request['latitude'] : '';
        $param['longitude'] = isset($request['longitude']) ? $request['longitude'] : '';
        $param['lead_type_id'] = isset($request['lead_type_id']) ? $request['lead_type_id'] : '';
        $param['user_ids'] = isset($request['target_user_id']) ? trim($request['target_user_id']) : '';
        $param['status_ids'] = isset($request['status_id']) ? trim($request['status_id']) : '';
        $param['radius'] = isset($request['radius']) ? $request['radius'] : 500;
        $param['time_slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $time_slot_map[$request['time_slot']] : '' : '';
        $param['slot'] = isset($request['time_slot']) ? (isset($time_slot_map[$request['time_slot']])) ? $request['time_slot'] : '' : '';
        $this->__is_ajax = true;
        $response = $this->__validateRequestParams($request->all(), $param_rules);
        if ($this->__is_error == true)
            return $response;

        $param['user_id'] = $request['user_id'];
        $param['company_id'] = $request['company_id'];
        $response = Lead::getListIndexV2($param);

        return $this->__sendResponse('LeadMap', $response, 200, 'Lead list retrieved successfully.');
    }

}
