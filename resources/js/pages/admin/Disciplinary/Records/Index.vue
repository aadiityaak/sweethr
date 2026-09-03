<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import Chart from '@/components/ui/Chart.vue';
import { debounce } from 'lodash';
import { BarChart3, Filter, Plus, Search, Trash2, X } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface RecordItem {
    id: number;
    user_id: number;
    occurred_at: string;
    points: number;
    notes: string | null;
    source: string;
    user?: { id: number; name: string; position?: { title: string } };
    violation?: { id: number; code: string; name: string; category: string };
}

interface Props {
    violations: { data: RecordItem[]; links: any[] };
    masterViolations: { id: number; code: string; name: string; category: string; points: number }[];
    users: { id: number; name: string }[];
    topViolators: { id: number; name: string; total_points: number; violation_count: number }[];
    monthlyFeed: Record<string, Record<string, number>>;
    filters: { user: string; category: string; search: string };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Disiplin', href: '/admin/employee-violations' },
    { title: 'Catatan Pelanggaran', href: '' },
];

const search = ref(props.filters.search);
const userFilter = ref(props.filters.user);
const category = ref(props.filters.category);

watch([search, userFilter, category], debounce(() => {
    router.get('/admin/employee-violations',
        { search: search.value, user: userFilter.value, category: category.value },
        { preserveState: true, preserveScroll: true, replace: true });
}, 400));

// Monthly chart data (bar per kategori)
const months = Object.keys(props.monthlyFeed);
const chartData = {
    labels: months.map(m => {
        const [y, mo] = m.split('-');
        return ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'][Number(mo) - 1] + ' ' + y.slice(2);
    }),
    datasets: [
        { label: 'Ringan', backgroundColor: '#fbbf24', data: months.map(m => props.monthlyFeed[m].ringan) },
        { label: 'Sedang', backgroundColor: '#fb923c', data: months.map(m => props.monthlyFeed[m].sedang) },
        { label: 'Berat', backgroundColor: '#ef4444', data: months.map(m => props.monthlyFeed[m].berat) },
    ],
};
const chartOptions = { responsive: true, maintainAspectRatio: false, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } };

// Form catat pelanggaran
const showForm = ref(false);
const form = useForm({
    user_id: '',
    disciplinary_violation_id: '',
    occurred_at: new Date().toISOString().slice(0, 16),
    notes: '',
});

const selectedViolation = computed(() =>
    props.masterViolations.find(v => v.id === Number(form.disciplinary_violation_id))
);

const submit = () => {
    form.post('/admin/employee-violations', { onSuccess: () => showForm.value = false });
};

const remove = (record: RecordItem) => {
    if (confirm('Hapus catatan pelanggaran ini?')) {
        router.delete(`/admin/employee-violations/${record.id}`, { preserveScroll: true });
    }
};

const catBadge = (c: string) => ({
    ringan: 'bg-yellow-100 text-yellow-800',
    sedang: 'bg-orange-100 text-orange-800',
    berat: 'bg-red-100 text-red-800',
}[c] ?? 'bg-gray-100 text-gray-700');
const catLabel = (c: string) => ({ ringan: 'Ringan', sedang: 'Sedang', berat: 'Berat' }[c] ?? c);

const fmtDate = (dt: string) => new Date(dt).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
</script>

<template>
    <Head title="Catatan Pelanggaran" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Catatan Pelanggaran</h1>
                    <p class="mt-1 text-sm text-gray-500">Poin akumulasi rolling 6 bulan · clean record 3 bulan = -10 poin.</p>
                </div>
                <button @click="showForm = true"
                    class="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    <Plus class="h-4 w-4" /> Catat Pelanggaran
                </button>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Chart -->
                <div class="rounded-lg border bg-white p-4 lg:col-span-2">
                    <div class="mb-3 flex items-center gap-2">
                        <BarChart3 class="h-4 w-4 text-gray-500" />
                        <h2 class="text-sm font-semibold text-gray-700">Poin Pelanggaran Bulanan (6 bulan terakhir)</h2>
                    </div>
                    <div class="h-56">
                        <Chart type="bar" :data="chartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Top violators -->
                <div class="rounded-lg border bg-white p-4">
                    <h2 class="mb-3 text-sm font-semibold text-gray-700">Poin Tertinggi (rolling 6 bulan)</h2>
                    <div class="space-y-2">
                        <div v-for="(v, i) in topViolators" :key="v.id"
                            class="flex items-center justify-between rounded-md border px-3 py-2">
                            <div class="flex items-center gap-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold"
                                    :class="i < 3 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'">{{ i + 1 }}</span>
                                <span class="text-sm text-gray-800">{{ v.name }}</span>
                            </div>
                            <span class="text-sm font-bold" :class="v.total_points >= 35 ? 'text-red-600' : v.total_points >= 15 ? 'text-amber-600' : 'text-gray-600'">
                                {{ v.total_points }} poin
                            </span>
                        </div>
                        <p v-if="topViolators.length === 0" class="py-6 text-center text-sm text-gray-400">Tidak ada data.</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
                    <input v-model="search" type="text" placeholder="Cari karyawan / pelanggaran..."
                        class="w-full rounded-md border border-gray-300 py-2 pl-9 pr-3 text-sm" />
                </div>
                <select v-model="userFilter" class="rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua Karyawan</option>
                    <option v-for="u in users" :key="u.id" :value="String(u.id)">{{ u.name }}</option>
                </select>
                <select v-model="category" class="rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua Kategori</option>
                    <option value="ringan">Ringan</option>
                    <option value="sedang">Sedang</option>
                    <option value="berat">Berat</option>
                </select>
                <button @click="search = ''; userFilter = ''; category = ''"
                    class="inline-flex items-center gap-1 rounded-md border px-3 py-2 text-sm text-gray-600 hover:bg-gray-50">
                    <Filter class="h-4 w-4" /> Reset
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto rounded-lg border bg-white">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Waktu</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Karyawan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Pelanggaran</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Poin</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Sumber</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="record in violations.data" :key="record.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-xs text-gray-600">{{ fmtDate(record.occurred_at) }}</td>
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ record.user?.name }}</p>
                                <p class="text-xs text-gray-500">{{ record.user?.position?.title }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="catBadge(record.violation?.category ?? '')" class="rounded-full px-2 py-0.5 text-xs font-medium">
                                    {{ catLabel(record.violation?.category ?? '') }}
                                </span>
                                <p class="mt-1 text-sm text-gray-700">{{ record.violation?.name }}</p>
                                <p v-if="record.notes" class="text-xs text-gray-400">{{ record.notes }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-gray-900">+{{ record.points }}</td>
                            <td class="px-4 py-3">
                                <span :class="record.source === 'auto_attendance' ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-600'"
                                    class="rounded-full px-2 py-0.5 text-xs">
                                    {{ record.source === 'auto_attendance' ? 'Otomatis (absensi)' : 'Manual' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button @click="remove(record)" class="rounded p-1.5 text-red-600 hover:bg-red-50">
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="violations.data.length === 0">
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">Tidak ada catatan pelanggaran.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="violations.links.length > 3" class="flex justify-center gap-1">
                <button v-for="(link, i) in violations.links" :key="i" :disabled="!link.url"
                    @click="link.url && router.get(link.url, {}, { preserveState: true, preserveScroll: true })"
                    class="rounded-md border px-3 py-1.5 text-sm"
                    :class="link.active ? 'border-blue-600 bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                    v-html="link.label" />
            </div>

            <!-- Modal Form -->
            <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showForm = false">
                <div class="w-full max-w-lg rounded-lg bg-white p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Catat Pelanggaran</h2>
                        <button @click="showForm = false" class="rounded p-1 hover:bg-gray-100"><X class="h-4 w-4" /></button>
                    </div>
                    <form @submit.prevent="submit" class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Karyawan <span class="text-red-500">*</span></label>
                            <select v-model="form.user_id" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                <option value="">-- Pilih --</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                            </select>
                            <p v-if="form.errors.user_id" class="mt-1 text-xs text-red-600">{{ form.errors.user_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jenis Pelanggaran <span class="text-red-500">*</span></label>
                            <select v-model="form.disciplinary_violation_id" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                <option value="">-- Pilih --</option>
                                <optgroup v-for="cat in ['ringan', 'sedang', 'berat']" :key="cat" :label="catLabel(cat)">
                                    <option v-for="v in masterViolations.filter(mv => mv.category === cat)" :key="v.id" :value="v.id">
                                        [{{ v.code }}] {{ v.name }} (+{{ v.points }})
                                    </option>
                                </optgroup>
                            </select>
                            <p v-if="selectedViolation" class="mt-1 text-xs font-medium text-red-600">
                                Karyawan akan mendapat +{{ selectedViolation.points }} poin.
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Waktu Kejadian <span class="text-red-500">*</span></label>
                            <input v-model="form.occurred_at" type="datetime-local" required
                                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Catatan</label>
                            <textarea v-model="form.notes" rows="2" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm"></textarea>
                        </div>
                        <div class="flex justify-end gap-2 border-t pt-4">
                            <button type="button" @click="showForm = false" class="rounded-md border px-4 py-2 text-sm">Batal</button>
                            <button type="submit" :disabled="form.processing"
                                class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50">
                                Simpan Pelanggaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
