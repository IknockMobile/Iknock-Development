<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CMSController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function privacyPolicy()
    {
        return view('cms.privacyPolicy');
    }
}
