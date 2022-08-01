<?php

namespace Chronos\Http\Controllers;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{

    public function index()
    {
        return view('chronos::dashboard.index');
    }

}
