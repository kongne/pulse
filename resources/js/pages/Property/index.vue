<script setup lang="ts">
import { Loader } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link, router } from '@inertiajs/vue3';
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
import { index, update, destroy, store } from '@/routes/property';
import { local as storageLocal } from '@/routes/storage';
import { ref, computed } from 'vue';

interface Property {
    id: number;
    name: string;
    location?: string | null;
    price: number;
    description?: string | null;
    image?: string | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}
interface Props {
    properties: {
        data: Property[];
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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Properties',
        href: index.url(),
    },
];

const items = computed(() => props.properties?.data ?? []);

const editOpen = ref(false);
const deleteOpen = ref(false);
const createOpen = ref(false);
const selected = ref<Property | null>(null);
const newLocation = ref('');

const openEdit = (property: Property) => {
    selected.value = property;
    editOpen.value = true;
};
const openDelete = (property: Property) => {
    selected.value = property;
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
    <Head title="Properties" />
    <Card class="m-4 overflow-hidden">
        <CardHeader>
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-sm">Show</span>

                    <select
                        v-model="perPage"
                        @change="changePerPage"
                        class="rounded-md border px-2 py-1"
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
                <p class="text-sm text-muted-foreground">
                    Showing {{ props.properties.from }} to
                    {{ props.properties.to }} of
                    {{ props.properties.total }} entries
                </p>
            </div>
            <div class="flex items-center justify-between">
                <CardTitle>Properties</CardTitle>
                <Button size="sm" @click="createOpen = true">
                    Add location
                </Button>
            </div>
        </CardHeader>
        <CardContent>
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="border-6">
                            <th class="px-3 py-2 text-sm text-muted-foreground">
                                Image
                            </th>
                            <th class="px-3 py-2 text-sm text-muted-foreground">
                                Name
                            </th>
                            <th class="px-3 py-2 text-sm text-muted-foreground">
                                Location
                            </th>
                            <th class="px-3 py-2 text-sm text-muted-foreground">
                                Price
                            </th>
                            <th class="px-3 py-2 text-sm text-muted-foreground">
                                Description
                            </th>

                            <th
                                class="col-span-2 px-3 py-2 text-sm text-muted-foreground"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="properties in items"
                            :key="properties.id"
                            class="border-b hover:bg-muted/30"
                        >
                            <td class="px-3 py-2 text-sm">
                                <img
                                    v-if="imageUrl(properties.image)"
                                    :src="imageUrl(properties.image)"
                                    alt="Property image"
                                    class="h-14 w-20 rounded border bg-muted"
                                />
                            </td>
                            <td class="px-3 py-2 text-sm">
                                {{ properties.name }}
                            </td>
                            <td class="px-3 py-2 text-sm">
                                <div
                                    v-if="properties.location"
                                    v-html="formatEmbed(properties.location)"
                                    style="
                                        width: 140px;
                                        overflow: hidden;
                                        border-radius: 8px;
                                    "
                                ></div>
                            </td>
                            <td class="px-3 py-2 text-sm">
                                {{ properties.price }}
                            </td>
                            <td class="px-3 py-2 text-sm">
                                <div html class="text-sm text-muted-foreground">
                                    {{
                                        properties.description ||
                                        'Description unavailable'
                                    }}
                                </div>
                            </td>
                            <td class="px-3 py-2 text-sm">
                                <div class="flex gap-2">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="openEdit(properties)"
                                        >Edit</Button
                                    >
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="openDelete(properties)"
                                        >Delete</Button
                                    >
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="props.properties?.links?.length"
                class="mt-4 flex items-center gap-2"
            >
                <Link
                    v-for="link in props.properties.links"
                    :key="link.label"
                    :href="link.url ?? index().url"
                    class="rounded px-3 py-1 text-sm"
                    :class="[
                        link.active
                            ? 'bg-muted text-foreground'
                            : 'text-muted-foreground hover:bg-muted/60',
                    ]"
                    preserve-scroll
                >
                    <span v-html="link.label" />
                </Link>
            </div>
        </CardContent>
    </Card>

    <!--Edit Modal-->
    <Dialog v-model:open="editOpen">
        <DialogContent class="sm:max-w-135">
            <DialogHeader>
                <DialogTitle>Edit Property</DialogTitle>
                <DialogDescription
                    >Update whatsoeve you wish and the result will be
                    spontaneous</DialogDescription
                >
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
                <div class="grid gap-2">
                    <Label for="location">Location</Label>
                    <Input
                        id="location"
                        name="location"
                        type="text"
                        required
                        :default-value="selected?.location"
                    />
                    <InputError :message="errors?.location" />
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
                    <InputError :message="errors?.price" />
                </div>
                <div class="grid gap-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        name="description"
                        required
                        :default-value="selected?.description ?? ''"
                        placeholder="enter the required texts here."
                    />
                    <InputError :message="errors?.description" />
                </div>
                <div class="grid gap-2">
                    <Label for="image">Image</Label>
                    <Input
                        id="image"
                        name="image"
                        type="file"
                        accept="image/*"
                    />
                    <div
                        v-if="selected?.image"
                        class="text-xs text-muted-foreground"
                    >
                        Current image shown in table
                    </div>
                    <InputError :message="errors?.image" />
                </div>
                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="editOpen = false"
                        :disabled="processing"
                    >
                        Cancel</Button
                    >
                    <Button type="submit" :disabled="processing">
                        <Loader
                            v-if="processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        ></Loader>
                        Save changes
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
    <!--edit Modal-->

    <!--create starts-->
    <Dialog v-model:open="createOpen">
        <DialogContent class="sm:max-w-135">
            <DialogHeader>
                <DialogTitle>Add location</DialogTitle>
                <DialogDescription>
                    Enter property details to add a new location.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="store.form()"
                enctype="multipart/form-data"
                reset-on-error
                @success="
                    () => {
                        createOpen = false;
                        newLocation = '';
                    }
                "
                v-slot="{ errors, processing }"
                class="space-y-4"
            >
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" type="text" name="name" required />
                    <InputError :message="errors?.name" />
                </div>
                <div class="grid gap-2">
                    <Label for="location">Location</Label>
                    <Input
                        id="location"
                        type="text"
                        name="location"
                        v-model="newLocation"
                        required
                    />
                    <InputError :message="errors?.location" />
                    <div v-if="newLocation" class="rounded border p-2">
                        <div
                            style="
                                width: 150px;
                                height: 100px;
                                overflow: hidden;
                                border-radius: 8px;
                            "
                            v-html="formatEmbed(newLocation)"
                        ></div>
                    </div>
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
                    <InputError :message="errors?.price" />
                </div>
                <div class="grid gap-2">
                    <Label for="description">Description</Label>
                    <Input
                        id="description"
                        name="description"
                        type="text"
                        placeholder="Optional"
                    />
                    <InputError :message="errors?.description" />
                </div>
                <div class="grid gap-2">
                    <Label for="image">Image</Label>
                    <Input
                        id="image"
                        name="image"
                        type="file"
                        accept="image/*"
                    />
                    <InputError :message="errors?.image" />
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="createOpen"
                        :disabled="processing"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        <Loader
                            v-if="processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        create
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
    <!--end of create-->

    <!--delete starts-->

    <Dialog v-model:open="deleteOpen">
        <DialogContent class="sm:max-w-135">
            <DialogHeader>
                <DialogTitle>Delete Property</DialogTitle>
                <DialogDescription>
                    This action cannot be undone. Confirm deletion below.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-if="selected"
                v-bind="destroy.form(selected.id)"
                reset-on-error
                @success="
                    () => {
                        deleteOpen = false;
                        selected = null;
                    }
                "
                v-slot="{ processing }"
            >
                <p class="mb-4 text-sm text-muted-foreground">
                    Are you sure you want to delete this property?
                    <span class="font-medium text-foreground">{{
                        selected.name
                    }}</span>
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
                        class="bg-red-500 hover:text-blue-500"
                    >
                        Delete
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
