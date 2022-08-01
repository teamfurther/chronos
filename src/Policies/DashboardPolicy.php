<?php

namespace Chronos\Policies;

use Illuminate\Support\Facades\Auth;

class DashboardPolicy {

    public function index() {
        return Auth::user()->role()->hasPermission('view_dashboard');
    }

}