<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Marketing;
use App\Models\MarketingCampaign;
use App\Models\CampaignUser;
use App\Models\Campaign;
use MailchimpMarketing\ApiClient;
use App\Models\CampaignTag;
use App\Models\FollowStatus;
use App\Models\User;

class MarketingController extends Controller {

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index(Request $request) {
        $input = $request->all();

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



        $marketings = Marketing::select('marketings.*', 'user.first_name as in_first_name', 'user.last_name as in_last_name', 'user2.first_name as lead_first_name', 'user2.last_name as lead_last_name')
                ->leftjoin("user", "marketings.investore_id", "=", "user.id")
                ->leftjoin("user as user2", "marketings.user_detail", "=", "user2.id")
                ->where("marketings.is_followup", "=", 0);
        if (isset($request->search_text) AND $request->search_text != '') {
            $marketings = $marketings->where(function($querysub) use($input) {
                $querysub->orwhere('title', 'like', '%' . $input['search_text'] . '%')
                        ->orwhere('formatted_address', 'like', '%' . $input['search_text'] . '%')
                        ->orwhere('address', 'like', '%' . $input['search_text'] . '%')
                        ->orwhere('investore_note', 'like', '%' . $input['search_text'] . '%')
                        ->orwhere('admin_notes', 'like', '%' . $input['search_text'] . '%');
            });
        }

        if (isset($request->user_id_search) AND $request->user_id_search != '') {
            
            if (!is_array($input['user_id_search'])) {
            $input['user_id_search'] = explode(',', $input['user_id_search']);
        }
               
        
            $marketings = $marketings->where(function($querysub) use($input) {
                $querysub->orWhereIn('user_detail', $input['user_id_search'])
                        ->orWhereIn('investore_id', $input['user_id_search']);
            });
        }

        if (!empty($input['sort_column']) && !empty($input['sort_type'])) {
            switch ($input['sort_column']) {
                case 'homeowner_name':
                    $marketings = $marketings->orderby('marketings.title', $input['sort_type']);
                    break;

                case 'address':
                    $marketings = $marketings->orderby('marketings.formatted_address', $input['sort_type']);
                    break;
                
                case 'appt_email':
                    $marketings = $marketings->orderby('marketings.appt_email', $input['sort_type']);
                    break;
                
                case 'appt_phone':
                    $marketings = $marketings->orderby('marketings.appt_phone', $input['sort_type']);
                    break;      
                
                case 'marketing_email':
                    $marketings = $marketings->orderby('marketings.marketing_mail', $input['sort_type']);
                    break;
                
                case 'marketing_address':
                    $marketings = $marketings->orderby('marketings.marketing_address', $input['sort_type']);
                    break;

                case 'notes_and_actions':
                    $marketings = $marketings->orderby('marketings.admin_notes', $input['sort_type']);
                    break;   
                
                case 'investor_notes':
                    $marketings = $marketings->orderby('marketings.investore_note', $input['sort_type']);
                    break;                

                case 'auction_date':
                    $marketings = $marketings->orderby('marketings.auction', $input['sort_type']);
                    break;               

                case 'lead':
                    $marketings = $marketings->orderby('user2.first_name', $input['sort_type']);
                    break;

                case 'investor':
                    $marketings = $marketings->orderby('user.first_name', $input['sort_type']);
                    break;

                default:
                    break;
            }
        } else {
            $marketings = $marketings->latest();
        }

        $DisplayedPerScreen = settingForRecordsDisplayedPerScreen();
        if (isset($DisplayedPerScreen->marketing_lead_management)) {
            $marketings = $marketings->latest()->paginate($DisplayedPerScreen->marketing_lead_management);
        } else {
            $marketings = $marketings->latest()->paginate(10);
        }

        $tags = CampaignTag::where('is_show_marketing', '=', 1)->get();

        $statusList = FollowStatus::where('is_followup', '=', 1)->latest()->get();

        $users = User::orderby('status_id', 'desc')->where('status_id', 1)->get();

        return view('tenant.marketing.index', compact('marketings', 'tags', 'statusList', 'users'));
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function editCampaign($id) {
        createOrUpdateCampaign();
        createMarketingCampaign($id);

        $marketing = Marketing::find($id);

        return view('tenant.marketing.editCampaign', compact('marketing'));
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function campaignStatusUpdate(Request $request) {


        return true;
        $marketingCampaign = MarketingCampaign::where('marketing_id', '=', $request->id)
                ->where('campaign_id', '=', $request->champid)
                ->first();

        if (!is_null($marketingCampaign)) {
            $marketingCampaign->status = $request->value;
            $marketingCampaign->save();
            $listId = env('MAILCHIMP_LIST_ID');
            $mailchimpMarketing = marketingCampaignObj();
            $campaign_id = $request->champid;
//            $response = $mailchimpMarketing->campaigns->get($campaign_id);            
            updateCampaignContent($request->id, $campaign_id);
        }

        return response()->json(['success' => $marketingCampaign]);
    }

    public function tagStatusUpdate(Request $request) {

        $listId = env('MAILCHIMP_LIST_ID');

        $marketing_id = $request->marketing_id;

        $checked_tag = $request->checked_tag;

        $dataMarketing = Marketing::where('id', '=', $marketing_id)->first();

        $marketing_mail = '';
        if (isset($dataMarketing->marketing_mail)) {
            $marketing_mail = $dataMarketing->marketing_mail;
        }

        if ($marketing_mail == '') {
            $marketing_mail = $dataMarketing->appt_email;
        }

        $dataCampaignUser = CampaignUser::where('email_address', '=', $marketing_mail)->first();
        if (isset($dataCampaignUser->id)) {
            $campaignUserId = $dataCampaignUser->campaign_id;
        } else {
            $formatted_address = $dataMarketing->formatted_address;
            if ($formatted_address != '') {
                $addr1 = $formatted_address;
            } else {
                $addr1 = '';
            }

            $body = [];
            $body['email_address'] = $marketing_mail;
            $body['status_if_new'] = 'subscribed';
            $body['status'] = 'subscribed';
            $body['merge_fields']['FNAME'] = $dataMarketing->title;
            $body['merge_fields']['LNAME'] = '-';
            $body['merge_fields']['PHONE'] = '-';
            $body['merge_fields']['ADDRESS']['addr1'] = $addr1;
            $body['merge_fields']['ADDRESS']['city'] = '';
            $body['merge_fields']['ADDRESS']['state'] = '';
            $body['merge_fields']['ADDRESS']['zip'] = '';
            $body['merge_fields']['ADDRESS']['country'] = '';

            $data = json_encode($body);
            try {
                $mailchimpMarketing = marketingCampaignObj();
                $datacampaignUserId = $mailchimpMarketing->lists->addListMember($listId, $data, true);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                
            }

            $campaignUserId = $datacampaignUserId->id;
        }

        $tags = CampaignTag::latest()->get();
        $tags1 = [];
        foreach ($tags as $key => $tag) {
            $tags1['tags'][$key]['name'] = $tag->tag_name;
            if (in_array($tag->tag_id, $checked_tag)) {
                $tags1['tags'][$key]['status'] = "active";
            } else {
                $tags1['tags'][$key]['status'] = "inactive";
            }
        }

        if (!empty($checked_tag)) {
            $client = marketingCampaignObj();
            $response = $client->lists->updateListMemberTags($listId, $campaignUserId, $tags1);
        }

        return response()->json(['success' => '']);
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function editableField(Request $request) {
        $marketing = Marketing::find($request->pk);
        info($request->all());
        if (!is_null($marketing)) {
            if ($request->name == 'marketing_mail') {
                $marketing->marketing_mail = $request->value;
            } elseif ($request->name == 'marketing_address') {
                $marketing->marketing_address = $request->value;
            }

            $marketing->save();
            $listId = env('MAILCHIMP_LIST_ID');
            $mailchimp_key = env('MAILCHIMP_API_KEY');
            $MAILCHIMP_SERVER_PREFIX = env('MAILCHIMP_SERVER_PREFIX');

            $mailchimpMarketing = marketingCampaignObj();

            if ($request->name == 'marketing_mail') {

                $campaignUser = CampaignUser::where('email_address', $marketing->marketing_mail)
                        ->first();

                if (!empty($marketing->marketing_mail) && is_null($campaignUser)) {

                    $subscriber_hash = md5($marketing->marketing_mail);

                    $formatted_address = '';
                    if (isset($marketing->address) AND trim($marketing->address) != '') {
                        $formatted_address = $marketing->address;
                    }

                    if (isset($marketing->marketing_address) AND trim($marketing->marketing_address) != '') {
                        $formatted_address = $marketing->marketing_address;
                    }
                    $city = '';
                    $state = '';
                    $zip = '';
                    $country = '';

                    $body = [];
                    $body['email_address'] = trim($marketing->marketing_mail);
                    $body['status_if_new'] = 'subscribed';
                    $body['status'] = 'subscribed';
                    $body['merge_fields']['FNAME'] = $marketing->title;
                    $body['merge_fields']['LNAME'] = '-';
                    $body['merge_fields']['PHONE'] = '-';
                    $body['merge_fields']['ADDRESS']['addr1'] = $formatted_address;
                    $body['merge_fields']['ADDRESS']['city'] = $city;
                    $body['merge_fields']['ADDRESS']['state'] = $state;
                    $body['merge_fields']['ADDRESS']['zip'] = $zip;
                    $body['merge_fields']['ADDRESS']['country'] = $country;

                    $data = json_encode($body);
                    try {
                        $mailchimpMarketing->lists->addListMember($listId, $data, true);
                    } catch (\GuzzleHttp\Exception\ClientException $e) {
                        
                    }
                }
            } else {

                if (!empty($marketing->marketing_mail)) {
                    if ($request->name == 'marketing_address') {
                        
                    }
                }
            }

            createOrUpdateCampaign();
        }

        return response()->json('success');
    }

}
