<script setup lang="ts">
import { ExternalLink, Loader2, Plus, Pencil, Trash } from 'lucide-vue-next';
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
import { index, update, destroy, store } from '@/routes/lists';
import { local as storageLocal } from '@/routes/storage';
import { ref, computed } from 'vue';

const props = defineProps<{
    lists: Array<{
        id: number;
        name: string;
        color: string;
        created_at: string;
        tasks_count: number;
    }>;
}>();

const items = computed(() => props.lists);

const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);

const editingList = ref<{ id: number; name: string; color: string } | null>(
    null,
);
const deletingListId = ref<number | null>(null);

const createForm = useForm({
    name: '',
    color: '',
});
const editForm = useForm({
    name: '',
    color: '',
});

const openEditDialog = (list: any) => {
    editingList.value = {
        id: list.id,
        name: list.name,
        color: list.color || '#6366f1',
    };
    editForm.name = list.name;
    editForm.color = list.color || '#6366f1';
    isEditDialogOpen.value = true;
};

const createList = () => {
    createForm.post('/lists', {
        preserveScroll: true,
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            createForm.reset();
        },
    });
};

const updateList = () => {
    if (!editingList.value) return;

    editForm.put(`/lists/${editingList.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditDialogOpen.value = false;
            editForm.reset();
        },
    });
};

const deleteList = (listId: number) => {
    if (
        confirm(
            'Are you sure you want to delete this list? All asociated tatsks will also be deleted.',
        )
    ) {
        deletingListId.value = listId;
        router.delete(`/lists/${listId}`, {
            preserveScroll: true,
            onSuccess: () => {
                onFinish: () => {
                    deletingListId.value = null;
                };
            },
        });
    }
};
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
                title: 'List Management',
                href: index.url(),
            },
        ],
    }),
});
</script>

<template>
    <Head title="Lists" />

    <div
        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >
        <!-- Page Header -->
        <div class="m-4">
            <h1 class="text-3xl font-bold">Lists</h1>

            <p class="mt-1 text-muted-foreground">Manage your task lists</p>
        </div>

        <!-- Create List Dialog -->
        <Dialog v-model:open="isCreateDialogOpen">
            <DialogTrigger as-child class="m-4">
                <Button>
                    <Plus class="mr-2 h-4 w-4" />
                    Create List
                </Button>
            </DialogTrigger>

            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle> Create New List </DialogTitle>

                    <DialogDescription>
                        Create a new task list by entering its name and choosing
                        a color.
                    </DialogDescription>
                </DialogHeader>

                <!-- Create List Form -->
                <form @submit.prevent="createList" class="space-y-6">
                    <!-- Name -->
                    <div class="space-y-2">
                        <Label for="name"> List Name </Label>

                        <Input
                            id="name"
                            v-model="createForm.name"
                            placeholder="Example: Work Tasks"
                            autocomplete="off"
                        />

                        <InputError :message="createForm.errors.name" />
                    </div>

                    <!-- Color -->
                    <div class="space-y-2">
                        <Label for="color"> Color </Label>

                        <div class="flex items-center gap-3">
                            <Input
                                id="color"
                                type="color"
                                v-model="createForm.color"
                                class="h-11 w-20 cursor-pointer p-1"
                            />

                            <span class="text-sm text-muted-foreground">
                                {{ createForm.color }}
                            </span>
                        </div>

                        <InputError :message="createForm.errors.color" />
                    </div>

                    <!-- Footer -->
                    <DialogFooter class="gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            @click="isCreateDialogOpen = false"
                        >
                            Cancel
                        </Button>

                        <Button type="submit" :disabled="createForm.processing">
                            <Loader2
                                v-if="createForm.processing"
                                class="mr-2 h-4 w-4 animate-spin"
                            />

                            {{
                                createForm.processing
                                    ? 'Creating...'
                                    : 'Create List'
                            }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
    <!-- end of Create List Dialog -->

    <!-- Edit List Dialog -->

    <Dialog v-model:open="isEditDialogOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Edit List</DialogTitle>
                <DialogDescription>
                    Update the list name and color as needed.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="updateList" class="space-y-6">
                <!-- Name -->
                <div class="space-y-2">
                    <Label for="edit-name"> List Name </Label>

                    <Input
                        id="edit-name"
                        v-model="editForm.name"
                        placeholder="Example: Work Tasks"
                        autocomplete="off"
                    />

                    <InputError :message="editForm.errors.name" />
                </div>

                <div class="space-y-2">
                    <Label for="edit-color"> Color </Label>

                    <div class="flex items-center gap-3">
                        <Input
                            id="edit-color"
                            type="color"
                            class="h-11 w-20 cursor-pointer p-1"
                            v-model="editForm.color"
                        />

                        <span class="text-sm text-muted-foreground">
                            {{ editForm.color }}
                        </span>
                    </div>

                    <InputError :message="editForm.errors.color" />
                </div>

                <Button
                    type="submit"
                    class="w-full"
                    :disabled="editForm.processing"
                >
                    <Loader2
                        v-if="editForm.processing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />

                    {{ editForm.processing ? 'Updating...' : 'Update List' }}
                </Button>
            </form>
        </DialogContent>
    </Dialog>

    <!-- End of Edit List Dialog -->

    <div
        v-if="lists.length > 0"
        class="m-5 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
    >
        <Card
            v-for="list in lists"
            :key="list.id"
            class="group relative rounded-lg p-6 transition-shadow hover:shadow-md"
        >
            <CardHeader>
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <!-- List color -->
                        <div
                            class="h-3 w-3 shrink-0 rounded-full"
                            :style="{
                                backgroundColor: list.color || '#6366f1',
                            }"
                        ></div>

                        <!-- List name -->
                        <CardTitle class="text-base font-medium">
                            {{ list.name }}
                        </CardTitle>

                        <!-- Task count -->
                        <span class="text-2xl font-bold text-muted-foreground">
                            {{ list.tasks_count || 0 }}
                        </span>
                    </div>
                </div>
            </CardHeader>

            <CardContent>
                <p class="my-4 text-sm text-muted-foreground">
                    {{ list.tasks_count || 0 }}
                    {{ list.tasks_count === 1 ? 'task' : 'tasks' }}
                </p>

                <div class="flex gap-2">
                    <!-- View Tasks -->
                    <Link :href="`/tasks?list_id=${list.id}`" class="flex-1">
                        <Button variant="outline" size="sm" class="w-full">
                            <ExternalLink class="mr-2 h-4 w-4" />
                            View Tasks
                        </Button>
                    </Link>

                    <!-- Edit -->
                    <Button
                        @click="openEditDialog(list)"
                        variant="outline"
                        size="sm"
                    >
                        <Pencil class="h-4 w-4" />
                    </Button>

                    <!-- Delete -->
                    <Button
                        @click="deleteList(list.id)"
                        :disabled="deletingListId === list.id"
                        variant="destructive"
                        size="sm"
                    >
                        <Loader2
                            v-if="deletingListId === list.id"
                            class="mr-2 h-4 w-4 animate-spin"
                        />

                        <Trash v-else class="h-4 w-4" />
                    </Button>
                </div>
            </CardContent>
        </Card>
    </div>

    <!-- Empty state -->
    <Card v-else>
        <CardContent class="flex flex-col items-center justify-center py-12">
            <p class="mb-4 text-muted-foreground">No list yet</p>

            <p class="text-sm text-muted-foreground">
                Create your first list to get started
            </p>
        </CardContent>
    </Card>
    <!-- Delete List Dialog -->
</template>
