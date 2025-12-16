<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected string $messagingUrl ;
    protected string $credentialsPath ;

    public function __construct()
    {
        $this->credentialsPath = base_path(config('services.fcm.credentials_file'));

        $projectId = json_decode(file_get_contents($this->credentialsPath) , true )['project_id'];

        $this->messagingUrl = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
    }

    public function send(string $title , string $body , array $tokens , array $data = []): array
    {
        if(empty($tokens))
        {
            return ['success' => 0 , 'failed' => 0];
        }

        $accessToken = $this->getAccessToken();

        $success = 0 ;
        $failed = 0 ;

        foreach($tokens as $token)
        {
            $payload = [
                'message' => [
                    'token' => $token ,
                    'notification' => [
                        'title' => $title ,
                        'body' => $body ,
                    ],
                ]
            ];

            if (!empty($data)) {
                $payload['message']['data'] = $data;
            }

            $response = Http::withToken($accessToken)->acceptJson()->post($this->messagingUrl, $payload);

            if($response->successful())
            {
                $success++;
            }else{
                $failed++;

                Log::warning('FCM send failed' , [
                    'token' => $token ,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        }
        return compact('success' , 'failed');
    }

    protected function getAccessToken(): string
    {
        return Cache::remember('fcm_access_token' , now()->addMinutes(50), function () {

            $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
            $credentials = new ServiceAccountCredentials($scopes , $this->credentialsPath);
            $tokenData = $credentials->fetchAuthToken();

            return $tokenData['access_token'];
        });
    }
}
