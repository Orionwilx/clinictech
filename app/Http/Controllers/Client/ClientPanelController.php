<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;

abstract class ClientPanelController extends Controller
{
    private ?Client $resolvedClient = null;

    protected function client(): Client
    {
        return $this->resolvedClient ??= auth()->user()->client;
    }
}
