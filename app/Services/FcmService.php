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

    public function sendToToken($token, $title, $body, ?string $collapseKey = null)
    {
        $message = self::notificationMessage($title, $body, $collapseKey);
        $message['token'] = $token;

        return $this->messaging->send($message);
    }

    public function sendToMultiple($tokens, $title, $body, ?string $collapseKey = null)
    {
        return $this->messaging->sendMulticast(
            self::notificationMessage($title, $body, $collapseKey),
            $tokens
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function notificationMessage(string $title, string $body, ?string $collapseKey = null): array
    {
        $message = [
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
        ];

        if ($collapseKey === null || $collapseKey === '') {
            return $message;
        }

        $message['android'] = ['collapse_key' => $collapseKey];
        $message['apns'] = [
            'headers' => [
                'apns-collapse-id' => $collapseKey,
            ],
        ];

        return $message;
    }
}
