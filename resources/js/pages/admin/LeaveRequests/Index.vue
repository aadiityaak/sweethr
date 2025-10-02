<template>
    <Head title="Admin - Kelola Cuti" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Kelola Cuti</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Kelola pengajuan cuti karyawan</p>
                    </div>
                </div>
            </div>

            <!-- Overview Cards -->
            <div class="mb-10 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total Requests -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/10 ring-1 ring-blue-500/20">
                                <Calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Total Pengajuan</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Semua pengajuan cuti</p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-blue-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-blue-50/50 px-3 py-2 dark:bg-blue-950/30">
                            <span class="text-sm font-medium text-blue-700 dark:text-blue-400">Total</span>
                            <span class="text-sm font-semibold text-blue-800 dark:text-blue-300">{{ stats.total_requests }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Bulan Ini</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ stats.this_month_count }}
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Pending -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-amber-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-amber-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500/10 ring-1 ring-amber-500/20">
                                <Clock class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Menunggu</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Menunggu persetujuan</p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-amber-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-amber-50/50 px-3 py-2 dark:bg-amber-950/30">
                            <span class="text-sm font-medium text-amber-700 dark:text-amber-400">Menunggu</span>
                            <span class="text-sm font-semibold text-amber-800 dark:text-amber-300">{{ stats.pending_count }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white"> {{ stats.pending_percentage }}% </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-amber-500/5 to-orange-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Approved -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-emerald-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-emerald-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/10 ring-1 ring-emerald-500/20">
                                <CheckCircle class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Disetujui</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Pengajuan disetujui</p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-emerald-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-emerald-50/50 px-3 py-2 dark:bg-emerald-950/30">
                            <span class="text-sm font-medium text-emerald-700 dark:text-emerald-400">Disetujui</span>
                            <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">{{ stats.approved_count }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ Math.round((stats.approved_count / stats.total_requests) * 100) }}%
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Rejected -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-red-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-red-950/30"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-500/10 ring-1 ring-red-500/20">
                                <XCircle class="h-5 w-5 text-red-600 dark:text-red-400" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-white">Ditolak</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Pengajuan ditolak</p>
                            </div>
                        </div>
                        <div class="flex h-2 w-2 rounded-full bg-red-400"></div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-center justify-between rounded-lg bg-red-50/50 px-3 py-2 dark:bg-red-950/30">
                            <span class="text-sm font-medium text-red-700 dark:text-red-400">Ditolak</span>
                            <span class="text-sm font-semibold text-red-800 dark:text-red-300">{{ stats.rejected_count }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50/50 px-3 py-2 dark:bg-gray-800/50">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Persentase</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ Math.round((stats.rejected_count / stats.total_requests) * 100) }}%
                            </span>
                        </div>
                    </div>

                    <!-- Hover effect overlay -->
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-red-500/5 to-rose-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="mb-10 grid gap-8 lg:grid-cols-2">
                <!-- 30-Day Trend Chart -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-purple-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-purple-950/30"
                >
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-500/10 ring-1 ring-purple-500/20">
                            <Calendar class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tren Pengajuan 30 Hari Terakhir</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Statistik pengajuan cuti harian</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <LeaveRequestChart
                            type="line"
                            :trend-data="{
                                labels: dailyTrend.map((item) => item.date),
                                data: dailyTrend.map((item) => item.count),
                                approvedData: dailyTrend.map((item) => item.approved),
                                pendingData: dailyTrend.map((item) => item.pending),
                                rejectedData: dailyTrend.map((item) => item.rejected),
                            }"
                        />
                    </div>
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-purple-500/5 to-violet-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Status Distribution -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30"
                >
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20">
                            <CheckCircle class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Distribusi Status</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Perbandingan status pengajuan</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <LeaveRequestChart
                            type="doughnut"
                            :status-data="{
                                pending: stats.pending_count,
                                approved: stats.approved_count,
                                rejected: stats.rejected_count,
                            }"
                        />
                    </div>
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>
            </div>

            <!-- Additional Charts Row -->
            <div class="mb-10 grid gap-8 lg:grid-cols-2">
                <!-- Department-wise Leave Requests -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-emerald-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-emerald-950/30"
                >
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10 ring-1 ring-emerald-500/20">
                            <Users class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pengajuan per Departemen</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Distribusi pengajuan cuti per departemen</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <LeaveRequestChart
                            type="bar"
                            :department-data="{
                                labels: departmentStats.map((item) => item.department),
                                data: departmentStats.map((item) => item.total),
                            }"
                        />
                    </div>
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>

                <!-- Leave Type Distribution -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-amber-50/30 p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-amber-950/30"
                >
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/10 ring-1 ring-amber-500/20">
                            <Calendar class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Distribusi Tipe Cuti</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Pengajuan cuti berdasarkan tipe</p>
                        </div>
                    </div>
                    <div class="h-64">
                        <LeaveRequestChart
                            type="bar"
                            :leave-type-data="{
                                labels: leaveTypeStats.map((item) => item.type),
                                data: leaveTypeStats.map((item) => item.total),
                            }"
                        />
                    </div>
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-amber-500/5 to-orange-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>
            </div>

            <!-- Filters -->
            <div
                class="group relative mb-10 overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-gray-50/30 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-gray-900/30"
            >
                <div class="border-b border-gray-200/50 p-6 dark:border-gray-800/50">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-500/10 ring-1 ring-gray-500/20">
                            <FilterX class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Filter & Pencarian</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Filter pengajuan cuti berdasarkan kriteria</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid gap-4 md:grid-cols-6">
                        <div class="md:col-span-2">
                            <Input v-model="search" placeholder="Cari karyawan..." class="w-full" @input="debouncedSearch" />
                        </div>
                        <div>
                            <select
                                v-model="selectedStatus"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800"
                                @change="applyFilters"
                            >
                                <option value="">Semua Status</option>
                                <option value="pending">Menunggu</option>
                                <option value="approved">Disetujui</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>
                        <div>
                            <select
                                v-model="selectedLeaveType"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800"
                                @change="applyFilters"
                            >
                                <option value="">Semua Tipe</option>
                                <option v-for="type in leaveTypes" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <select
                                v-model="selectedDepartment"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800"
                                @change="applyFilters"
                            >
                                <option value="">Semua Departemen</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <Button variant="outline" @click="clearFilters" class="w-full">
                                <FilterX class="mr-2 h-4 w-4" />
                                Reset
                            </Button>
                        </div>
                    </div>
                </div>
                <div
                    class="absolute inset-0 rounded-xl bg-gradient-to-br from-gray-500/5 to-slate-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                ></div>
            </div>

            <!-- Leave Requests Table -->
            <div
                class="group relative overflow-hidden rounded-xl border border-gray-200/50 bg-gradient-to-br from-white to-blue-50/30 shadow-sm transition-all hover:shadow-md dark:border-gray-800/50 dark:from-gray-950 dark:to-blue-950/30"
            >
                <div class="border-b border-gray-200/50 px-6 py-4 dark:border-gray-800/50">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20">
                            <Calendar class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Pengajuan Cuti</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ leaveRequests.total }} pengajuan ditemukan</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-t border-gray-200 dark:border-gray-700">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Karyawan</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Tipe Cuti</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Durasi</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Diajukan</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="request in leaveRequests.data"
                                :key="request.id"
                                class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-900/50"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">{{
                                                getInitials(request.user?.name || '')
                                            }}</span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ request.user?.name || 'N/A' }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ request.user?.employee_id || 'N/A' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/20 dark:text-blue-400"
                                    >
                                        {{ request.leave_type?.name || 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ formatDate(request.start_date) }}</p>
                                        <p class="text-gray-500 dark:text-gray-400">s/d {{ formatDate(request.end_date) }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    <span class="font-medium">{{ request.duration }} hari</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="{
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400': request.status === 'pending',
                                            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': request.status === 'approved',
                                            'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': request.status === 'rejected',
                                        }"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        {{ getStatusLabel(request.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    {{ formatDate(request.created_at) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Button
                                            v-if="request.status === 'pending'"
                                            size="sm"
                                            @click="approveRequest(request)"
                                            class="text-green-600 hover:bg-green-50"
                                            variant="ghost"
                                        >
                                            <Check class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            v-if="request.status === 'pending'"
                                            size="sm"
                                            @click="openRejectModal(request)"
                                            class="text-red-600 hover:bg-red-50"
                                            variant="ghost"
                                        >
                                            <X class="h-4 w-4" />
                                        </Button>
                                        <Button size="sm" variant="ghost" @click="viewDetails(request)">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                        <Button size="sm" @click="openDeleteModal(request)" class="text-red-600 hover:bg-red-50" variant="ghost">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="leaveRequests.total > 10" class="border-t border-gray-200 p-4 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Menampilkan {{ leaveRequests.from || 0 }} sampai {{ leaveRequests.to || 0 }} dari {{ leaveRequests.total }} hasil
                        </p>
                        <div class="flex gap-2">
                            <Button
                                v-for="link in leaveRequests.links"
                                :key="link.label"
                                :disabled="!link.url"
                                :variant="link.active ? 'default' : 'outline'"
                                size="sm"
                                @click="link.url && router.visit(link.url)"
                                class="pagination-btn"
                            >
                                <span v-html="link.label"></span>
                            </Button>
                        </div>
                    </div>
                    <div
                        class="absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/5 to-indigo-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Details Modal -->
        <Dialog v-model:open="showDetailsModal">
            <DialogContent class="flex max-h-[90vh] flex-col sm:max-w-2xl">
                <DialogHeader class="shrink-0">
                    <DialogTitle>Detail Pengajuan Cuti</DialogTitle>
                    <DialogDescription> Informasi lengkap pengajuan cuti {{ selectedRequest?.user?.name || 'N/A' }} </DialogDescription>
                </DialogHeader>
                <div v-if="selectedRequest" class="flex-1 space-y-6 overflow-y-auto pr-2">
                    <!-- Employee Info -->
                    <div class="rounded-lg border p-4">
                        <h4 class="mb-3 font-semibold">Informasi Karyawan</h4>
                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Nama</label>
                                <p class="text-sm">{{ selectedRequest.user?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">ID Karyawan</label>
                                <p class="text-sm">{{ selectedRequest.user?.employee_id || 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Leave Details -->
                    <div class="rounded-lg border p-4">
                        <h4 class="mb-3 font-semibold">Detail Cuti</h4>
                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Tipe Cuti</label>
                                <p class="text-sm">{{ selectedRequest.leave_type?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Status</label>
                                <span
                                    :class="{
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400':
                                            selectedRequest.status === 'pending',
                                        'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': selectedRequest.status === 'approved',
                                        'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': selectedRequest.status === 'rejected',
                                    }"
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                >
                                    {{ getStatusLabel(selectedRequest.status) }}
                                </span>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Tanggal Mulai</label>
                                <p class="text-sm">{{ formatDate(selectedRequest.start_date) }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Tanggal Selesai</label>
                                <p class="text-sm">{{ formatDate(selectedRequest.end_date) }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Durasi</label>
                                <p class="text-sm">{{ selectedRequest.duration }} hari</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Tanggal Pengajuan</label>
                                <p class="text-sm">{{ formatDate(selectedRequest.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Reason -->
                    <div class="rounded-lg border p-4">
                        <h4 class="mb-3 font-semibold">Alasan Cuti</h4>
                        <p class="text-sm whitespace-pre-wrap">{{ selectedRequest.reason || 'Tidak ada alasan yang diberikan' }}</p>
                    </div>

                    <!-- Admin Notes (if exists) -->
                    <div v-if="selectedRequest.admin_notes" class="rounded-lg border p-4">
                        <h4 class="mb-3 font-semibold">Catatan Admin</h4>
                        <p class="text-sm whitespace-pre-wrap">{{ selectedRequest.admin_notes }}</p>
                    </div>
                </div>
                <DialogFooter class="mt-4 shrink-0 border-t pt-4">
                    <Button variant="outline" @click="showDetailsModal = false">Tutup</Button>
                    <div v-if="selectedRequest?.status === 'pending'" class="flex gap-2">
                        <Button
                            @click="
                                approveRequest(selectedRequest);
                                showDetailsModal = false;
                            "
                            class="bg-green-600 hover:bg-green-700"
                        >
                            <Check class="mr-2 h-4 w-4" />
                            Setujui
                        </Button>
                        <Button
                            @click="
                                openRejectModal(selectedRequest);
                                showDetailsModal = false;
                            "
                            class="bg-red-600 hover:bg-red-700"
                        >
                            <X class="mr-2 h-4 w-4" />
                            Tolak
                        </Button>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Approve Confirmation Modal -->
        <ConfirmationModal
            :show="showApproveModal"
            title="Setujui Pengajuan Cuti"
            :message="`Yakin ingin menyetujui pengajuan cuti ${selectedRequest?.user?.name || 'N/A'}?`"
            confirm-text="Setujui"
            cancel-text="Batal"
            type="info"
            @confirm="confirmApprove"
            @cancel="showApproveModal = false"
        />

        <!-- Reject Modal -->
        <Dialog v-model:open="showRejectModal">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Tolak Pengajuan Cuti</DialogTitle>
                    <DialogDescription> Berikan alasan penolakan untuk {{ selectedRequest?.user?.name || 'N/A' }} </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium">Alasan Penolakan *</label>
                        <Textarea v-model="rejectForm.admin_notes" placeholder="Jelaskan alasan penolakan..." class="mt-1" :rows="4" />
                        <p v-if="rejectForm.errors.admin_notes" class="mt-1 text-sm text-red-600">
                            {{ rejectForm.errors.admin_notes }}
                        </p>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showRejectModal = false">Batal</Button>
                    <Button @click="submitReject" :disabled="rejectForm.processing" class="bg-red-600 hover:bg-red-700">
                        {{ rejectForm.processing ? 'Menolak...' : 'Tolak Pengajuan' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal
            :show="showDeleteModal"
            title="Hapus Pengajuan Cuti"
            :message="`Yakin ingin menghapus pengajuan cuti ${selectedRequest?.user?.name || 'N/A'}? Tindakan ini tidak dapat dibatalkan.`"
            confirm-text="Hapus"
            cancel-text="Batal"
            type="danger"
            @confirm="confirmDelete"
            @cancel="showDeleteModal = false"
        />
    </AppLayout>
</template>

<script setup lang="ts">
import ConfirmationModal from '@/components/ConfirmationModal.vue';
import LeaveRequestChart from '@/components/LeaveRequestChart.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/components/ui/toast/use-toast';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { Calendar, Check, CheckCircle, Clock, Eye, FilterX, Trash2, X, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';

interface LeaveRequest {
    id: number;
    user: {
        id: number;
        name: string;
        employee_id: string;
    };
    leave_type: {
        id: number;
        name: string;
    };
    start_date: string;
    end_date: string;
    duration: number;
    status: 'pending' | 'approved' | 'rejected';
    reason: string;
    admin_notes?: string;
    created_at: string;
}

interface Stats {
    total_requests: number;
    pending_count: number;
    approved_count: number;
    rejected_count: number;
    this_month_count: number;
    pending_percentage: number;
}

interface Props {
    leaveRequests: {
        data: LeaveRequest[];
        total: number;
        from: number;
        to: number;
        links: any[];
    };
    stats: Stats;
    monthlyTrend: any[];
    dailyTrend: any[];
    departmentStats: any[];
    leaveTypeStats: any[];
    leaveTypes: any[];
    departments: any[];
    filters: {
        search?: string;
        status?: string;
        leave_type?: string;
        department?: string;
        date_from?: string;
        date_to?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dasbor',
        href: '/dashboard',
    },
    {
        title: 'Kelola Cuti',
        href: '/admin/leave-requests',
    },
];

// Filter states
const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');
const selectedLeaveType = ref(props.filters.leave_type || '');
const selectedDepartment = ref(props.filters.department || '');

// Modal states
const showRejectModal = ref(false);
const showDetailsModal = ref(false);
const showApproveModal = ref(false);
const showDeleteModal = ref(false);
const selectedRequest = ref<LeaveRequest | null>(null);

// Toast
const { toast } = useToast();

// Forms
const rejectForm = useForm({
    admin_notes: '',
});

// Methods
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase();
};

const getStatusLabel = (status: string) => {
    const labels = {
        pending: 'Menunggu',
        approved: 'Disetujui',
        rejected: 'Ditolak',
    };
    return labels[status as keyof typeof labels] || status;
};

const debouncedSearch = debounce(() => {
    applyFilters();
}, 300);

const applyFilters = () => {
    router.get(
        '/admin/leave-requests',
        {
            search: search.value,
            status: selectedStatus.value,
            leave_type: selectedLeaveType.value,
            department: selectedDepartment.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const clearFilters = () => {
    search.value = '';
    selectedStatus.value = '';
    selectedLeaveType.value = '';
    selectedDepartment.value = '';
    router.get('/admin/leave-requests');
};

const approveRequest = (request: LeaveRequest) => {
    selectedRequest.value = request;
    showApproveModal.value = true;
};

const confirmApprove = () => {
    if (!selectedRequest.value) return;

    router.patch(
        `/admin/leave-requests/${selectedRequest.value.id}/approve`,
        {},
        {
            preserveState: false,
            preserveScroll: false,
            onSuccess: () => {
                toast({
                    title: 'Berhasil!',
                    description: `Pengajuan cuti ${selectedRequest.value?.user?.name || 'N/A'} telah disetujui.`,
                    variant: 'success',
                    duration: 3000,
                });
                showApproveModal.value = false;
                selectedRequest.value = null;
            },
            onError: () => {
                toast({
                    title: 'Gagal!',
                    description: 'Terjadi kesalahan saat menyetujui pengajuan.',
                    variant: 'destructive',
                    duration: 4000,
                });
            },
        },
    );
};

const openRejectModal = (request: LeaveRequest) => {
    selectedRequest.value = request;
    rejectForm.reset();
    showRejectModal.value = true;
};

const submitReject = () => {
    if (!selectedRequest.value) return;

    rejectForm.patch(`/admin/leave-requests/${selectedRequest.value.id}/reject`, {
        preserveState: false,
        preserveScroll: false,
        onSuccess: () => {
            toast({
                title: 'Berhasil!',
                description: `Pengajuan cuti ${selectedRequest.value?.user?.name || 'N/A'} telah ditolak.`,
                variant: 'warning',
                duration: 3000,
            });
            showRejectModal.value = false;
            selectedRequest.value = null;
        },
        onError: () => {
            toast({
                title: 'Gagal!',
                description: 'Terjadi kesalahan saat menolak pengajuan.',
                variant: 'destructive',
                duration: 4000,
            });
        },
    });
};

const viewDetails = (request: LeaveRequest) => {
    selectedRequest.value = request;
    showDetailsModal.value = true;
};

const openDeleteModal = (request: LeaveRequest) => {
    selectedRequest.value = request;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!selectedRequest.value) return;

    router.delete(`/admin/leave-requests/${selectedRequest.value.id}`, {
        preserveState: false,
        preserveScroll: false,
        onSuccess: () => {
            toast({
                title: 'Berhasil!',
                description: `Pengajuan cuti ${selectedRequest.value?.user?.name || 'N/A'} telah dihapus.`,
                variant: 'success',
                duration: 3000,
            });
            showDeleteModal.value = false;
            selectedRequest.value = null;
        },
        onError: () => {
            toast({
                title: 'Gagal!',
                description: 'Terjadi kesalahan saat menghapus pengajuan.',
                variant: 'destructive',
                duration: 4000,
            });
        },
    });
};
</script>

<style>
.pagination-btn[disabled] {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
