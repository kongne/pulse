<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, Clock, ListTodo, TrendingUp } from 'lucide-vue-next';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

import { Button } from '@/components/ui/button';

import { dashboard } from '@/routes';

import { index, update, destroy, store } from '@/routes/lists';

import { local as storageLocal } from '@/routes/storage';

/*
|--------------------------------------------------------------------------
| Types
|--------------------------------------------------------------------------
*/

interface Task {
    id: number;
    title: string;
    description?: string | null;
    priority: 'low' | 'normal' | 'high';
    completed: boolean;
    created_at: string;
    list_id: number;
    list?: {
        id: number;
        name: string;
        color?: string | null;
    };
}

interface TodoList {
    id: number;
    name: string;
    color?: string | null;
    tasks_count: number;
    completed_tasks_count: number;
    created_at: string;
}

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps<{
    lists: TodoList[];
    recentTasks: Task[];
    totalTasks: number;
    completedTasks: number;
    pendingTasks: number;
}>();

/*
|--------------------------------------------------------------------------
| Layout
|--------------------------------------------------------------------------
*/

defineOptions({
    layout: (props: { currentTeam?: Team | null }) => ({
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: props.currentTeam
                    ? dashboard(props.currentTeam.slug)
                    : '/',
            },
        ],
    }),
});

/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const completionRate = computed(() => {
    if (props.totalTasks === 0) {
        return 0;
    }

    return Math.round((props.completedTasks / props.totalTasks) * 100);
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const getPriorityLabel = (priority: Task['priority']) => {
    return priority.charAt(0).toUpperCase() + priority.slice(1);
};

const getPriorityClass = (priority: Task['priority']) => {
    switch (priority) {
        case 'high':
            return 'text-red-600 dark:text-red-400';

        case 'normal':
            return 'text-yellow-600 dark:text-yellow-400';

        case 'low':
            return 'text-green-600 dark:text-green-400';

        default:
            return 'text-muted-foreground';
    }
};
</script>

<template>
    <Head title="Overview" />

    <!-- Pending invitations -->
    <PendingInvitationsModal
        v-if="pendingInvitations?.length"
        :invitations="pendingInvitations"
    />

    <div class="min-h-screen space-y-6 p-4 sm:p-6">
        <!-- ========================================================= -->
        <!-- PAGE HEADER -->
        <!-- ========================================================= -->

        <div>
            <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">
                Overview
            </h1>

            <p class="mt-1 text-sm text-muted-foreground sm:text-base">
                Overview of your tasks and lists
            </p>
        </div>

        <!-- ========================================================= -->
        <!-- STATISTICS -->
        <!-- ========================================================= -->

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <!-- Total Lists -->
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium">
                        Total Lists
                    </CardTitle>

                    <ListTodo class="h-4 w-4 text-muted-foreground" />
                </CardHeader>

                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ lists.length }}
                    </div>

                    <p class="mt-1 text-xs text-muted-foreground">
                        Your task lists
                    </p>
                </CardContent>
            </Card>

            <!-- Total Tasks -->
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium">
                        Total Tasks
                    </CardTitle>

                    <Clock class="h-4 w-4 text-muted-foreground" />
                </CardHeader>

                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ totalTasks }}
                    </div>

                    <p class="mt-1 text-xs text-muted-foreground">
                        All your tasks
                    </p>
                </CardContent>
            </Card>

            <!-- Completed Tasks -->
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium">
                        Completed Tasks
                    </CardTitle>

                    <CheckCircle2 class="h-4 w-4 text-muted-foreground" />
                </CardHeader>

                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ completedTasks }}
                    </div>

                    <p class="mt-1 text-xs text-muted-foreground">
                        Tasks completed
                    </p>
                </CardContent>
            </Card>

            <!-- Completion Rate -->
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle class="text-sm font-medium">
                        Completion Rate
                    </CardTitle>

                    <TrendingUp class="h-4 w-4 text-muted-foreground" />
                </CardHeader>

                <CardContent>
                    <div class="text-2xl font-bold">{{ completionRate }}%</div>

                    <p class="mt-1 text-xs text-muted-foreground">
                        {{ pendingTasks }} tasks remaining
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- ========================================================= -->
        <!-- MAIN CONTENT -->
        <!-- ========================================================= -->

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- ===================================================== -->
            <!-- YOUR LISTS -->
            <!-- ===================================================== -->

            <Card>
                <CardHeader
                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <CardTitle>Your Lists</CardTitle>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Overview of your task lists
                        </p>
                    </div>

                    <Link href="/lists">
                        <Button
                            variant="outline"
                            size="sm"
                            class="w-full sm:w-auto"
                        >
                            View all
                        </Button>
                    </Link>
                </CardHeader>

                <CardContent>
                    <div v-if="lists.length > 0" class="space-y-3">
                        <Link
                            v-for="list in lists"
                            :key="list.id"
                            href="/lists"
                            class="block rounded-lg border p-3 transition-colors hover:bg-accent"
                        >
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <div class="flex min-w-0 items-center gap-3">
                                    <div
                                        class="h-3 w-3 shrink-0 rounded-full"
                                        :style="{
                                            backgroundColor:
                                                list.color || '#6366f1',
                                        }"
                                    />

                                    <div class="min-w-0">
                                        <p class="truncate font-medium">
                                            {{ list.name }}
                                        </p>

                                        <p
                                            class="text-xs text-muted-foreground sm:text-sm"
                                        >
                                            {{ list.completed_tasks_count }}
                                            /
                                            {{ list.tasks_count }}
                                            completed
                                        </p>
                                    </div>
                                </div>

                                <span class="shrink-0 text-sm font-medium">
                                    {{ list.tasks_count }}
                                </span>
                            </div>

                            <div
                                v-if="list.tasks_count > 0"
                                class="mt-3 h-1.5 overflow-hidden rounded-full bg-muted"
                            >
                                <div
                                    class="h-full rounded-full bg-primary transition-all"
                                    :style="{
                                        width: `${
                                            (list.completed_tasks_count /
                                                list.tasks_count) *
                                            100
                                        }%`,
                                    }"
                                />
                            </div>
                        </Link>
                    </div>

                    <div
                        v-else
                        class="py-10 text-center text-sm text-muted-foreground"
                    >
                        <ListTodo class="mx-auto mb-3 h-8 w-8" />

                        <p>No lists yet.</p>

                        <p class="mt-1">
                            Create a list to organize your tasks.
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- ===================================================== -->
            <!-- RECENT TASKS -->
            <!-- ===================================================== -->

            <Card>
                <CardHeader
                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <CardTitle>Recent Tasks</CardTitle>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Your latest tasks
                        </p>
                    </div>

                    <Link href="/tasks">
                        <Button
                            variant="outline"
                            size="sm"
                            class="w-full sm:w-auto"
                        >
                            View all
                        </Button>
                    </Link>
                </CardHeader>

                <CardContent>
                    <!-- Recent tasks -->
                    <div v-if="recentTasks.length > 0" class="space-y-3">
                        <div
                            v-for="task in recentTasks"
                            :key="task.id"
                            class="rounded-lg border p-3 transition-colors hover:bg-accent"
                        >
                            <div class="flex items-start gap-3">
                                <!-- Completion icon -->
                                <div class="mt-0.5 shrink-0">
                                    <CheckCircle2
                                        v-if="task.completed"
                                        class="h-5 w-5 text-green-600"
                                    />

                                    <div
                                        v-else
                                        class="h-5 w-5 rounded-full border-2 border-muted-foreground"
                                    />
                                </div>

                                <!-- Task content -->
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="truncate text-sm font-medium"
                                        :class="{
                                            'text-muted-foreground line-through':
                                                task.completed,
                                        }"
                                    >
                                        {{ task.title }}
                                    </p>

                                    <div
                                        class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs"
                                    >
                                        <span
                                            :class="
                                                getPriorityClass(task.priority)
                                            "
                                        >
                                            {{
                                                getPriorityLabel(task.priority)
                                            }}
                                        </span>

                                        <span
                                            v-if="task.list"
                                            class="text-muted-foreground"
                                        >
                                            •
                                            {{ task.list.name }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Status -->
                                <span
                                    class="shrink-0 text-xs text-muted-foreground"
                                >
                                    {{ task.completed ? 'Done' : 'Pending' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-else
                        class="py-10 text-center text-sm text-muted-foreground"
                    >
                        <CheckCircle2 class="mx-auto mb-3 h-8 w-8" />

                        <p>No recent tasks yet.</p>

                        <p class="mt-1">Your latest tasks will appear here.</p>
                        <Link href="/tasks" class="mt-4">
                            <Button size="sm"> Create task </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
