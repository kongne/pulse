<?php

namespace App\Exports;

use App\Contracts\ExportDefinitionInterface;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\Response;

class XmlExporter
{
    public function export(
        Builder $query,
        string $filename,
        ExportDefinitionInterface $definition
    ): Response {
        $xml = new \XMLWriter();

        $xml->openMemory();

        $xml->startDocument(
            '1.0',
            'UTF-8'
        );

        $xml->startElement('export');

        $xml->writeElement(
            'generated_at',
            now()->toIso8601String()
        );

        $xml->startElement('items');

        $query->chunk(
            500,
            function ($items) use (
                $xml,
                $definition
            ) {
                foreach ($items as $item) {
                    $mapped = $definition->map($item);

                    $xml->startElement('item');

                    foreach (
                        $definition->headers()
                        as $index => $header
                    ) {
                        $key = strtolower(
                            preg_replace(
                                '/[^a-zA-Z0-9]+/',
                                '_',
                                $header
                            )
                        );

                        $xml->writeElement(
                            $key,
                            (string) ($mapped[$index] ?? '')
                        );
                    }

                    $xml->endElement();
                }
            }
        );

        $xml->endElement();

        $xml->endElement();

        $xml->endDocument();

        return response(
            $xml->outputMemory(),
            200,
            [
                'Content-Type' =>
                'application/xml; charset=UTF-8',

                'Content-Disposition' =>
                'attachment; filename="' .
                    $filename .
                    '.xml"',
            ]
        );
    }
}
