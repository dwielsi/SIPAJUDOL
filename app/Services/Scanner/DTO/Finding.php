<?php

namespace App\Services\Scanner\DTO;

final class Finding
{
    public function __construct(
        public readonly string $category,
        public readonly string $severity,
        public readonly string $message,
        public readonly ?string $evidence = null,
        public readonly ?string $pageUrl = null,
        public readonly ?string $location = null,
    ) {}
}
