<?php

namespace App\Exports;

use App\Contracts\ExportDefinitionInterface;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Collection;

class ExcelExporter
{
    public function export(
        Builder $query,
        ExportDefinitionInterface $definition
    ): BinaryFileResponse {
        $export = new class($query, $definition)
        implements FromCollection, WithHeadings
        {
            public function __construct(
                private Builder $query,
                private ExportDefinitionInterface $definition,
            ) {}

            public function collection(): Collection
            {
                return $this->query
                    ->get()
                    ->map(
                        fn($item) => $this->definition->map($item)
                    );
            }

            public function headings(): array
            {
                return $this->definition->headers();
            }
        };

        return Excel::download(
            $export,
            $definition->filename() . '.xlsx'
        );
    }
}
