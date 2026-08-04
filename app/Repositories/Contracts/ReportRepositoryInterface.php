<?php

namespace App\Repositories\Contracts;

use App\Models\Report;
use Illuminate\Database\Eloquent\Builder;

interface ReportRepositoryInterface
{
    public function query(): Builder;

    public function find(int $id): Report;

    public function create(array $data): Report;

    public function update(Report $report, array $data): Report;

    public function delete(Report $report): bool;
}
