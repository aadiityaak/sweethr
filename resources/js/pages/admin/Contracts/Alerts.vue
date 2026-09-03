<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { AlertTriangle, BadgeCheck, CalendarClock, RefreshCw, Users } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface Contract {
    id: number;
    contract_number: string;
    start_date: string;
    end_date: string | null;
    days_remaining: number | null;
    user?: { id: number; name: string; position?: { title: string } };
}

interface EmployeeAlert {
    id: number;
    name: string;
    employee_id: string;
    contract_end_date: string | null;
    contract_days_remaining: number | null;
    contract_alert_level: string | null;
    position?: { title: string };
    department?: { name: string };
}

interface Props {
    alerts: { warning: Contract[]; critical: Contract[]; expired: Contract[] };
    employeeAlerts: { warning: EmployeeAlert[]; critical: EmployeeAlert[]; expired: EmployeeAlert[] };
    stats: Record<string, number>;
}

const props = withDefaults(defineProps<Props>(), {
    employeeAlerts: () => ({ warning: [], critical: [], expired: [] }),
});

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

const employeeCount = (key: string) => (props.employeeAlerts as any)?.[key]?.length ?? 0;
const hasEmployeeAlerts = () => employeeCount('expired') + employeeCount('critical') + employeeCount('warning') > 0;
</script>

<template>
    <Head title="Alert Kontrak" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Alert Kontrak</h1>
                <p class="mt-1 text-sm text-gray-500">
                    PKWT mendekati akhir masa kontrak — modul Kontrak: {{ stats.expiring_60 }} (H-60) · {{ stats.expiring_30 }} (H-30)
                    <span v-if="hasEmployeeAlerts()">
                        · dari Data Karyawan: {{ employeeCount('warning') }} (H-60) · {{ employeeCount('critical') }} (H-30) · {{ employeeCount('expired') }} expired
                    </span>
                </p>
            </div>

            <!-- Sumber 1: employment_contracts -->
            <div class="flex items-center gap-2">
                <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-700">Sumber: Modul Kontrak (PKWT)</span>
                <Link href="/admin/contracts" class="text-xs text-blue-600 hover:underline">Kelola kontrak →</Link>
            </div>

            <div v-for="section in sections" :key="'c-' + section.key" class="rounded-lg border" :class="section.bg">
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

            <!-- Sumber 2: users.contract_end_date (yang tampil di /employees) -->
            <div class="flex items-center gap-2 pt-2">
                <span class="rounded-full bg-purple-100 px-2.5 py-1 text-xs font-bold text-purple-700">Sumber: Data Karyawan (Status Ketenagakerjaan — /employees)</span>
                <Link href="/employees" class="text-xs text-purple-600 hover:underline">Lihat di daftar karyawan →</Link>
            </div>
            <p class="text-xs text-gray-500">Diambil dari field <span class="font-semibold">Masa Berakhir Kontrak (PKWT)</span> di form karyawan. Atur di <code class="rounded bg-gray-100 px-1">/employees/{id}/edit</code>. Sinkron dengan banner Count-Down H-60/H-30 di /employees.</p>

            <div v-for="section in sections" :key="'e-' + section.key" class="rounded-lg border" :class="section.bg">
                <div class="flex items-center gap-2 border-b px-4 py-3" :class="section.bg">
                    <component :is="section.icon" class="h-5 w-5" :class="section.color" />
                    <h2 class="font-semibold" :class="section.color">{{ section.title }}</h2>
                    <span class="ml-2 rounded-full bg-purple-100 px-2 py-0.5 text-[10px] font-bold text-purple-700">Data Karyawan</span>
                    <span class="ml-auto rounded-full bg-white px-2 py-0.5 text-xs font-medium text-gray-600">
                        {{ employeeCount(section.key) }} karyawan
                    </span>
                </div>
                <div class="divide-y divide-gray-100 bg-white">
                    <div v-for="emp in (employeeAlerts as any)[section.key]" :key="emp.id"
                        class="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ emp.name }} <span class="font-normal text-gray-400">· {{ emp.employee_id }}</span></p>
                            <p class="text-xs text-gray-500">
                                {{ emp.position?.title }}<span v-if="emp.department?.name"> · {{ emp.department.name }}</span> · berakhir {{ emp.contract_end_date }}
                                <span v-if="emp.contract_days_remaining !== null" class="ml-1 font-semibold" :class="section.color">
                                    ({{ (emp.contract_days_remaining ?? 0) < 0 ? `lewat ${Math.abs(emp.contract_days_remaining ?? 0)} hari` : `H-${emp.contract_days_remaining}` }})
                                </span>
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <Link :href="`/employees/${emp.id}/edit`"
                                class="inline-flex items-center gap-1 rounded-md bg-purple-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-purple-700">
                                <Users class="h-3.5 w-3.5" /> Kelola di Karyawan
                            </Link>
                        </div>
                    </div>
                    <p v-if="employeeCount(section.key) === 0" class="px-4 py-6 text-center text-sm text-gray-400">
                        Tidak ada data karyawan PKWT pada kategori ini.
                    </p>
                </div>
            </div>

            <p v-if="!hasEmployeeAlerts()" class="rounded-lg border border-dashed bg-gray-50 px-4 py-3 text-xs text-gray-500">
                Belum ada Masa Berakhir PKWT yang diisi di Data Karyawan. Isi di <Link href="/employees" class="font-semibold text-purple-600 hover:underline">/employees</Link> → Edit karyawan → pilih PKWT → isi tanggal.
            </p>

            <Link href="/admin/contracts" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline">
                ← Kembali ke daftar kontrak
            </Link>
        </div>
    </AppLayout>
</template>
