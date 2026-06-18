<?php

namespace Chronos\Http\Controllers\Settings;

use Chronos\Http\Controllers\Controller;

class LanguagesController extends Controller
{

    public function index()
    {
        return view('chronos::settings.languages.index');
    }

}
