<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Technician;

abstract class TechnicianPanelController extends Controller
{
    private ?Technician $resolved = null;

    protected function technician(): Technician
    {
        return $this->resolved ??= auth()->user()->technician;
    }
}
