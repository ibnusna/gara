<?php

namespace App\Integrations\Google;

use Google\Client;
use Illuminate\Support\Facades\DB;

class GoogleOAuthService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(route('guru.google.callback'));
        $this->client->setScopes([
            'https://www.googleapis.com/auth/drive.readonly',
            'https://www.googleapis.com/auth/drive.file',
            'https://www.googleapis.com/auth/documents.readonly',
            'email',
        ]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    public function getAuthUrl(): string
    {
        if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
            throw new \Exception('Konfigurasi GOOGLE_CLIENT_ID atau GOOGLE_CLIENT_SECRET belum diset di file .env Anda.');
        }
        return $this->client->createAuthUrl();
    }

    public function exchangeCodeForTokens(string $code): array
    {
        $this->client->fetchAccessTokenWithAuthCode($code);
        $token = $this->client->getAccessToken();

        return [
            'access_token'  => $token['access_token'] ?? null,
            'refresh_token' => $token['refresh_token'] ?? null,
            'token_expiry'  => isset($token['expires_in'])
                ? date('Y-m-d H:i:s', time() + (int) $token['expires_in'] - 60)
                : null,
        ];
    }

    public function saveTokens(int $userId, string $googleEmail, array $tokens): void
    {
        DB::connection('mysql_auth')->table('guru_oauth_tokens')->updateOrInsert(
            ['id_user' => $userId],
            [
                'google_email'  => $googleEmail,
                'access_token'  => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'token_expiry'  => $tokens['token_expiry'],
                'updated_at'    => now(),
            ]
        );

        
        
        
        
        session(['google_oauth_connected_' . $userId => true]);
    }

    public function getEmailFromCode(string $code): ?string
    {
        $this->client->fetchAccessTokenWithAuthCode($code);
        $oauth2 = new \Google\Service\Oauth2($this->client);
        $userInfo = $oauth2->userinfo->get();
        return $userInfo->getEmail();
    }

    public function revokeAndDelete(int $userId): void
    {
        $record = DB::connection('mysql_auth')
            ->table('guru_oauth_tokens')
            ->where('id_user', $userId)
            ->first();

        if ($record && $record->access_token) {
            $this->client->setAccessToken(['access_token' => $record->access_token]);
            $this->client->revokeToken();
        }

        DB::connection('mysql_auth')
            ->table('guru_oauth_tokens')
            ->where('id_user', $userId)
            ->delete();

        
        session()->forget('google_oauth_connected_' . $userId);
    }

    public function getConnectedStatus(int $userId): ?object
    {
        
        
        
        
        
        $record = DB::connection('mysql_auth')
            ->table('guru_oauth_tokens')
            ->where('id_user', $userId)
            ->whereNotNull('refresh_token')
            ->first();

        if (!$record) {
            
            session()->forget('google_oauth_connected_' . $userId);
            return null;
        }

        
        
        
        
        
        if (!session('google_oauth_connected_' . $userId)) {
            session(['google_oauth_connected_' . $userId => true]);
        }

        return $record;
    }
}
