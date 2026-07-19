<?php

namespace App\Services;

class EligibilityResult
{
    public function __construct(
        public bool $allowed,
        public ?string $reason = null,
        public string $type = 'rule', // 'rule' | 'frequency'
    ) {}
}
