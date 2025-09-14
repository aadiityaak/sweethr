<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Home, Clock, Calendar, Settings } from 'lucide-vue-next';

interface Props {
    currentRoute?: string;
}

const props = withDefaults(defineProps<Props>(), {
    currentRoute: ''
});

const navigationItems = [
    {
        href: '/home',
        icon: Home,
        label: 'Beranda',
        key: 'home'
    },
    {
        href: '/attendance',
        icon: Clock,
        label: 'Absensi',
        key: 'attendance'
    },
    {
        href: '/leave-requests',
        icon: Calendar,
        label: 'Cuti',
        key: 'leave-requests'
    },
    {
        href: '/user/profile',
        icon: Settings,
        label: 'Profil',
        key: 'profile'
    }
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

    if (href === '/attendance') {
        return current.includes('/attendance');
    }

    return current.startsWith(href.toLowerCase());
};
</script>

<template>
    <!-- Fixed Bottom Navigation - Full Width -->
    <div class="fixed bottom-0 left-0 right-0 bg-background/95 backdrop-blur-md border-t border-border/50 z-50">
        <div class="mx-auto max-w-[480px]">
            <div class="grid grid-cols-4 px-2 py-1">
                <Link
                    v-for="item in navigationItems"
                    :key="item.key"
                    :href="item.href"
                    class="flex flex-col items-center py-2 px-1 group transition-all duration-200"
                >
                    <div class="relative">
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center group-hover:bg-primary/20 transition-colors duration-200"
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
                            class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-1 h-1 bg-primary rounded-full"
                        ></div>
                    </div>
                    <span
                        class="text-[10px] font-medium mt-1 transition-colors duration-200"
                        :class="isActive(item.href) ? 'text-primary' : 'text-muted-foreground group-hover:text-foreground'"
                    >
                        {{ item.label }}
                    </span>
                </Link>
            </div>
        </div>
    </div>
</template>