<?php

namespace App\Domain\Auth;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Password reset via opaque link tokens.
 *
 * Delivery channels (email / WhatsApp) only ever carry the reset URL — never
 * a plaintext password. Tokens are stored hashed in `password_reset_tokens`
 * (email PK). When a person has no email, a synthetic key
 * `person-{id}@password-reset.local` is used so WhatsApp-only resets work.
 */
class PasswordResetLinkService
{
    public function __construct(
        private readonly int $expireMinutes = 60
    ) {}

    /**
     * Token table key for a person: real email when present, else synthetic.
     */
    public function tokenKeyForPerson(int $personId, ?string $email): string
    {
        $email = strtolower(trim((string) $email));
        if ($email !== '') {
            return $email;
        }

        return 'person-'.$personId.'@password-reset.local';
    }

    /**
     * Replace any existing token for $email and return an absolute reset URL.
     * The plain token exists only inside that URL.
     */
    public function issueResetUrl(string $email): string
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            throw new RuntimeException('Cannot issue a password reset without an email address.');
        }

        $plainToken = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($plainToken),
                'created_at' => Carbon::now(),
            ]
        );

        return url('/reset-password/'.urlencode($plainToken)).'?'.http_build_query([
            'email' => $email,
        ]);
    }

    public function issueResetUrlForPerson(int $personId, ?string $email): string
    {
        return $this->issueResetUrl($this->tokenKeyForPerson($personId, $email));
    }

    public function tokenIsValid(string $email, string $plainToken): bool
    {
        $email = strtolower(trim($email));
        $row = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $row || ! Hash::check($plainToken, $row->token)) {
            return false;
        }

        $createdAt = $row->created_at ? Carbon::parse($row->created_at) : null;

        return $createdAt !== null
            && ! $createdAt->copy()->addMinutes($this->expireMinutes)->isPast();
    }

    public function consumeToken(string $email): void
    {
        DB::table('password_reset_tokens')->where('email', strtolower(trim($email)))->delete();
    }

    public function expireMinutes(): int
    {
        return $this->expireMinutes;
    }
}
