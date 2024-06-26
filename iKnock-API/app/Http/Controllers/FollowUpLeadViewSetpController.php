<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FollowUpLeadViewSetp;
use Illuminate\Support\Str;

class FollowUpLeadViewSetpController extends Controller
{
     
    public $inpuTypes = [];
    public $pickListTypes = [];

    public function __construct()
    {
       $followUpLeadViewSetp = new FollowUpLeadViewSetp();

       $this->inpuTypes = $followUpLeadViewSetp->inpuyTypes;
       $this->pickListTypes = $followUpLeadViewSetp->pickListTypes;
    }   
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $followUpLeadViewSetps = FollowUpLeadViewSetp::orderBy('order_no','asc')->get();
        
        return view('tenant.followUpLeadViewSetp.index',compact('followUpLeadViewSetps'));
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

        return view('tenant.followUpLeadViewSetp.create',compact('inputType','pickListTypes'));
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

        FollowUpLeadViewSetp::create($input);

        \Session::put('success', 'Follow-up Lead View Setup created successfully');
        return redirect()->route('tenant.follow-leadview.index');
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
    public function edit(FollowUpLeadViewSetp $follow_leadview)
    {
        $inputType = $this->inpuTypes;
        $pickListTypes = $this->pickListTypes;

        return view('tenant.followUpLeadViewSetp.edit',compact('follow_leadview','inputType','pickListTypes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FollowUpLeadViewSetp $follow_leadview)
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

        $follow_leadview->update($input);

        \Session::put('success', 'Follow-up Lead View Setup update successfully');
        return redirect()->route('tenant.follow-leadview.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(FollowUpLeadViewSetp $follow_leadview)
    {
        $follow_leadview->delete();

        \Session::put('success', 'Follow-up view setup deleted successfully');
        return response()->json(['success'=>true,'code'=>200,'message'=>'Follow-up view setup delete successfully']);
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function sortableUpdate(Request $request)
    {   
        if(!empty($request->sortedids)){

            foreach ($request->sortedids as $key => $value) {
                $followUpLeadViewSetp = FollowUpLeadViewSetp::find($value);
                
                if(!is_null($followUpLeadViewSetp)){
                    $followUpLeadViewSetp->order_no = $key;
                    $followUpLeadViewSetp->save();
                }

            }
        }

        return response()->json(['success'=>true,'code'=>200,'message'=>'Sort Update successfully']);
    }
    
    public function sortableUpdatePurchase(Request $request)
    {   
        if(!empty($request->sortedids)){

            foreach ($request->sortedids as $key => $value) {
                $followUpLeadViewSetp = \App\Models\PurchaseLeadViewSetp::find($value);
                
                if(!is_null($followUpLeadViewSetp)){
                    $followUpLeadViewSetp->order_no = $key;
                    $followUpLeadViewSetp->save();
                }

            }
        }

        return response()->json(['success'=>true,'code'=>200,'message'=>'Sort Update successfully']);
    }
}
