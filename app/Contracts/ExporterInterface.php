<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface ExporterInterface
{
    public function export(
        Builder $query,
        string $filename,
    ): StreamedResponse;
}
