<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client;
use Google\Service\Gmail;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        $client = new Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));
        $client->addScope(Gmail::GMAIL_SEND);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        // Generate the Google OAuth URL
        $authUrl = $client->createAuthUrl();

        // Redirect user to Google login
        return redirect()->away($authUrl);
    }

    public function handleGoogleCallback(Request $request)
    {
        $client = new Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));

        if ($request->has('code')) {
            $token = $client->fetchAccessTokenWithAuthCode($request->code);

            if (isset($token['error'])) {
                return response()->json(['error' => $token['error_description']], 400);
            }

            // ✅ Access & refresh tokens
            $accessToken = $token['access_token'];
            $refreshToken = $token['refresh_token'] ?? null;

            // Optional: store them somewhere (DB or .env for now)
            // file_put_contents(storage_path('google_token.json'), json_encode($token));

            return response()->json([
                'success' => true,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
            ]);
        }

        return response()->json(['error' => 'No code parameter found'], 400);
    }
}
