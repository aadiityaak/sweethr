# RENCANA IMPLEMENTASI — DASHBOARD LMS, DISCIPLINARY SYSTEM & RAPORT SDM

**Perusahaan:** PT Warung Mas Mbull
**Scope:** Database Karyawan + Kontrak (PKWT/PKWTT), LMS Kurikulum per Jabatan, Sistem Poin Disiplin, Raport Semester, Reward Engine, Executive Dashboard
**Target Pengguna:** CEO, HR Manager, Operational Head, Store/Outlet Managers
**Dokumen Basis:** Rancangan Strategic HR (Nugra Pratama — HR Manager, disetujui CEO)

---

## 1. RINGKASAN EKSEKUTIF

Sistem yang sudah berjalan (SweetHR — Laravel 12 + Inertia Vue 3 + shadcn-vue + MySQL) sudah memiliki fondasi ± 40–50%:

| Fondasi Eksisting | Status | Keterangan |
|---|---|---|
| Data karyawan (identitas, jabatan, outlet, join date) | ✅ Ada | `users` + [User.php](file:///d:/dev/sweethr/app/Models/User.php), relasi `position`, `department` |
| Status kepegawaian | ⚠️ Sebagian | Ada `employment_status` (active/inactive/terminated) — **belum ada klasifikasi PKWT/PKWTT & tanggal akhir kontrak** |
| LMS core (kategori, materi, kuis, tugas) | ✅ Lengkap | `LmsCategory`, `LmsMaterial`, `LmsMaterialRead`, `LmsQuiz`, `LmsQuizQuestion`, `LmsQuizAttempt`, `LmsAssignment`, `LmsAssignmentSubmission` |
| Appraisal kinerja | ⚠️ Ada, sederhana | [LmsPerformanceAppraisal.php](file:///d:/dev/sweethr/app/Models/LmsPerformanceAppraisal.php) — 14 parameter, **belum ada bobot 50/30/20 & predikat** |
| Absensi + keterlambatan | ✅ Ada | `attendances` dengan `late_duration` → bisa jadi sumber otomatis pelanggaran "terlambat" |
| Payroll & insentif | ✅ Ada | `payrolls`, `salary_settings` → integrasi bonus/denda |
| Disciplinary point system | ❌ Belum ada | Harus dibangun dari nol |
| Raport semester + predikat A–E | ❌ Belum ada | Harus dibangun dari nol |
| Reward/rekomendasi karir otomatis | ❌ Belum ada | Harus dibangun dari nol |

**Total estimasi:** 15–23 hari kerja (3–5 minggu), dibagi 3 gelombang implementasi.

---

## 2. ARSITEKTUR DATA (MIGRATION BARU)

### 2.1 Tabel: `employment_contracts` — Kontrak PKWT/PKWTT
> Mencegah keterlambatan perpanjangan kontrak via alert H-60/H-30.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | FK → users | |
| `contract_number` | string unique | Nomor kontrak |
| `type` | enum(`pkwt`,`pkwtt`) | Kontrak tertentu vs tetap |
| `start_date` | date | Mulai kontrak |
| `end_date` | date nullable | Null = PKWTT / tanpa batas |
| `status` | enum(`active`,`expired`,`renewed`,`terminated`) | |
| `salary_grade` | string nullable | Grade jabatan saat kontrak |
| `notes` | text nullable | |
| `created_by` | FK → users nullable | |

**Index:** `(user_id, status)`, `(end_date)` untuk query alert.
**Model:** `App\Models\EmploymentContract` + relasi `contractHistories()` di User.
**Alert engine:** `days_remaining` computed + scope `expiringWithin($days)`.

### 2.2 Tabel: `disciplinary_violations` — Master Kategori Pelanggaran
> Kategorisasi & bobot poin (data master, di-seed sekali).

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `code` | string unique | mis. `LT-01`, `FS-02` |
| `name` | string | mis. "Datang terlambat < 15 menit" |
| `category` | enum(`ringan`,`sedang`,`berat`) | |
| `points` | unsignedTinyInteger | Ringan 5–10, Sedang 15–25, Berat 35+ |
| `description` | text nullable | |
| `is_active` | boolean default true | |

**Seeder:** `DisciplinaryViolationSeeder` — isi lengkap sesuai matriks rancangan (terlambat, seragam, area kotor, alpha, selisih kasir berulang, FIFO, food safety, manipulasi data, dll).

### 2.3 Tabel: `employee_violations` — Pencatatan Pelanggaran Karyawan

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | FK → users | |
| `disciplinary_violation_id` | FK | |
| `occurred_at` | datetime | Waktu kejadian |
| `points` | unsignedTinyInteger | Snapshot poin saat kejadian |
| `notes` | text nullable | |
| `evidence_path` | string nullable | Foto/dokumen |
| `reported_by` | FK → users | SPV/HR pelapor |
| `source` | enum(`manual`,`auto_attendance`) | Auto = terlambat dari absensi |
| `created_at/updated_at` | timestamps | |

**Index:** `(user_id, occurred_at)` — untuk kalkulasi rolling 6 bulan.
**Model:** `App\Models\EmployeeViolation`.

### 2.4 Tabel: `disciplinary_actions` — SP & Tindakan Otomatis

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | FK → users | |
| `action_type` | enum(`teguran_lisan`,`sp1`,`sp2`,`sp3`,`phk_eval`) | Tier 15/35/50/70/85 poin |
| `triggered_points` | unsignedTinyInteger | Akumulasi saat trigger |
| `status` | enum(`active`,`resolved`,`revoked`) | |
| `issued_at` | datetime | |
| `document_path` | string nullable | PDF SP yang di-generate |
| `notes` | text nullable | |
| `issued_by` | FK → users nullable | Konfirmasi manual HR (opsional) |

**Efek otomatis per tier (kolom tambahan/flag):**
- `sp1` → freeze promotion 3 bulan (field `freeze_until` date)
- `sp2` → flag `required_remediation = true` (wajib remedial LMS)
- `sp3` → penangguhan insentif (integrasi `payroll_details`)

### 2.5 Tabel: `semester_reports` — Raport Kinerja Semester

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | FK → users | |
| `year` | smallint | |
| `semester` | enum(`1`,`2`) | Semester I / II |
| `kpi_score` | decimal(5,2) | Nilai KPI operasional (input manual SPV/evaluasi) |
| `lms_score` | decimal(5,2) | Otomatis: rata-rata kuis + penyelesaian modul + praktik |
| `discipline_score` | decimal(5,2) | Otomatis: kehadiran + poin pelanggaran 6 bulan |
| `final_score` | decimal(5,2) | `(kpi × 0.5) + (lms × 0.3) + (discipline × 0.2)` |
| `grade` | enum(`A`,`B`,`C`,`D`,`E`) | 90–100 / 80–89 / 70–79 / 60–69 / <60 |
| `total_violation_points` | unsignedTinyInteger | Snapshot poin periode |
| `recommendation` | json | Output reward engine |
| `published_at` | datetime nullable | Terbit setelah approve HR |
| `generated_by` | FK → users nullable | |

**Unique:** `(user_id, year, semester)` — 1 raport per karyawan per semester.
**Model:** `App\Models\SemesterReport`.

### 2.6 Tabel: `lms_curriculum_matrix` — Matriks Kurikulum per Jabatan
> Jabatan → modul wajib (Cleaner, Pramusaji, Kasir, Koki, Ast SPV, SPV Outlet).

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `position_id` | FK → positions | |
| `lms_category_id` | FK nullable | Kategori modul utama |
| `lms_material_id` | FK nullable | Materi wajib |
| `lms_quiz_id` | FK nullable | Kuis/ujian wajib |
| `item_type` | enum(`material`,`quiz`,`assignment`) | |
| `is_mandatory` | boolean default true | Wajib vs opsional |
| `deadline_days` | unsignedTinyInteger nullable | Batas hari sejak assignment |

> Catatan: `lms_categories` sudah punya `visible_roles` — matriks ini memperluas jadi granular per `position_id`.

---

## 3. ENGINE BACKEND (SERVICE CLASS BARU)

Semua engine ditaruh di `app/Services/` mengikuti pola existing ([PayrollService.php](file:///d:/dev/sweethr/app/Services/PayrollService.php), `DatabaseHealthService.php`).

### 3.1 `ContractAlertService`
- `getExpiringContracts(int $days = 60)` — kontrak PKWT aktif yang berakhir ≤ X hari.
- `getAlerts()` — return H-60 & H-30 bucket + karyawan terdampak.
- Dipanggil oleh: widget dashboard + command scheduled harian (notif ke HR).

### 3.2 `DisciplinaryPointService` ⭐ (paling kompleks)
- `getActivePoints(User $user)` — akumulasi poin **rolling 6 bulan** (query `employee_violations` where `occurred_at >= now()->subMonths(6)`).
- `applyCleanRecordBonus(User $user)` — jika 3 bulan berturut-turut tanpa pelanggaran → **−10 poin** (log sebagai adjustment, simpan di tabel `point_adjustments` atau kolom `bonus_applied_at` di users).
- `checkAndTriggerActions(User $user)` — cek ambang & buat `disciplinary_actions`:
  | Poin | Aksi Otomatis | Dampak LMS/Karir |
  |---|---|---|
  | ≥15 | Teguran Lisan | Wajib retake modul SOP ≤ 3 hari |
  | ≥35 | SP 1 | Freeze promotion 3 bulan + generator draf SP |
  | ≥50 | SP 2 | Remedial & retraining LMS + evaluasi SPV/HR |
  | ≥70 | SP 3 | Tahan insentif + pertimbangan demosi |
  | ≥85 | Evaluasi PHK | Flag evaluasi PHK |
- `autoDetectFromAttendance()` — deteksi terlambat dari `attendances.late_duration` (<15 mnt = ringan, >30 mnt tanpa konfirmasi = sedang) → buat `employee_violations` dengan `source = auto_attendance`.
- **Pencegahan duplikat SP:** 1 SP per level aktif; trigger hanya naik level.

### 3.3 `SemesterReportService`
- `generate(User $user, int $year, int $semester): SemesterReport`
  1. **KPI (50%):** rata-rata `LmsPerformanceAppraisal` parameter inti periode (atau input manual SPV).
  2. **LMS (30%):** rata-rata skor `LmsQuizAttempt` + persentase penyelesaian modul (`LmsMaterialRead`/submission) tepat waktu.
  3. **Disiplin (20%):** skor kehadiran (dari `attendances`) + konversi poin pelanggaran (0 poin = 100, berkurang per tier).
- `determineGrade(float $final): string` — A/B/C/D/E.
- `buildRecommendation(SemesterReport)` → delegasi ke RewardRecommendationService.
- Publikasi: `publish()` — status draft → published (visible ke karyawan).

### 3.4 `RewardRecommendationService`
Matriks keputusan:

| Hasil Raport | Reward & Insentif | Rekomendasi Karir |
|---|---|---|
| **A 2 semester beruntun** | Kenaikan gaji berkala + bonus outstanding | Prioritas promosi grade + opsi PKWTT |
| **A / B 1 semester** | Bonus insentif semesteran | Rekomendasi promosi + Pre-Promotion Course |
| **C** | Gaji standar, insentif normal | Perpanjangan PKWT standar |
| **D** | Tahan bonus | Freeze promotion + masuk PIP |
| **E** | Tanpa bonus | Evaluasi non-perpanjangan / demosi / PHK |

- Cek freeze promotion aktif (dari `disciplinary_actions`) → **blok rekomendasi promosi** meski predikat bagus.
- Output JSON disimpan di `semester_reports.recommendation`.

---

## 4. COMMANDS & SCHEDULING (Console)

| Command | Schedule | Fungsi |
|---|---|---|
| `contracts:send-expiry-alerts` | Harian 07:00 | Kirim notif H-60/H-30 ke HR & karyawan |
| `discipline:auto-detect-attendance` | Harian 23:00 | Deteksi terlambat → pelanggaran otomatis |
| `discipline:check-point-thresholds` | Harian 23:30 | Rekomendasi poin → trigger SP tier |
| `discipline:apply-clean-record-bonus` | Bulanan tgl 1 | Bonus −10 poin (3 bulan bersih) |
| `reports:generate-semester` | Manual + awal semester | Generate raport massal (draft) |

Registrasi di `routes/console.php`, schedule via `Schedule::` facade (pola Laravel 12).

---

## 5. CONTROLLERS & ROUTES

### Admin (middleware `admin` — pola existing di [web.php](file:///d:/dev/sweethr/app/Http/Controllers/Admin/))

| Controller | Routes | Halaman |
|---|---|---|
| `Admin\EmploymentContractController` | resource `contracts` + `GET contracts/alerts` | Index, Create, Edit + alert list |
| `Admin\DisciplinaryViolationController` | resource `disciplinary-violations` (master CRUD) | Index, Create, Edit |
| `Admin\EmployeeViolationController` | resource `employee-violations` + `POST bulk-import` | Index, Create, Show + timeline |
| `Admin\DisciplinaryActionController` | `GET disciplinary-actions` + `PATCH {id}/confirm` + `GET {id}/sp-pdf` | Index, Show, generate PDF SP |
| `Admin\SemesterReportController` | `GET semester-reports` + `POST generate` + `PATCH {id}/publish` + `GET {id}/pdf` | Index, Show, generate massal |
| `Admin\CurriculumMatrixController` | resource `curriculum-matrix` | Index, Create, Edit |
| `Admin\LmsDashboardController` | `GET admin/lms-dashboard` | Executive view (widget 5-in-1) |

### User (karyawan)
- `GET my/contract` — lihat kontrak & statusnya sendiri.
- `GET my/disciplinary-record` — poin aktif, riwayat pelanggaran, SP.
- `GET my/semester-report` — raport semester pribadi (setelah publish).

---

## 6. FRONTEND PAGES (Vue 3 + Inertia + shadcn-vue)

Semua halaman baru di `resources/js/pages/`, komponen memakai ui kit existing (`resources/js/components/ui/`).

### 6.1 Halaman Admin
```
admin/
├── Contracts/
│   ├── Index.vue            (tabel + badge status + countdown hari, filter PKWT/PKWTT)
│   ├── Create.vue / Edit.vue
│   └── Alerts.vue           (daftar H-60/H-30, CTA perpanjangan)
├── Disciplinary/
│   ├── Violations/Index.vue  (master kategori + poin)
│   ├── Records/Index.vue     (feed pelanggaran semua karyawan + filter outlet)
│   ├── Records/Create.vue    (form catat pelanggaran, preview poin)
│   └── Actions/Index.vue     (list SP + status + aksi PDF)
├── Lms/
│   ├── CurriculumMatrix/Index.vue  (matriks jabatan × modul, grid)
│   └── SemesterReport/
│       ├── Index.vue          (list per periode, tombol generate)
│       └── Show.vue           (raport card + breakdown bobot + rekomendasi)
└── LmsDashboard/
    └── Index.vue              (EXECUTIVE VIEW — 5 widget)
```

### 6.2 Widget Executive Dashboard ([Dashboard.vue](file:///d:/dev/sweethr/resources/js/pages/admin/Dashboard.vue) atau halaman terpisah)
1. **Personnel Status Widget** — donut chart rasio PKWT vs PKWTT + list kontrak H-60/H-30 (Chart.js, komponen `Chart.vue` existing).
2. **Outlet Training Progress** — bar chart perbandingan % penyelesaian LMS antar outlet (real-time).
3. **Disciplinary & Violation Feed** — line chart akumulasi poin bulanan + feed pelanggaran terbaru.
4. **Automated SP Generator Panel** — kontrak mencapai 35 poin → tombol generate draf SP 1 + status lock promosi.
5. **Semester Report Card & Reward Engine** — tabel raport + badge predikat + rekomendasi gaji/grade + tombol export PDF.

### 6.3 Halaman Karyawan
```
user/
├── MyContract.vue            (kartu kontrak + countdown pribadi)
├── MyDisciplinaryRecord.vue  (poin aktif + timeline + SP)
└── MySemesterReport.vue      (raport + predikat + rekomendasi)
```

### 6.4 Komponen reusable baru
- `components/Disciplinary/PointBadge.vue` — badge poin dengan warna tier (hijau/kuning/orange/merah).
- `components/Contracts/ContractCountdown.vue` — countdown H-X dengan warna dinamis.
- `components/Lms/RaportCard.vue` — kartu raport semester (grade A–E, breakdown bobot).
- `components/Lms/CurriculumMatrixTable.vue` — grid jabatan × modul.

---

## 7. INTEGRASI DENGAN SISTEM EKSISTING

| Titik Integrasi | Detail |
|---|---|
| **Absensi → Disiplin** | `attendances.late_duration` → auto violation (terlambat <15 mnt / >30 mnt) |
| **LMS → Disiplin** | Poin ≥15 → assign ulang modul SOP (buat `LmsAssignment` retake otomatis, deadline 3 hari) |
| **SP 1 → LMS/Karir** | `freeze_until` → blokir tombol/flux promosi + flag di rekomendasi reward |
| **Appraisal → Raport** | `LmsPerformanceAppraisal` (periode 6 bulan) → komponen KPI 50% |
| **Raport → Payroll** | Rekomendasi bonus/denda → catatan di `payroll_details` (insentif semesteran) |
| **Kontrak → Payroll** | Grade/skala upah → `salary_settings` |
| **Notifikasi** | Laravel Notification (database + email) untuk alert kontrak, SP, raport terbit |

---

## 8. FASE IMPLEMENTASI (3 GELOMBANG)

### 🚀 Gelombang 1 — Quick Win (5–7 hari)
1. Migration `employment_contracts` + model + CRUD admin + halaman Vue.
2. `ContractAlertService` + command harian + widget Personnel Status.
3. Migration `lms_curriculum_matrix` + seeder matriks jabatan (Cleaner s/d SPV Outlet) + halaman matriks.
4. Halaman `MyContract.vue` karyawan.
- **Value:** HR/CEO langsung dapat notifikasi kontrak mau habis.

### ⚙️ Gelombang 2 — Core Disciplinary (6–10 hari)
5. Migration `disciplinary_violations`, `employee_violations`, `disciplinary_actions` + seeder master.
6. `DisciplinaryPointService` lengkap (rolling 6 bulan, clean bonus, tiering SP, auto-detect absensi).
7. CRUD pelanggaran + feed + halaman `MyDisciplinaryRecord.vue`.
8. Generator draf SP (PDF, pakai `barryvdh/laravel-dompdf` atau sejenis) + widget Violation Feed.
9. Integrasi retake LMS otomatis + freeze promotion.
- **Value:** Operasional outlet langsung bisa pakai untuk pembinaan.

### 📈 Gelombang 3 — Raport & Reward (4–6 hari)
10. Migration `semester_reports` + `SemesterReportService` (agregasi 3 sumber, bobot 50/30/20).
11. `RewardRecommendationService` (matriks A2x/A-B/C/D/E + cek freeze).
12. Halaman raport admin + karyawan + export PDF.
13. Executive Dashboard 5 widget lengkap + halaman `LmsDashboard/Index.vue`.
14. Command generate raport massal.
- **Value:** Alat keputusan strategis CEO (gaji, grade, PKWTT, PHK).

### 🧪 Testing (paralel tiap gelombang)
- Unit test: `DisciplinaryPointService` (rolling window, bonus, tier) & `SemesterReportService` (bobot, grade).
- Feature test: trigger SP, generate raport, alert kontrak.
- Seeder data dummy: 20 karyawan beragam jabatan/outlet + pelanggaran + kontrak mendekati expired (untuk demo dashboard).

---

## 9. RISIKO & CATATAN TEKNIS

| Risiko | Mitigasi |
|---|---|
| Kalkulasi poin rolling 6 bulan berat jika data besar | Index `(user_id, occurred_at)` + cache poin aktif (invalidate saat violation baru) |
| Double-trigger SP | Unique constraint `user_id + action_type` pada status `active` |
| Selisih definisi "KPI operasional" | Fase 1 pakai `LmsPerformanceAppraisal` sebagai sumber KPI; sambungkan data POS/sales nanti bila ada |
| Timezone & tanggal rolling | Simpan semua tanggal UTC + `occurred_at` datetime (bukan date) |
| PDF SP di shared hosting | Fallback generator HTML-to-print bila dompdf gagal |
| Migration di production | Tabel baru bersifat additive — aman, tanpa ubah struktur tabel existing |

**Keputusan desain penting (perlu konfirmasi saat eksekusi):**
1. SP otomatis murni, atau perlu approval HR sebelum terbit? (rancangan: generator draf → HR approve)
2. Clean record bonus −10: berlaku per 3 bulan kalender atau 3 bulan sejak pelanggaran terakhir?
3. Konversi poin → skor disiplin (0 poin = 100): rumus linear atau per tier?

---

## 10. CHECKLIST ACCEPTANCE

- [ ] HR bisa tambah/edit kontrak PKWT & PKWTT + lihat countdown H-60/H-30.
- [ ] Notifikasi otomatis kontrak mendekati berakhir harian.
- [ ] Matriks kurikulum per jabatan terisi sesuai dokumen rancangan (6 jabatan).
- [ ] HR/SPV bisa catat pelanggaran; poin terakumulasi rolling 6 bulan.
- [ ] Karyawan 3 bulan bersih otomatis −10 poin.
- [ ] Trigger SP otomatis di 15/35/50/70/85 poin dengan draf SP + lock promosi di 35.
- [ ] Pelanggaran "terlambat" terdeteksi otomatis dari absensi.
- [ ] Raport semester ter-generate dengan bobot 50/30/20 + predikat A–E benar.
- [ ] Rekomendasi reward/karir muncul sesuai matriks, freeze promotion berfungsi.
- [ ] Executive dashboard menampilkan 5 widget dengan data real-time.
- [ ] Karyawan bisa melihat kontrak, poin disiplin, dan raport miliknya sendiri.

---

*Disusun otomatis berdasarkan audit codebase SweetHR (Laravel 12 + Inertia Vue 3) & dokumen rancangan HR PT Warung Mas Mbull.*
