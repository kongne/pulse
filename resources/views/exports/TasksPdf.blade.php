<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>{{ $title }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 14mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #111827;
            background: #ffffff;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #111827;
            padding-bottom: 10px;
        }

        .title {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }

        .subtitle {
            margin-top: 4px;
            color: #6b7280;
            font-size: 10px;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            color: #6b7280;
            font-size: 9px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .table th {
            padding: 7px;
            border: 1px solid #d1d5db;
            background: #f3f4f6;
            color: #111827;
            font-size: 9px;
            font-weight: 700;
            text-align: left;
        }

        .table td {
            padding: 7px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
            word-wrap: break-word;
        }

        .table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .id {
            width: 6%;
        }

        .title-column {
            width: 17%;
        }

        .description {
            width: 25%;
        }

        .priority {
            width: 10%;
        }

        .list {
            width: 14%;
        }

        .completed {
            width: 10%;
        }

        .created {
            width: 18%;
        }

        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: 600;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .priority-high {
            color: #b91c1c;
            font-weight: 700;
        }

        .priority-medium {
            color: #b45309;
            font-weight: 700;
        }

        .priority-low {
            color: #15803d;
            font-weight: 700;
        }

        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #d1d5db;
            color: #6b7280;
            font-size: 8px;
            text-align: right;
        }

        .empty {
            padding: 30px;
            text-align: center;
            color: #6b7280;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1 class="title">
            {{ $title }}
        </h1>

        <div class="subtitle">
            Tasks report
        </div>
    </div>

    <div class="meta">
        <span>
            Generated: {{ now()->format('Y-m-d H:i:s') }}
        </span>

        <span>
            Total tasks: {{ $tasks->count() }}
        </span>
    </div>

    @if ($tasks->isEmpty())

        <div class="empty">
            No tasks found.
        </div>

    @else

        <table class="table">

            <thead>
                <tr>
                    <th class="id">ID</th>
                    <th class="title-column">Title</th>
                    <th class="description">Description</th>
                    <th class="priority">Priority</th>
                    <th class="list">List</th>
                    <th class="completed">Status</th>
                    <th class="created">Created At</th>
                </tr>
            </thead>

            <tbody>

                @foreach ($tasks as $task)

                    <tr>

                        <td>
                            {{ $task->id }}
                        </td>

                        <td>
                            {{ $task->title }}
                        </td>

                        <td>
                            {{ $task->description ?: '—' }}
                        </td>

                        <td>
                            @php
                                $priorityClass = match (strtolower((string) $task->priority)) {
                                    'high' => 'priority-high',
                                    'medium' => 'priority-medium',
                                    'low' => 'priority-low',
                                    default => '',
                                };
                            @endphp

                            <span class="{{ $priorityClass }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </td>

                        <td>
                            {{ $task->list?->name ?? '—' }}
                        </td>

                        <td>
                            @if ($task->completed)
                                <span class="badge badge-success">
                                    Completed
                                </span>
                            @else
                                <span class="badge badge-pending">
                                    Pending
                                </span>
                            @endif
                        </td>

                        <td>
                            {{ $task->created_at?->format('Y-m-d H:i:s') ?? '—' }}
                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    @endif

    <div class="footer">
        Pulse Task Management System
    </div>

</body>

</html>
