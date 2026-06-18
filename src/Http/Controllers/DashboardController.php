<?php

namespace Chronos\Http\Controllers;

use Chronos\Http\Controllers\Controller;

class DashboardController extends Controller
{

    public function index()
    {
        return view('chronos::dashboard.index');
    }

}
