<?php
namespace App\Services;

use App\Models\User;
use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendar;

class GoogleCalendarApi
{
    public static function getCalendarService(User $user)
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setScopes(['https://www.googleapis.com/auth/calendar']);

        if (empty($user->google_token)) {
            throw new \Exception('Google token is not available for this user.');
        }

        $client->setAccessToken($user->google_token);

        if ($client->isAccessTokenExpired() && $user->google_refresh_token) {
            $newToken = $client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
            if (isset($newToken['error'])) {
                throw new \Exception('Error refreshing token: ' . $newToken['error']);
            }
            if (! isset($newToken['refresh_token'])) {
                $newToken['refresh_token'] = $user->google_refresh_token;
            }
            $user->google_token = $newToken;
            $user->save();
            $client->setAccessToken($newToken);
        }

        return new GoogleCalendar($client);
    }
}
