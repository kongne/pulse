<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';

interface Props {
    url: string;
    filters?: Record<string, string | number | null | undefined>;
    label?: string;
    title?: string;
}

interface ExportFormat {
    key: string;
    label: string;
    description: string;
    icon: string;
}

const props = withDefaults(defineProps<Props>(), {
    filters: () => ({}),
    label: 'Export',
    title: 'Export data as',
});

const isOpen = ref(false);

const formats: ExportFormat[] = [
    {
        key: 'csv',
        label: 'CSV',
        description: 'Spreadsheet data',
        icon: 'CSV',
    },
    {
        key: 'json',
        label: 'JSON',
        description: 'Developer data',
        icon: '{}',
    },
    {
        key: 'xlsx',
        label: 'Excel',
        description: 'XLSX spreadsheet',
        icon: 'XLSX',
    },
    {
        key: 'pdf',
        label: 'PDF',
        description: 'Printable report',
        icon: 'PDF',
    },
];

const buildExportUrl = (format: string): string => {
    const params = new URLSearchParams();

    params.set('format', format);

    Object.entries(props.filters).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            params.set(key, String(value));
        }
    });

    return `${props.url}?${params.toString()}`;
};

const exportData = (format: string): void => {
    const url = buildExportUrl(format);

    closeDropdown();

    if (format === 'pdf') {
        window.open(url, '_blank', 'noopener,noreferrer');

        return;
    }

    window.location.href = url;
};

const toggleDropdown = (): void => {
    isOpen.value = !isOpen.value;
};

const closeDropdown = (): void => {
    isOpen.value = false;
};

const handleEscape = (event: KeyboardEvent): void => {
    if (event.key === 'Escape') {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleEscape);
});

onBeforeUnmount(() => {
    document.removeEventListener('keydown', handleEscape);
});
</script>

<template>
    <div class="relative w-full sm:w-auto">
        <!-- Trigger -->
        <button
            type="button"
            class="inline-flex h-9 w-full items-center justify-center gap-2 rounded-md border border-input bg-background px-3 text-sm font-medium text-foreground shadow-sm transition-colors hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none sm:w-auto"
            :aria-expanded="isOpen"
            aria-haspopup="menu"
            @click="toggleDropdown"
        >
            <!-- Download icon -->
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path d="M12 3v12" />
                <path d="m7 10 5 5 5-5" />
                <path d="M5 21h14" />
            </svg>

            <span>{{ label }}</span>

            <!-- Chevron -->
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="14"
                height="14"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                :class="[
                    'transition-transform duration-200',
                    isOpen ? 'rotate-180' : '',
                ]"
                aria-hidden="true"
            >
                <path d="m6 9 6 6 6-6" />
            </svg>
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="scale-95 opacity-0"
            enter-to-class="scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="scale-100 opacity-100"
            leave-to-class="scale-95 opacity-0"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 z-50 mt-2 w-[calc(100vw-2rem)] max-w-64 overflow-hidden rounded-lg border border-border bg-popover p-1 text-popover-foreground shadow-lg sm:w-64"
                role="menu"
            >
                <!-- Header -->
                <div class="border-b border-border px-3 py-2">
                    <p class="text-sm font-medium">
                        {{ title }}
                    </p>

                    <p class="mt-0.5 text-xs text-muted-foreground">
                        Choose a format
                    </p>
                </div>

                <!-- Formats -->
                <div class="py-1">
                    <button
                        v-for="format in formats"
                        :key="format.key"
                        type="button"
                        role="menuitem"
                        class="flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-left transition-colors hover:bg-muted focus:bg-muted focus:outline-none"
                        @click="exportData(format.key)"
                    >
                        <!-- Format badge -->
                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-muted text-[10px] font-bold text-muted-foreground"
                        >
                            {{ format.icon }}
                        </span>

                        <!-- Description -->
                        <span class="min-w-0 flex-1">
                            <span
                                class="block text-sm font-medium text-foreground"
                            >
                                {{ format.label }}
                            </span>

                            <span
                                class="block truncate text-xs text-muted-foreground"
                            >
                                {{ format.description }}
                            </span>
                        </span>

                        <!-- Arrow -->
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="14"
                            height="14"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="shrink-0 text-muted-foreground"
                            aria-hidden="true"
                        >
                            <path d="m9 18 6-6-6-6" />
                        </svg>
                    </button>
                </div>

                <!-- Close -->
                <div class="border-t border-border px-1 pt-1">
                    <button
                        type="button"
                        class="w-full rounded-md px-3 py-2 text-xs text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                        @click="closeDropdown"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
