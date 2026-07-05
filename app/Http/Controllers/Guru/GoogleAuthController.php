<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Integrations\Google\GoogleOAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoogleAuthController extends Controller
{
    protected GoogleOAuthService $oauthService;

    public function __construct()
    {
        $this->oauthService = new GoogleOAuthService();
    }

    public function redirect()
    {
        try {
            $authUrl = $this->oauthService->getAuthUrl();
            return redirect()->away($authUrl);
        } catch (\Exception $e) {
            return redirect()->route('guru.profile.index')
                ->with('error', $e->getMessage());
        }
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('guru.profile.index')
                ->with('error', 'Koneksi Google dibatalkan.');
        }

        $code = $request->input('code');

        try {
            $oauthService = new GoogleOAuthService();
            $tokens = $oauthService->exchangeCodeForTokens($code);

            $client = new \Google\Client();
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setAccessToken(['access_token' => $tokens['access_token'], 'token_type' => 'Bearer']);

            $oauth2 = new \Google\Service\Oauth2($client);
            $userInfo = $oauth2->userinfo->get();
            $googleEmail = $userInfo->getEmail();

            $oauthService->saveTokens(Auth::id(), $googleEmail, $tokens);

            return redirect()->route('guru.profile.index')
                ->with('success', 'Akun Google berhasil dihubungkan: ' . $googleEmail);
        } catch (\Exception $e) {
            return redirect()->route('guru.profile.index')
                ->with('error', 'Gagal menghubungkan akun Google. Silakan coba lagi.');
        }
    }

    public function disconnect()
    {
        try {
            $this->oauthService->revokeAndDelete(Auth::id());
            return redirect()->route('guru.profile.index')
                ->with('success', 'Akun Google berhasil diputus.');
        } catch (\Exception $e) {
            return redirect()->route('guru.profile.index')
                ->with('error', 'Gagal memutus koneksi Google.');
        }
    }

    public function getPickerToken()
    {
        $userId = Auth::id();

        
        
        
        
        $this->oauthService->getConnectedStatus($userId);

        if (!session('google_oauth_connected_' . $userId)) {
            return response()->json(['error' => 'Akun Google belum dihubungkan. Silakan hubungkan terlebih dahulu di halaman Profil.'], 401);
        }

        $tokenManager = new \App\Integrations\Google\GoogleTokenManager();
        $accessToken = $tokenManager->getValidAccessToken($userId);

        if (!$accessToken) {
            
            
            session()->forget('google_oauth_connected_' . $userId);
            return response()->json(['error' => 'Token tidak valid. Silakan hubungkan ulang akun Google di halaman Profil.'], 401);
        }

        return response()->json(['access_token' => $accessToken]);
    }

    public function resolveForm(Request $request)
    {
        $formId = $request->input('form_id');
        if (!$formId) {
            return response()->json(['error' => 'Form ID is required.'], 400);
        }

        $url = "https://docs.google.com/forms/d/{$formId}/viewform";

        try {
            $context = stream_context_create([
                'http' => [
                    'follow_location' => false,
                    'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
                ]
            ]);

            $headers = get_headers($url, false, $context);
            if ($headers) {
                foreach ($headers as $header) {
                    if (stripos($header, 'Location:') === 0) {
                        $location = trim(substr($header, 9));
                        
                        if (stripos($location, '?') === false) {
                            $location .= '?usp=dialog';
                        }
                        
                        return response()->json(['resolved_url' => $location]);
                    }
                }
            }

            return response()->json(['resolved_url' => $url . '?usp=dialog']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to resolve form URL.'], 500);
        }
    }
}
