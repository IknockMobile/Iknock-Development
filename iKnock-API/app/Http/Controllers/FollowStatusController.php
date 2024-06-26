<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FollowStatus;

class FollowStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $followStatuses = FollowStatus::latest()->get();

        return view('tenant.followStatus.index',compact('followStatuses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
          return view('tenant.followStatus.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input['title'] = $request->title;
        $input['color_code'] = $request->color;
        $input['is_purchase'] = $request->is_purchase;
        $input['is_followup'] = $request->is_followup;

        FollowStatus::create($input);

        \Session::put('success', 'Follow-up status created successfully');
        return response()->json(['success'=>true]);
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
    public function edit(FollowStatus $follow_status)
    {
        return view('tenant.followStatus.edit',compact('follow_status'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FollowStatus $follow_status)
    {
        $input['title'] = $request->title;
        $input['color_code'] = $request->color;
        $input['is_purchase'] = $request->is_purchase;
        $input['is_followup'] = $request->is_followup;

        $follow_status->update($input);

        \Session::put('success', 'Follow-up status updated successfully');

        return response()->json(['success'=>true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(FollowStatus $follow_status)
    {
        $follow_status->delete();

        \Session::put('success', 'Follow-up status deleted successfully');
        return response()->json(['success'=>true,'code'=>200,'message'=>'Status delete successfully']);
    }
}
