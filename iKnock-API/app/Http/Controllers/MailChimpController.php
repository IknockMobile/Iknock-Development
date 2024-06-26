<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MailChimpController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index()
    {
        $listId = env('MAILCHIMP_LIST_ID');

        // $mailchimp = new \Mailchimp(env('MAILCHIMP_API_KEY'));

        $mailchimpMarketing = new \MailchimpMarketing\ApiClient();

         $mailchimpMarketing = $mailchimpMarketing->setConfig([
                                        'apiKey' => '5cbc7fe46dc1f4c3694c3878f96af435',
                                        'server' => 'us21',
                                ]);


        // $response = $mailchimpMarketing->campaigns->setContent("0fe3c07ee7", ['html'=>'<h1>Hi new mail</h1>']);

        $response = $mailchimpMarketing->campaigns->getContent("0fe3c07ee7");

         // $response = $mailchimpMarketing->campaigns->list();
         // $response = $mailchimpMarketing->campaigns->get("60a713ee79");
         // $response = $mailchimp->campaigns->send('c4b4819101');
         // $response = $mailchimpMarketing->lists->getListMembersInfo($listId);
        dd($response);


        // *CREATE MAILS *
        // $campaign = $mailchimp->campaigns->create('regular', [
        //     'list_id' => $listId,
        //     'subject' => 'Example Mail new',
        //     'from_email' => 'dharmiktank128@gmail.com',
        //     'from_name' => 'Dharmik',
        //     'to_name' => 'Smit Marketing'

        // ], [
        //     'html' => '<h1>Dharmik Marketing</h1>',
        //     'text' => 'Dharmik Marketing'
        // ]);

        // * SEND MAILS *

        //* MEMBERS LIST GET *
        // $membersList = $mailchimp->lists->members($listId);
        
        // $list = $mailchimp->campaigns->getList();
        // dd($list);

        //* CREATE GROUP *
        // $groupCreate = $mailchimp->lists->interestGroupAdd($listId,'Super Dev');
            
        // // GROUP List
        // $groupList = $mailchimp->lists->interestGroupings($listId);
        
        //segments list
        $segments = $mailchimp->lists->segments($listId);

        dd($segments);

        $segment_opts = array(
                        'match' => 'any', // or 'all' or 'none'
                        'conditions' => array (
                            array(
                                'condition_type' => 'Interests', // note capital I
                                'field' => 'interests-565', // ID of interest category
                                'op' => 'one', // or interestcontainsall, interestcontainsnone
                                'value' => array (
                                    '439d9eac0f',  // ID of interest in that category
                                    'caa36c0a7d' // ID of another interest in that category
                                )
                            )

                          )
                        );

        $opts = ['type'=>'saved','name'=>'super_cops','segment_opts'=>$segment_opts];


        $segment = $mailchimp->lists->segmentAdd($listId,$opts);

        dd($segment);
    }
}
