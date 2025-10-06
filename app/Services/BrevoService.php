<?php

namespace App\Services;

use Brevo\Client\Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Model\SendSmtpEmail;
use GuzzleHttp\Client as Guzzle;

class BrevoService
{
    private TransactionalEmailsApi $emails;
    private string $fromEmail;
    private string $fromName;

    public function __construct()
    {
        // Get API key and mail details directly from .env
        $apiKey = env('BREVO_API_KEY');
        $this->fromEmail = env('MAIL_FROM_ADDRESS', 'noreply@shamandorascout.com');
        $this->fromName  = env('MAIL_FROM_NAME', 'shamandorascout');

        $config = Configuration::getDefaultConfiguration()
            ->setApiKey('api-key', $apiKey);

        $client = new Guzzle();
        $this->emails = new TransactionalEmailsApi($client, $config);
    }

    public function sendTempPassword(string $toEmail, string $toName, string $personId, string $password)
    {
        $subject = 'كلمة سر مؤقتة لحسابك';
        $html = "
            <p>أهلاً {$toName},</p>
            <p>رقمك التعريفي: <b>{$personId}</b></p>
            <p>كلمة السر المؤقتة: <b>{$password}</b></p>
            <p>يرجى تغييرها عند أول تسجيل دخول.</p>
        ";

        $mail = new SendSmtpEmail([
            'subject' => $subject,
            'sender'  => ['email' => $this->fromEmail, 'name' => $this->fromName],
            'to'      => [['email' => $toEmail, 'name' => $toName]],
            'htmlContent' => $html,
        ]);

        return $this->emails->sendTransacEmail($mail);
    }
}