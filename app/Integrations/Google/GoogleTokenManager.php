<?php

namespace App\Integrations\Google;

use Google\Client;
use Illuminate\Support\Facades\DB;

class GoogleTokenManager
{
    public function getValidAccessToken(int $userId): ?string
    {
        $record = DB::connection('mysql_auth')
            ->table('guru_oauth_tokens')
            ->where('id_user', $userId)
            ->first();

        if (!$record || !$record->refresh_token) {
            return null;
        }

        $isExpired = !$record->token_expiry
            || now()->gte(\Carbon\Carbon::parse($record->token_expiry));

        if (!$isExpired && $record->access_token) {
            return $record->access_token;
        }

        return $this->refreshAccessToken($userId, $record->refresh_token);
    }

    protected function refreshAccessToken(int $userId, string $refreshToken): ?string
    {
        $client = new Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));

        $newToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);

        if (isset($newToken['error'])) {
            DB::connection('mysql_auth')
                ->table('guru_oauth_tokens')
                ->where('id_user', $userId)
                ->delete();
            return null;
        }

        $expiry = isset($newToken['expires_in'])
            ? date('Y-m-d H:i:s', time() + (int) $newToken['expires_in'] - 60)
            : null;

        DB::connection('mysql_auth')->table('guru_oauth_tokens')
            ->where('id_user', $userId)
            ->update([
                'access_token' => $newToken['access_token'],
                'token_expiry' => $expiry,
                'updated_at'   => now(),
            ]);

        return $newToken['access_token'];
    }
}
