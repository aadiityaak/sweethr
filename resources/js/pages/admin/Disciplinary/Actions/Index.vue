<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import { Check, FileWarning, Filter, Gavel, Undo2 } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface Action {
    id: number;
    action_type: string;
    triggered_points: number;
    status: string;
    issued_at: string;
    freeze_until: string | null;
    required_remediation: boolean;
    suspend_incentive: boolean;
    notes: string | null;
    user?: { id: number; name: string; position?: { title: string } };
    issuer?: { id: number; name: string } | null;
    confirmer?: { id: number; name: string } | null;
}

interface Props {
    actions: { data: Action[]; links: any[] };
    filters: { status: string; type: string };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Disiplin', href: '/admin/employee-violations' },
    { title: 'SP & Tindakan', href: '' },
];

const status = ref(props.filters.status);
const type = ref(props.filters.type);

watch([status, type], debounce(() => {
    router.get('/admin/disciplinary-actions', { status: status.value, type: type.value },
        { preserveState: true, preserveScroll: true, replace: true });
}, 300));

const confirm = (action: Action) => {
    if (confirm(`Konfirmasi terbit ${typeLabel(action.action_type)} untuk ${action.user?.name}?`)) {
        router.patch(`/admin/disciplinary-actions/${action.id}/confirm`, {}, { preserveScroll: true });
    }
};
const resolve = (action: Action) => {
    router.patch(`/admin/disciplinary-actions/${action.id}/resolve`, {}, { preserveScroll: true });
};
const revoke = (action: Action) => {
    if (confirm('Cabut tindakan disiplin ini?')) {
        router.patch(`/admin/disciplinary-actions/${action.id}/revoke`, {}, { preserveScroll: true });
    }
};

function typeLabel(t: string) {
    return ({
        teguran_lisan: 'Teguran Lisan',
        sp1: 'SP 1',
        sp2: 'SP 2',
        sp3: 'SP 3',
        phk_eval: 'Evaluasi PHK',
    } as Record<string, string>)[t] ?? t;
}
function typeBadge(t: string) {
    return ({
        teguran_lisan: 'bg-yellow-100 text-yellow-800',
        sp1: 'bg-orange-100 text-orange-800',
        sp2: 'bg-red-100 text-red-800',
        sp3: 'bg-red-200 text-red-900',
        phk_eval: 'bg-gray-900 text-white',
    } as Record<string, string>)[t] ?? 'bg-gray-100 text-gray-700';
}
const statusBadge = (s: string) => ({
    active: 'bg-red-50 text-red-700',
    resolved: 'bg-green-100 text-green-800',
    revoked: 'bg-gray-100 text-gray-600',
}[s] ?? 'bg-gray-100 text-gray-700');
const statusLabel = (s: string) => ({ active: 'Aktif', resolved: 'Selesai', revoked: 'Dicabut' }[s] ?? s);

const fmtDate = (dt: string) => new Date(dt).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
</script>

<template>
    <Head title="SP & Tindakan Disiplin" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">SP & Tindakan Disiplin</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Tier otomatis: 15 poin Teguran · 35 SP1 (freeze promosi 3 bln) · 50 SP2 (remedial) · 70 SP3 (tahan insentif) · 85+ evaluasi PHK.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <select v-model="status" class="rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="resolved">Selesai</option>
                    <option value="revoked">Dicabut</option>
                </select>
                <select v-model="type" class="rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Semua Tipe</option>
                    <option value="teguran_lisan">Teguran Lisan</option>
                    <option value="sp1">SP 1</option>
                    <option value="sp2">SP 2</option>
                    <option value="sp3">SP 3</option>
                    <option value="phk_eval">Evaluasi PHK</option>
                </select>
                <button @click="status = ''; type = ''"
                    class="inline-flex items-center gap-1 rounded-md border px-3 py-2 text-sm text-gray-600 hover:bg-gray-50">
                    <Filter class="h-4 w-4" /> Reset
                </button>
            </div>

            <div class="overflow-x-auto rounded-lg border bg-white">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Diterbitkan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Karyawan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tipe</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Poin</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Dampak</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="action in actions.data" :key="action.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-xs text-gray-600">
                                {{ fmtDate(action.issued_at) }}
                                <p class="text-gray-400">oleh {{ action.issuer?.name ?? 'Sistem' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ action.user?.name }}</p>
                                <p class="text-xs text-gray-500">{{ action.user?.position?.title }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="typeBadge(action.action_type)" class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold">
                                    <Gavel class="h-3 w-3" /> {{ typeLabel(action.action_type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-gray-900">{{ action.triggered_points }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                <p v-if="action.freeze_until">Freeze promosi s/d {{ action.freeze_until }}</p>
                                <p v-if="action.required_remediation">Wajib remedial LMS</p>
                                <p v-if="action.suspend_incentive">Insentif ditangguhkan</p>
                                <p v-if="!action.freeze_until && !action.required_remediation && !action.suspend_incentive" class="text-gray-400">—</p>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="statusBadge(action.status)" class="rounded-full px-2 py-0.5 text-xs font-medium">
                                    {{ statusLabel(action.status) }}
                                </span>
                                <p v-if="action.confirmer" class="mt-1 text-xs text-gray-400">dikonfirmasi {{ action.confirmer.name }}</p>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <button v-if="action.status === 'active' && !action.confirmed_at" @click="confirm(action)"
                                        class="rounded p-1.5 text-green-600 hover:bg-green-50" title="Konfirmasi terbit">
                                        <Check class="h-4 w-4" />
                                    </button>
                                    <button v-if="action.status === 'active'" @click="resolve(action)"
                                        class="rounded p-1.5 text-blue-600 hover:bg-blue-50" title="Tandai selesai">
                                        <FileWarning class="h-4 w-4" />
                                    </button>
                                    <button v-if="action.status !== 'revoked'" @click="revoke(action)"
                                        class="rounded p-1.5 text-gray-500 hover:bg-gray-100" title="Cabut">
                                        <Undo2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="actions.data.length === 0">
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-500">Belum ada tindakan disiplin.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="actions.links.length > 3" class="flex justify-center gap-1">
                <button v-for="(link, i) in actions.links" :key="i" :disabled="!link.url"
                    @click="link.url && router.get(link.url, {}, { preserveState: true, preserveScroll: true })"
                    class="rounded-md border px-3 py-1.5 text-sm"
                    :class="link.active ? 'border-blue-600 bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                    v-html="link.label" />
            </div>
        </div>
    </AppLayout>
</template>
