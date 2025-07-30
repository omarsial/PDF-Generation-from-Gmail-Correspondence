<?php

namespace App\Services;

use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Illuminate\Support\Facades\Log;

class GmailService
{
    public $client;
    protected $service;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect'));
        $this->client->addScope(Gmail::GMAIL_READONLY);
        $this->client->setAccessType('offline');
        $this->client->setApprovalPrompt('force');
        
        $this->service = new Gmail($this->client);
    }

    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function setAccessToken($token)
    {
        $this->client->setAccessToken($token);
    }

    public function getThreadsBetween($email1, $email2, $maxResults = 35)
    {

        // Verify we have an access token
        if (!$this->client->getAccessToken()) {
            throw new \Exception('No access token available');
        }

        // Verify token is not expired
        if ($this->client->isAccessTokenExpired()) {
            throw new \Exception('Access token expired');
        }

        try {
            $query = "(from:{$email1} to:{$email2}) OR (from:{$email2} to:{$email1})";
            $optParams = [
                'maxResults' => $maxResults,
                'q' => $query
            ];
            
            // Make the API call
            $response = $this->service->users_threads->listUsersThreads('me', $optParams);
            
            // Log the full response for debugging
            Log::debug('Gmail API Threads Response', [
                'response' => $response,
                'threads_count' => count($response->getThreads() ?? []),
                'email1' => $email1,
                'email2' => $email2
            ]);

            if (empty($response->getThreads())) {
                // Try a more general query to verify API is working
                $testQuery = $this->service->users_threads->listUsersThreads('me', ['maxResults' => 1]);
                Log::debug('Test query results', ['test' => $testQuery]);
                
                throw new \Exception("No threads found between {$email1} and {$email2}");
            }

            $fullThreads = [];
            foreach ($response->getThreads() as $thread) {
                $fullThreads[] = $this->service->users_threads->get('me', $thread->getId());
            }
            
            return $fullThreads;
            
        } catch (\Exception $e) {
            Log::error('Gmail API Error: '.$e->getMessage());
            throw $e;
        }
    }
}
