<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FollowUpLeadViewSetp;
use Illuminate\Support\Str;
use App\Models\PurchaseLeadViewSetp;

class PurchaseLeadViewSetpController extends Controller
{
     
    public $inpuTypes = [];
    public $pickListTypes = [];

    public function __construct()
    {
       $PurchaseLeadViewSetp = new PurchaseLeadViewSetp();

       $this->inpuTypes = $PurchaseLeadViewSetp->inpuyTypes;
       $this->pickListTypes = $PurchaseLeadViewSetp->pickListTypes;
    }   
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $PurchaseLeadViewSetpSetps = PurchaseLeadViewSetp::orderBy('order_no','asc')->get();
        
        return view('tenant.purchaseLeadViewSetp.index',compact('PurchaseLeadViewSetpSetps'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        $inputType = $this->inpuTypes;
        $pickListTypes = $this->pickListTypes;

        return view('tenant.purchaseLeadViewSetp.create',compact('inputType','pickListTypes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        $request->validate([
            'title' => 'required',
            'view_type' => 'required',
        ]);

        $input['title'] = $request->title;
        $input['title_slug'] = Str::slug($request->title, '_');
        $input['order_no'] = $request->order_no ?? 1;
        $input['view_type'] = $request->view_type;
        $input['pick_list_type'] = $request->pick_list_type ?? 1;
        $input['pick_list_content_model'] = $request->pick_list_content_model ?? 1;
        
        if(!empty($request->custompick) && !empty($request->custompick[1])){
            $input['pick_list_content'] = implode(",",$request->custompick);
        }

        $input['input_type'] = $request->input_type ?? 1;

        PurchaseLeadViewSetp::create($input);

        \Session::put('success', 'Purchase Lead View Setup created successfully');
        return redirect()->route('tenant.purchase-leadview.index');
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
    public function edit(PurchaseLeadViewSetp $purchase_leadview)
    {                
        
        $inputType = $this->inpuTypes;
        $pickListTypes = $this->pickListTypes;
        $follow_leadview = $purchase_leadview;
        return view('tenant.purchaseLeadViewSetp.edit',compact('follow_leadview','inputType','pickListTypes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PurchaseLeadViewSetp $purchase_leadview)
    {
         $request->validate([
            'title' => 'required',
            'view_type' => 'required',
        ]);

        $input['title'] = $request->title;
        $input['title_slug'] = Str::slug($request->title, '_');
        $input['order_no'] = $request->orderno;
        $input['view_type'] = $request->view_type;
        $input['pick_list_type'] = $request->pick_list_type ?? 1;
        $input['pick_list_content_model'] = $request->pick_list_content_model ?? 1;
        
        $custompick = array_values($request->custompick);

        if(!empty($request->custompick) && !empty($custompick[0])){
            $input['pick_list_content'] = implode(",",$custompick);
        }

        $input['input_type'] = $request->input_type ?? 1;

        $purchase_leadview->update($input);

        \Session::put('success', 'Purchase Lead View Setup update successfully');
        return redirect()->route('tenant.purchase-leadview.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(PurchaseLeadViewSetp $purchase_leadview)
    {
        $purchase_leadview->delete();

        \Session::put('success', 'Purchase view setup deleted successfully');
        return response()->json(['success'=>true,'code'=>200,'message'=>'Follow-up view setup delete successfully']);
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function sortableUpdatePurchase(Request $request)
    {   
        if(!empty($request->sortedids)){

            foreach ($request->sortedids as $key => $value) {
                $followUpLeadViewSetp = PurchaseLeadViewSetp::find($value);
                
                if(!is_null($followUpLeadViewSetp)){
                    $followUpLeadViewSetp->order_no = $key;
                    $followUpLeadViewSetp->save();
                }

            }
        }

        return response()->json(['success'=>true,'code'=>200,'message'=>'Sort Update successfully']);
    }
}
