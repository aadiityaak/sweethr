<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
// import { welcome } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Building,
    Calendar,
    Clock,
    DollarSign,
    FileText,
    FolderOpen,
    LayoutGrid,
    MapPin,
    Megaphone,
    Minus,
    RefreshCw,
    Settings,
    UserCheck,
    Users,
    UsersRound,
    Wallet,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Menu items for regular users
const userNavItems: NavItem[] = [
    {
        title: 'Dasbor',
        href: '/home',
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
    {
        title: 'Slip Gaji',
        href: '/payrolls',
        icon: FileText,
    },
];

// Menu items for admins
const adminNavItems: NavItem[] = [
    {
        title: 'Dasbor',
        href: '/admin/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Manajemen Karyawan',
        icon: UsersRound,
        items: [
            {
                title: 'Data Karyawan',
                href: '/employees',
                icon: Users,
            },
            {
                title: 'Departemen',
                href: '/admin/departments',
                icon: Building,
            },
            {
                title: 'Manajemen Shift',
                href: '/admin/work-shifts',
                icon: UserCheck,
            },
            {
                title: 'Dokumen Karyawan',
                href: '/admin/documents',
                icon: FolderOpen,
            },
        ],
    },
    {
        title: 'Kehadiran & Cuti',
        icon: Clock,
        items: [
            {
                title: 'Kelola Kehadiran',
                href: '/admin/attendance',
                icon: Clock,
            },
            {
                title: 'Kelola Cuti',
                href: '/admin/leave-requests',
                icon: Calendar,
            },
            {
                title: 'Request Tukar Libur',
                href: '/admin/shift-change-requests',
                icon: RefreshCw,
            },
        ],
    },
    {
        title: 'Penggajian',
        icon: DollarSign,
        items: [
            {
                title: 'Daftar Payroll',
                href: '/admin/payrolls',
                icon: FileText,
            },
            {
                title: 'Pengaturan Gaji',
                href: '/admin/salary-settings',
                icon: Wallet,
            },
            {
                title: 'Aturan Potongan',
                href: '/admin/deduction-rules',
                icon: Minus,
            },
        ],
    },
    {
        title: 'Pengumuman',
        href: '/admin/announcements',
        icon: Megaphone,
    },
    {
        title: 'LMS',
        icon: BookOpen,
        items: [
            {
                title: 'Pengelolaan Konten',
                href: '/admin/lms-materials',
                icon: FileText,
            },
        ],
    },
    {
        title: 'Pengaturan Sistem',
        icon: Settings,
        items: [
            {
                title: 'Lokasi Kantor',
                href: '/office-locations',
                icon: MapPin,
            },
            {
                title: 'Pengaturan Umum',
                href: '/admin/settings',
                icon: Settings,
            },
        ],
    },
];

// Computed property to get the appropriate menu items based on user role
const mainNavItems = computed<NavItem[]>(() => {
    return user.value?.is_admin ? adminNavItems : userNavItems;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/home">
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
