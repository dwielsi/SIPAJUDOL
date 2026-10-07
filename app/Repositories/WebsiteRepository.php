<?php

namespace App\Repositories;

use App\Models\Website;
use App\Repositories\Contracts\WebsiteRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class WebsiteRepository implements WebsiteRepositoryInterface
{
    public function query(): Builder
    {
        return Website::query();
    }

    public function find(int $id): Website
    {
        return Website::findOrFail($id);
    }

    public function create(array $data): Website
    {
        return Website::create($data);
    }

    public function update(Website $website, array $data): Website
    {
        $website->update($data);

        return $website;
    }

    /**
     * Hapus permanen agar domain dapat didaftarkan kembali; hasil pemindaian ikut terhapus
     * lewat cascade foreign key.
     */
    public function delete(Website $website): bool
    {
        return (bool) $website->forceDelete();
    }
}
