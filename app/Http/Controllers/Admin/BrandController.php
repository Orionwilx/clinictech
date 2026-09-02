<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Brand\StoreBrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        $this->authorize('view brands');

        $brands = Brand::withCount(['models', 'equipment'])->orderBy('name')->paginate(20);

        return view('admin.brands.index', compact('brands'));
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
