<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DealLeadViewSetp;
use Illuminate\Support\Str;

class DealLeadViewSetpContoller extends Controller
{
    public $inpuTypes = [];
    public $pickListTypes = [];

    public function __construct()
    {
       $PurchaseLeadViewSetp = new DealLeadViewSetp();

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
        $dealLeadViewSetps = DealLeadViewSetp::orderBy('order','asc')->get();

        return view('tenant.dealLeadViewSetp.index',compact('dealLeadViewSetps')); 
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

        return view('tenant.dealLeadViewSetp.create',compact('inputType','pickListTypes')); 
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
        $input['order'] = $request->order ?? 1;
        $input['view_type'] = $request->view_type;
        $input['pick_list_type'] = $request->pick_list_type ?? 1;
        $input['pick_list_content_model'] = $request->pick_list_content_model ?? 1;
        
        if(!empty($request->custompick) && !empty($request->custompick[1])){
            $input['pick_list_content'] = implode(",",$request->custompick);
        }

        $input['input_type'] = $request->input_type ?? 1;

        DealLeadViewSetp::create($input);

        \Session::put('success', 'Deal Lead View Setup created successfully');
        return redirect()->route('tenant.dealead-viewsetp.index');
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
    public function edit(DealLeadViewSetp $dealead_viewsetp)
    {
        $inputType = $this->inpuTypes;
        $pickListTypes = $this->pickListTypes;

      return view('tenant.dealLeadViewSetp.edit',compact('dealead_viewsetp','inputType','pickListTypes')); 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DealLeadViewSetp $dealead_viewsetp)
    {
        $input['title'] = $request->title;
        $input['title_slug'] = Str::slug($request->title, '_');
        $input['order'] = $request->order ?? 1;
        $input['view_type'] = $request->view_type;
        $input['pick_list_type'] = $request->pick_list_type ?? 1;
        $input['pick_list_content_model'] = $request->pick_list_content_model ?? 1;
        
        $custompick = array_values($request->custompick);

        if(!empty($request->custompick) && !empty($custompick[0])){
            $input['pick_list_content'] = implode(",",$custompick);
        }

        $input['input_type'] = $request->input_type ?? 1;

        $dealead_viewsetp->update($input);

        \Session::put('success', 'Deal Lead View Setup update successfully');
        return redirect()->route('tenant.dealead-viewsetp.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DealLeadViewSetp $dealead_viewsetp)
    {
        $dealead_viewsetp->delete();

        \Session::put('success', 'Deal view setup deleted successfully');
        return response()->json(['success'=>true,'code'=>200,'message'=>'Deal view setup delete successfully']);
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
                $dealLeadViewSetp = DealLeadViewSetp::find($value);
                
                if(!is_null($dealLeadViewSetp)){
                    $dealLeadViewSetp->order = $key;
                    $dealLeadViewSetp->save();
                }

            }
        }

        return response()->json(['success'=>true,'code'=>200,'message'=>'Sort Update successfully']);
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function showField(Request $request)
    {
        $dealLeadViewSetp = DealLeadViewSetp::find($request->id); 

        if(!is_null($dealLeadViewSetp)){
            $dealLeadViewSetp->is_show = $request->value;

            $dealLeadViewSetp->save();
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
    public function indexListDelete(Request $request)
    {

        if(!empty($request->ids)){
            foreach ($request->ids as $key => $id) {
                // code...
            }
        }

        return response()->json(['success', 'code' => 200]);
    }
}
