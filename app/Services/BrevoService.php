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
    private string $replyToEmail;
    private string $replyToName;

    public function __construct()
    {
        $apiKey = env('BREVO_API_KEY');
        $this->fromEmail   = env('MAIL_FROM_ADDRESS', 'noreply@shamandorascout.com');
        $this->fromName    = env('MAIL_FROM_NAME',    'Shamandora Scout');
        $this->replyToEmail = env('MAIL_REPLYTO_ADDRESS', 'support@shamandorascout.com');
        $this->replyToName  = env('MAIL_REPLYTO_NAME',    'Support');

        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
        $client = new Guzzle([
            'timeout' => 12,
            'connect_timeout' => 5,
        ]);

        $this->emails = new TransactionalEmailsApi($client, $config);
    }

    /**
     * Sends a professional bilingual (AR/EN) reset email with a header logo.
     *
     * @param string $logoUrl   Public HTTPS URL for your logo (e.g. https://yourcdn.com/logo.png)
     * @param string $loginUrl  Your app login URL (optional)
     */
    public function sendTempPasswordBilingual(
        string $toEmail,
        string $toName,
        string $personId,
        string $password,
        string $logoUrl,
        string $loginUrl = '#'
    ) {
        $subject = 'كلمة سر مؤقتة — Temporary Password';

        // --- HTML email (600px container, header logo, AR then EN) ---
        $html = <<<HTML
<!doctype html>
<html lang="en">
  <body style="margin:0; padding:0; background:#f5f7fb; font-family:Tahoma, Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f7fb;">
      <tr>
        <td align="center" style="padding:24px;">
          <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.05);">
            <!-- Header -->
            <tr>
              <td align="center" style="padding:24px 24px 8px;">
            <img src="{$this->escape($logoUrl)}" 
     alt="Shamandora Scout" 
     style="max-width:160px; height:auto; display:block;">
              </td>
            </tr>
            <tr>
              <td align="center" style="padding:0 24px 16px; color:#111827; font-size:18px; font-weight:700;">
                Shamandora Scout
              </td>
            </tr>

            <!-- Arabic block (RTL) -->
            <tr>
              <td style="padding:0 24px 16px;">
                <div dir="rtl" lang="ar" style="text-align:right; color:#111827;">
                  <h2 style="margin:0 0 12px; font-size:18px;">مرحباً {$this->escape($toName)},</h2>
                  <p style="margin:0 0 8px; font-size:14px; line-height:1.6;">
                    تم إنشاء <strong>كلمة سر مؤقتة</strong> لحسابك. يُرجى استخدامها لتسجيل الدخول ثم تغييرها فوراً حفاظاً على أمان حسابك.
                  </p>
                  <table role="presentation" style="margin:12px 0; font-size:14px;">
                    <tr>
                      <td style="padding:6px 0; color:#374151;">الرقم التعريفي:</td>
                      <td style="padding:6px 12px; color:#111827;"><strong>{$this->escape($personId)}</strong></td>
                    </tr>
                    <tr>
                      <td style="padding:6px 0; color:#374151;">كلمة السر المؤقتة:</td>
                      <td style="padding:6px 12px; color:#111827;"><strong>{$this->escape($password)}</strong></td>
                    </tr>
                  </table>
                  <p style="margin:0 0 16px; font-size:14px; line-height:1.6;">
                    لتسجيل الدخول، اضغط الزر التالي:
                  </p>
                  <p style="margin:0 0 20px;">
                    <a href="{$this->escape($loginUrl)}" style="background:#0ea5e9; color:#ffffff; text-decoration:none; padding:10px 16px; border-radius:8px; font-weight:600; display:inline-block;">تسجيل الدخول</a>
                  </p>
                </div>
              </td>
            </tr>

            <!-- Divider -->
            <tr>
              <td style="padding:0 24px;">
                <hr style="border:none; border-top:1px solid #e5e7eb; margin:8px 0 0;">
              </td>
            </tr>

            <!-- English block (LTR) -->
            <tr>
              <td style="padding:16px 24px 24px;">
                <div dir="ltr" lang="en" style="text-align:left; color:#111827;">
                  <h2 style="margin:0 0 12px; font-size:18px;">Hello {$this->escape($toName)},</h2>
                  <p style="margin:0 0 8px; font-size:14px; line-height:1.6;">
                    A <strong>temporary password</strong> has been generated for your account. Please use it to sign in and change it immediately to keep your account secure.
                  </p>
                  <table role="presentation" style="margin:12px 0; font-size:14px;">
                    <tr>
                      <td style="padding:6px 0; color:#374151;">ID:</td>
                      <td style="padding:6px 12px; color:#111827;"><strong>{$this->escape($personId)}</strong></td>
                    </tr>
                    <tr>
                      <td style="padding:6px 0; color:#374151;">Temporary password:</td>
                      <td style="padding:6px 12px; color:#111827;"><strong>{$this->escape($password)}</strong></td>
                    </tr>
                  </table>
                  <p style="margin:0 0 16px; font-size:14px; line-height:1.6;">
                    Click the button below to sign in:
                  </p>
                  <p style="margin:0 0 4px;">
                    <a href="{$this->escape($loginUrl)}" style="background:#0ea5e9; color:#ffffff; text-decoration:none; padding:10px 16px; border-radius:8px; font-weight:600; display:inline-block;">Sign in</a>
                  </p>
                </div>
              </td>
            </tr>

            <!-- Footer -->
            <tr>
              <td style="padding:16px 24px 24px; color:#6b7280; font-size:12px; text-align:center;">
                © {date('Y')} Shamandora Scout — All rights reserved.
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>
HTML;

        $text = "AR\n"
              . "مرحباً {$toName}\n"
              . "الرقم التعريفي: {$personId}\n"
              . "كلمة السر المؤقتة: {$password}\n"
              . "فضلاً، قم بتغييرها فوراً بعد تسجيل الدخول.\n\n"
              . "EN\n"
              . "Hello {$toName}\n"
              . "ID: {$personId}\n"
              . "Temporary password: {$password}\n"
              . "Please change it immediately after signing in.\n"
              . "Login: {$loginUrl}\n";

        $mail = new SendSmtpEmail([
            'subject'      => $subject,
            'sender'       => ['email' => $this->fromEmail, 'name' => $this->fromName],
            'replyTo'      => ['email' => $this->replyToEmail, 'name' => $this->replyToName],
            'to'           => [['email' => $toEmail, 'name' => $toName]],
            'htmlContent'  => $html,
            'textContent'  => $text,
            'headers'      => ['X-Entity-Ref-ID' => (string)$personId],
            'tags'         => ['password-reset', 'bilingual'],
        ]);

        return $this->emails->sendTransacEmail($mail);
    }

    /** Small helper to escape user/content in HTML contexts */
    private function escape(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}