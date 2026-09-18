<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Upload;
use App\Support\CompanySignature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanySignatureController extends Controller
{
    public function edit(): View
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $upload = CompanySignature::current();
        $base64 = CompanySignature::base64();

        return view('admin.company_signature.edit', compact('upload', 'base64'));
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $request->validate(['signature' => ['required', 'image', 'max:4096']]);

        // Purge any existing company signature.
        Upload::where('collection', 'company_signature')->get()->each->purge();

        $file = $request->file('signature');
        $path = $file->store('signatures/company', 'private');

        auth()->user()->uploads()->create([
            'collection' => 'company_signature',
            'disk' => 'private',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()->route('admin.company-signature.edit')
            ->with('status', 'Firma de empresa actualizada correctamente.');
    }

    public function destroy(): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        Upload::where('collection', 'company_signature')->get()->each->purge();

        return redirect()->route('admin.company-signature.edit')
            ->with('status', 'Firma de empresa eliminada.');
    }
}
