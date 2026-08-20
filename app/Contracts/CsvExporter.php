<?php

namespace App\Exports;

use App\Contracts\ExporterInterface;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporter implements ExporterInterface
{
    public function export(
        Builder $query,
        string $filename,
        array $headers,
        callable $mapper
    ): StreamedResponse {
        return response()->streamDownload(
            function () use ($query, $headers, $mapper) {
                $handle = fopen('php://output', 'w');

                fwrite($handle, "\xEF\xBB\xBF");

                fputcsv($handle, $headers);

                $query->chunk(500, function ($items) use (
                    $handle,
                    $mapper
                ) {
                    foreach ($items as $item) {
                        fputcsv(
                            $handle,
                            $mapper($item)
                        );
                    }
                });

                fclose($handle);
            },
            "{$filename}.csv",
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]
        );
    }
}
