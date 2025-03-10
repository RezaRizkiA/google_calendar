<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendar;
use App\Models\User;

class GoogleCalendarApi
{
    public static function getCalendarService(User $user)
    {
        // Buat instance Google Client
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        
        // Set token user
        $client->setAccessToken($user->google_token);

        // Jika token kadaluarsa, refresh menggunakan refresh_token 
        if ($client->isAccessTokenExpired() && $user->google_refresh_token) {
            $client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
            // Update token user di DB
            $user->google_token = $client->getAccessToken()['access_token'];
            $user->save();
        }

        // Buat Calendar Service
        return new GoogleCalendar($client);
    }
}
