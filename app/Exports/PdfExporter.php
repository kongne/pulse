<?php

namespace App\Exports;

use App\Contracts\ExportDefinitionInterface;
use Illuminate\Database\Eloquent\Builder;
use Spatie\LaravelPdf\Facades\Pdf;

class PdfExporter
{
    public function export(
        Builder $query,
        ExportDefinitionInterface $definition
    ) {
        $tasks = $query
            ->latest()
            ->get();

        return Pdf::view('exports.TasksPdf', [
            'tasks' => $tasks,
            'title' => $definition->title(),
            'headers' => $definition->headers(),
            'definition' => $definition,
        ])
            ->format('a4')
            ->portrait()
            ->download($definition->filename() . '.pdf');
    }
    //use function inline($definition->filename(). '.pdf') for browser preview before downloading tasks on your own
}
