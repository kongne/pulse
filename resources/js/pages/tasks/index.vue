<script setup lang="ts">
import {
    Loader2,
    Plus,
    Pencil,
    Trash2,
    X,
    Search,
    Circle,
    CheckCircle2,
} from 'lucide-vue-next';
import { dashboard } from '@/routes';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Link, router, Head, useForm } from '@inertiajs/vue3';

import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
    CardFooter,
} from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { index, update, destroy, store } from '@/routes/tasks';
import { local as storageLocal } from '@/routes/storage';
import { ref, computed } from 'vue';
import { watchDebounced } from '@vueuse/core';
import { Badge } from '@/components/ui/badge';
import ExportDropdown from '@/components/ExportDropdown.vue';

interface Task {
    id: number;
    title: string;
    description?: string;
    priority: 'low' | 'medium' | 'high';
    completed: boolean;
    created_at: string;

    list: {
        id: number;
        name: string;
        color?: string;
    };
    list_id: number;
}

interface TodoList {
    id: number;
    name: string;
    color?: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedTasks {
    data: Task[];
    currrent_page: number;
    first_page: string;
    last_page: string;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: PaginationLink[];
}

const props = defineProps<{
    tasks: PaginatedTasks;
    lists: TodoList[];
    filters: {
        search?: string;
        priority?: string;
        list_id?: string;
    };
}>();

defineOptions({
    layout: (props: { currentTeam?: Team | null }) => ({
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: props.currentTeam
                    ? dashboard(props.currentTeam.slug)
                    : '/',
            },
            {
                title: 'Tasks Management',
                href: index.url(),
            },
        ],
    }),
});

// filter state
const search = ref(props.filters.search ?? '');
const priority = ref(props.filters.priority ?? '');
const listId = ref(props.filters.list_id ?? '');

//Dialog state
const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const editingTask = ref<Task | null>(null);
const deletingTaskId = ref<number | null>(null);

//form state create
const createForm = useForm({
    title: '',
    description: '',
    priority: 'normal',
    list_id: props.filters.list_id ?? '',
});

//form state edit
const editForm = useForm({
    title: '',
    description: '',
    priority: 'normal',
    list_id: 'number',
});

//watch for filter changes and update URL with bounce
watchDebounced(
    [search, priority, listId],
    () => {
        router.get(
            '/tasks',
            {
                search: search.value || '',
                priority: priority.value || '',
                list_id: listId.value || '',
            },
            { preserveScroll: true, preserveState: true },
        );
    },
    { debounce: 300 },
);

//clear filter after searched

const clearFilters = () => {
    search.value = '';
    priority.value = '';
    listId.value = '';
};

//toggle task completion
const toggleTaskCompletion = (task: Task) => {
    router.put(
        `/tasks/${task.id}`,
        {
            title: task.title,
            description: task.description,
            priority: task.priority,
            completed: !task.completed,
        },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
};

//create task
const createTask = () => {
    createForm.post('/lists/tasks', {
        preserveScroll: true,
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            createForm.reset();
        },
    });
};

//update task
const updateTask = () => {
    if (!editingTask.value) return;
    editForm.put(`/tasks/${editingTask.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditDialogOpen.value = false;
            editForm.reset();
        },
    });
};

//delete task
const deleteTask = (taskId: number) => {
    if (confirm('Are you sure you want to delete this task?')) {
        deletingTaskId.value = taskId;
        router.delete(`/tasks/${taskId}`, {
            preserveScroll: true,
            onFinish: () => {
                deletingTaskId.value = null;
            },
        });
    }
};

//edit open dialog

const openEditDialog = (task: Task) => {
    editingTask.value = { ...task };
    editForm.title = task.title;
    editForm.list_id = task.list_id;
    editForm.description = task.description || '';
    editForm.priority = task.priority;
    isEditDialogOpen.value = true;
};

// get priority variant

const getPriorityVariant = (
    priority: string,
): 'default' | 'secondary' | 'destructive' => {
    switch (priority) {
        case 'low':
            return 'secondary';
        case 'high':
            return 'destructive';
        default:
            return 'default';
    }
};

const perPage = ref(props.filters.per_page ?? '');
const changePerPage = () => {
    router.get(
        index.url(),
        {
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <Head title="All Tasks" />
    <div class="min-h-screen space-y-6 p-4 sm:p-6">
        <!-- ========================================================= -->
        <!-- PAGE HEADER -->
        <!-- ========================================================= -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="min-w-0">
                <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">
                    All Tasks
                </h1>

                <p class="mt-1 text-sm text-muted-foreground sm:text-base">
                    View and manage all your tasks
                    <span class="font-medium"> ({{ tasks.total }} total) </span>
                </p>
            </div>

            <!-- Create Task -->
            <div class="w-full sm:w-auto">
                <Dialog v-model:open="isCreateDialogOpen">
                    <DialogTrigger as-child>
                        <Button class="w-full sm:w-auto">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Task
                        </Button>
                    </DialogTrigger>

                    <DialogContent
                        class="max-h-[90vh] w-[calc(100%-2rem)] max-w-lg overflow-y-auto"
                    >
                        <DialogHeader>
                            <DialogTitle>Create New Task</DialogTitle>

                            <DialogDescription>
                                Create a new task and assign it to a list
                            </DialogDescription>
                        </DialogHeader>

                        <form @submit.prevent="createTask" class="space-y-4">
                            <!-- Title -->
                            <div class="space-y-2">
                                <Label for="title"> Task Title </Label>

                                <Input
                                    id="title"
                                    v-model="createForm.title"
                                    placeholder="Example: Enter task title"
                                    autocomplete="off"
                                    required
                                    class="w-full"
                                />

                                <InputError
                                    :message="createForm.errors?.title"
                                />
                            </div>

                            <!-- List -->
                            <div class="space-y-2">
                                <Label for="list_id"> List </Label>

                                <select
                                    id="list_id"
                                    v-model="createForm.list_id"
                                    required
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                >
                                    <option value="" disabled>
                                        Select a list
                                    </option>

                                    <option
                                        v-for="list in lists"
                                        :key="list.id"
                                        :value="list.id"
                                    >
                                        {{ list.name }}
                                    </option>
                                </select>

                                <InputError
                                    :message="createForm.errors?.list_id"
                                />
                            </div>

                            <!-- Description -->
                            <div class="space-y-2">
                                <Label for="description"> Description </Label>

                                <Textarea
                                    id="description"
                                    v-model="createForm.description"
                                    placeholder="Add description..."
                                    rows="3"
                                    class="w-full resize-y"
                                />

                                <InputError
                                    :message="createForm.errors?.description"
                                />
                            </div>

                            <!-- Priority -->
                            <div class="space-y-2">
                                <Label for="priority"> Priority </Label>

                                <select
                                    id="priority"
                                    v-model="createForm.priority"
                                    required
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                >
                                    <option value="" disabled>
                                        Select priority
                                    </option>

                                    <option value="low">Low</option>

                                    <option value="normal">Normal</option>

                                    <option value="high">High</option>
                                </select>

                                <InputError
                                    :message="createForm.errors?.priority"
                                />
                            </div>

                            <!-- Submit -->
                            <Button
                                type="submit"
                                class="w-full"
                                :disabled="createForm.processing"
                            >
                                <Loader2
                                    v-if="createForm.processing"
                                    class="mr-2 h-4 w-4 animate-spin"
                                />

                                {{
                                    createForm.processing
                                        ? 'Creating...'
                                        : 'Create Task'
                                }}
                            </Button>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- EDIT TASK DIALOG -->
        <!-- ========================================================= -->

        <Dialog v-model:open="isEditDialogOpen">
            <DialogContent
                class="max-h-[90vh] w-[calc(100%-2rem)] max-w-lg overflow-y-auto"
            >
                <DialogHeader>
                    <DialogTitle> Edit Task </DialogTitle>

                    <DialogDescription>
                        Update the task as you wish
                    </DialogDescription>
                </DialogHeader>

                <form
                    v-if="editingTask"
                    @submit.prevent="updateTask"
                    class="space-y-4"
                >
                    <!-- Title -->
                    <div class="space-y-2">
                        <Label for="edit-title"> Task Title </Label>

                        <Input
                            id="edit-title"
                            v-model="editForm.title"
                            placeholder="Example: Enter task title"
                            autocomplete="off"
                            required
                            class="w-full"
                        />

                        <InputError :message="editForm.errors?.title" />
                    </div>

                    <!-- List -->
                    <div class="space-y-2">
                        <Label for="edit-list_id"> List </Label>

                        <select
                            id="edit-list_id"
                            v-model="editForm.list_id"
                            required
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                        >
                            <option value="" disabled>Select a list</option>

                            <option
                                v-for="list in lists"
                                :key="list.id"
                                :value="list.id"
                            >
                                {{ list.name }}
                            </option>
                        </select>

                        <InputError :message="editForm.errors?.list_id" />
                    </div>

                    <!-- Description -->
                    <div class="space-y-2">
                        <Label for="edit-description"> Description </Label>

                        <Textarea
                            id="edit-description"
                            v-model="editForm.description"
                            placeholder="Add description..."
                            rows="3"
                            class="w-full resize-y"
                        />

                        <InputError :message="editForm.errors?.description" />
                    </div>

                    <!-- Priority -->
                    <div class="space-y-2">
                        <Label for="edit-priority"> Priority </Label>

                        <select
                            id="edit-priority"
                            v-model="editForm.priority"
                            required
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                        >
                            <option value="low">Low</option>

                            <option value="normal">Normal</option>

                            <option value="high">High</option>
                        </select>

                        <InputError :message="editForm.errors?.priority" />
                    </div>

                    <!-- Submit -->
                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="editForm.processing"
                    >
                        <Loader2
                            v-if="editForm.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />

                        {{
                            editForm.processing ? 'Updating...' : 'Update Task'
                        }}
                    </Button>
                </form>
            </DialogContent>
        </Dialog>

        <!-- ========================================================= -->
        <!-- FILTERS -->
        <!-- ========================================================= -->

        <Card>
            <CardHeader class="pb-3">
                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <CardTitle> Filters </CardTitle>

                    <Button
                        variant="ghost"
                        size="sm"
                        class="self-start sm:self-auto"
                        @click="clearFilters"
                    >
                        <X class="mr-1 h-4 w-4" />
                        Clear filters
                    </Button>
                </div>
            </CardHeader>

            <CardContent>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <!-- Search -->
                    <div class="space-y-2 md:col-span-1">
                        <Label for="search"> Search </Label>

                        <div class="relative">
                            <Search
                                class="absolute top-2.5 left-3 h-4 w-4 text-muted-foreground"
                            />

                            <Input
                                id="search"
                                v-model="search"
                                placeholder="Search tasks..."
                                class="w-full pl-9"
                            />
                        </div>
                    </div>

                    <!-- List -->
                    <div class="space-y-2">
                        <Label for="list"> List </Label>

                        <select
                            id="list"
                            v-model="listId"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                        >
                            <option value="">All Lists</option>

                            <option
                                v-for="list in lists"
                                :key="list.id"
                                :value="list.id"
                            >
                                {{ list.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Priority -->
                    <div class="space-y-2">
                        <Label for="filter-priority"> Priority </Label>

                        <select
                            id="filter-priority"
                            v-model="priority"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                        >
                            <option value="">All Priorities</option>

                            <option value="low">Low</option>

                            <option value="normal">Normal</option>

                            <option value="high">High</option>
                        </select>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- ========================================================= -->
        <!-- TASKS -->
        <!-- ========================================================= -->

        <Card>
            <CardHeader>
                <CardTitle
                    class="flex flex-col gap-3 text-lg sm:flex-row sm:items-center sm:justify-between sm:text-xl"
                >
                    <!-- Title -->
                    <div class="flex flex-wrap items-center gap-1">
                        <span>Tasks</span>

                        <span class="text-sm font-normal text-muted-foreground">
                            ({{ tasks.data.length }} of {{ tasks.total }})
                        </span>
                    </div>

                    <!-- Controls -->
                    <div
                        class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-sm whitespace-nowrap">
                                Show
                            </span>

                            <select
                                v-model="perPage"
                                @change="changePerPage"
                                class="h-9 rounded-md border border-input bg-background px-2 text-sm text-foreground focus:ring-2 focus:ring-ring focus:outline-none"
                            >
                                <option :value="5">5</option>
                                <option :value="10">10</option>
                                <option :value="15">15</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>

                            <span class="text-sm whitespace-nowrap">
                                entries
                            </span>
                        </div>

                        <ExportDropdown
                            url="/tasks/export"
                            :filters="filters"
                        />
                    </div>
                </CardTitle>
            </CardHeader>

            <CardContent class="p-0 sm:p-6">
                <div v-if="tasks.data.length > 0" class="space-y-4">
                    <!-- ================================================= -->
                    <!-- MOBILE TASK CARDS -->
                    <!-- ================================================= -->

                    <div class="block space-y-3 sm:hidden">
                        <div
                            v-for="task in tasks.data"
                            :key="task.id"
                            class="space-y-3 rounded-lg border p-4"
                        >
                            <!-- Title -->
                            <div class="flex items-start gap-3">
                                <button
                                    @click="toggleTaskCompletion(task)"
                                    :disabled="task.completed"
                                    class="flex shrink-0 items-center justify-center"
                                >
                                    <CheckCircle2
                                        v-if="task.completed"
                                        class="h-5 w-5 text-green-600"
                                    />

                                    <Circle
                                        v-else
                                        class="h-5 w-5 text-muted-foreground"
                                    />
                                </button>

                                <div class="min-w-0">
                                    <p
                                        class="font-medium break-words"
                                        :class="{
                                            'text-muted-foreground line-through':
                                                task.completed,
                                        }"
                                    >
                                        {{ task.title }}
                                    </p>

                                    <p
                                        v-if="task.description"
                                        class="mt-1 text-sm break-words text-muted-foreground"
                                        :class="{
                                            'line-through': task.completed,
                                        }"
                                    >
                                        {{ task.description }}
                                    </p>
                                </div>
                            </div>

                            <!-- List + Priority -->
                            <div class="flex flex-wrap items-center gap-2">
                                <div
                                    class="flex items-center gap-2 text-sm text-muted-foreground"
                                >
                                    <div
                                        class="h-3 w-3 shrink-0 rounded-full"
                                        :style="{
                                            backgroundColor:
                                                task.list?.color || '#6366f1',
                                        }"
                                    />

                                    <span>
                                        {{ task.list?.name }}
                                    </span>
                                </div>

                                <Badge
                                    :variant="getPriorityVariant(task.priority)"
                                >
                                    {{ task.priority }}
                                </Badge>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end gap-2 border-t pt-2">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="openEditDialog(task)"
                                >
                                    <Pencil class="h-4 w-4" />
                                </Button>

                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="deleteTask(task.id)"
                                    :disabled="deletingTaskId === task.id"
                                >
                                    <Loader2
                                        v-if="deletingTaskId === task.id"
                                        class="h-4 w-4 animate-spin"
                                    />

                                    <Trash2 v-else class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- ================================================= -->
                    <!-- DESKTOP TABLE -->
                    <!-- ================================================= -->

                    <div
                        class="hidden overflow-x-auto rounded-md border sm:block"
                    >
                        <table class="w-full text-sm">
                            <thead class="border-b bg-muted/30">
                                <tr>
                                    <th class="h-12 px-4 text-left font-medium">
                                        Title
                                    </th>

                                    <th class="h-12 px-4 text-left font-medium">
                                        Description
                                    </th>

                                    <th class="h-12 px-4 text-left font-medium">
                                        List
                                    </th>

                                    <th class="h-12 px-4 text-left font-medium">
                                        Priority
                                    </th>

                                    <th class="h-12 px-4 text-left font-medium">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="task in tasks.data"
                                    :key="task.id"
                                    class="border-b last:border-0 hover:bg-muted/50"
                                >
                                    <!-- Title -->
                                    <td class="p-4 align-middle">
                                        <div class="flex items-center gap-3">
                                            <button
                                                @click="
                                                    toggleTaskCompletion(task)
                                                "
                                                :disabled="task.completed"
                                                class="shrink-0"
                                            >
                                                <CheckCircle2
                                                    v-if="task.completed"
                                                    class="h-4 w-4 text-green-600"
                                                />

                                                <Circle
                                                    v-else
                                                    class="h-4 w-4 text-muted-foreground"
                                                />
                                            </button>

                                            <span
                                                class="break-words"
                                                :class="{
                                                    'text-muted-foreground line-through':
                                                        task.completed,
                                                }"
                                            >
                                                {{ task.title }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Description -->
                                    <td class="max-w-xs p-4 align-middle">
                                        <span
                                            class="text-sm break-words text-muted-foreground"
                                            :class="{
                                                'line-through': task.completed,
                                            }"
                                        >
                                            {{ task.description || '—' }}
                                        </span>
                                    </td>

                                    <!-- List -->
                                    <td class="p-4 align-middle">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="h-3 w-3 shrink-0 rounded-full"
                                                :style="{
                                                    backgroundColor:
                                                        task.list?.color ||
                                                        '#6366f1',
                                                }"
                                            />

                                            <span class="text-sm">
                                                {{ task.list?.name }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Priority -->
                                    <td class="p-4 align-middle">
                                        <Badge
                                            :variant="
                                                getPriorityVariant(
                                                    task.priority,
                                                )
                                            "
                                        >
                                            {{ task.priority }}
                                        </Badge>
                                    </td>

                                    <!-- Actions -->
                                    <td class="p-4 align-middle">
                                        <div class="flex items-center gap-1">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="openEditDialog(task)"
                                            >
                                                <Pencil class="h-4 w-4" />
                                            </Button>

                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="deleteTask(task.id)"
                                                :disabled="
                                                    deletingTaskId === task.id
                                                "
                                            >
                                                <Loader2
                                                    v-if="
                                                        deletingTaskId ===
                                                        task.id
                                                    "
                                                    class="h-4 w-4 animate-spin"
                                                />

                                                <Trash2
                                                    v-else
                                                    class="h-4 w-4"
                                                />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ================================================= -->
                    <!-- PAGINATION -->
                    <!-- ================================================= -->

                    <div
                        class="flex flex-col gap-4 px-4 sm:flex-row sm:items-center sm:justify-between sm:px-0"
                    >
                        <p class="text-sm text-muted-foreground">
                            Showing page
                            {{ tasks.current_page }}
                            of
                            {{ tasks.last_page }}
                        </p>

                        <div class="flex flex-wrap items-center gap-1">
                            <Link
                                v-for="(link, index) in tasks.links"
                                :key="`${link.url ?? link.label}-${index}`"
                                :href="link.url ?? '#'"
                                :class="[
                                    'rounded-md px-2.5 py-1.5 text-xs sm:px-3 sm:text-sm',
                                    link.active
                                        ? 'bg-primary text-primary-foreground'
                                        : link.url
                                          ? 'hover:bg-muted'
                                          : 'cursor-not-allowed opacity-50',
                                ]"
                                preserve-state
                                preserve-scroll
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div
                    v-else
                    class="px-4 py-12 text-center text-sm text-muted-foreground"
                >
                    No tasks found.
                    <br />
                    Try adjusting your filters.
                </div>
            </CardContent>
        </Card>
    </div>
</template>
