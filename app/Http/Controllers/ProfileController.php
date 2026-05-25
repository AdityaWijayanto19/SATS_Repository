<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Services\ProfileService;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService
    ) {}

    public function edit()
    {
        $data = $this->profileService->getProfileData(Auth::user());

        return view('pages.profile.edit', $data);
    }

    public function update(UpdateProfileRequest $request)
    {
        $this->profileService->updateProfile(Auth::user(), $request->validated());

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }
}
