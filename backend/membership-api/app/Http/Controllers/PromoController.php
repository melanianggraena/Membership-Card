<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PromoController extends Controller
{
    public function index(Request $r) { $promos = Promo::query()->when($r->status, fn($q,$v) => $q->where('status',$v))->latest()->paginate(10)->withQueryString(); return view('promos.index', compact('promos')); }
    public function create() { return view('promos.form'); }
    public function edit(Promo $promo) { return view('promos.form', compact('promo')); }
    public function store(Request $r) { Promo::create($this->validated($r)); return redirect()->route('promos.index')->with('success', 'Promo berhasil ditambahkan.'); }
    public function update(Request $r, Promo $promo) { $data = $this->validated($r); if (isset($data['image_path']) && $promo->image_path) Storage::disk('public')->delete($promo->image_path); $promo->update($data); return redirect()->route('promos.index')->with('success', 'Promo berhasil diperbarui.'); }
    public function destroy(Promo $promo) { if ($promo->image_path) Storage::disk('public')->delete($promo->image_path); $promo->delete(); return back()->with('success', 'Promo berhasil dihapus.'); }
    private function validated(Request $r): array { $data = $r->validate(['title'=>'required|string|max:255','image'=>'nullable|image|max:3072','description'=>'required|string','terms'=>'nullable|string','start_date'=>'required|date','end_date'=>'required|date|after_or_equal:start_date','status'=>['required',Rule::in(['active','inactive'])]]); if ($r->hasFile('image')) $data['image_path'] = $r->file('image')->store('promos','public'); unset($data['image']); return $data; }
}
