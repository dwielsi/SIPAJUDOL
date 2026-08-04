<?php

namespace App\Repositories\Contracts;

use App\Models\Website;
use Illuminate\Database\Eloquent\Builder;

interface WebsiteRepositoryInterface
{
    public function query(): Builder;

    public function find(int $id): Website;

    public function create(array $data): Website;

    public function update(Website $website, array $data): Website;

    public function delete(Website $website): bool;
}
