<script setup lang="ts">
import BottomNavigation from '@/components/BottomNavigation.vue';
import { Head, Link } from '@inertiajs/vue3';
import { CalendarClock, FileText, ShieldAlert, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

interface Contract {
    id: number;
    contract_number: string;
    type: 'pkwt' | 'pkwtt';
    start_date: string;
    end_date: string | null;
    status: string;
    salary_grade: string | null;
    days_remaining: number | null;
    alert_level: string | null;
}

interface Props {
    contracts: Contract[];
}

const { contracts } = defineProps<Props>();

const activeContract = computed(() => contracts.find(c => c.status === 'active'));
const history = computed(() => contracts.filter(c => c.status !== 'active'));

const typeLabel = (t: string) => (t === 'pkwt' ? 'PKWT' : 'PKWTT');
const typeBadge = (t: string) =>
    t === 'pkwt' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-purple-50 text-purple-700 border-purple-200';

const statusBadge = (s: string) => ({
    active: 'bg-green-50 text-green-700 border-green-200',
    expired: 'bg-red-50 text-red-700 border-red-200',
    renewed: 'bg-gray-50 text-gray-600 border-gray-200',
    terminated: 'bg-red-50 text-red-700 border-red-200',
}[s] ?? 'bg-muted text-muted-foreground border-border');

const statusLabel = (s: string) => ({
    active: 'Aktif', expired: 'Berakhir', renewed: 'Diperbarui', terminated: 'Dihentikan',
}[s] ?? s);

const alertText = (c: Contract) => {
    if (c.type !== 'pkwt' || c.status !== 'active' || c.days_remaining === null) return null;
    if (c.days_remaining < 0) return { text: 'Kontrak sudah berakhir', cls: 'text-red-600' };
    return { text: `Berakhir dalam ${c.days_remaining} hari`, cls: c.days_remaining <= 30 ? 'text-red-600' : 'text-amber-600' };
};

const formatDate = (d: string | null) =>
    d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '—';
</script>

<template>
    <Head title="Kontrak Saya" />
    <div class="min-h-screen bg-background">
        <div class="mx-auto min-h-screen max-w-[480px] bg-background">
            <!-- Header -->
            <div class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur-sm">
                <div class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <Link href="/home" class="rounded-md bg-secondary p-2 text-secondary-foreground transition-colors hover:bg-secondary/80">
                            <FileText class="h-4 w-4" />
                        </Link>
                        <div>
                            <h1 class="text-lg font-semibold">Kontrak Kerja Saya</h1>
                            <p class="text-sm text-muted-foreground">PKWT &amp; PKWTT</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-4 py-6 pb-24">
                <!-- Active contract card -->
                <div v-if="activeContract" class="rounded-lg border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <span :class="typeBadge(activeContract.type)" class="rounded-full border px-2.5 py-0.5 text-xs font-bold">
                            {{ typeLabel(activeContract.type) }}
                        </span>
                        <span :class="statusBadge(activeContract.status)" class="rounded-full border px-2.5 py-0.5 text-xs font-medium">
                            {{ statusLabel(activeContract.status) }}
                        </span>
                    </div>

                    <p class="mt-3 text-xs uppercase tracking-wide text-muted-foreground">Nomor Kontrak</p>
                    <p class="text-lg font-bold">{{ activeContract.contract_number }}</p>

                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between border-t pt-2">
                            <dt class="text-muted-foreground">Mulai</dt>
                            <dd class="font-medium">{{ formatDate(activeContract.start_date) }}</dd>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <dt class="text-muted-foreground">Berakhir</dt>
                            <dd class="font-medium">{{ formatDate(activeContract.end_date) }}</dd>
                        </div>
                        <div v-if="activeContract.salary_grade" class="flex justify-between border-t pt-2">
                            <dt class="text-muted-foreground">Grade Gaji</dt>
                            <dd class="font-medium">{{ activeContract.salary_grade }}</dd>
                        </div>
                    </dl>

                    <div v-if="alertText(activeContract)" class="mt-4 flex items-center gap-2 rounded-md bg-amber-50 p-3 dark:bg-amber-900/20">
                        <CalendarClock class="h-4 w-4 text-amber-600" />
                        <p class="text-sm font-medium" :class="alertText(activeContract)!.cls">
                            {{ alertText(activeContract)!.text }}
                        </p>
                    </div>
                </div>

                <div v-else class="rounded-lg border bg-card p-8 text-center">
                    <FileText class="mx-auto h-10 w-10 text-muted-foreground" />
                    <p class="mt-3 text-sm text-muted-foreground">
                        Belum ada kontrak aktif. Hubungi HR untuk informasi lebih lanjut.
                    </p>
                </div>

                <!-- History -->
                <div v-if="history.length" class="mt-6">
                    <h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground">Riwayat Kontrak</h2>
                    <div class="space-y-2">
                        <div v-for="contract in history" :key="contract.id" class="rounded-lg border bg-card p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold">{{ contract.contract_number }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ formatDate(contract.start_date) }} — {{ formatDate(contract.end_date) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span :class="typeBadge(contract.type)" class="rounded-full border px-2 py-0.5 text-[10px] font-bold">
                                        {{ typeLabel(contract.type) }}
                                    </span>
                                    <p class="mt-1 text-xs" :class="statusBadge(contract.status).replace('bg-', 'text-').split(' ')[0]">
                                        {{ statusLabel(contract.status) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expired highlight -->
                <div v-if="activeContract && activeContract.days_remaining !== null && activeContract.days_remaining < 0"
                    class="mt-4 flex items-start gap-2 rounded-md bg-red-50 p-3 dark:bg-red-900/20">
                    <XCircle class="mt-0.5 h-4 w-4 text-red-600" />
                    <p class="text-xs text-red-700 dark:text-red-400">
                        Kontrak Anda telah berakhir. Segera hubungi HR untuk proses perpanjangan atau pengangkatan PKWTT.
                    </p>
                </div>

                <div class="mt-6 flex items-center gap-2 rounded-md bg-muted p-3">
                    <ShieldAlert class="h-4 w-4 text-muted-foreground" />
                    <p class="text-xs text-muted-foreground">
                        Status kontrak dikelola oleh HR. Perpanjangan PKWT atau pengangkatan PKWTT akan muncul di halaman ini.
                    </p>
                </div>
            </div>

            <BottomNavigation current-route="/my/contract" />
        </div>
    </div>
</template>
