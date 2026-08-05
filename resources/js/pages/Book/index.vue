<script setup lang="ts">
import { Loader } from 'lucide-vue-next';
import { dashboard } from '@/routes';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Link, router, Head } from '@inertiajs/vue3';

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
import { index, update, destroy, store } from '@/routes/book';
import { local as storageLocal } from '@/routes/storage';
import { ref, computed } from 'vue';

interface Book {
    id: number;
    category_id: number;
    title: string;
    author: string;
    price: number;
    cover_image?: string | null;
    category?: {
        name: string;
    };
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    books: {
        data: Book[];
        links: PaginationLink[];

        current_page: number;
        last_page: number;
        per_page: number;

        total: number;
        from: number | null;
        to: number | null;
    };
    categories: {
        id: number;
        name: string;
    }[];

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
                title: 'Book Management',
                href: index.url(),
            },
        ],
    }),
});

const items = computed(() => props.books?.data ?? []);

const editOpen = ref(false);
const selected = ref<Book | null>(null);
const deleteOpen = ref(false);
const createOpen = ref(false);

const openEdit = (book: Book) => {
    selected.value = book;
    editOpen.value = true;
};

const openDelete = (book: Book) => {
    selected.value = book;
    deleteOpen.value = true;
};

const openCreate = () => {
    createOpen.value = true;
};

const closeAll = () => {
    editOpen.value = false;
    deleteOpen.value = false;
    createOpen.value = false;
};

const imageUrl = (path?: string | null) => {
    if (!path) return undefined;

    return storageLocal(path).url;
};
</script>

<template>
    <Head title="Book Management" />

    <Card class="m-4 overflow-hidden">
        <CardHeader>
            <div class="mb-2 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-sm">Show</span>

                    <select
                        v-model="perPage"
                        @change="changePerPage"
                        class="rounded-md border px-2 py-1 text-black dark:text-gray-500"
                    >
                        <option :value="5">5</option>
                        <option :value="10">10</option>
                        <option :value="15">15</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                        <option :value="100">100</option>
                    </select>

                    <span class="text-sm">entries</span>
                </div>
                <Button size="sm" @click="createOpen = true"> Add book </Button>
            </div>
            <div class="flex items-center justify-between">
                <CardTitle
                    ><p class="mt-1 text-sm text-muted-foreground">
                        Showing {{ props.books.from }} to
                        {{ props.books.to }} of {{ props.books.total }} entries
                    </p>
                </CardTitle>
            </div>
        </CardHeader>

        <CardContent>
            <div class="overflow-x-auto">
                <table class="w-full" text left>
                    <thead>
                        <tr class="border-b">
                            <th class="px-3 py-2 text-sm text-muted-foreground">
                                Cover Image
                            </th>
                            <th class="px-3 py-2 text-sm text-muted-foreground">
                                Title
                            </th>
                            <th class="px-3 py-2 text-sm text-muted-foreground">
                                Author
                            </th>
                            <th class="px-3 py-2 text-sm text-muted-foreground">
                                Category
                            </th>
                            <th class="px-3 py-2 text-sm text-muted-foreground">
                                Price
                            </th>
                            <th class="px-3 py-2 text-sm text-muted-foreground">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="book in items"
                            :key="book.id"
                            class="border-b"
                        >
                            <td class="px-3 py-3">
                                <img
                                    v-if="imageUrl(book.cover_image)"
                                    :src="imageUrl(book.cover_image)"
                                    alt="book cover"
                                    class="h-16 w-16 rounded border object-cover"
                                />
                                <div
                                    v-else
                                    class="flex h-16 w-16 items-center justify-center rounded border bg-muted"
                                ></div>
                            </td>
                            <td class="px-3 py-3">{{ book.title }}</td>
                            <td class="px-3 py-3">{{ book.author }}</td>
                            <td class="px-3 py-3">
                                <span
                                    class="inline-block rounded bg-blue-500 px-2 py-1 text-xs font-medium text-white"
                                    >{{ book.category?.name || '' }}</span
                                >
                            </td>
                            <td class="px-3 py-3">
                                {{
                                    Number(book.price).toLocaleString(
                                        undefined,
                                        {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2,
                                        },
                                    )
                                }}
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex gap-2">
                                    <Button
                                        size="sm"
                                        @click="openEdit(book)"
                                        variant="outline"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        size="sm"
                                        @click="openDelete(book)"
                                        variant="destructive"
                                    >
                                        Delete
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="props.books.links.length"
                class="flex items-center gap-2"
            >
                <Link
                    v-for="link in props.books.links"
                    :key="link.label"
                    :href="link.url || index().url"
                    class="rounded px-3 py-1 text-sm"
                    :class="[
                        link.active
                            ? 'bg-muted text-foreground'
                            : 'bg-muted text-muted-foreground hover:bg-muted/60',
                        !link.url ? 'pointer-events-none opacity-50' : '',
                    ]"
                    preserve-scroll
                >
                    <span v-html="link.label"></span>
                </Link>
            </div>
            <!-- <div class="text-sm text-muted-foreground">
                    Showing <span class="font-medium">{{ (currentPage - 1) * itemsPerPage + 1 }}</span> to <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, totalItems) }}</span> of <span class="font-medium">{{ totalItems }}</span> results
                </div>
                <div class="flex gap-2">
                    <Button
                        size="sm"
                        variant="outline"
                        @click="changePage(currentPage - 1)"
                        :disabled="currentPage === 1"
                    >
                        Previous
                    </Button>
                    <Button
                        size="sm"
                        variant="outline"
                        @click="changePage(currentPage + 1)"
                        :disabled="currentPage === totalPages"
                    >
                        Next
                    </Button>
                </div>
            </div>-->
            <!-- End of Pagination -->
        </CardContent>
    </Card>

    <!-- Create Book Dialog -->
    <Dialog v-model:open="createOpen">
        <DialogContent class="sm:max-w-[540px]">
            <DialogHeader>
                <DialogTitle>Add Book</DialogTitle>

                <DialogDescription>
                    Enter book details to add a new book.
                </DialogDescription>
            </DialogHeader>
            <Form
                v-bind="store.form()"
                enctype="multipart/form-data"
                reset-on-error
                @success="createOpen = false"
                v-slot="{ errors, processing }"
                class="space-y-4"
            >
                <div class="grid gap-2">
                    <Label for="title">Title</Label>
                    <Input id="title" name="title" type="text" required />
                    <InputError :message="errors.title" />
                </div>

                <div class="grid gap-2">
                    <Label for="author">Author</Label>
                    <Input id="author" name="author" type="text" required />
                    <InputError :message="errors.author" />
                </div>

                <div class="grid gap-2">
                    <Label for="categories_id">Category</Label>
                    <select
                        id="categories_id"
                        name="categories_id"
                        required
                        class="mt-1 w-full rounded border bg-background px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option disabled selected value="">
                            Select a category
                        </option>
                        <option
                            v-for="category in props.categories"
                            :key="category.id"
                            :value="category.id"
                            class="bg:text-gray-900 text-white"
                        >
                            {{ category.name }}
                        </option>
                    </select>
                    <InputError :message="errors.categories_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="price">Price</Label>
                    <Input
                        id="price"
                        name="price"
                        type="number"
                        step="0.01"
                        required
                    />
                    <InputError :message="errors.price" />
                </div>

                <div class="grid gap-2">
                    <Label for="cover_image">Cover Image</Label>
                    <Input
                        id="cover_image"
                        name="cover_image"
                        type="file"
                        accept="image/*"
                        required
                        class="mt-1 w-full rounded border bg-background px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus:outline-none"
                    />
                    <InputError :message="errors.cover_image" />
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="createOpen = false"
                        :disabled="processing"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        <span v-if="processing" class="mr-2">
                            <Loader class="h-4 w-4 animate-spin" />
                        </span>
                        Add Book
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
    <!-- End of Create Book Dialog -->
    <!-- Edit Book Dialog -->
    <Dialog v-model:open="editOpen">
        <DialogContent class="sm:max-w-[540px]">
            <DialogHeader>
                <DialogTitle>Edit Book</DialogTitle>
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
                    <Label for="title">Title</Label>
                    <Input
                        id="title"
                        name="title"
                        type="text"
                        required
                        :default-value="selected?.title"
                    />
                    <InputError :message="errors.title" />
                </div>

                <div class="grid gap-2">
                    <Label for="author">Author</Label>
                    <Input
                        id="author"
                        name="author"
                        type="text"
                        required
                        :default-value="selected?.author"
                    />
                    <InputError :message="errors.author" />
                </div>

                <div class="grid gap-2">
                    <Label for="categories_id">Category</Label>
                    <select
                        id="categories_id"
                        name="categories_id"
                        required
                        :value="selected?.category_id"
                        class="mt-1 w-full rounded border border-input bg-background px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option
                            disabled-value=""
                            v-for="category in props.categories"
                            :key="category.id"
                            :value="category.id"
                            class="text-white"
                        >
                            {{ category.name }}
                        </option>
                    </select>
                    <InputError :message="errors.categories_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="price">Price</Label>
                    <Input
                        id="price"
                        name="price"
                        type="number"
                        step="0.01"
                        required
                        :default-value="selected?.price"
                    />
                    <InputError :message="errors.price" />
                </div>

                <div class="grid gap-2">
                    <Label for="cover_image">Cover Image</Label>
                    <Input
                        id="cover_image"
                        name="cover_image"
                        type="file"
                        accept="image/*"
                    />
                    <InputError :message="errors.cover_image" />
                    <div
                        v-if="selected?.cover_image"
                        class="text-xs text-muted-foreground"
                    >
                        Current cover shown in the gallery:
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="editOpen = false"
                            :disabled="processing"
                        >
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="processing">
                            <span v-if="processing" class="mr-2">
                                <Loader class="h-4 w-4 animate-spin" />
                            </span>
                            Update Book
                        </Button>
                    </DialogFooter>
                </div>
            </Form>
        </DialogContent>
    </Dialog>
    <!-- End of Edit Book Dialog -->

    <!-- Delete Book Dialog -->
    <Dialog v-model:open="deleteOpen">
        <DialogContent class="sm:max-w-[540px]">
            <DialogHeader>
                <DialogTitle>Delete Book</DialogTitle>
            </DialogHeader>

            <Form
                v-if="selected"
                v-bind="destroy.form(selected.id)"
                reset-on-error
                @success="deleteOpen = false"
                v-slot="{ processing }"
            >
                <p class="mb-4 text-sm text-muted-foreground">
                    Are you sure you want to delete this book
                    <span class="font-medium text-foreground">{{
                        selected?.title
                    }}</span
                    >?
                </p>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="deleteOpen = false"
                        :disabled="processing"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        variant="destructive"
                        :disabled="processing"
                    >
                        <span v-if="processing" class="mr-2">
                            <Loader class="h-4 w-4 animate-spin" />
                        </span>
                        Delete Book
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
    <!-- End of Delete Book Dialog -->
</template>
