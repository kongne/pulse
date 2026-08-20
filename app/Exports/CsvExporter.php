<?php

namespace App\Exports;

use App\Contracts\ExportDefinitionInterface;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporter
{
    public function export(
        Builder $query,
        ExportDefinitionInterface $definition
    ): StreamedResponse {
        return response()->streamDownload(
            function () use ($query, $definition) {
                $handle = fopen('php://output', 'w');

                // UTF-8 BOM for Microsoft Excel
                fwrite($handle, "\xEF\xBB\xBF");

                fputcsv(
                    $handle,
                    $definition->headers()
                );

                $query->chunk(
                    500,
                    function ($items) use ($handle, $definition) {
                        foreach ($items as $item) {
                            fputcsv(
                                $handle,
                                $definition->map($item)
                            );
                        }
                    }
                );

                fclose($handle);
            },
            $definition->filename() . '.csv',
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' =>
                'attachment; filename="' .
                    $definition->filename() .
                    '.csv"',
            ]
        );
    }
}
