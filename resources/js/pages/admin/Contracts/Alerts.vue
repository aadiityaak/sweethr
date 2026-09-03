<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { AlertTriangle, BadgeCheck, CalendarClock, RefreshCw } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface Contract {
    id: number;
    contract_number: string;
    start_date: string;
    end_date: string | null;
    days_remaining: number | null;
    user?: { id: number; name: string; position?: { title: string } };
}

interface Props {
    alerts: { warning: Contract[]; critical: Contract[]; expired: Contract[] };
    stats: Record<string, number>;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Kontrak Kerja', href: '/admin/contracts' },
    { title: 'Alert Kontrak', href: '' },
];

const renew = (contract: Contract) => {
    const base = contract.end_date ? new Date(contract.end_date) : new Date();
    base.setMonth(base.getMonth() + 6);
    const newEnd = base.toISOString().slice(0, 10);
    if (confirm(`Perpanjang kontrak ${contract.user?.name} sampai ${newEnd}?`)) {
        router.patch(`/admin/contracts/${contract.id}/renew`, { new_end_date: newEnd }, { preserveScroll: true });
    }
};

const convertPkwtt = (contract: Contract) => {
    if (confirm(`Angkat ${contract.user?.name} menjadi PKWTT?`)) {
        router.patch(`/admin/contracts/${contract.id}/convert-pkwtt`, {}, { preserveScroll: true });
    }
};

const sections = [
    { key: 'expired', title: 'Sudah Lewat Jatuh Tempo', icon: AlertTriangle, color: 'text-red-700', bg: 'bg-red-50 border-red-200' },
    { key: 'critical', title: 'Kritis — ≤ 30 Hari (H-30)', icon: CalendarClock, color: 'text-red-600', bg: 'bg-orange-50 border-orange-200' },
    { key: 'warning', title: 'Perhatian — ≤ 60 Hari (H-60)', icon: CalendarClock, color: 'text-amber-600', bg: 'bg-amber-50 border-amber-200' },
];
</script>

<template>
    <Head title="Alert Kontrak" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Alert Kontrak</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Masa Berakhir PKWT — sinkron otomatis dengan Data Karyawan (edit di <Link href="/employees" class="font-semibold text-blue-600 hover:underline">/employees</Link> atau di <Link href="/admin/contracts" class="font-semibold text-blue-600 hover:underline">/admin/contracts</Link>).
                    · H-60: {{ stats.expiring_60 }} · H-30: {{ stats.expiring_30 }} · Lewat: {{ stats.expired }}
                </p>
            </div>

            <div v-for="section in sections" :key="section.key" class="rounded-lg border" :class="section.bg">
                <div class="flex items-center gap-2 border-b px-4 py-3" :class="section.bg">
                    <component :is="section.icon" class="h-5 w-5" :class="section.color" />
                    <h2 class="font-semibold" :class="section.color">{{ section.title }}</h2>
                    <span class="ml-auto rounded-full bg-white px-2 py-0.5 text-xs font-medium text-gray-600">
                        {{ (alerts as any)[section.key].length }} karyawan
                    </span>
                </div>
                <div class="divide-y divide-gray-100 bg-white">
                    <div v-for="contract in (alerts as any)[section.key]" :key="contract.id"
                        class="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ contract.user?.name }}</p>
                            <p class="text-xs text-gray-500">
                                {{ contract.user?.position?.title }} · {{ contract.contract_number }} · berakhir {{ contract.end_date }}
                                <span v-if="contract.days_remaining !== null" class="ml-1 font-semibold" :class="section.color">
                                    ({{ contract.days_remaining < 0 ? `lewat ${Math.abs(contract.days_remaining)} hari` : `H-${contract.days_remaining}` }})
                                </span>
                                <Link :href="`/employees/${contract.user?.id}/edit`" class="ml-2 text-blue-600 hover:underline">Edit karyawan →</Link>
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <button @click="renew(contract)"
                                class="inline-flex items-center gap-1 rounded-md bg-amber-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-600">
                                <RefreshCw class="h-3.5 w-3.5" /> Perpanjang
                            </button>
                            <button @click="convertPkwtt(contract)"
                                class="inline-flex items-center gap-1 rounded-md bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700">
                                <BadgeCheck class="h-3.5 w-3.5" /> Angkat PKWTT
                            </button>
                        </div>
                    </div>
                    <p v-if="(alerts as any)[section.key].length === 0" class="px-4 py-6 text-center text-sm text-gray-400">
                        Tidak ada kontrak pada kategori ini.
                    </p>
                </div>
            </div>

            <Link href="/admin/contracts" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline">
                ← Kembali ke daftar kontrak
            </Link>
        </div>
    </AppLayout>
</template>
