<?php

namespace App\Support;

/**
 * Coerce whole numeric inputs such as "200.00" to int 200.
 * Fractional values like "200.50" are left unchanged so integer validation still fails.
 */
class WholeNumberInput
{
    public static function coerce(mixed $value): mixed
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            if ($value != (int) $value) {
                return $value;
            }

            return (int) $value;
        }

        if (! is_string($value)) {
            return $value;
        }

        $trimmed = trim($value);
        if ($trimmed === '' || ! preg_match('/^-?\d+(?:\.0+)?$/', $trimmed)) {
            return $value;
        }

        return (int) $trimmed;
    }
}
