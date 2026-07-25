<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PendingInvitationsModal from '@/components/PendingInvitationsModal.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { dashboard } from '@/routes';
import type { DashboardInvitation, Team } from '@/types';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';

interface Course {
    id: number;
    name: string;
    description: string;
    image: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    pendingInvitations?: DashboardInvitation[];
    courses: Course[];
}

const props = defineProps<Props>();
defineOptions({
    layout: (props: { currentTeam?: Team | null }) => ({
        breadcrumbs: [
            {
                title: 'Course',
                href: props.currentTeam
                    ? dashboard(props.currentTeam.slug)
                    : '/',
            },
        ],
    }),
});
</script>

<template>
    <Head title="Courses" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <div class="mb-3 text-center">
            <h1 class="text-gray mb-4 text-4xl font-bold dark:text-white">
                Our Courses
            </h1>
            <p
                class="mx-auto max-w-3xl text-center text-xl text-gray-500 dark:text-gray-400"
            >
                we offer a wide range of courses to help you learn and grow.
                Browse through our selection and find the perfect course for
                you.
            </p>
        </div>

        <!--course card start -->

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            <Card
                v-for="course in props.courses"
                :key="course.id"
                class="pbg-white rounded-lg p-6 shadow-md transition-shadow duration-300 hover:shadow-lg dark:bg-gray-800"
            >
                <div
                    class="relative h-48 w-full overflow-hidden rounded-t-xl bg-gray-100 dark:bg-gray-700"
                >
                    <img
                        :src="course.image"
                        :alt="course.name"
                        class="h-full w-full object-cover"
                    />
                    <div
                        class="absolute top-4 right-4 rounded-full bg-blue-600 px-3 py-1 text-sm font-medium text-white"
                    >
                        {{ course.price }}
                    </div>
                </div>

                <CardHeader>
                    <CardTitle
                        class="test-lg font-semibold text-gray-900 dark:text-white"
                        >{{ course.name }}
                    </CardTitle>
                    <CardDescription
                        class="line-clamp-2 text-gray-600 dark:text-gray-300"
                        >{{ course.description }}</CardDescription
                    >
                </CardHeader>

                <CardContent>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Created at {{ course.created_at }}
                    </p>
                </CardContent>
                <CardFooter> </CardFooter>
            </Card>
        </div>
    </div>
    <!--coursecard end -->
</template>
