<script setup lang="ts">
import { Loader } from 'lucide-vue-next';
import { Head, Form } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';

import { dashboard } from '@/routes';
import { store as contactStore } from '@/routes/contact';

import type { BreadcrumbItem } from '@/types';

import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
    CardFooter,
} from '@/components/ui/card';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

import InputError from '@/components/InputError.vue';

interface Props {
    pendingInvitations?: DashboardInvitation[];
    courses: Course[];
}

const props = defineProps<Props>();
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
                title: 'Contact',
                href: props.currentTeam
                    ? dashboard(props.currentTeam.slug)
                    : '/',
            },
        ],
    }),
});
</script>

<template>
    <Head title="Contact" />

    <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div
            class="relative flex-1 rounded-xl border border-sidebar-border/70 p-4"
        >
            <Card
                class="bg-sidebar-background/50 mx-auto w-full max-w-4xl rounded-xl border border-sidebar-border/70 p-6 shadow-lg backdrop-blur-md"
            >
                <CardHeader>
                    <CardTitle>Contact Us</CardTitle>
                    <CardDescription>
                        Send us your details and we'll get back to you.
                    </CardDescription>
                </CardHeader>

                <Form
                    v-bind="contactStore.form()"
                    reset-on-success
                    :options="{ preserveScroll: true }"
                    v-slot="{ errors, processing }"
                >
                    <CardContent class="space-y-6">
                        <!-- Name -->
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>

                            <Input
                                id="name"
                                name="name"
                                type="text"
                                autocomplete="name"
                                required
                                autofocus
                                :aria-invalid="!!errors?.name"
                            />

                            <InputError :message="errors?.name as string" />
                        </div>

                        <!-- Email -->
                        <div class="grid gap-2">
                            <Label for="email">Email</Label>

                            <Input
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                required
                                autofocus
                                :aria-invalid="!!errors?.email"
                            />

                            <InputError :message="errors?.email as string" />
                        </div>

                        <!-- Phone -->
                        <div class="grid gap-2">
                            <Label for="phone">Phone</Label>

                            <Input
                                id="phone"
                                name="phone"
                                type="tel"
                                autocomplete="tel"
                                required
                                :aria-invalid="!!errors?.phone"
                            />

                            <InputError :message="errors?.phone as string" />
                        </div>

                        <!-- Message -->
                        <div class="grid gap-2">
                            <Label for="message">Message</Label>

                            <Textarea
                                id="message"
                                name="message"
                                rows="5"
                                required
                                placeholder="Write your message here..."
                                :aria-invalid="!!errors?.message"
                            />

                            <InputError :message="errors?.message as string" />
                        </div>
                    </CardContent>

                    <CardFooter>
                        <Button
                            type="submit"
                            class="mt-4 w-full justify-center"
                            :disabled="processing"
                        >
                            <Loader
                                v-if="processing"
                                class="mr-2 h-4 w-4 animate-spin"
                            />

                            Send Message
                        </Button>
                    </CardFooter>
                </Form>
            </Card>
        </div>
    </div>
</template>
