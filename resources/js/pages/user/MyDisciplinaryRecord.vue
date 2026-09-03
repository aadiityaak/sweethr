<script setup lang="ts">
import BottomNavigation from '@/components/BottomNavigation.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Award, Ban, CheckCircle2, FileWarning, ShieldAlert, Star, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

interface Violation {
    id: number;
    occurred_at: string;
    points: number;
    notes: string | null;
    violation?: { code: string; name: string; category: string };
}

interface Action {
    id: number;
    action_type: string;
    triggered_points: number;
    status: string;
    issued_at: string;
    freeze_until: string | null;
    required_remediation: string | null;
    suspend_incentive: boolean;
}

interface Props {
    activePoints: number;
    breakdown: Record<string, { points: number; count: number }>;
    cleanRecordBonus: number;
    violations: Violation[];
    actions: Action[];
}

const { activePoints, breakdown, cleanRecordBonus, violations, actions } = defineProps<Props>();

const tier = computed(() =>
    activePoints >= 85 ? { label: 'Evaluasi PHK', cls: 'bg-red-50 text-red-700 border-red-200' }
    : activePoints >= 70 ? { label: 'SP 3', cls: 'bg-red-50 text-red-700 border-red-200' }
    : activePoints >= 50 ? { label: 'SP 2', cls: 'bg-orange-50 text-orange-700 border-orange-200' }
    : activePoints >= 35 ? { label: 'SP 1', cls: 'bg-orange-50 text-orange-700 border-orange-200' }
    : activePoints >= 15 ? { label: 'Teguran', cls: 'bg-amber-50 text-amber-700 border-amber-200' }
    : { label: 'Aman', cls: 'bg-green-50 text-green-700 border-green-200' }
);

const categories = computed(() => [
    { key: 'ringan', label: 'Ringan', bar: 'bg-yellow-400' },
    { key: 'sedang', label: 'Sedang', bar: 'bg-orange-500' },
    { key: 'berat', label: 'Berat', bar: 'bg-red-600' },
]);

const categoryLabel = (c: string) => ({ ringan: 'Ringan', sedang: 'Sedang', berat: 'Berat' }[c] ?? c);
const categoryBadge = (c: string) => ({
    ringan: 'bg-yellow-50 text-yellow-700 border-yellow-200',
    sedang: 'bg-orange-50 text-orange-700 border-orange-200',
    berat: 'bg-red-50 text-red-700 border-red-200',
}[c] ?? 'bg-muted text-muted-foreground border-border');

const actionLabel = (t: string) => ({
    teguran_lisan: 'Teguran Lisan', sp1: 'SP 1', sp2: 'SP 2', sp3: 'SP 3', phk_eval: 'Evaluasi PHK',
}[t] ?? t);

const actionStatusBadge = (s: string) => ({
    active: 'bg-red-50 text-red-700 border-red-200',
    resolved: 'bg-green-50 text-green-700 border-green-200',
    revoked: 'bg-gray-50 text-gray-500 border-gray-200',
}[s] ?? 'bg-muted text-muted-foreground border-border');

const actionStatusLabel = (s: string) => ({ active: 'Aktif', resolved: 'Selesai', revoked: 'Dicabut' }[s] ?? s);

const formatDate = (d: string) =>
    new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });

const formatDateTime = (d: string) =>
    new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
</script>

<template>
    <Head title="Catatan Disiplin Saya" />
    <div class="min-h-screen bg-background">
        <div class="mx-auto min-h-screen max-w-[480px] bg-background">
            <!-- Header -->
            <div class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur-sm">
                <div class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <Link href="/home" class="rounded-md bg-secondary p-2 text-secondary-foreground transition-colors hover:bg-secondary/80">
                            <ShieldAlert class="h-4 w-4" />
                        </Link>
                        <div>
                            <h1 class="text-lg font-semibold">Catatan Disiplin</h1>
                            <p class="text-sm text-muted-foreground">Poin pelanggaran 6 bulan terakhir</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-4 py-6 pb-24">
                <!-- Active points card -->
                <div class="rounded-lg border bg-card p-5 text-center">
                    <p class="text-xs uppercase tracking-wide text-muted-foreground">Poin Aktif</p>
                    <p class="mt-1 text-5xl font-extrabold"
                        :class="activePoints >= 35 ? 'text-red-600' : activePoints >= 15 ? 'text-amber-600' : 'text-green-600'">
                        {{ activePoints }}
                    </p>
                    <span :class="tier.cls" class="mt-2 inline-block rounded-full border px-3 py-1 text-xs font-bold">
                        {{ tier.label }}
                    </span>
                    <p v-if="cleanRecordBonus > 0" class="mt-3 flex items-center justify-center gap-1 text-xs font-medium text-green-700">
                        <Star class="h-3.5 w-3.5" /> Clean record: −{{ cleanRecordBonus }} poin (3 bulan bersih)
                    </p>
                    <p v-else class="mt-3 text-xs text-muted-foreground">
                        3 bulan tanpa pelanggaran = −10 poin (clean record bonus)
                    </p>
                </div>

                <!-- Breakdown -->
                <div class="mt-4 rounded-lg border bg-card p-4">
                    <h2 class="text-sm font-semibold">Rincian per Kategori</h2>
                    <div class="mt-3 space-y-3">
                        <div v-for="cat in categories" :key="cat.key">
                            <div class="flex items-center justify-between text-sm">
                                <p class="text-muted-foreground">{{ cat.label }}</p>
                                <p class="font-medium">{{ breakdown[cat.key]?.points ?? 0 }} poin · {{ breakdown[cat.key]?.count ?? 0 }}x</p>
                            </div>
                            <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                                <div :class="cat.bar" class="h-full rounded-full"
                                    :style="{ width: Math.min(100, (breakdown[cat.key]?.points ?? 0)) + '%' }" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SP actions -->
                <div v-if="actions.length" class="mt-4">
                    <h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground">Surat Peringatan</h2>
                    <div class="space-y-2">
                        <div v-for="action in actions" :key="action.id" class="rounded-lg border bg-card p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <FileWarning class="h-4 w-4" :class="action.status === 'active' ? 'text-red-500' : 'text-muted-foreground'" />
                                    <p class="text-sm font-bold">{{ actionLabel(action.action_type) }}</p>
                                </div>
                                <span :class="actionStatusBadge(action.status)" class="rounded-full border px-2 py-0.5 text-xs font-medium">
                                    {{ actionStatusLabel(action.status) }}
                                </span>
                            </div>
                            <dl class="mt-2 space-y-1 text-xs">
                                <div class="flex justify-between">
                                    <dt class="text-muted-foreground">Diterbitkan</dt>
                                    <dd class="font-medium">{{ formatDate(action.issued_at) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-muted-foreground">Poin pemicu</dt>
                                    <dd class="font-medium">{{ action.triggered_points }}</dd>
                                </div>
                                <div v-if="action.freeze_until" class="flex justify-between">
                                    <dt class="flex items-center gap-1 text-muted-foreground">
                                        <Ban class="h-3 w-3" /> Freeze promosi s/d
                                    </dt>
                                    <dd class="font-medium text-red-600">{{ formatDate(action.freeze_until) }}</dd>
                                </div>
                                <div v-if="action.required_remediation" class="flex justify-between">
                                    <dt class="text-muted-foreground">Remediasi</dt>
                                    <dd class="font-medium">{{ action.required_remediation }}</dd>
                                </div>
                                <div v-if="action.suspend_incentive" class="flex justify-between">
                                    <dt class="text-muted-foreground">Insentif</dt>
                                    <dd class="font-medium text-red-600">Ditangguhkan</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Violation history -->
                <div class="mt-6">
                    <h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground">Riwayat Pelanggaran</h2>
                    <div v-if="violations.length" class="space-y-2">
                        <div v-for="violation in violations" :key="violation.id" class="rounded-lg border bg-card p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium">{{ violation.violation?.name ?? 'Pelanggaran' }}</p>
                                    <p class="mt-0.5 text-xs text-muted-foreground">{{ formatDateTime(violation.occurred_at) }}</p>
                                    <span :class="categoryBadge(violation.violation?.category ?? '')"
                                        class="mt-1 inline-block rounded-full border px-2 py-0.5 text-[10px] font-medium">
                                        {{ categoryLabel(violation.violation?.category ?? '') }}
                                    </span>
                                </div>
                                <span class="text-sm font-bold"
                                    :class="violation.points >= 35 ? 'text-red-600' : violation.points >= 15 ? 'text-orange-600' : 'text-gray-600'">
                                    +{{ violation.points }}
                                </span>
                            </div>
                            <p v-if="violation.notes" class="mt-2 border-t pt-2 text-xs text-muted-foreground">{{ violation.notes }}</p>
                        </div>
                    </div>
                    <div v-else class="rounded-lg border bg-card p-8 text-center">
                        <CheckCircle2 class="mx-auto h-10 w-10 text-green-500" />
                        <p class="mt-3 text-sm text-muted-foreground">Tidak ada pelanggaran tercatat. Pertahankan!</p>
                    </div>
                </div>

                <!-- Tier info -->
                <div class="mt-6 rounded-md bg-muted p-3">
                    <p class="flex items-center gap-1 text-xs font-medium text-muted-foreground">
                        <Award class="h-3.5 w-3.5" /> Ambang aksi disiplin (rolling 6 bulan)
                    </p>
                    <div class="mt-2 grid grid-cols-5 gap-1 text-center text-[10px]">
                        <div class="rounded bg-amber-100 py-1 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">Teguran<br />15</div>
                        <div class="rounded bg-orange-100 py-1 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400">SP1<br />35</div>
                        <div class="rounded bg-orange-200 py-1 text-orange-900 dark:bg-orange-900/40 dark:text-orange-400">SP2<br />50</div>
                        <div class="rounded bg-red-100 py-1 text-red-800 dark:bg-red-900/30 dark:text-red-400">SP3<br />70</div>
                        <div class="rounded bg-red-200 py-1 text-red-900 dark:bg-red-900/40 dark:text-red-400">PHK<br />85</div>
                    </div>
                </div>
            </div>

            <BottomNavigation current-route="/my/disciplinary-record" />
        </div>
    </div>
</template>
