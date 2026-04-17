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

    // Function untuk proses login
    public function login(Request $request)
    {
        // Validasi sederhana
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Sementara dummy login
        if ($request->email === 'admin@gmail.com' && $request->password === '123') {
            
            // Redirect ke dashboard
            return redirect()->route('dashboard');
        }

        // Kondisi kalau gagal
        return back()->withErrors([
            'email' => 'Email atau password salah'
        ])->withInput();
    }
}
