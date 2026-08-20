<?php

namespace App\Contracts;

interface ExportDefinitionInterface
{
    public function title(): string;

    public function filename(): string;

    public function headers(): array;

    public function map(mixed $item): array;
}
