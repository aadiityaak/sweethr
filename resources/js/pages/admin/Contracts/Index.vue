<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { debounce } from 'lodash';
import {
    ArrowLeft, BadgeCheck, CalendarClock, FileText, Filter, Pencil,
    Plus, RefreshCw, Search, Trash2, TrendingUp,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface Contract {
    id: number;
    user_id: number;
    contract_number: string;
    type: 'pkwt' | 'pkwtt';
    start_date: string;
    end_date: string | null;
    status: string;
    salary_grade: string | null;
    days_remaining: number | null;
    alert_level: string | null;
    user?: { id: number; name: string; position?: { title: string }; department?: { name: string } };
}

interface Props {
    contracts: { data: Contract[]; links: any[] };
    stats: Record<string, number>;
    filters: { type: string; status: string; search: string };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Kontrak Kerja', href: '/admin/contracts' },
];

const search = ref(props.filters.search);
const type = ref(props.filters.type);
const status = ref(props.filters.status);

watch([search, type, status], debounce(() => {
    router.get('/admin/contracts', {
        search: search.value, type: type.value, status: status.value,
    }, { preserveState: true, preserveScroll: true, replace: true });
}, 400));

const clearFilters = () => {
    search.value = ''; type.value = ''; status.value = '';
};

const deleteForm = useForm({});
const confirmDelete = (contract: Contract) => {
    if (confirm(`Hapus kontrak ${contract.contract_number}?`)) {
        deleteForm.delete(`/admin/contracts/${contract.id}`, { preserveScroll: true });
    }
};

const renewTarget = ref<Contract | null>(null);
const renewForm = useForm({ new_end_date: '', salary_grade: '' });
const openRenew = (contract: Contract) => {
    renewTarget.value = contract;
    const base = contract.end_date ? new Date(contract.end_date) : new Date();
    base.setMonth(base.getMonth() + 6);
    renewForm.new_end_date = base.toISOString().slice(0, 10);
    renewForm.salary_grade = contract.salary_grade ?? '';
};
const submitRenew = () => {
    if (!renewTarget.value) return;
    renewForm.patch(`/admin/contracts/${renewTarget.value.id}/renew`, {
        preserveScroll: true,
        onSuccess: () => { renewTarget.value = null; },
    });
};

const convertToPkwtt = (contract: Contract) => {
    if (confirm(`Angkat ${contract.user?.name} menjadi karyawan tetap (PKWTT)?`)) {
        router.patch(`/admin/contracts/${contract.id}/convert-pkwtt`, {}, { preserveScroll: true });
    }
};

const typeLabel = (t: string) => t === 'pkwtt' ? 'PKWTT (Tetap)' : 'PKWT (Kontrak)';
const statusBadge = (s: string) => ({
    active: 'bg-green-100 text-green-800', expired: 'bg-red-100 text-red-800',
    renewed: 'bg-blue-100 text-blue-800', terminated: 'bg-gray-200 text-gray-700',
}[s] ?? 'bg-gray-100 text-gray-700');
const statusLabel = (s: string) => ({ active: 'Aktif', expired: 'Kedaluwarsa', renewed: 'Diperpanjang', terminated: 'Dihentikan' }[s] ?? s);

const countdownClass = computed(() => (c: Contract) => {
    if (c.type !== 'pkwt' || c.status !== 'active' || c.days_remaining === null) return '';
    if (c.days_remaining < 0) return 'text-red-700 font-bold';
    if (c.days_remaining <= 30) return 'text-red-600 font-semibold';
    if (c.days_remaining <= 60) return 'text-amber-600 font-semibold';
    return 'text-gray-600';
});
</script>

<template>
    <Head title="Kontrak Kerja" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Kontrak Kerja</h1>
                    <p class="mt-1 text-sm text-gray-500">Kelola PKWT/PKWTT & pantau masa akhir kontrak (H-60/H-30).</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/admin/contracts/alerts" class="inline-flex items-center gap-2 rounded-md border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-800 hover:bg-amber-100">
                        <CalendarClock class="h-4 w-4" /> Alert Kontrak
                    </Link>
                    <Link href="/admin/contracts/create" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        <Plus class="h-4 w-4" /> Tambah Kontrak
                    </Link>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
                <div class="rounded-lg border bg-white p-4">
                    <p class="text-xs font-medium text-gray-500">PKWT Aktif</p>
                    <p class="mt-1 text-2xl font-bold text-blue-600">{{ stats.pkwt_active }}</p>
                </div>
                <div class="rounded-lg border bg-white p-4">
                    <p class="text-xs font-medium text-gray-500">PKWTT (Tetap)</p>
                    <p class="mt-1 text-2xl font-bold text-green-600">{{ stats.pkwtt_active }}</p>
                </div>
                <div class="rounded-lg border bg-white p-4">
                    <p class="text-xs font-medium text-gray-500">H-60</p>
                    <p class="mt-1 text-2xl font-bold text-amber-600">{{ stats.expiring_60 }}</p>
                </div>
                <div class="rounded-lg border bg-white p-4">
                    <p class="text-xs font-medium text-gray-500">H-30</p>
                    <p class="mt-1 text-2xl font-bold text-red-600">{{ stats.expiring_30 }}</p>
                </div>
                <div class="rounded-lg border bg-white p-4">
                    <p class="text-xs font-medium text-gray-500">Lewat Jatuh Tempo</p>
                    <p class="mt-1 text-2xl font-bold text-red-700">{{ stats.expired }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col gap-3 rounded-lg border bg-white p-4 sm:flex-row">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
                    <input v-model="search" type="text" placeholder="Cari nomor kontrak / nama karyawan..."
                        class="w-full rounded-md border border-gray-300 py-2 pl-9 pr-3 text-sm focus:border-blue-500 focus:outline-none" />
                </div>
                <select v-model="type" class="rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua Tipe</option>
                    <option value="pkwt">PKWT</option>
                    <option value="pkwtt">PKWTT</option>
                </select>
                <select v-model="status" class="rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="expired">Kedaluwarsa</option>
                    <option value="renewed">Diperpanjang</option>
                    <option value="terminated">Dihentikan</option>
                </select>
                <button @click="clearFilters" class="inline-flex items-center gap-1 rounded-md border px-3 py-2 text-sm text-gray-600 hover:bg-gray-50">
                    <Filter class="h-4 w-4" /> Reset
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto rounded-lg border bg-white">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Karyawan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">No. Kontrak</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tipe</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Periode</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Sisa Hari</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="contract in contracts.data" :key="contract.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ contract.user?.name }}</p>
                                <p class="text-xs text-gray-500">{{ contract.user?.position?.title }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-700">{{ contract.contract_number }}</td>
                            <td class="px-4 py-3">
                                <span :class="contract.type === 'pkwtt' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'"
                                    class="rounded-full px-2 py-0.5 text-xs font-medium">
                                    {{ typeLabel(contract.type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ contract.start_date }}<br>
                                <span v-if="contract.end_date">s/d {{ contract.end_date }}</span>
                                <span v-else class="text-green-600">tanpa batas</span>
                            </td>
                            <td class="px-4 py-3 text-sm" :class="countdownClass(contract)">
                                <template v-if="contract.type === 'pkwt' && contract.status === 'active' && contract.days_remaining !== null">
                                    <span v-if="contract.days_remaining < 0">Lewat {{ Math.abs(contract.days_remaining) }} hari</span>
                                    <span v-else>H-{{ contract.days_remaining }}</span>
                                </template>
                                <span v-else class="text-gray-400">—</span>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="statusBadge(contract.status)" class="rounded-full px-2 py-0.5 text-xs font-medium">
                                    {{ statusLabel(contract.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <button v-if="contract.type === 'pkwt' && contract.status === 'active'" @click="openRenew(contract)"
                                        class="rounded p-1.5 text-amber-600 hover:bg-amber-50" title="Perpanjang">
                                        <RefreshCw class="h-4 w-4" />
                                    </button>
                                    <button v-if="contract.type === 'pkwt' && contract.status === 'active'" @click="convertToPkwtt(contract)"
                                        class="rounded p-1.5 text-green-600 hover:bg-green-50" title="Angkat jadi PKWTT">
                                        <BadgeCheck class="h-4 w-4" />
                                    </button>
                                    <Link :href="`/admin/contracts/${contract.id}/edit`" class="rounded p-1.5 text-blue-600 hover:bg-blue-50" title="Edit">
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                    <button @click="confirmDelete(contract)" class="rounded p-1.5 text-red-600 hover:bg-red-50" title="Hapus">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="contracts.data.length === 0">
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-500">
                                <FileText class="mx-auto mb-2 h-8 w-8 text-gray-300" />
                                Belum ada data kontrak.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="contracts.links.length > 3" class="flex justify-center gap-1">
                <button v-for="(link, i) in contracts.links" :key="i" :disabled="!link.url"
                    @click="link.url && router.get(link.url, {}, { preserveState: true, preserveScroll: true })"
                    class="rounded-md border px-3 py-1.5 text-sm"
                    :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 hover:bg-gray-50'"
                    :disabled-attr="!link.url ? '' : undefined"
                    v-html="link.label" />
            </div>

            <!-- Renew Modal -->
            <div v-if="renewTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="renewTarget = null">
                <div class="w-full max-w-md rounded-lg bg-white p-6">
                    <h2 class="text-lg font-semibold">Perpanjang Kontrak</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ renewTarget.user?.name }} — {{ renewTarget.contract_number }}</p>
                    <form @submit.prevent="submitRenew" class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Akhir Baru</label>
                            <input v-model="renewForm.new_end_date" type="date" required
                                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                            <p v-if="renewForm.errors.new_end_date" class="mt-1 text-xs text-red-600">{{ renewForm.errors.new_end_date }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Grade Gaji (opsional)</label>
                            <input v-model="renewForm.salary_grade" type="text"
                                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="renewTarget = null" class="rounded-md border px-4 py-2 text-sm">Batal</button>
                            <button type="submit" :disabled="renewForm.processing" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                                <RefreshCw class="mr-1 inline h-4 w-4" /> Perpanjang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
