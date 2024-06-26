<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CampaignTag;
use App\Models\CampaignUser;

class CampaignuserController extends Controller {

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function index(Request $request) {
        $listId = env('MAILCHIMP_LIST_ID');
        $mailchimp_key = env('MAILCHIMP_API_KEY');

        $mailchimpMarketing = marketingCampaignObj();

        $userList = $mailchimpMarketing->lists->getListMembersInfo($listId, null, null, 100);

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

        $users = CampaignUser::latest()->get();

        return view('tenant.campaignUser.index', compact('users'));
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function create() {
        $tags = CampaignTag::latest()->get();

        return view('tenant.campaignUser.create', compact('tags'));
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function store(Request $request) {
        $request->validate([
            'email' => 'required',
        ]);


        $body = [];
        $body['email_address'] = $request->email;
        $body['status_if_new'] = 'subscribed';
        $body['status'] = 'subscribed';
        $body['merge_fields']['FNAME'] = $request->FNAME;
        $body['merge_fields']['LNAME'] = $request->LNAME;
        $body['merge_fields']['PHONE'] = $request->PHONE;
        $body['merge_fields']['ADDRESS']['addr1'] = $request->addr1;
        $body['merge_fields']['ADDRESS']['city'] = $request->city;
        $body['merge_fields']['ADDRESS']['state'] = $request->state;
        $body['merge_fields']['ADDRESS']['zip'] = $request->zip;
        $body['merge_fields']['ADDRESS']['country'] = $request->country;
        
        $mailchimpMarketing = marketingCampaignObj();

        $list_id = env('MAILCHIMP_LIST_ID');
        
        $response = $mailchimpMarketing->lists->addListMember($list_id, $body);                
        
        $tags = CampaignTag::latest()->get();
        $tags1 = [];
        foreach ($tags as $key => $tag) {
            $tags1['tags'][$key]['name'] = $tag->tag_name;
            if(in_array($tag->tag_id,$request->tags)){
            $tags1['tags'][$key]['status'] = "active";    
            }else{
            $tags1['tags'][$key]['status'] = "inactive";    
            }
            
        }

        if (isset($request->tags[0])) {
            $client = marketingCampaignObj();

            $response = $client->lists->updateListMemberTags($list_id, $response->id, $tags1);
        }

        return redirect()->route('tenant.campaign.user.index');
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function edit(CampaignUser $campaign_user) {

        $tags = CampaignTag::latest()->get();

        return view('tenant.campaignUser.edit', compact('campaign_user', 'tags'));
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function update(CampaignUser $campaign_user, Request $request) {
        $request->validate([
            'email' => 'required',
        ]);

        $user_data = json_decode($campaign_user->user_data);

        $href = $user_data->_links[2]->href;


        $body = [];
        $body['status'] = 'subscribed';
        $body['merge_fields']['FNAME'] = $request->FNAME;
        $body['merge_fields']['LNAME'] = $request->LNAME;
        $body['merge_fields']['PHONE'] = $request->PHONE;
        $body['merge_fields']['ADDRESS']['addr1'] = $request->addr1;
        $body['merge_fields']['ADDRESS']['city'] = $request->city;
        $body['merge_fields']['ADDRESS']['state'] = $request->state;
        $body['merge_fields']['ADDRESS']['zip'] = $request->zip;
        $body['merge_fields']['ADDRESS']['country'] = $request->country;
        membersUpdateMailChimp($body, $href);


        $tags = CampaignTag::latest()->get();
        $tags1 = [];
        foreach ($tags as $key => $tag) {
            $tags1['tags'][$key]['name'] = $tag->tag_name;
            if(in_array($tag->tag_id,$request->tags)){
            $tags1['tags'][$key]['status'] = "active";    
            }else{
            $tags1['tags'][$key]['status'] = "inactive";    
            }
            
        }

        if (isset($request->tags[0])) {
            $client = marketingCampaignObj();

            $response = $client->lists->updateListMemberTags($user_data->list_id, $user_data->id, $tags1);
        }



        return redirect()->route('tenant.campaign.user.index');
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function delete(CampaignUser $campaign_user) {

//        $user_data = json_decode($campaign_user->user_data);
//        
//        $href =  $user_data->_links[4]->href;
//        
//        membersRemoveMailChimp($href);

        $user_data = json_decode($campaign_user->user_data);

        $href = $user_data->_links[2]->href;


        $body = [];
        $body['status'] = 'unsubscribed';

        membersUpdateMailChimp($body, $href);

        return response()->json(['success' => true, 'code' => 200, 'message' => 'Campaign Tag delete successfully']);
    }

}
