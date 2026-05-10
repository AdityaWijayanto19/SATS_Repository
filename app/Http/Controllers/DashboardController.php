<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private function getViewByRole($page)
    {
        $role = Auth::user()->role;

        if ($role === 'nakes') {
            return "pages.nakes.$page";
        }

        if ($role === 'dokter') {
            return "pages.dokter.$page";
        }

        if ($role === 'superadmin') {
            return "pages.superadmin.$page";
        }

        abort(403);
    }

    // Menampilkan halaman dashboard
    public function viewDashboardPage(){
        return view($this->getViewByRole('dashboard'));
    }

    // Menampilkan halaman manajemen alat (superadmin)
    public function viewManajemenAlatPage(){
        return view($this->getViewByRole('manajemen-alat'));
    }

    // Menampilkan halaman manajemen user (superadmin)
    public function viewManajemenUserPage(){
        return view($this->getViewByRole('manajemen-user'));
    }

    // Menampilkan halaman input data pasien
    public function viewInputDataPasienPage(){
        return view($this->getViewByRole('inputdata'));
    }

    // Menampilkan halaman laporan
    public function viewLaporanPage(){
        return view($this->getViewByRole('laporan'));
    }

    // Menampilkan halaman login
    public function viewLoginPage(){
        return view('pages.login');
    }
}
