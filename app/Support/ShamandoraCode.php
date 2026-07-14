<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Generates the human-facing "SH-00001" style ShamandoraCode from a
 * PersonInformation.PersonID (or an equivalent staging PersonID) value.
 *
 * Centralizing this avoids re-implementing the padding logic (and its
 * inconsistencies) in every place a person record is created.
 */
class ShamandoraCode
{
    public const PREFIX = 'SH-';
    public const PADDED_LENGTH = 5;

    /**
     * Build a ShamandoraCode for the given person id.
     *
     * @throws InvalidArgumentException if $personId is not a positive integer.
     */
    public static function fromPersonId(int $personId): string
    {
        if ($personId < 1) {
            throw new InvalidArgumentException("Person ID must be a positive integer, got {$personId}.");
        }

        return self::PREFIX . str_pad((string) $personId, self::PADDED_LENGTH, '0', STR_PAD_LEFT);
    }
}
