<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Http\Request;

Route::get('/', function () {   
    return view('welcome');
}); 

Route::get('/tenant/login', 'UserController@loginIndex');
Route::get('/tenant', 'UserController@loginIndex');
Route::post('/tenant/login', 'UserController@loginWeb');

Route::get('appointment-remove', 'TestingController@AppRemove');

Route::get('app-address-update', 'TestingController@updateApptAddress');

Route::get('latlongupdate', 'TestingController@LatLongUpdate');

Route::get('knock_latlongupdate', 'TestingController@KNockLatLongUpdate');

Route::get('sendmail', 'TestingController@index');
Route::get('followup/address-update', 'TestingController@followupAddressUpdate');
Route::get('dev-2', 'TestingController@debug');
Route::get('mailchip/clear', 'TestingController@mailchipClear');
Route::get('migrate', 'TestingController@migrateRun');
Route::get('cache-clear', 'TestingController@cacheClear');
Route::get('/leads/wizard/is-followup/{id}', 'TestingController@isfollowup');

Route::get('/login', function (Request $request) {
    return view('tenant.login.index');
});
Route::get('tenant/login/forget_password', function () {
    return view('tenant.login.forget_password');
});

Route::post('user/forgot/password', 'UserController@forgotPassword');
Route::get('reset-password/{token}', 'UserController@showResetPasswordForm')->name('reset.password.get');
Route::post('reset-password','UserController@submitResetPasswordForm')->name('reset.password.post');

Route::get('privacypolicy', 'CMSController@privacyPolicy')->name('privacy.policy');

/*static routes*/
Route::group(['middleware' => ['login.auth'], 'prefix'=>'tenant','as'=>'tenant.'], function() {
    Route::get('user/export','UserController@userExportIndex')->name('user.export');
        
    Route::post('field/update/editable','LeadController@editableField')->name('field.update.editable');
    Route::get('deals/import','DealController@dealImportIndex')->name('deal.import');
    Route::get('deals/export','DealController@dealExportIndex')->name('deal.export');
    Route::post('deals/import','DealController@dealImport')->name('deal.import.file');
    Route::post('deal/update/editable','DealController@dealEditable')->name('field.edtable.deal');
    Route::post('field/update/editable/history','LeadController@editableFieldHistory')->name('field.update.editable.history');
    Route::post('marketing/email/editable','MarketingController@editableField')->name('marketing.update.editable');
    
    Route::get('campaign/user','CampaignuserController@Index')->name('campaign.user.index');
    Route::get('campaign/user/create','CampaignuserController@create')->name('campaign.user.create');
    Route::post('campaign/user/store','CampaignuserController@store')->name('campaign.user.store');
    Route::get('campaign/user/{campaign_user}/edit','CampaignuserController@edit')->name('campaign.user.edit');
    Route::post('campaign/user/{campaign_user}/update','CampaignuserController@update')->name('campaign.user.update');
    Route::delete('campaign/user/{campaign_user}/delete','CampaignuserController@delete')->name('campaign.user.delete');
    
    Route::get('campaign/tag','CampaignTagController@Index')->name('campaign.tag.index');
    Route::get('campaign/tag/create','CampaignTagController@create')->name('campaign.tag.create');
    Route::post('campaign/tag/store','CampaignTagController@store')->name('campaign.tag.store');
    Route::delete('campaign/tag/{campaign_tag}/delete','CampaignTagController@delete')->name('campaign.tag.delete');
    Route::get('campaign/tag/{campaign_tag}/edit','CampaignTagController@edit')->name('campaign.tag.edit');
    Route::post('campaign/tag/{campaign_tag}/update','CampaignTagController@update')->name('campaign.tag.update');
        
    Route::post('deallead/delete','DealController@indexListDelete')->name('deallead.list.delete');
    Route::post('deal/custom-field/update/editable','DealController@editableCustomField')->name('deal.custom.field.update.editable');
    Route::resource('deals','DealController'); 

    Route::get('commission', 'UserCommissionController@commissionView')->name('commission');
    Route::get('commission/create', 'UserCommissionController@indexView');
    Route::get('commission/import', 'UserCommissionController@importUserCommission')->name('commission.import');
    Route::post('commission/import/store', 'UserCommissionController@importUserCommissionStore')->name('commission.import.store');
    
    Route::get('commission_event', function () {
        return view('tenant.commission_event.comm_event_mgmt');
    });
    
    Route::get('commission_event/create', function () {
        return view('tenant.commission_event.add_comm_event');
    });
    
    Route::get('commission_event/edit/{id}', function () {
        return view('tenant.commission_event.edit_comm_event');
    });
    
    Route::get('template/add', function () {
        return view('tenant.template.add_template');
    });
    
    Route::get('template', function () {
        return view('tenant.template.template_list');
    });
    
    Route::get('lead-default-order', function () {
        return view('tenant.template.lead_default_view');
    });

    Route::post('tenant-custom-field/in-active','LeadController@tenantCustomField')->name('tenant.custom.field.active');

     Route::get('template/edit/{id}', function (Request $request) {
         if($request['id'] == 'max'){
             $record = \App\Models\TenantTemplate::getByMax($request['company_id']);
         }else
            $record = \App\Models\TenantTemplate::getById($request['id']);
         return view('tenant.template.edit_template',['data'=>$record]);
    });

    Route::get('template/update/{id}/{temp_id}','TenantQueryController@updateTemplate');
    Route::get('lead-default/edit/{id}','TenantQueryController@updateTenantDefaulTemplate');
    Route::get('template/fields', 'LeadController@templateFieldList');
    Route::post('template/field/index/update', 'LeadController@updateTemplateFieldIndex');
    Route::post('template/field/clear/indexes', 'LeadController@updateTemplateFieldIndexClear');
    Route::get('lead/default/fields', 'LeadController@defaultFieldList');
    Route::get('/logout', 'UserController@logout');

    Route::get('template/create/{id}', function () {
        return view('tenant.template.add_field');
    });

    Route::get('commission/edit/{id}', 'UserCommissionController@updateView');

    Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);

    Route::get('dashboard', 'LeadController@dashboard');

    Route::get('field', function () {
        return view('tenant.field.field_mgmt');
    });

    Route::get('field/create', function () {
        return view('tenant.field.add_field');
    });
    
    Route::get('marketing','MarketingController@index')->name('marketing.index');
    Route::post('marketing/campaign','MarketingController@campaignStatusUpdate')->name('marketing.campaign.status.update');
    Route::post('marketing/tags','MarketingController@tagStatusUpdate')->name('marketing.tag.status.update');
    Route::get('marketing/edit/{id}/campaign','MarketingController@editCampaign')->name('marketing.edit.campaign');
    Route::get('send', 'MailChimpController@index')->name('send.mail.using.mailchimp.index');
    Route::get('campaign', 'CampaignController@index')->name('campaign.index');
    Route::get('campaign/{id}/segment', 'CampaignController@segment')->name('campaign.segment');
    Route::get('campaign/create', 'CampaignController@create')->name('campaign.create');
    Route::get('campaign/{id}/view/content', 'CampaignController@viewContent')->name('campaign.view.content');
    Route::post('campaign/segment-status/user','CampaignController@segmentUserStatusUpdate')->name('campaign.segment.status.user');

    Route::post('followup/field/update/editable','FollowingLeadsController@editableField')->name('followup.field.update.editable');
    Route::get('followup/export','FollowingLeadsController@exportLead')->name('followup.export.lead');
    Route::get('marketing/export','FollowingLeadsController@exportMarketing')->name('marketing.export.lead');
    
    Route::post('followup/field/update/show','FollowingLeadsController@showField')->name('followup.field.update.show');
    Route::post('followup/custom-field/update/editable','FollowingLeadsController@editableCustomField')->name('followup.custom.field.update.editable');
    
    Route::post('purcahse/field/update/show','FollowingLeadsController@showFieldPurchase')->name('purcahse.field.update.show');
    Route::get('purchase-lead','PurchaseLeadsController@index')->name('purchase-lead.index');
    Route::resource('purchase-leadview','PurchaseLeadViewSetpController'); 
    Route::get('purchase/export','PurchaseLeadsController@exportLead')->name('purchase.export.lead');
    Route::get('purchase/leads/history/export', 'PurchaseLeadsController@LeadsHistoryExport');
    Route::post('purchase-lead','PurchaseLeadsController@indexList')->name('purchase-lead.list.index');
    Route::post('purchase-lead/delete','PurchaseLeadsController@indexListDelete')->name('purchase-lead.list.delete');
    Route::get('purchase-lead/{purchase_lead}/edit','PurchaseLeadsController@edit')->name('purchase-lead.edit');
    Route::post('purchase-lead/{purchase_lead}/edit','PurchaseLeadsController@update')->name('purchase-lead.update');
    Route::post('purchase/custom-field/update/editable','PurchaseLeadsController@editableCustomField')->name('purchase.custom.field.update.editable');
    Route::post('purchase/field/update/editable','PurchaseLeadsController@editableField')->name('purchase.field.update.editable');
    Route::post('purchase/field/update/show','PurchaseLeadsController@showField')->name('purchase.field.update.show');
    Route::post('purchase-lead/is-followup','FollowingLeadsController@isFollowup')->name('purchase-lead.followup');
    Route::post('marketing-lead/is-followup','FollowingLeadsController@isFollowupMarketing')->name('marketing-lead.followup');
    
    
    Route::get('followup-lead','FollowingLeadsController@index')->name('followup-lead.index');
    Route::post('followup-lead/isRetired','FollowingLeadsController@updateIsRetired')->name('followup-lead.is.retired');
    Route::post('followup-lead/is-marketing','FollowingLeadsController@isMarketing')->name('followup-lead.marketing');
    
    Route::post('followup-lead/is-purchase','FollowingLeadsController@isPurchase')->name('followup-lead.purchase');
    Route::post('followup-lead/is-deal','FollowingLeadsController@isDeal')->name('followup-lead.deal');
    Route::post('followup-lead','FollowingLeadsController@indexList')->name('followup-lead.list.index');
    Route::post('followup-lead/delete','FollowingLeadsController@indexListDelete')->name('followup-lead.list.delete');
    
    Route::post('marketing-lead/delete','FollowingLeadsController@MarketingListDelete')->name('marketing-lead.list.delete');
    
    Route::get('followup-lead/{followup_lead}/edit','FollowingLeadsController@edit')->name('followup-lead.edit');
    Route::get('followup/leads/history/export', 'FollowingLeadsController@followupLeadsHistoryExport');
    Route::post('followup-lead/{followup_lead}/edit','FollowingLeadsController@update')->name('followup-lead.update');
    
    Route::post('followup-lead/appointment/add','FollowingLeadsController@ApptUpdate')->name('followup.appointment.add');
    
    Route::post('marketing-lead/{marketing}/edit','FollowingLeadsController@updateMarketing')->name('marketing-lead.update');
    
    Route::get('marketing-lead/{followup_lead}/edit','FollowingLeadsController@marketingEdit')->name('marketing-lead.edit');
    
    Route::resource('follow-status','FollowStatusController'); 
    Route::post('follow/sortable','FollowUpLeadViewSetpController@sortableUpdate'); 
    Route::post('purchase/sortable','PurchaseLeadViewSetpController@sortableUpdatePurchase'); 
    Route::resource('follow-leadview','FollowUpLeadViewSetpController'); 
    Route::post('deal/sortable','DealLeadViewSetpContoller@sortableUpdate'); 
    Route::post('deal/field/update/show','DealLeadViewSetpContoller@showField')->name('deal.field.update.show');
    Route::resource('dealead-viewsetp','DealLeadViewSetpContoller'); 


    // Field Management
    Route::get('query/list', 'TenantQueryController@index');
    Route::post('query/update/sorting', 'TenantQueryController@updateSorting');
    Route::post('query/create', 'TenantQueryController@store');
    Route::post('template/field/create', 'TenantQueryController@storeTemplateField');
    Route::post('template/field/update', 'TenantQueryController@updateTemplateField');
    Route::post('lead/default/field/update', 'TenantQueryController@updateLeadDefaultField');
    Route::post('template/field/delete/{id}', 'TenantQueryController@destroyTemplateField');
    Route::post('lead/default/field/delete/{id}', 'TenantQueryController@destroyLeadDefaultField');
    Route::get('field/detail/{id}', 'TenantQueryController@show');
    Route::post('field/edit/{id}', 'TenantQueryController@update');
    Route::post('field/delete/{id}', 'TenantQueryController@destroy');
    Route::get('field/edit/{id}', function () {
        return view('tenant.field.edit_field');
    });

    Route::get('lead', 'LeadController@indexView');
    Route::get('lead/edit/{id}', 'LeadController@edit');
    Route::post('lead/bulk/update', 'LeadController@bulkUpdate');
   
    Route::get('audit', 'AuditController@index')->name('audit.index');
    Route::get('audit/{audit}/view', 'AuditController@show')->name('audit.view');

    Route::get('lead/add_lead', 'LeadController@addView');
    Route::get('lead/add_lead/get/{id}', 'LeadController@getFields');

    Route::get('lead/wizard', 'LeadController@wizardView');
    Route::get('lead/lead_status', function () {
        return view('tenant.lead.lead_status');
    });
    
    Route::get('lead/lead_status/edit/{id}', function () {
        return view('tenant.lead.edit_leadstatus');
    });
    Route::get('lead/lead_status/create', function () {
        return view('tenant.lead.add_leadstatus');
    });
    
    
    Route::get('lead/setting/create', function () {
        return view('tenant.lead.setting');
    });
    
    
    // alerts  
    Route::get('lead/appoiment_alerts/edit/{id}', function () {
        return view('tenant.scheduling.edit_alerts');
    });
    Route::get('lead/appoiment_alerts/create', function () {
        return view('tenant.scheduling.add_alerts');
    });
            
    Route::get('lead/appoiment_alerts', function () {
        return view('tenant.scheduling.alerts');
    });
    
    Route::post('alerts/create', 'CompanyController@storeAlerts');

    //User
    Route::post('agent/profile', 'UserController@profile');
    Route::post('user/profile', 'UserController@profile');
    Route::post('agent/update', 'UserController@updateAgent');
    Route::post('agent/is/status', 'UserController@updateStatusAgent')->name('agent.is.status');
    Route::post('user/update', 'UserController@updateBusiness');
    Route::post('printer/email/update', 'UserController@updatePrinterEmailAddress');

    //Printer Email Routes
    Route::get('printer_email', 'UserController@showPrinterEmail');
    Route::get('printer_email/create', function () {
        return view('tenant.printer.add_printer_email');
    });
    Route::get('printer_email/edit/{id}', function () {
        return view('tenant.printer.edit_printer_email');
    }); 
    
    Route::get('dashboard/purchase/list', 'LeadknocksController@purchaseList')->name('purchase.list');
    Route::get('dashboard/contract/list', 'LeadknocksController@contractList')->name('contract.list');
    Route::get('dashboard/appointments_kept/list', 'LeadknocksController@appointmentsKeptList')->name('appointments_kept.list');
    Route::get('dashboard/appointments_requested/list', 'LeadknocksController@appointmentsRequestedList')->name('appointments_requested.list');
    
    Route::get('dashboard/purchase/list/export', 'LeadknocksController@purchaseListExport')->name('purchase.list.export');
    Route::get('dashboard/contract/list/export', 'LeadknocksController@contractListExport')->name('contract.list.export');
    Route::get('dashboard/appointments_kept/list/export', 'LeadknocksController@appointmentsKeptListExport')->name('appointments_kept.list.export');
    Route::get('dashboard/appointments_requested/list/export', 'LeadknocksController@appointmentsRequestedListExport')->name('appointments_requested.list.export');
    
    
    Route::get('lead/knocks/user/list', 'LeadknocksController@index')->name('knock');
    Route::get('lead/knocks/import', 'LeadknocksController@indexImport')->name('knock.index.import');
    Route::post('lead/knocks/import/store', 'LeadknocksController@storeImport')->name('knock.store.import');
    Route::post('lead/knocks/is-verified', 'LeadknocksController@isVerified')->name('knocks.isverified');
    Route::get('lead/knocks/user/list-export', 'LeadknocksController@export')->name('knocks.export');
    Route::post('lead/knocks/user/list', 'LeadknocksController@deleteBulk')->name('knocks.delete.bulk');
    Route::get('knocks/user/{id}/list', 'LeadknocksController@indexknocks')->name('knock.index');
    Route::post('knocks/{id}/delete', 'LeadknocksController@destroyknock')->name('knock.delete');
    Route::get('knocks/{id}/edit', 'LeadknocksController@editknock')->name('knock.edit');
    Route::post('knocks/{id}/edit', 'LeadknocksController@updateknock')->name('knock.update');
    
    Route::post('history/{id}/delete', 'LeadknocksController@destroyHistory')->name('knock.delete');    
    Route::get('history/{id}/edit', 'LeadknocksController@editHistory')->name('history.edit');
    Route::post('history/{id}/edit', 'LeadknocksController@updateHistory')->name('history.update');

//Lead Start
    Route::post('lead/create', 'LeadController@store');
    Route::get('lead/list', 'LeadController@listView')->name('export');
    Route::get('lead/status/list', 'LeadController@statusListView');
    Route::get('alerts/list', 'LeadController@alertsListView');
    Route::get('lead/history', 'LeadController@history');
    Route::get('leads/history/export', 'LeadController@leadsHistoryExport');
    Route::get('lead/history/export/{lead_id}', 'LeadController@historyExport');
    Route::post('lead/query/{id}', 'LeadController@updateQuery');
    Route::post('lead/delete/{id}', 'LeadController@destroy');
    Route::get('/leads/map', 'LeadController@leadsMap');
    Route::get('leads/{id}', 'LeadController@show');
    Route::post('leads/{id}', 'LeadController@update');
    
    //Lead End
    Route::post('get/field/list', 'TenantQueryController@getFieldList')->name('get.field.list');

    //company
    Route::post('setting/create', 'CompanyController@storeSetting');
    
    Route::post('status/create', 'CompanyController@storeStatus');
    Route::post('status/edit/{id}', 'CompanyController@updateStatusValue');
    Route::post('alerts/edit/{id}', 'CompanyController@updateAlertsValue');
    
    Route::post('status/delete/{id}', 'CompanyController@deleteStatus');
    Route::post('alerts/delete/{id}', 'CompanyController@deleteAlerts');
    Route::get('status/detail/{id}', 'CompanyController@getStatusDetail');
    Route::get('alerts/detail/{id}', 'CompanyController@getAlertsDetail');
    Route::post('status/sorting/update', 'CompanyController@updateStatusSorting');
    Route::get('type/detail/{id}', 'CompanyController@getTypeDetail');
    Route::post('type/create', 'CompanyController@storeType');
    Route::post('type/edit/{id}', 'CompanyController@updateTypeValue');
    Route::post('type/delete/{id}', 'CompanyController@deleteType');
    Route::get('status/list', 'CompanyController@statusList');
    Route::get('type/list', 'CompanyController@typeList');

//user commission
    Route::post('user/commission/create', 'UserCommissionController@store');
    Route::get('user/commission/list', 'UserCommissionController@index');
    Route::get('user/commission/export', 'UserCommissionController@exportCSV');
    Route::post('user/commission/delete/{id}', 'UserCommissionController@destroy');
    Route::get('user/commission/report', 'UserCommissionController@commissionReport');
    Route::post('user/commission/{id}', 'UserCommissionController@update');
    Route::get('user/commission/{id}', 'UserCommissionController@show');

//user commission event
    Route::get('commission/event/detail/{id}', 'CompanyController@getCommissionEventDetail');
    Route::get('commission/event/list', 'CompanyController@getCommissionEventList');
    Route::post('commission/event/create', 'CompanyController@storeCommissionEvent');
    Route::post('commission/event/edit/{id}', 'CompanyController@updateCommissionEvent');
    Route::post('commission/event/delete/{id}', 'CompanyController@deleteCommissionEvent');


    Route::get('user/lead/report', 'LeadController@leadReport');
    //Route::get('user/lead/stats/report', 'LeadController@leadStatsReport');
    Route::get('user/lead/stats/report', 'LeadController@leadStatusStatsReport');
    Route::get('user/lead/types/report', 'LeadController@leadTypesStatsReport');
    Route::get('user/lead/status/report/pie', 'LeadController@leadTypesStatsReportPie');
    Route::get('user/lead/status/report/followup/pie', 'LeadController@leadTypesStatsReportFollowupPie');
    Route::get('user/lead/status/report/current/pie', 'LeadController@leadTypesStatsReportCurrentPie');
    Route::get('user/lead/status/report', 'LeadController@leadUserReport');
    Route::get('user/lead/status/report/export', 'LeadController@leadUserReportExport')->name('lead.user.report.export');
    Route::get('commission/event/export', 'LeadController@commissionReportExport');
    Route::get('lead/status/user/report', 'LeadController@leadStatusUserReport');
    Route::get('lead/type/user/report', 'LeadController@leadTypeUserReport');
    Route::get('lead/status/user/report/new', 'LeadController@leadStatusUserReportNew');
    Route::get('lead/status/user/report/followup', 'LeadController@leadStatusUserReportFollowUp');
    Route::get('lead/status/user/report/dashboard_knocks_statistics', 'LeadController@leadStatusUserReportDashboardKnocksStatistics');
    Route::get('lead/status/user/report/current/new', 'LeadController@leadStatusUserReportCurrentNew');
    Route::get('user/lead/appointment/list', 'UserLeadAppointmentController@index');
    
    
    Route::get('lead/status/user/report/knock/withcolour', 'LeadController@userReportKnockWithColour');
    Route::get('lead/status/user/report/knock', 'LeadController@userReportKnock');
    Route::get('lead/status/user/report/knock/not/contacted', 'LeadController@userReportKnockNotContracted');
    Route::get('user/lead/status/report/knock/pie', 'LeadController@KnockReportPie');
    
    Route::get('lead/status/user/report/knock/day', 'LeadController@userReportKnockDayReport');

    

    Route::get('lead/lead_type', function () {
        return view('tenant.lead.lead_type');
    });
    Route::get('lead/lead_type/create', function () {
        return view('tenant.lead.add_leadtype');
    });

    Route::get('lead/lead_type/edit/{id}', function () {
        return view('tenant.lead.edit_leadtype');
    });
    Route::get('lead/{id}', 'LeadController@show');


    Route::get('/forget_password', function () {
        return view('tenant.login.forget_password');
    });

    Route::get('/team-performance/comm_report', 'UserCommissionController@viewCommissionReport');
    Route::get('/team-performance/user_report', 'UserCommissionController@viewUserReport');
    Route::get('/team-performance/user_report_status', 'UserCommissionController@viewUserReportStatus');
    Route::get('/team-performance/user_report_followup_status', 'UserCommissionController@viewUserReportFollowUpStatus');
    Route::get('/team-performance/dashboard_knocks_statistics', 'UserCommissionController@viewDashboardKnocksStatistics');
    Route::get('/team-performance/user_report_status_current', 'UserCommissionController@viewUserReportStatusCurrent');
    Route::get('/team-performance/user_report_type', 'UserCommissionController@viewUserReportType');
    Route::get('/team-performance/team_report', 'LeadController@viewLeadReport');
    Route::get('/team-performance/knock_dashboard', 'UserCommissionController@viewUserKnockReport');
    Route::get('/team-performance/knock_dashboard/not/contacted', 'UserCommissionController@viewUserKnockReportNotContacted');
    Route::get('/team-performance/knock_dashboard/day_report', 'UserCommissionController@viewUserKnockReportDayReport');

    Route::get('/training', function () {
        return view('tenant.training.training_mgmt');
    });
    Route::get('/training/create', function () {
        return view('tenant.training.add_script');
    });

    Route::get('training/edit/{id}', function () {
        return view('tenant.training.edit_script');
    });
//Training
    Route::post('user/training/create', 'UserTrainingScriptController@store');
    Route::get('user/training/list', 'UserTrainingScriptController@index');
    Route::post('user/training/{id}', 'UserTrainingScriptController@update');
    Route::get('user/training/{id}', 'UserTrainingScriptController@show');
    Route::post('user/training/delete/{id}', 'UserTrainingScriptController@deleteTrainingScript');

    Route::get('/agent', function () {
        return view('tenant.agent.agent_mgmt');
    });

    Route::get('agent/edit_profile', 'UserController@showView');

    Route::get('/user/list', 'UserController@tenantUserList');

    Route::get('/agent/create', function () {
        return view('tenant.agent.add_agent');
    });
    Route::post('agent/create', 'UserController@storeAgent');
    Route::post('agent/delete/{id}', 'UserController@deleteAgent');
    Route::post('agent/reset/{id}', 'UserController@resetAgentPassword');

    Route::get('/agent/edit/{id}', function () {
        return view('tenant.agent.edit_agent');
    });

    Route::post('/lead/wizard/upload', 'LeadController@uploadLeads');
    Route::post('/template/create', 'LeadController@addTemplate');
    Route::post('/lead/wizard/template', 'LeadController@wizardTemplate');
    Route::post('/leads/wizard/fields', 'LeadController@wizardFields');
    Route::get('/template/copy/{slug}', 'LeadController@CopyTemplate');

    Route::post('/leads/wizard/is-exprired', 'LeadController@isExpriredUpdate');
    Route::post('/leads/wizard/is-followup', 'LeadController@isfollowup');    
    
    Route::post('/leads/wizard/is-leadup', 'LeadController@isleadup');

    Route::get('/lead/template/list', 'LeadController@templateList');
    Route::post('/lead/template/delete', 'LeadController@deleteTemplate');
    Route::get('/lead/template/{id}', 'LeadController@templateShow');
    Route::post('/lead/template/update/{id}', 'LeadController@templateUpdate');
    Route::post('/lead/template/delete/{id}', 'LeadController@templateDestroy');

    Route::get('scheduling', 'UserLeadAppointmentController@schedulingView');
    Route::get('scheduling/create','UserLeadAppointmentController@storeView');
    Route::post('scheduling/create', 'UserLeadAppointmentController@store');
    Route::post('scheduling/store', 'UserLeadAppointmentController@store');
    Route::get('scheduling/getAppointments', 'UserLeadAppointmentController@getAppointments');
    Route::get('scheduling/{id}', 'UserLeadAppointmentController@show');
    Route::post('scheduling', 'UserLeadAppointmentController@update');
    Route::post('scheduling/delete/{id}', 'UserLeadAppointmentController@destroy');



}); /*Tenant Group End*/

/*static routes end*/



/* API routes*/




/*Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');*/
Route::get('/layout/about', 'HomeController@layoutAbout');
Route::get('/layout/contact', 'HomeController@layoutSecurity');
Route::get('/privacy-policy', function () {
    return view('layouts.privacy-policy');
});

Route::get('/terms-conditions', function () {
    return view('layouts.terms-conditions');
});
Route::post('/admin/subscribe/user', 'UserController@subscribe');
Route::post('/admin/user/donate', 'UserController@addDonation');
Route::any('/user/subscribe', 'UserController@updateSubscription');
Route::any('/user/forgot/password/{token}', 'UserController@changePasswordWeb');
Route::any('/user/registration/{token}', 'UserController@changePasswordWeb');

