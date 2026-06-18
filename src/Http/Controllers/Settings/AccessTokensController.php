<?php

namespace Chronos\Http\Controllers\Settings;

use Chronos\Http\Controllers\Controller;

class AccessTokensController extends Controller
{

    public function index()
    {
        return view('chronos::settings.access_tokens.index');
    }

}
