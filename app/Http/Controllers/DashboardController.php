<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // Menampilkan dashboard
    public function viewDashboard(){
        return view('pages.dashboard');
    }

    // Menampilkan login page
    public function viewLogin(){
        return view('pages.login');
    }
}
