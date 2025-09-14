<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, LayoutGrid, Clock, Users, Calendar, MapPin, Settings, Shield, Building, UserCheck } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Menu items for regular users
const userNavItems: NavItem[] = [
    {
        title: 'Dasbor',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Kehadiran',
        href: '/attendance',
        icon: Clock,
    },
    {
        title: 'Pengajuan Cuti',
        href: '/leave-requests',
        icon: Calendar,
    },
];

// Menu items for admins
const adminNavItems: NavItem[] = [
    {
        title: 'Dasbor',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Kelola Kehadiran',
        href: '/admin/attendance',
        icon: Clock,
    },
    {
        title: 'Karyawan',
        href: '/employees',
        icon: Users,
    },
    {
        title: 'Pengajuan Cuti',
        href: '/leave-requests',
        icon: Calendar,
    },
    {
        title: 'Lokasi Kantor',
        href: '/office-locations',
        icon: MapPin,
    },
    {
        title: 'Manajemen Shift',
        href: '/work-shifts',
        icon: UserCheck,
    },
    {
        title: 'Departemen',
        href: '/departments',
        icon: Building,
    },
];

// Computed property to get the appropriate menu items based on user role
const mainNavItems = computed<NavItem[]>(() => {
    return user.value?.is_admin ? adminNavItems : userNavItems;
});

// Footer menu items for regular users
const userFooterNavItems: NavItem[] = [
    {
        title: 'Pengaturan',
        href: '/settings/profile',
        icon: Settings,
    },
    {
        title: 'Bantuan & Dukungan',
        href: '/help',
        icon: BookOpen,
    },
];

// Footer menu items for admins
const adminFooterNavItems: NavItem[] = [
    {
        title: 'Pengaturan',
        href: '/settings/profile',
        icon: Settings,
    },
    {
        title: 'Admin Panel',
        href: '/admin',
        icon: Shield,
    },
    {
        title: 'Bantuan & Dukungan',
        href: '/help',
        icon: BookOpen,
    },
];

// Computed property to get the appropriate footer items based on user role
const footerNavItems = computed<NavItem[]>(() => {
    return user.value?.is_admin ? adminFooterNavItems : userFooterNavItems;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
