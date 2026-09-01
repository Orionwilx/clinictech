<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Technician\StoreTechnicianRequest;
use App\Http\Requests\Technician\UpdateTechnicianRequest;
use App\Models\Technician;
use App\Services\TechnicianService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TechnicianController extends Controller
{
    public function __construct(private readonly TechnicianService $technicians) {}

    public function index(): View
    {
        $this->authorize('view technicians');

        $technicians = Technician::withTrashed()->latest()->paginate(15);

        return view('admin.technicians.index', compact('technicians'));
    }

    public function create(): View
    {
        $this->authorize('create technicians');

        return view('admin.technicians.create');
    }

    public function store(StoreTechnicianRequest $request): RedirectResponse
    {
        $this->technicians->create($request->validated());

        return redirect()->route('admin.technicians.index')
            ->with('status', 'Técnico creado correctamente.');
    }

    public function show(Technician $technician): View
    {
        $this->authorize('view technicians');

        return view('admin.technicians.show', compact('technician'));
    }

    public function edit(Technician $technician): View
    {
        $this->authorize('update technicians');

        return view('admin.technicians.edit', compact('technician'));
    }

    public function update(UpdateTechnicianRequest $request, Technician $technician): RedirectResponse
    {
        $this->technicians->update($technician, $request->validated());

        return redirect()->route('admin.technicians.index')
            ->with('status', 'Técnico actualizado correctamente.');
    }

    public function destroy(Technician $technician): RedirectResponse
    {
        $this->authorize('delete technicians');

        $this->technicians->delete($technician);

        return redirect()->route('admin.technicians.index')
            ->with('status', 'Técnico eliminado (recuperable).');
    }

    public function restore(int $id): RedirectResponse
    {
        $this->authorize('delete technicians');

        Technician::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.technicians.index')
            ->with('status', 'Técnico recuperado correctamente.');
    }
}
