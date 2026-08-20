<?php

namespace App\Exports\Definitions;

use App\Contracts\ExportDefinitionInterface;
use App\Models\Task;

class TaskExportDefinition implements ExportDefinitionInterface
{
    public function title(): string
    {
        return 'Tasks Export';
    }

    public function filename(): string
    {
        return 'tasks-' . now()->format('Y-m-d-His');
    }

    public function headers(): array
    {
        return [
            'ID',
            'Title',
            'Description',
            'Priority',
            'List',
            'Completed',
            'Created At',
        ];
    }

    public function map(mixed $item): array
    {
        /** @var Task $task */
        $task = $item;

        return [
            $task->id,
            $task->title,
            $task->description ?? '',
            $task->priority,
            $task->list?->name ?? '',
            $task->completed ? 'Yes' : 'No',
            $task->created_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
