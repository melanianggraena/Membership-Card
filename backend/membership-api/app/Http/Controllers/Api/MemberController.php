<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    private function ok(mixed $data, string $message = 'Data berhasil diambil'): JsonResponse { return response()->json(['success' => true, 'message' => $message, 'data' => $data]); }
    private function member(Request $request): array { $m = $request->user(); return ['id' => $m->id, 'member_code' => $m->member_code, 'full_name' => $m->full_name, 'phone' => $m->phone, 'email' => $m->email, 'balance' => (float) $m->balance, 'status' => $m->status, 'last_used' => $m->last_used, 'expired_at' => $m->expired_at]; }
    public function profile(Request $r) { return $this->ok($this->member($r)); }
    public function updateProfile(Request $r) { $m = $r->user(); $m->update($r->validate(['full_name' => ['required','string','max:255'], 'email' => ['nullable','email',Rule::unique('members')->ignore($m)], 'phone' => ['required','string','max:30',Rule::unique('members')->ignore($m)]])); return $this->ok($this->member($r), 'Profil berhasil diperbarui.'); }
    public function membership(Request $r) { return $this->ok($this->member($r)); }
    public function balance(Request $r) { return $this->ok(['balance' => (float) $r->user()->balance]); }
    public function home(Request $r) { return $this->ok(['member' => $this->member($r), 'promos' => Promo::active()->latest('start_date')->get()]); }
    public function transactions(Request $r) { return $this->ok($r->user()->transactions()->with('room:id,room_name')->latest()->paginate(15)); }
    public function transaction(Request $r, int $id) { return $this->ok($r->user()->transactions()->with('room:id,room_name')->findOrFail($id)); }
    public function accesses(Request $r) { return $this->ok($r->user()->accessHistories()->with('room:id,room_name')->latest('scanned_at')->paginate(15)); }
    public function access(Request $r, int $id) { return $this->ok($r->user()->accessHistories()->with('room:id,room_name')->findOrFail($id)); }
    public function promos() { return $this->ok(Promo::active()->latest('start_date')->get()); }
    public function promo(int $id) { return $this->ok(Promo::active()->findOrFail($id)); }
}
