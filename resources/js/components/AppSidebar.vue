<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Clock, Users, Calendar, MapPin, Settings } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
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
];

const footerNavItems: NavItem[] = [
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
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
