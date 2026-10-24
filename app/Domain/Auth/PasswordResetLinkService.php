<?php

namespace App\Domain\Auth;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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
    public function issueResetUrl(string $email, ?int $personId = null): string
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            throw new RuntimeException('Cannot issue a password reset without an email address.');
        }

        $plainToken = Str::random(64);
        $row = [
            'token' => Hash::make($plainToken),
            'created_at' => Carbon::now(),
        ];

        if ($personId !== null && Schema::hasColumn('password_reset_tokens', 'person_id')) {
            $row['person_id'] = $personId;
        }

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            $row
        );

        $root = rtrim((string) config('app.url'), '/');

        return $root.'/reset-password/'.urlencode($plainToken).'?'.http_build_query([
            'email' => $email,
        ]);
    }

    public function issueResetUrlForPerson(int $personId, ?string $email): string
    {
        return $this->issueResetUrl($this->tokenKeyForPerson($personId, $email), $personId);
    }

    /**
     * Person the token was issued for. The email query string is only the
     * lookup key for the token row, never a second identity.
     */
    public function personIdForValidToken(string $email, string $plainToken): ?int
    {
        if (! $this->tokenIsValid($email, $plainToken)) {
            return null;
        }

        $email = strtolower(trim($email));

        if (Schema::hasColumn('password_reset_tokens', 'person_id')) {
            $bound = DB::table('password_reset_tokens')->where('email', $email)->value('person_id');
            if ($bound) {
                return (int) $bound;
            }
        }

        if (preg_match('/^person-(\d+)@password-reset\.local$/', $email, $m)) {
            return (int) $m[1];
        }

        $personId = DB::table('PersonInformation')
            ->whereRaw('LOWER(TRIM(PersonalEmail)) = ?', [$email])
            ->value('PersonID');

        return $personId ? (int) $personId : null;
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
