<?php

namespace App\Services;

class ServiceHistoryResult
{
    public function __construct(
        public bool $allowed,
        public ?string $message = null,
        public ?int $daysRemaining = null,
    ) {}
}
