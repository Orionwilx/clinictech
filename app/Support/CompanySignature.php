<?php

namespace App\Support;

use App\Models\Upload;

class CompanySignature
{
    public static function current(): ?Upload
    {
        return Upload::where('collection', 'company_signature')->latest()->first();
    }

    public static function base64(): ?string
    {
        $upload = static::current();
        if (! $upload) {
            return null;
        }

        $path = $upload->absolutePath();

        if (! file_exists($path)) {
            return null;
        }

        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };

        return "data:{$mime};base64,".base64_encode((string) file_get_contents($path));
    }

    public static function absolutePath(): ?string
    {
        $upload = static::current();
        if (! $upload) {
            return null;
        }

        return $upload->absolutePath();
    }
}
