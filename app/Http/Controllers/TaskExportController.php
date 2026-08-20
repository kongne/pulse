<?php

namespace App\Http\Controllers;

use App\Exports\Definitions\TaskExportDefinition;
use App\Models\Task;
use App\Services\ExportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;


class TaskExportController extends Controller
{
    public function __construct(
        private readonly ExportService $exportService
    ) {}

    public function export(Request $request)
    {
        $format = strtolower(
            (string) $request->input('format', 'csv')
        );

        $query = Task::query()
            ->with('list:id,name,color');

        $this->applyFilters($query, $request);

        return $this->exportService->export(
            query: $query,
            format: $format,
            definition: new TaskExportDefinition(),
        );
    }

    private function applyFilters(
        Builder $query,
        Request $request
    ): void {
        if ($request->filled('search')) {
            $search = $request
                ->string('search')
                ->toString();

            $query->where(function (Builder $query) use ($search) {
                $query
                    ->where(
                        'title',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'description',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        if ($request->filled('priority')) {
            $query->where(
                'priority',
                $request->input('priority')
            );
        }

        if ($request->filled('list_id')) {
            $query->where(
                'list_id',
                $request->input('list_id')
            );
        }
    }
}
