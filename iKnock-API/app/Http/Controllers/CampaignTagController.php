<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CampaignTag;

class CampaignTagController extends Controller {

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function index(Request $request) {
        $mailchimpMarketing = marketingCampaignObj();

        $list_id = env('MAILCHIMP_LIST_ID');

        $response = $mailchimpMarketing->lists->tagSearch($list_id);
        $tags = $response->tags;

        if (!empty($tags)) {
            foreach ($tags as $key => $tag) {
                $campaignTag = CampaignTag::where('tag_id', $tag->id)
                        ->first();

                $input['tag_id'] = $tag->id;
                $input['tag_name'] = $tag->name;

                if (is_null($campaignTag)) {
                    CampaignTag::create($input);
                } else {
                    $campaignTag->update($input);
                }
            }
        }

        $mailchimpMarketing = marketingCampaignObj();

        $list_id = env('MAILCHIMP_LIST_ID');
        $subscriber_hash = md5('info@lighthousesolutions.com');

        $response = $mailchimpMarketing->lists->getListMemberTags($list_id, $subscriber_hash);
        $tags = $response->tags;

        if (!empty($tags)) {
            foreach ($tags as $key => $tag) {
                $campaignTag = CampaignTag::where('tag_id', $tag->id)
                        ->first();

                $input['tag_id'] = $tag->id;
                $input['tag_name'] = $tag->name;

                if (is_null($campaignTag)) {
                    CampaignTag::create($input);
                } else {
                    $campaignTag->update($input);
                }
            }
        }

        $tags = CampaignTag::latest()->get();

        return view('tenant.campaignTag.index', compact('tags'));
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function create() {
        return view('tenant.campaignTag.create');
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
            'name' => 'required',
        ]);

        $mailchimpMarketing = marketingCampaignObj();

        $list_id = env('MAILCHIMP_LIST_ID');

        $subscriber_hash = md5('info@lighthousesolutions.com');

        $response = $mailchimpMarketing->lists->updateListMemberTags($list_id, $subscriber_hash, [
            "tags" => [["name" => $request->name, 'status' => 'active']],
        ]);

        return redirect()->route('tenant.campaign.tag.index');
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function edit(CampaignTag $campaign_tag) {
        return view('tenant.campaignTag.edit', compact('campaign_tag'));
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function update(CampaignTag $campaign_tag, Request $request) {
        $request->validate([
            'name' => 'required',
        ]);

        $mailchimpMarketing = marketingCampaignObj();

        $list_id = env('MAILCHIMP_LIST_ID');

        $subscriber_hash = md5('info@lighthousesolutions.com');

        $response = $mailchimpMarketing->lists->updateListMemberTags($list_id, $subscriber_hash, [
            "tags" => [["name" => $request->name]],
        ]);

        $campaignTag = CampaignTag::where('id', $campaign_tag->id)
                ->first();
        
        if (isset($campaignTag->id)) {
            $campaignTag->is_show_marketing = $request->is_show_marketing;
            $campaignTag->update();
        }


        return redirect()->route('tenant.campaign.tag.index');
    }

    /**
     * write code for.
     *
     * @param  \Illuminate\Http\Request  
     * @return \Illuminate\Http\Response
     * @author <>
     */
    public function delete(CampaignTag $campaign_tag) {
        $mailchimpMarketing = marketingCampaignObj();

        $list_id = env('MAILCHIMP_LIST_ID');

        $subscriber_hash = md5('info@lighthousesolutions.com');

        $response = $mailchimpMarketing->lists->updateListMemberTags($list_id, $subscriber_hash, [
            "tags" => [["name" => $campaign_tag->tag_name, 'status' => 'inactive']],
        ]);

        return response()->json(['success' => true, 'code' => 200, 'message' => 'Campaign Tag delete successfully']);
    }

}
