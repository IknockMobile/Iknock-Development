<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DealLead;
use App\Models\User;
use App\Imports\DealLeadsImport;
use App\Exports\DealLeadsExport;
use App\Models\DealLeadViewSetp;
use App\Models\DealLeadViewCustomFields;
use App\Http\Resources\DealLeadsResource;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\FollowingLead;

class DealController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {           
        $dealLeads = getDealScreenData($request->all());

        $mobileusersArray = User::latest()->whereIn('user_group_id', [2,4])->get();

        $mobileusers = userArraySetJson($mobileusersArray);

        $dealLeadObj = new DealLead();

        $dealTypes = dataArrayToJson($dealLeadObj->dealType);
        $dealStatus = dataArrayToJson($dealLeadObj->dealStatus);
        $purchaseFinance = dataArrayToJson($dealLeadObj->purchaseFinance);
        $ownershipList = dataArrayToJson($dealLeadObj->ownershipList);

        $dealLeadViewSetpDropDown = DealLeadViewSetp::orderBy('order','asc')->get();
        $dealLeadViewSetp = DealLeadViewSetp::where('is_show','1')->orderBy('order','asc')->get();

        $input = $request->all();

        $dealExportUrl = route('tenant.deal.export',$input);

        unset($input['is_ajax']);

        $tableBodyView = view('tenant.deal.tableMain',compact('dealLeadViewSetp','dealLeads','input','mobileusers','dealTypes','dealStatus','purchaseFinance','ownershipList'));

        if($request->has('is_ajax') && $request->is_ajax == 1){
            return response()->json(['success'=>"$tableBodyView",'dealExportUrl'=>$dealExportUrl]);
        }

        return view('tenant.deal.index',compact('dealLeads','mobileusers','mobileusersArray','dealLeadObj','tableBodyView','dealLeadViewSetp','dealLeadViewSetpDropDown','dealLeads','dealExportUrl'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(DealLead $deal)
    {
        $mobileusers = User::latest()->whereIn('user_group_id', [2,4])->get();

        return view('tenant.deal.edit',compact('deal','mobileusers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DealLead $deal)
    {
        $input['title'] = $request->title;
        $input['address'] = $request->address;
        $input['city'] = $request->city;
        $input['state'] = $request->state;
        $input['zip_code'] = $request->zip_code;
        $input['county'] = $request->county;
        $input['sq_ft'] = $request->sq_ft;
        $input['yr_blt'] = $request->yr_blt;
        $input['investor_id'] = $request->investor_id;
        $input['closer_id'] = $request->closer_id;
        $input['deal_status'] = $request->deal_status;
        $input['deal_type'] = $request->deal_type;
        $input['purchase_finance'] = $request->purchase_finance;
        $input['ownership'] = $request->ownership;
        $input['purchase_price'] = $request->purchase_price;
        $input['purchase_closing_costs'] = $request->purchase_closing_costs;
        $input['cash_in_at_purchase'] = $request->cash_in_at_purchase;
        $input['rehab_and_other_costs'] = $request->rehab_and_other_costs;
        $input['total_cash_in'] = $request->total_cash_in;
        $input['Investor_commission'] = $request->Investor_commission;
        $input['total_cost'] = $request->total_cost;
        $input['sales_value'] = $request->sales_value;
        $input['sales_cash_proceeds'] = $request->sales_cash_proceeds;
        $input['lh_profit_after_sharing'] = $request->lh_profit_after_sharing;
        $input['notes'] = $request->notes;
        if($request->purchase_date != '-'){
            $input['purchase_date'] = dynamicDateFormat($request->purchase_date,2);
        }
        if($request->sell_date != '-'){
            $input['sell_date'] = dynamicDateFormat($request->sell_date,2);
        }
        

        $deal->update($input);

        return response()->json('success');
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function editableCustomField(Request $request) {

        $dealCustomFields = DealLeadViewCustomFields::find($request->name);

        info($dealCustomFields);
        info($request->all());

        if (!empty($dealCustomFields)) {
            $dealCustomFields->field_value = $request->value;

            $dealCustomFields->save();
        }

        return response()->json(['success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function dealEditable(Request $request)
    {
        $dealLead = DealLead::find($request->pk);
            
        info($request->all());    

        if(!is_null($dealLead)){
            $inputName = $request->name;
            $dealLead->$inputName = $request->value;
            $dealLead->save();
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
    public function dealImportIndex(Request $request)
    {
        return view('tenant.deal.import');
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function dealImport(Request $request)
    {
         $request->validate([
              'deal_file' => 'required',
        ]);

        Excel::import(new DealLeadsImport,request()->file('deal_file'));

        notificationMsg('success','Deal leads import successfully.');
        return redirect()->route('tenant.deals.index');        
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function dealExportIndex(Request $request)
    {
        
        $dealLeads = getDealScreenDataExport($request->all(),'all');

        $dealLeadViewSetp = DealLeadViewSetp::whereNotIn('title_slug',['action','no','all_delete'])->where('is_show','1')->orderBy('order','asc')->get();
            
        $dealLeads = setupExportDealView($dealLeads,$dealLeadViewSetp);

        return Excel::download(new DealLeadsExport($dealLeads), 'deal-Leads-'.date('m-d-Y').'-'.time().'.csv');
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function indexListDelete(Request $request)
    {

        if(!empty($request->ids)){
            foreach ($request->ids as $key => $id) {
                $dealLead = DealLead::find($id);

                if(!is_null($dealLead)){

                     $followingLead = FollowingLead::where('id',$dealLead->followup_id)->first();

                    if(!is_null($followingLead)){
                         $followingLead->update(['is_deal'=> 0]);
                    }

                    $dealLead->delete();
                }
            }
        }

        return response()->json(['success', 'code' => 200]);
    }
}
