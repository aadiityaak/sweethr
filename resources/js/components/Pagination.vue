<template>
    <nav class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 dark:border-gray-700 dark:bg-gray-900">
        <div class="flex flex-1 justify-between sm:hidden">
            <Link
                v-if="links.prev"
                :href="links.prev"
                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
            >
                Previous
            </Link>
            <Link
                v-if="links.next"
                :href="links.next"
                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
            >
                Next
            </Link>
        </div>
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    Showing <span class="font-medium">{{ from }}</span> to <span class="font-medium">{{ to }}</span> of
                    <span class="font-medium">{{ total }}</span> results
                </p>
            </div>
            <div>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    <Link
                        v-if="links.prev"
                        :href="links.prev"
                        class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:ring-gray-600 dark:hover:bg-gray-700"
                    >
                        <span class="sr-only">Previous</span>
                        <ChevronLeft class="h-5 w-5" />
                    </Link>

                    <template v-for="link in paginationLinks" :key="link.label">
                        <Link
                            v-if="link.url && !link.active"
                            :href="link.url"
                            class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-700"
                        >
                            <span v-html="link.label"></span>
                        </Link>
                        <span
                            v-else-if="link.active"
                            class="relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                        >
                            <span v-html="link.label"></span>
                        </span>
                        <span
                            v-else
                            class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 ring-inset dark:text-gray-300 dark:ring-gray-600"
                        >
                            <span v-html="link.label"></span>
                        </span>
                    </template>

                    <Link
                        v-if="links.next"
                        :href="links.next"
                        class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:ring-gray-600 dark:hover:bg-gray-700"
                    >
                        <span class="sr-only">Next</span>
                        <ChevronRight class="h-5 w-5" />
                    </Link>
                </nav>
            </div>
        </div>
    </nav>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    links: {
        prev: string | null;
        next: string | null;
        data: PaginationLink[];
        from: number;
        to: number;
        total: number;
    };
}

const props = defineProps<Props>();

const from = computed(() => props.links.from || 0);
const to = computed(() => props.links.to || 0);
const total = computed(() => props.links.total || 0);

const paginationLinks = computed(() => {
    if (!props.links.data) return [];

    return props.links.data.filter((link: PaginationLink) => {
        return link.label !== 'Previous' && link.label !== 'Next';
    });
});
</script>
