<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Campaign; 
use App\Models\CampaignUser; 
use App\Models\CampaignSegment; 
use App\Models\SegmentUserStatus; 
use App\Models\MarketingCampaign; 
use App\Models\Marketing; 

class CampaignController extends Controller
{   
    public function index(Request $request)
    {
        createOrUpdateCampaign();
        $campaigns = Campaign::latest()->paginate(10);

        return view('tenant.campaign.index',compact('campaigns'));
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function create()
    {
        return view('tenant.campaign.create');
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function store($campaignList)
    {
                       
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function segment(Request $request)
    {                     
        $campaign = Campaign::find($request->id);
        $campaignUsers = CampaignUser::latest()->get();
        
        $name = 'Segment For ' . $campaign->title;

        $campaignSegment = CampaignSegment::where('campaign_id',$campaign->id)->first();
        
        
        if(is_null($campaignSegment)){
            $listId = env('MAILCHIMP_LIST_ID');
            $mailchimpMarketing = marketingCampaignObj();

            $body = '{"name":"'.$name.'",
                     "options":{
                    "match":"all",
                    "conditions":[] 
                    } }';

            $segment = $mailchimpMarketing->lists->createSegment($listId,$body);
            
            

            $input['campaign_id'] = $campaign->id; 
            $input['segment_id'] = $segment->id; 
            $input['segment_name'] = $name; 
            $input['segment_data'] = json_encode($segment,true);

            $campaignSegment = CampaignSegment::create($input);

        }

        return view('tenant.campaign.segment',compact('campaignUsers','campaignSegment'));
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function segmentUserStatusUpdate(Request $request)
    {
        $input['segment_id'] = $request->segment_id;
        $input['status'] = $request->value;
        $input['user_id'] = $request->id;

        $campaignSegment = CampaignSegment::find($input['segment_id']);

        $campaignUser = CampaignUser::find($request->id);

        $segmentUserStatus = SegmentUserStatus::where('segment_id', $input['segment_id'])
                                              ->where('user_id', $input['user_id'])
                                              ->first();
        if(is_null($segmentUserStatus)){
            SegmentUserStatus::create($input);
        }else{
             $segmentUserStatus->update($input);
        }

        $mailchimpMarketing = marketingCampaignObj();

        $segmentUserStatusList = SegmentUserStatus::where('status',1)->where('segment_id',$input['segment_id'])->get();
        
        $listId = env('MAILCHIMP_LIST_ID');

        $emaillist = [];
        $emaillistnew = [];

        $inputSegment['name'] = $campaignSegment->segment_name;
        $inputSegment['options']['match'] = 'any';
        $inputSegment['options']['conditions'] = [];

        if(!empty($segmentUserStatusList)){
            foreach ($segmentUserStatusList as $key => $segmentUserStatus) {
                $emaillist[$key]['condition_type'] = 'EmailAddress';
                $emaillist[$key]['field'] = 'EMAIL';
                $emaillist[$key]['op'] = 'is';
                $emaillist[$key]['value'] = $segmentUserStatus->user->email_address;
            }

            foreach ($segmentUserStatusList as $key => $segmentUserStatus) {
                $emaillistnew[$key]['field'] = 'merge0';
                $emaillistnew[$key]['op'] = 'eq';
                $emaillistnew[$key]['value'] = $segmentUserStatus->user->email_address;
            }

            $inputSegment['options']['conditions'] = $emaillist;
        }

        $data = json_encode($inputSegment,true);
        
        $campaign = Campaign::find($campaignSegment->campaign_id);

        $response = $mailchimpMarketing->lists->updateSegment($listId, $campaignSegment->segment_id, $data);
      
        $campaignSegment->segment_data = json_encode($response,true);
        $campaignSegment->save();

        // $saveSegmentNew['saved_segment_id']  = $campaignSegment->segment_id;
        // $saveSegmentNew['match']  = 'any';
        // $saveSegmentNew['conditions']  = $emaillistnew;

        // $saveSegment =json_decode($campaign->campaign_data,true);

        // info($saveSegment);
        // $saveSegment['segment_opts'] = $saveSegmentNew;
        // info($saveSegment);

        // $saveSegment  = json_encode($saveSegment,true);

        // $responseUpdate = $mailchimpMarketing->campaigns->updateWithHttpInfo($campaign->campaign_id,$saveSegment);

        // $campaign->campaign_data =  json_encode($responseUpdate,true);

        // $campaign->save();

        return response()->json('success');
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function viewContent(Request $request)
    {
        $campaign = Campaign::find($request->id);

        $marketingIds = MarketingCampaign::where('status',1)->where('campaign_db_id', $campaign->id)->pluck('marketing_id')->toArray();

        $marketings = Marketing::whereIn('id',$marketingIds)->latest()->get();

        $marketingListMail = view('tenant.marketing.listMail',compact('marketings'))->render();

        return view('tenant.campaign.content',compact('marketingListMail','campaign'));
    }

}
