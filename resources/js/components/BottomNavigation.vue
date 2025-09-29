<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Calendar, Clock, Home, RefreshCw, Settings } from 'lucide-vue-next';

interface Props {
    currentRoute?: string;
}

const props = withDefaults(defineProps<Props>(), {
    currentRoute: '',
});

const navigationItems = [
    {
        href: '/home',
        icon: Home,
        label: 'Beranda',
        key: 'home',
    },
    {
        href: '/attendance',
        icon: Clock,
        label: 'Absensi',
        key: 'attendance',
    },
    {
        href: '/leave-requests',
        icon: Calendar,
        label: 'Cuti',
        key: 'leave-requests',
    },
    {
        href: '/shift-change-requests',
        icon: RefreshCw,
        label: 'Tukar Libur',
        key: 'shift-change-requests',
    },
    {
        href: '/user/profile',
        icon: Settings,
        label: 'Profil',
        key: 'profile',
    },
];

const isActive = (href: string) => {
    const current = props.currentRoute.toLowerCase();

    if (href === '/home') {
        return current === '/home' || current === '/' || current === '';
    }

    if (href === '/user/profile') {
        return current.includes('/profile') || current.includes('/user/profile');
    }

    if (href === '/leave-requests') {
        return current.includes('/leave-request');
    }

    if (href === '/shift-change-requests') {
        return current.includes('/shift-change-request');
    }

    if (href === '/attendance') {
        return current.includes('/attendance');
    }

    return current.startsWith(href.toLowerCase());
};
</script>

<template>
    <!-- Fixed Bottom Navigation - Full Width -->
    <div class="fixed right-0 bottom-0 left-0 z-50 border-t border-border/50 bg-background/95 backdrop-blur-md">
        <div class="mx-auto max-w-[480px]">
            <div class="grid grid-cols-5 px-2 py-1">
                <Link
                    v-for="item in navigationItems"
                    :key="item.key"
                    :href="item.href"
                    class="group flex flex-col items-center px-1 py-2 transition-all duration-200"
                >
                    <div class="relative">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full transition-colors duration-200 group-hover:bg-primary/20"
                            :class="isActive(item.href) ? 'bg-primary/15' : 'bg-transparent group-hover:bg-muted'"
                        >
                            <component
                                :is="item.icon"
                                class="h-4 w-4 transition-colors duration-200"
                                :class="isActive(item.href) ? 'text-primary' : 'text-muted-foreground group-hover:text-foreground'"
                            />
                        </div>
                        <div
                            v-if="isActive(item.href)"
                            class="absolute -bottom-1 left-1/2 h-1 w-1 -translate-x-1/2 transform rounded-full bg-primary"
                        ></div>
                    </div>
                    <span
                        class="mt-1 text-[10px] font-medium transition-colors duration-200"
                        :class="isActive(item.href) ? 'text-primary' : 'text-muted-foreground group-hover:text-foreground'"
                    >
                        {{ item.label }}
                    </span>
                </Link>
            </div>
        </div>
    </div>
</template>
