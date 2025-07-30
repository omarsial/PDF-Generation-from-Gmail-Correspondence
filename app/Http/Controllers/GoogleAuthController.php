<?php

namespace App\Http\Controllers;

use App\Services\GmailService;
use Illuminate\Http\Request;

class GoogleAuthController extends Controller
{
    protected $gmail;

    public function __construct(GmailService $gmail)
    {
        $this->gmail = $gmail;
    }

    public function redirectToGoogle()
    {
        return redirect()->away($this->gmail->getAuthUrl());
    }

    public function handleGoogleCallback(Request $request)
    {
        $code = $request->get('code');
        
        $this->gmail->client->fetchAccessTokenWithAuthCode($code);
        $token = $this->gmail->client->getAccessToken();
        $accessToken = $token['access_token'];
        session(['gmail_token' => $accessToken]);
        
        return redirect('/');
    }
}
