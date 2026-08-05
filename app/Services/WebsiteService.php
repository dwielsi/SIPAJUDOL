<?php

namespace App\Services;

use App\Models\Website;
use App\Repositories\Contracts\WebsiteRepositoryInterface;

class WebsiteService
{
    public function __construct(
        private readonly WebsiteRepositoryInterface $websites,
    ) {}

    public function create(array $data): Website
    {
        return $this->websites->create($this->normalize($data));
    }

    public function update(Website $website, array $data): Website
    {
        return $this->websites->update($website, $this->normalize($data));
    }

    public function delete(Website $website): bool
    {
        return $this->websites->delete($website);
    }

    private function normalize(array $data): array
    {
        if (! empty($data['domain'])) {
            $data['domain'] = rtrim(preg_replace('#^https?://#i', '', trim($data['domain'])), '/');
        }

        if (empty($data['website_name'])) {
            $data['website_name'] = $data['domain'] ?? $data['opd_name'] ?? null;
        }

        $data['name'] = $data['website_name'];

        $data['status'] = $data['status'] ?? 'needs_review';

        return $data;
    }
}
