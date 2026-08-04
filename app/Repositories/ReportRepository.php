<?php

namespace App\Repositories;

use App\Models\Report;
use App\Repositories\Contracts\ReportRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class ReportRepository implements ReportRepositoryInterface
{
    public function query(): Builder
    {
        return Report::query();
    }

    public function find(int $id): Report
    {
        return Report::findOrFail($id);
    }

    public function create(array $data): Report
    {
        return Report::create($data);
    }

    public function update(Report $report, array $data): Report
    {
        $report->update($data);

        return $report;
    }

    public function delete(Report $report): bool
    {
        return (bool) $report->delete();
    }
}
