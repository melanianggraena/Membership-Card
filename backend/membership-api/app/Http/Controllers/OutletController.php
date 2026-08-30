<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OutletController extends Controller
{
    public function index(Request $request): View
    {
        $outlets = Outlet::withCount('transactions')
            ->when($request->search, fn ($query, $search) => $query->where(fn ($query) => $query->where('outlet_code', 'like', "%{$search}%")->orWhere('outlet_name', 'like', "%{$search}%")))
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest()->paginate(10)->withQueryString();
        return view('outlets.index', compact('outlets'));
    }

    public function show(Outlet $outlet): View
    {
        $outlet->loadCount('transactions');
        $transactions = $outlet->transactions()->with(['member', 'admin'])->latest()->paginate(10);
        return view('outlets.show', compact('outlet', 'transactions'));
    }

    public function store(Request $request): RedirectResponse
    {
        Outlet::create($this->validated($request));
        return back()->with('success', 'Outlet berhasil ditambahkan.');
    }

    public function update(Request $request, Outlet $outlet): RedirectResponse
    {
        $outlet->update($this->validated($request, $outlet));
        return back()->with('success', 'Outlet berhasil diperbarui.');
    }

    private function validated(Request $request, ?Outlet $outlet = null): array
    {
        return $request->validate([
            'outlet_code' => ['required', 'string', 'max:30', Rule::unique('outlets')->ignore($outlet)],
            'outlet_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }
}
