<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Brand\StoreBrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view brands');

        $filters = $request->only(['search']);

        $brands = Brand::withCount(['models', 'equipment'])
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where('name', 'like', "%$s%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.brands.index', compact('brands', 'filters'));
    }

    public function create(): View
    {
        $this->authorize('create brands');

        return view('admin.brands.create');
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        Brand::create($request->validated());

        return redirect()->route('admin.brands.index')
            ->with('status', 'Marca creada correctamente.');
    }

    public function edit(Brand $brand): View
    {
        $this->authorize('update brands');

        return view('admin.brands.edit', compact('brand'));
    }

    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $brand->update($request->validated());

        return redirect()->route('admin.brands.index')
            ->with('status', 'Marca actualizada correctamente.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $this->authorize('delete brands');

        $brand->delete();

        return redirect()->route('admin.brands.index')
            ->with('status', 'Marca eliminada.');
    }
}
