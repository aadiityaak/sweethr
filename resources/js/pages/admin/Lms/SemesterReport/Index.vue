<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Download, Eye, FileSpreadsheet, Send, X } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface Report {
    id: number;
    user_id: number;
    year: number;
    semester: string;
    kpi_score: number;
    lms_score: number;
    discipline_score: number;
    final_score: number;
    grade: string;
    total_violation_points: number;
    status: string;
    user?: { id: number; name: string; position?: { title: string }; department?: { name: string } };
}

interface Props {
    reports: { data: Report[]; links: any[] };
    gradeStats: Record<string, number>;
    filters: { year: number; semester: number; grade: string };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'LMS', href: '/admin/lms-dashboard' },
    { title: 'Raport Semester', href: '' },
];

const year = ref(props.filters.year);
const semester = ref(String(props.filters.semester));
const grade = ref(props.filters.grade);

const applyFilter = () => {
    router.get('/admin/semester-reports',
        { year: year.value, semester: semester.value, grade: grade.value },
        { preserveState: true, preserveScroll: true, replace: true });
};

// Generate modal
const showGenerate = ref(false);
const generateForm = useForm({ year: props.filters.year, semester: String(props.filters.semester), user_id: '' });
const submitGenerate = () => {
    generateForm.post('/admin/semester-reports/generate', { onSuccess: () => showGenerate.value = false });
};

const publish = (report: Report) => {
    if (confirm(`Terbitkan raport ${report.user?.name}? Karyawan akan melihatnya.`)) {
        router.patch(`/admin/semester-reports/${report.id}/publish`, {}, { preserveScroll: true });
    }
};

const bulkPublish = () => {
    if (confirm('Terbitkan SEMUA raport draft pada periode ini?')) {
        router.post('/admin/semester-reports/bulk-publish', { year: year.value, semester: semester.value }, { preserveScroll: true });
    }
};

const gradeBadge = (g: string) => ({
    A: 'bg-green-100 text-green-800', B: 'bg-blue-100 text-blue-800',
    C: 'bg-yellow-100 text-yellow-800', D: 'bg-orange-100 text-orange-800',
    E: 'bg-red-100 text-red-800',
}[g] ?? 'bg-gray-100 text-gray-700');
const gradeLabel = (g: string) => ({
    A: 'Outstanding', B: 'Good', C: 'Meets Standard', D: 'Needs Improvement', E: 'Unsatisfactory',
}[g] ?? '');

const gradeSummary = ['A', 'B', 'C', 'D', 'E'].map(g => ({ grade: g, total: props.gradeStats[g] ?? 0 }));
</script>

<template>
    <Head title="Raport Semester" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Raport Kinerja Semester</h1>
                    <p class="mt-1 text-sm text-gray-500">Bobot: KPI 50% · LMS 30% · Disiplin 20% → Predikat A–E + rekomendasi reward.</p>
                </div>
                <div class="flex gap-2">
                    <button @click="bulkPublish"
                        class="inline-flex items-center gap-2 rounded-md border border-green-300 bg-green-50 px-4 py-2 text-sm font-medium text-green-800 hover:bg-green-100">
                        <Send class="h-4 w-4" /> Terbitkan Semua Draft
                    </button>
                    <button @click="showGenerate = true"
                        class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        <FileSpreadsheet class="h-4 w-4" /> Generate Raport
                    </button>
                </div>
            </div>

            <!-- Grade summary -->
            <div class="grid grid-cols-5 gap-3">
                <div v-for="gs in gradeSummary" :key="gs.grade" class="rounded-lg border bg-white p-3 text-center">
                    <span :class="gradeBadge(gs.grade)" class="inline-block rounded-full px-3 py-0.5 text-sm font-bold">{{ gs.grade }}</span>
                    <p class="mt-1 text-xl font-bold text-gray-900">{{ gs.total }}</p>
                    <p class="text-xs text-gray-500">{{ gradeLabel(gs.grade) }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col gap-3 sm:flex-row">
                <input v-model.number="year" type="number" @change="applyFilter" class="w-28 rounded-md border border-gray-300 px-3 py-2 text-sm" />
                <select v-model="semester" @change="applyFilter" class="rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="1">Semester I (Jan–Jun)</option>
                    <option value="2">Semester II (Jul–Des)</option>
                </select>
                <select v-model="grade" @change="applyFilter" class="rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua Predikat</option>
                    <option v-for="g in ['A', 'B', 'C', 'D', 'E']" :key="g" :value="g">{{ g }}</option>
                </select>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto rounded-lg border bg-white">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Karyawan</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">KPI (50%)</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">LMS (30%)</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Disiplin (20%)</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Skor Akhir</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Predikat</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Poin Pelanggaran</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="report in reports.data" :key="report.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ report.user?.name }}</p>
                                <p class="text-xs text-gray-500">{{ report.user?.position?.title }} · {{ report.user?.department?.name }}</p>
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-gray-700">{{ report.kpi_score.toFixed(1) }}</td>
                            <td class="px-4 py-3 text-center text-sm text-gray-700">{{ report.lms_score.toFixed(1) }}</td>
                            <td class="px-4 py-3 text-center text-sm text-gray-700">{{ report.discipline_score.toFixed(1) }}</td>
                            <td class="px-4 py-3 text-center text-sm font-bold text-gray-900">{{ report.final_score.toFixed(1) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span :class="gradeBadge(report.grade)" class="rounded-full px-2.5 py-0.5 text-xs font-bold">{{ report.grade }}</span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm" :class="report.total_violation_points >= 35 ? 'font-bold text-red-600' : 'text-gray-600'">
                                {{ report.total_violation_points }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span :class="report.status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                                    class="rounded-full px-2 py-0.5 text-xs font-medium">
                                    {{ report.status === 'published' ? 'Terbit' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <button @click="router.get(`/admin/semester-reports/${report.id}`)"
                                        class="rounded p-1.5 text-blue-600 hover:bg-blue-50" title="Lihat detail">
                                        <Eye class="h-4 w-4" />
                                    </button>
                                    <a :href="`/admin/semester-reports/${report.id}/pdf`"
                                        class="rounded p-1.5 text-gray-600 hover:bg-gray-50 inline-flex" title="Download PDF">
                                        <Download class="h-4 w-4" />
                                    </a>
                                    <button v-if="report.status === 'draft'" @click="publish(report)"
                                        class="rounded p-1.5 text-green-600 hover:bg-green-50" title="Terbitkan">
                                        <Send class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="reports.data.length === 0">
                            <td colspan="9" class="px-4 py-12 text-center text-sm text-gray-500">
                                Belum ada raport pada periode ini. Klik "Generate Raport" untuk membuat.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="reports.links.length > 3" class="flex justify-center gap-1">
                <button v-for="(link, i) in reports.links" :key="i" :disabled="!link.url"
                    @click="link.url && router.get(link.url, {}, { preserveState: true, preserveScroll: true })"
                    class="rounded-md border px-3 py-1.5 text-sm"
                    :class="link.active ? 'border-blue-600 bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                    v-html="link.label" />
            </div>

            <!-- Generate Modal -->
            <div v-if="showGenerate" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showGenerate = false">
                <div class="w-full max-w-md rounded-lg bg-white p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Generate Raport Semester</h2>
                        <button @click="showGenerate = false" class="rounded p-1 hover:bg-gray-100"><X class="h-4 w-4" /></button>
                    </div>
                    <form @submit.prevent="submitGenerate" class="mt-4 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tahun</label>
                                <input v-model.number="generateForm.year" type="number" min="2020" max="2030" required
                                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Semester</label>
                                <select v-model="generateForm.semester" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                    <option value="1">Semester I</option>
                                    <option value="2">Semester II</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Karyawan (kosongkan = semua karyawan aktif)</label>
                            <input v-model="generateForm.user_id" type="number" placeholder="ID karyawan (opsional)"
                                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                        </div>
                        <p class="rounded-md bg-amber-50 p-3 text-xs text-amber-800">
                            Raport dibuat sebagai <strong>draft</strong>. KPI dihitung dari Performance Appraisal pada periode,
                            LMS dari kuis & tugas, Disiplin dari kehadiran + poin pelanggaran.
                        </p>
                        <div class="flex justify-end gap-2 border-t pt-4">
                            <button type="button" @click="showGenerate = false" class="rounded-md border px-4 py-2 text-sm">Batal</button>
                            <button type="submit" :disabled="generateForm.processing"
                                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                                Generate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
