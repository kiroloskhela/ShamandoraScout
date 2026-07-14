<?php

namespace App\Services;

use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
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
        $this->fromEmail = env('MAIL_FROM_ADDRESS', 'noreply@shamandorascout.com');
        $this->fromName = env('MAIL_FROM_NAME', 'Shamandora Scout');
        $this->replyToEmail = env('MAIL_REPLYTO_ADDRESS', 'support@shamandorascout.com');
        $this->replyToName = env('MAIL_REPLYTO_NAME', 'Support');

        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
        $client = new Guzzle([
            'timeout' => 12,
            'connect_timeout' => 5,
        ]);

        $this->emails = new TransactionalEmailsApi($client, $config);
    }

    /**
     * Bilingual password-reset email with a one-time link (no plaintext password).
     * The same $resetUrl can later be sent via WhatsApp.
     */
    public function sendPasswordResetLinkBilingual(
        string $toEmail,
        string $toName,
        string $personId,
        string $resetUrl,
        string $logoUrl,
        int $expireMinutes = 60
    ) {
        $subject = 'إعادة تعيين كلمة السر — Password Reset';

        $html = <<<HTML
<!doctype html>
<html lang="en">
  <body style="margin:0; padding:0; background:#f5f7fb; font-family:Tahoma, Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f7fb;">
      <tr>
        <td align="center" style="padding:24px;">
          <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.05);">
            <tr>
              <td align="center" style="padding:24px 24px 8px;">
                <img src="{$this->escape($logoUrl)}" alt="Shamandora Scout" style="max-width:160px; height:auto; display:block;">
              </td>
            </tr>
            <tr>
              <td align="center" style="padding:0 24px 16px; color:#111827; font-size:18px; font-weight:700;">
                Shamandora Scout
              </td>
            </tr>
            <tr>
              <td style="padding:0 24px 16px;">
                <div dir="rtl" lang="ar" style="text-align:right; color:#111827;">
                  <h2 style="margin:0 0 12px; font-size:18px;">مرحباً {$this->escape($toName)},</h2>
                  <p style="margin:0 0 8px; font-size:14px; line-height:1.6;">
                    طلبت إعادة تعيين كلمة السر لحسابك (الرقم التعريفي: <strong>{$this->escape($personId)}</strong>).
                    اضغط الزر أدناه لاختيار كلمة سر جديدة. الرابط صالح لمدة <strong>{$expireMinutes}</strong> دقيقة.
                  </p>
                  <p style="margin:16px 0 20px;">
                    <a href="{$this->escape($resetUrl)}" style="background:#0ea5e9; color:#ffffff; text-decoration:none; padding:10px 16px; border-radius:8px; font-weight:600; display:inline-block;">إعادة تعيين كلمة السر</a>
                  </p>
                  <p style="margin:0; font-size:12px; color:#6b7280; line-height:1.5;">
                    إذا لم تطلب ذلك، تجاهل هذه الرسالة. لن تتغير كلمة السر إلا بعد فتح الرابط واختيار كلمة جديدة.
                  </p>
                </div>
              </td>
            </tr>
            <tr>
              <td style="padding:0 24px;"><hr style="border:none; border-top:1px solid #e5e7eb; margin:8px 0 0;"></td>
            </tr>
            <tr>
              <td style="padding:16px 24px 24px;">
                <div dir="ltr" lang="en" style="text-align:left; color:#111827;">
                  <h2 style="margin:0 0 12px; font-size:18px;">Hello {$this->escape($toName)},</h2>
                  <p style="margin:0 0 8px; font-size:14px; line-height:1.6;">
                    You requested a password reset for your account (ID: <strong>{$this->escape($personId)}</strong>).
                    Click the button below to choose a new password. This link expires in <strong>{$expireMinutes}</strong> minutes.
                  </p>
                  <p style="margin:16px 0 20px;">
                    <a href="{$this->escape($resetUrl)}" style="background:#0ea5e9; color:#ffffff; text-decoration:none; padding:10px 16px; border-radius:8px; font-weight:600; display:inline-block;">Reset password</a>
                  </p>
                  <p style="margin:0; font-size:12px; color:#6b7280; line-height:1.5;">
                    If you did not request this, ignore this email. Your password will not change until you open the link and set a new one.
                  </p>
                </div>
              </td>
            </tr>
            <tr>
              <td style="padding:16px 24px 24px; color:#6b7280; font-size:12px; text-align:center;">
                Shamandora Scout — All rights reserved.
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
              . "أعد تعيين كلمة السر من هنا (صالح {$expireMinutes} دقيقة):\n"
              . "{$resetUrl}\n\n"
              . "EN\n"
              . "Hello {$toName}\n"
              . "ID: {$personId}\n"
              . "Reset your password here (expires in {$expireMinutes} minutes):\n"
              . "{$resetUrl}\n";

        $mail = new SendSmtpEmail([
            'subject' => $subject,
            'sender' => ['email' => $this->fromEmail, 'name' => $this->fromName],
            'replyTo' => ['email' => $this->replyToEmail, 'name' => $this->replyToName],
            'to' => [['email' => $toEmail, 'name' => $toName]],
            'htmlContent' => $html,
            'textContent' => $text,
            'headers' => ['X-Entity-Ref-ID' => (string) $personId],
            'tags' => ['password-reset', 'bilingual', 'reset-link'],
        ]);

        return $this->emails->sendTransacEmail($mail);
    }

    private function escape(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
