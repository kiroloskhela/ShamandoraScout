<?php

namespace App\Domain\Enrolment;

class MigrateEnrolmentResult
{
    /**
     * @param  array<int, array{person_id: int, message: string}>  $failures
     */
    public function __construct(
        public int $migrated_count = 0,
        public int $failed_count = 0,
        public array $failures = [],
    ) {}

    public function hasFailures(): bool
    {
        return $this->failed_count > 0;
    }

    /**
     * @return array{migrated_count: int, failed_count: int, failures: array<int, array{person_id: int, message: string}>}
     */
    public function toArray(): array
    {
        return [
            'migrated_count' => $this->migrated_count,
            'failed_count' => $this->failed_count,
            'failures' => $this->failures,
        ];
    }
}
