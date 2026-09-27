<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        return Auth::check() ? redirect()->route('dashboard') : view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($request->boolean('remember')) {
            config(['session.lifetime' => 60 * 24 * 30, 'session.expire_on_close' => false]);
        }

        if (! Auth::attempt($credentials)) {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'Email atau password tidak sesuai.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang kembali.');
    }

    /**
     * Redirect an administrator to Keycloak's OpenID Connect login screen.
     */
    public function redirectToKeycloak()
    {
        return Socialite::driver('keycloak')->redirect();
    }

    /**
     * Exchange the authorization code and establish the normal Laravel session.
     */
    public function handleKeycloakCallback(Request $request)
    {
        try {
            $ssoUser = Socialite::driver('keycloak')->user();
            $ssoId = $ssoUser->getId();
            $email = $ssoUser->getEmail();
            $name = $ssoUser->getName() ?: ($ssoUser->getNickname() ?: 'Pengguna SSO');

            if (! $email) {
                $email = ($ssoUser->getNickname() ?: 'user_'.Str::random(6)).'@technolife.local';
            }
            $email = strtolower(trim($email));

            // Deteksi role dari token Keycloak (client: membership)
            $detectedRole = $this->extractClientRole($ssoUser);

            // Cari atau buat admin baru secara otomatis
            $admin = Admin::query()
                ->when($ssoId, fn ($query) => $query->where('sso_id', $ssoId))
                ->orWhereRaw('LOWER(email) = ?', [$email])
                ->first();

            if (! $admin) {
                // Auto-create jika pertama kali login
                $admin = Admin::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => bcrypt(Str::random(24)),
                    'role' => $detectedRole,
                    'sso_id' => $ssoId,
                ]);
            } else {
                // Link sso_id dan update role terbaru dari Keycloak
                if (! $admin->sso_id && $ssoId) {
                    $admin->sso_id = $ssoId;
                }
                if ($admin->role !== $detectedRole) {
                    $admin->role = $detectedRole;
                }
                $admin->save();
            }

            Auth::login($admin);
            $request->session()->regenerate();

            // Simpan id_token untuk federated logout
            $idToken = $ssoUser->accessTokenResponseBody['id_token'] ?? null;
            if ($idToken) {
                session(['keycloak_id_token' => $idToken]);
            }

            return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang kembali.');
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('login')->withErrors([
                'email' => 'Login SSO gagal: '.$exception->getMessage(),
            ]);
        }
    }

    public function logout(Request $request)
    {
        $isSso = $request->user()?->sso_id !== null;
        $idToken = session('keycloak_id_token');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($isSso) {
            $baseUrl = rtrim(config('services.keycloak.base_url', 'http://192.168.1.19:8080'), '/');
            $realm = config('services.keycloak.realms', 'technolife');
            $clientId = config('services.keycloak.client_id', 'membership');
            $redirectUri = urlencode(route('login'));

            $keycloakLogoutUrl = "{$baseUrl}/realms/{$realm}/protocol/openid-connect/logout?post_logout_redirect_uri={$redirectUri}&client_id={$clientId}";

            if ($idToken) {
                $keycloakLogoutUrl .= "&id_token_hint={$idToken}";
            }

            return redirect()->away($keycloakLogoutUrl);
        }

        return redirect()->route('login');
    }

    /**
     * Ekstrak role untuk client membership dari JWT token atau userinfo.
     */
    private function extractClientRole($ssoUser): string
    {
        $roles = [];
        $clientId = config('services.keycloak.client_id', 'membership');

        // 1. Cek dari array userinfo
        if (! empty($ssoUser->user['resource_access'][$clientId]['roles'])) {
            $roles = $ssoUser->user['resource_access'][$clientId]['roles'];
        } elseif (! empty($ssoUser->user['roles'])) {
            $roles = $ssoUser->user['roles'];
        }

        // 2. Decode JWT Access Token jika ada
        if (empty($roles) && ! empty($ssoUser->token)) {
            $parts = explode('.', $ssoUser->token);
            if (count($parts) >= 2) {
                $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                if (! empty($payload['resource_access'][$clientId]['roles'])) {
                    $roles = $payload['resource_access'][$clientId]['roles'];
                } elseif (! empty($payload['realm_access']['roles'])) {
                    $roles = $payload['realm_access']['roles'];
                }
            }
        }

        $rolesUpper = array_map('strtoupper', (array) $roles);

        if (in_array('ADMIN', $rolesUpper) || in_array('MANAGEMENT', $rolesUpper)) {
            return 'admin';
        }

        return 'cashier';
    }
}
