<?php

namespace App\Services;

use Google\Client;
use Google\Service\Gmail;

class GmailService
{
    public function getClient()
    {
        $client = new Client();
        $client->setApplicationName('Gmail PDF App');
        $client->setScopes([Gmail::GMAIL_READONLY]);
        $client->setAuthConfig(storage_path('app/credentials.json'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // token.json stores the user token
        $tokenPath = storage_path('app/token.json');
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // Refresh or get new token
        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Redirect to OAuth consent screen
                printf("Open this link: %s\n", $client->createAuthUrl());
                print('Enter verification code: ');
                $authCode = trim(fgets(STDIN));
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);
                session(['gmail_token' => $accessToken]);
                file_put_contents($tokenPath, json_encode($accessToken));
            }
        }

        return new Gmail($client);
    }

    public function getMessagesBetween($from, $to)
    {
        $service = $this->getClient();
        $query = "from:$from to:$to OR from:$to to:$from";

        $messages = $service->users_messages->listUsersMessages('me', ['q' => $query]);
        $threads = [];

        foreach ($messages->getMessages() as $msg) {
            $message = $service->users_messages->get('me', $msg->getId(), ['format' => 'full']);
            $parts = $message->getPayload()->getParts();
            $body = $parts ? base64_decode($parts[0]['body']['data']) : '';

            $threads[] = $body;
        }

        return $threads;
    }
}
