<script setup lang="ts">
import { Loader } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Link, router } from '@inertiajs/vue3';
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
} from '@/components/ui/dialog';
import { index, update, destroy, store } from '@/routes/category';
import { local as storageLocal } from '@/routes/storage';
import { ref, computed } from 'vue';

interface Category {
    id: number;
    name: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}
interface Props {
    categories: {
        data: Category[];
        links: PaginationLink[];

        current_page: number;
        last_page: number;
        per_page: number;

        total: number;
        from: number | null;
        to: number | null;
    };
    filters: {
        per_page: number;
    };
}

const props = defineProps<Props>();

const perPage = ref(props.filters.per_page ?? 5);
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
                title: 'Category Management',
                href: index.url(),
            },
        ],
    }),
});

const items = computed(() => props.categories?.data ?? []);

const editOpen = ref(false);
const deleteOpen = ref(false);
const createOpen = ref(false);
const selected = ref<Category | null>(null);
const newName = ref('');

const openEdit = (category: Category) => {
    selected.value = category;
    editOpen.value = true;
};
const openDelete = (category: Category) => {
    selected.value = category;
    deleteOpen.value = true;
};

const imageUrl = (path?: string | null) => {
    if (!path) return null;

    return storageLocal(path).url;
};

const decodeEntities = (str: string) => {
    const textArea = document.createElement('textarea');
    textArea.innerHTML = str;
    return textArea.value;
};

const formatEmbed = (input?: string | null) => {
    if (!input) return null;

    const decoded = decodeEntities(input);

    return decoded
        .replace(/width\s*=\s*["']\d+["']/gi, 'width="560"')
        .replace(/height\s*=\s*["']\d+["']/gi, 'width="460"');
};
const openCreate = () => {
    createOpen.value = true;
};

const closeAll = () => {
    editOpen.value = false;
    deleteOpen.value = false;
    createOpen.value = false;
};
</script>

<template>
    <Card class="m-4 overflow-hidden">
        <CardHeader>
            <div class="flex items-center justify-between">
                <CardTitle> Categories </CardTitle>

                <Button size="sm" @click="createOpen = true"
                    >Add Category</Button
                >
            </div>
        </CardHeader>

        <CardContent>
            <div class="overflow-x-auto">
                <table class="w-full" text left>
                    <thead>
                        <tr class="border-b">
                            <th class="text sm px-3 py-2 text-muted-foreground">
                                Name
                            </th>

                            <th class="px-3 py-2 text-sm text-muted-foreground">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="category in items"
                            :key="category.id"
                            class="border-b hover:bg-muted/30"
                        >
                            <td class="px-3 py-3">{{ category.name }}</td>

                            <td>
                                <div class="gap 1 flex">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="openEdit(category)"
                                        >Edit</Button
                                    >

                                    <Button
                                        size="sm"
                                        variant="destructive"
                                        @click="openDelete(category)"
                                        >Delete</Button
                                    >
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="props.categories?.links?.length"
                class="mt-4 flex items-center gap-2"
            >
                <Link
                    v-for="link in props.categories.links"
                    :key="link.label"
                    :href="link.url ?? index().url"
                    class="rounded px-3 py-1 text-sm"
                    :class="[
                        link.active
                            ? 'bg-muted text-foreground'
                            : 'text-muted-foreground hover:bg-muted/60',

                        !link.url ? 'pointer-events-none opacity-50' : '',
                    ]"
                    preserve-scroll
                >
                    <span v-html="link.label" />
                </Link>

                <!--Edit Modal-->

                <Dialog v-model:open="editOpen">
                    <DialogContent class="sm:max-x-130">
                        <DialogHeader>
                            <DialogTitle>Edit Category</DialogTitle>

                            <DialogDescription>
                                Update Category details and save your changes.
                            </DialogDescription>
                        </DialogHeader>

                        <Form
                            v-if="selected"
                            v-bind="update.form(selected.id)"
                            enctype="multipart/form-data"
                            reset-on-error
                            @success="editOpen = false"
                            v-slot="{ errors, processing }"
                            class="space-y-4"
                        >
                            <div class="grid gap-2">
                                <Label for="name">Name</Label>

                                <Input
                                    id="name"
                                    name="name"
                                    type="text"
                                    required
                                    :default-value="selected?.name"
                                />

                                <InputError :message="errors?.name" />
                            </div>

                            <DialogFooter>
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="editOpen = false"
                                    :disabled="processing"
                                    >Cancel</Button
                                >

                                <Button type="submit" :disabled="processing"
                                    >Save</Button
                                >
                            </DialogFooter>
                        </Form>
                    </DialogContent>
                </Dialog>

                <!--End of Edit-->

                <!--Create Modal-->

                <Dialog v-model:open="createOpen">
                    <DialogContent class="sm:max-w-135">
                        <DialogHeader>
                            <DialogTitle>Add Category</DialogTitle>

                            <DialogDescription>
                                Enter category details to add a new category.
                            </DialogDescription>
                        </DialogHeader>

                        <Form
                            v-bind="store.form()"
                            enctype="multipart/form-data"
                            reset-on-error
                            @success="
                                () => {
                                    createOpen = false;

                                    newName.value = '';
                                }
                            "
                            v-slot="{ errors, processing }"
                            class="space-y-4"
                        >
                            <div class="grid gap-2">
                                <Label for="new-name">Name</Label>

                                <Input
                                    id="name"
                                    name="name"
                                    type="text"
                                    v-model="newName"
                                    required
                                />

                                <InputError :message="errors?.name" />
                            </div>

                            <DialogFooter>
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="createOpen = false"
                                    :disabled="processing"
                                    >Cancel</Button
                                >

                                <Button type="submit" :disabled="processing"
                                    >Create</Button
                                >
                            </DialogFooter>
                        </Form>
                    </DialogContent>
                </Dialog>

                <!--- end of Create --->

                <!-- Delete modal --->

                <Dialog v-model:open="deleteOpen">
                    <DialogContent class="sm:max-w-135">
                        <DialogHeader>
                            <DialogTitle>Delete Category</DialogTitle>

                            <DialogDescription
                                >This action cannot be undone. Confirm it
                                below</DialogDescription
                            >
                        </DialogHeader>

                        <Form
                            v-if="selected"
                            v-bind="destroy.form(selected.id)"
                            reset-on-error
                            @success="deleteOpen = false"
                            v-slot="{ processing }"
                        >
                            <p class="mb-4 text-sm text-muted-foreground">
                                Are you sure you want to delete this item

                                <span class="font-medium text-foreground">{{
                                    selected?.name
                                }}</span
                                >?
                            </p>

                            <DialogFooter>
                                <Button
                                    type="button"
                                    variant="destructive"
                                    @click="deleteOpen = false"
                                    :disabled="processing"
                                    >Cancel</Button
                                >

                                <Button
                                    type="submit"
                                    variant="destructive"
                                    :disabled="processing"
                                    >Delete</Button
                                >
                            </DialogFooter>
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>
        </CardContent>
    </Card>
</template>
