<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function updateSignature(Request $request): RedirectResponse
    {
        $request->validate(['signature' => ['required', 'image', 'max:4096']]);

        $user = $request->user();

        // Purge previous signature if any.
        $user->signature?->purge();

        $file = $request->file('signature');
        $path = $file->store("signatures/{$user->id}", 'private');

        $user->uploads()->create([
            'collection' => 'signature',
            'disk' => 'private',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $user->id,
        ]);

        return Redirect::route('profile.edit')->with('status', 'signature-updated');
    }

    public function destroySignature(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->signature?->purge();

        return Redirect::route('profile.edit')->with('status', 'signature-deleted');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        // Autoeliminación: borrado definitivo (libera el email). El borrado
        // recuperable (soft delete) es solo para bajas gestionadas por admin.
        $user->forceDelete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
