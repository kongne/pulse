<script setup lang="ts">
import { computed } from 'vue';

interface Task {
    id: number;
    title: string;
    description?: string | null;
    priority: string;
    completed: boolean | number;
    created_at?: string | null;
    list?: {
        id: number;
        name: string;
        color?: string | null;
    } | null;
}

interface Props {
    tasks: Task[];
    title?: string;
    filters?: {
        search?: string | null;
        priority?: string | null;
        list_id?: string | number | null;
    };
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Tasks Export',
    filters: () => ({}),
});

const generatedAt = computed(() => {
    return new Intl.DateTimeFormat('en', {
        dateStyle: 'long',
        timeStyle: 'short',
    }).format(new Date());
});

const completedCount = computed(() => {
    return props.tasks.filter((task) => Boolean(task.completed)).length;
});

const pendingCount = computed(() => {
    return props.tasks.length - completedCount.value;
});

const priorityClass = (priority: string) => {
    switch (priority.toLowerCase()) {
        case 'high':
            return 'bg-red-100 text-red-700';

        case 'medium':
            return 'bg-yellow-100 text-yellow-700';

        case 'low':
            return 'bg-green-100 text-green-700';

        default:
            return 'bg-gray-100 text-gray-700';
    }
};

const statusClass = (completed: boolean | number) => {
    return Boolean(completed)
        ? 'bg-green-100 text-green-700'
        : 'bg-gray-100 text-gray-700';
};

const printPdf = () => {
    window.print();
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 p-4 sm:p-6 print:bg-white print:p-0">
        <!-- Print button -->
        <div class="mx-auto mb-4 flex max-w-6xl justify-end print:hidden">
            <button
                type="button"
                @click="printPdf"
                class="rounded-lg bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-800"
            >
                Print / Save as PDF
            </button>
        </div>

        <!-- PDF document -->
        <main
            class="mx-auto max-w-6xl bg-white p-6 shadow-sm sm:p-8 print:max-w-none print:p-0 print:shadow-none"
        >
            <!-- Header -->
            <header
                class="mb-6 flex flex-col gap-4 border-b pb-6 sm:flex-row sm:items-start sm:justify-between"
            >
                <div>
                    <div class="mb-2 text-2xl font-bold tracking-tight">
                        PULSE
                    </div>

                    <h1 class="text-xl font-semibold sm:text-2xl">
                        {{ title }}
                    </h1>

                    <p class="mt-1 text-sm text-gray-500">
                        Generated {{ generatedAt }}
                    </p>
                </div>

                <div class="text-left sm:text-right">
                    <p class="text-xs tracking-wide text-gray-500 uppercase">
                        Total Tasks
                    </p>

                    <p class="text-2xl font-bold">
                        {{ tasks.length }}
                    </p>
                </div>
            </header>

            <!-- Summary -->
            <section class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-lg border p-4">
                    <p class="text-xs tracking-wide text-gray-500 uppercase">
                        Total
                    </p>

                    <p class="mt-1 text-xl font-semibold">
                        {{ tasks.length }}
                    </p>
                </div>

                <div class="rounded-lg border p-4">
                    <p class="text-xs tracking-wide text-gray-500 uppercase">
                        Completed
                    </p>

                    <p class="mt-1 text-xl font-semibold">
                        {{ completedCount }}
                    </p>
                </div>

                <div class="rounded-lg border p-4">
                    <p class="text-xs tracking-wide text-gray-500 uppercase">
                        Pending
                    </p>

                    <p class="mt-1 text-xl font-semibold">
                        {{ pendingCount }}
                    </p>
                </div>
            </section>

            <!-- Filters -->
            <section
                v-if="filters.search || filters.priority || filters.list_id"
                class="mb-6 rounded-lg border bg-gray-50 p-4"
            >
                <h2 class="mb-3 text-sm font-semibold">Applied Filters</h2>

                <div class="flex flex-wrap gap-2">
                    <span
                        v-if="filters.search"
                        class="rounded-md border bg-white px-3 py-1 text-xs"
                    >
                        Search: {{ filters.search }}
                    </span>

                    <span
                        v-if="filters.priority"
                        class="rounded-md border bg-white px-3 py-1 text-xs"
                    >
                        Priority: {{ filters.priority }}
                    </span>

                    <span
                        v-if="filters.list_id"
                        class="rounded-md border bg-white px-3 py-1 text-xs"
                    >
                        List: {{ filters.list_id }}
                    </span>
                </div>
            </section>

            <!-- Tasks table -->
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50 text-left">
                            <th class="px-3 py-3 font-semibold">#</th>

                            <th class="px-3 py-3 font-semibold">Task</th>

                            <th class="px-3 py-3 font-semibold">Priority</th>

                            <th class="px-3 py-3 font-semibold">List</th>

                            <th class="px-3 py-3 font-semibold">Status</th>

                            <th class="px-3 py-3 font-semibold">Created</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="task in tasks"
                            :key="task.id"
                            class="border-b last:border-0"
                        >
                            <td class="px-3 py-3 align-top">
                                {{ task.id }}
                            </td>

                            <td class="px-3 py-3 align-top">
                                <div class="font-medium">
                                    {{ task.title }}
                                </div>

                                <div
                                    v-if="task.description"
                                    class="mt-1 max-w-md text-xs text-gray-500"
                                >
                                    {{ task.description }}
                                </div>
                            </td>

                            <td class="px-3 py-3 align-top">
                                <span
                                    class="inline-flex rounded-md px-2 py-1 text-xs font-medium capitalize"
                                    :class="priorityClass(task.priority)"
                                >
                                    {{ task.priority }}
                                </span>
                            </td>

                            <td class="px-3 py-3 align-top">
                                {{ task.list?.name ?? '—' }}
                            </td>

                            <td class="px-3 py-3 align-top">
                                <span
                                    class="inline-flex rounded-md px-2 py-1 text-xs font-medium"
                                    :class="statusClass(task.completed)"
                                >
                                    {{
                                        Boolean(task.completed)
                                            ? 'Completed'
                                            : 'Pending'
                                    }}
                                </span>
                            </td>

                            <td
                                class="px-3 py-3 align-top text-xs whitespace-nowrap text-gray-500"
                            >
                                {{ task.created_at ?? '—' }}
                            </td>
                        </tr>

                        <tr v-if="tasks.length === 0">
                            <td
                                colspan="6"
                                class="px-3 py-10 text-center text-sm text-gray-500"
                            >
                                No tasks found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <footer class="mt-8 border-t pt-4 text-xs text-gray-500">
                <div
                    class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                >
                    <span> PULSE — Tasks Management </span>

                    <span> Generated {{ generatedAt }} </span>
                </div>
            </footer>
        </main>
    </div>
</template>

<style>
@media print {
    @page {
        size: A4 landscape;
        margin: 12mm;
    }

    body {
        background: white !important;
    }

    table {
        page-break-inside: auto;
    }

    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }

    thead {
        display: table-header-group;
    }

    tfoot {
        display: table-footer-group;
    }
}
</style>
