<?php

namespace App\Services;

use Kreait\Firebase\Factory;

class FcmService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(storage_path('app/firebase.json'));

        $this->messaging = $factory->createMessaging();
    }

    public function sendToToken($token, $title, $body)
    {
        $message = [
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
        ];

        return $this->messaging->send($message);
    }

    public function sendToMultiple($tokens, $title, $body)
    {
        $message = [
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
        ];

        return $this->messaging->sendMulticast($message, $tokens);
    }
}