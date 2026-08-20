<?php

namespace App\Exports;

use App\Contracts\ExportDefinitionInterface;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\Response;

class JsonExporter
{
    public function export(
        Builder $query,
        ExportDefinitionInterface $definition
    ): Response {
        $rows = [];

        $query->chunk(
            500,
            function ($items) use (&$rows, $definition) {
                foreach ($items as $item) {
                    $rows[] = array_combine(
                        $definition->headers(),
                        $definition->map($item)
                    );
                }
            }
        );

        return response()->json(
            [
                'title' => $definition->title(),
                'data' => $rows,
            ],
            200,
            [
                'Content-Disposition' =>
                'attachment; filename="' .
                    $definition->filename() .
                    '.json"',
            ]
        );
    }
}
