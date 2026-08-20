<?php

namespace App\Services;

use App\Contracts\ExportDefinitionInterface;
use App\Exports\CsvExporter;
use App\Exports\JsonExporter;
use App\Exports\ExcelExporter;
use App\Exports\PdfExporter;
use Illuminate\Database\Eloquent\Builder;

class ExportService
{
    public function __construct(
        protected CsvExporter $csvExporter,
        protected JsonExporter $jsonExporter,
        protected ExcelExporter $excelExporter,
        protected PdfExporter $pdfExporter,
    ) {}

    public function export(
        Builder $query,
        string $format,
        ExportDefinitionInterface $definition
    ) {
        $format = strtolower(trim($format));

        return match ($format) {
            'csv' => $this->csvExporter->export(
                $query,
                $definition
            ),

            'json' => $this->jsonExporter->export(
                $query,
                $definition
            ),

            'xlsx' => $this->excelExporter->export(
                $query,
                $definition
            ),

            'pdf' => $this->pdfExporter->export(
                $query,
                $definition
            ),

            default => abort(
                400,
                "Unsupported export format: {$format}"
            ),
        };
    }
}
