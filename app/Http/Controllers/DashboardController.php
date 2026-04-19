<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // Menampilkan halaman dashboard
    public function viewDashboardPage(){
        return view('pages.dashboard');
    }

    // Menampilkan halaman input data pasien
    public function viewInputDataPasienPage(){
        return view('pages.inputdata');
    }

    // Menampilkan halaman laporan
    public function viewLaporanPage(){
        return view('pages.laporan');
    }

    // Menampilkan halaman login
    public function viewLoginPage(){
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
