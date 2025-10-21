# 📋 Rencana Fitur Sistem HR Management

Daftar lengkap fitur yang akan dikembangkan untuk sistem HR management.

## ✅ Status Fitur

**Legend:**
- ✅ **Sudah Selesai** - Fitur telah diimplementasi dan berfungsi
- 🚧 **Dalam Pengembangan** - Sedang dikerjakan
- ⏳ **Direncanakan** - Belum dikerjakan, masuk roadmap
- 🔄 **Perlu Update** - Sudah ada tapi perlu perbaikan/enhancement

---

## 📱 Presensi & Kehadiran

| No | Fitur | Status | Estimasi | Keterangan |
|----|-------|--------|----------|------------|
| 1 | Presensi Mandiri dari Mobile Apps | ✅ | 5 hari | Face recognition + GPS location |
| 2 | Presensi Masuk, Istirahat, Keluar, dan Lembur | ✅ | 3 hari | Check-in/out dengan validasi lokasi |
| 3 | Jadwal Kerja Harian | ✅ | 2 hari | Work shifts management |
| 4 | Jadwal Kerja Lembur | ⏳ | 3 hari | Overtime scheduling |
| 5 | Tukar Jadwal Shift | ⏳ | 4 hari | Shift swap requests |
| 6 | Informasi Lokasi Presensi | ✅ | 2 hari | Office locations dengan GPS radius |
| 7 | Pelacoran Teks Rencana Kerja | ⏳ | 2 hari | Work plan reporting |
| 8 | Swafoto | ✅ | 1 hari | Face photo capture untuk attendance |
| 9 | Pengingatir Jadwal Harian | ⏳ | 2 hari | Daily schedule reminders |
| 10 | Pengingatir Jadwal Lembur | ⏳ | 1 hari | Overtime reminders |
| 11 | Pengingatir Jadwal Libur | ⏳ | 1 hari | Holiday reminders |
| 12 | Presensi Ganti Izin Jam | ⏳ | 3 hari | Time-off replacement |
| 13 | **Pengumuman Perusahaan** | ✅ | 2 hari | **Banner carousel dengan modal detail** |
| 14 | Permohonan Izin Hari dan Jam | ⏳ | 4 hari | Time-off requests |
| 15 | Permohonan Cuti Tahunan | ✅ | 3 hari | Annual leave requests |
| 16 | Kalender Libur | ⏳ | 3 hari | Company holiday calendar |
| 17 | Webhook Presensi | ⏳ | 4 hari | API webhooks untuk integrasi |
| 18 | Notifikasi Presensi via Slack/Telegram | ⏳ | 5 hari | External notifications |
| 19 | Pelaporan File Rencana Kerja | ⏳ | 2 hari | Work plan file uploads |
| 20 | **Face Recognition** | ✅ | 6 hari | **Verifikasi wajah real-time** |
| 21 | Liveness Detection | ⏳ | 4 hari | Anti-spoofing detection |
| 22 | Perangkat/Device presensi | ⏳ | 3 hari | Device management |
| 23 | Lokasi Presensi | ✅ | 2 hari | GPS-based location validation |
| 24 | Fake/mock GPS | ⏳ | 5 hari | GPS spoofing detection |
| 25 | Pengaturan Akurasi Validasi Presensi | ⏳ | 2 hari | Accuracy configuration |
| 26 | Pengaturan Realtime/Background Validasi | ⏳ | 3 hari | Validation settings |

---

## 👥 Manajemen Karyawan

| No | Fitur | Status | Estimasi | Keterangan |
|----|-------|--------|----------|------------|
| 27 | Manajemen Karyawan | ✅ | 4 hari | CRUD karyawan dengan role-based access |
| 28 | **Manajemen Dokumen Karyawan** | ⏳ | 6 hari | **🔥 HIGH PRIORITY - Document management system** |
| 29 | Kepesertaan BPJS | ⏳ | 3 hari | BPJS membership tracking |
| 30 | Pengingatir Masa Berakhir Dokumen Karyawan | ⏳ | 3 hari | Document expiry notifications |
| 31 | Manajemen Jadwal Hari Kerja & Shift | ✅ | 3 hari | Work schedule management |
| 32 | Kalender Perusahaan | ⏳ | 4 hari | Company calendar |
| 33 | Unduh Kalender Tahunan | ⏳ | 2 hari | Annual calendar export |
| 34 | Manajemen Denda Terlambat | ⏳ | 3 hari | Late penalty management |
| 35 | Pembaruan Cuti Tahunan/Khusus Otomatis | ⏳ | 2 hari | Automatic leave balance updates |
| 36 | Review Lembur, Izin dan Cuti | ⏳ | 4 hari | Overtime/leave approval workflow |
| 37 | Notifikasi Admin via Slack/Telegram | ⏳ | 3 hari | Admin notifications |
| 38 | Supervisor Divisi | ⏳ | 2 hari | Division supervisor roles |
| 39 | Supervisi Divisi | ⏳ | 3 hari | Division supervision |
| 40 | Pengingatir Ulang Tahun Karyawan | ⏳ | 1 hari | Birthday reminders |

---

## 💰 Payroll & Penggajian

| No | Fitur | Status | Estimasi | Keterangan |
|----|-------|--------|----------|------------|
| 41 | **Payroll Cut-Off Period** | ✅ | 6 hari | **Periode gaji kustom (26 Sep - 25 Okt 2025)** |
| 42 | Payroll Generation & Management | ✅ | 4 hari | Generate payroll dengan perhitungan otomatis |
| 43 | Slip Gaji Karyawan | ✅ | 2 hari | Employee payroll slip dengan detail lengkap |
| 44 | Perhitungan Lembur | ✅ | 3 hari | Overtime calculation dengan rate berbeda |
| 45 | Potongan Gaji (Late/Absent) | ✅ | 2 hari | Automatic deductions untuk keterlambatan |
| 46 | Pengaturan Gaji & Tunjangan | ✅ | 3 hari | Salary settings dengan allowances |
| 47 | Payroll Reports | ⏳ | 3 hari | Laporan payroll per periode/employee |
| 48 | Payroll Export (PDF/Excel) | ⏳ | 2 hari | Export slip gaji dan laporan |
| 49 | Bank Integration | ⏳ | 5 hari | Transfer gaji otomatis ke bank |
| 50 | Pajak Penghasilan (PPh 21) | ⏳ | 4 hari | Perhitungan pajak otomatis |

---

## 📊 Laporan & Analitik

| No | Fitur | Status | Estimasi | Keterangan |
|----|-------|--------|----------|------------|
| 51 | Statistik Performa Karyawan | ⏳ | 5 hari | Performance analytics |
| 52 | Laporan Catatan Kerja Karyawan | ⏳ | 3 hari | Work log reports |
| 53 | Laporan Izin dan Cuti | ✅ | 2 hari | Leave reports (basic) |
| 54 | Ringkasan Kehadiran | ✅ | 2 hari | Attendance summary dashboard |
| 55 | Rekap Tukar Jadwal Shift | ⏳ | 2 hari | Shift swap reports |

---

## 📱 Mobile & PWA

| No | Fitur | Status | Estimasi | Keterangan |
|----|-------|--------|----------|------------|
| 56 | **PWA Implementation** | ⏳ | 5 hari | **🔥 HIGH PRIORITY - Service workers, offline, install prompt** |
| 57 | **Mobile UI Optimization** | ⏳ | 4 hari | **🔥 HIGH PRIORITY - Touch-friendly interface** |
| 58 | **Push Notifications** | ⏳ | 3 hari | **🔥 HIGH PRIORITY - Real-time notifications** |
| 59 | Offline Mode | ⏳ | 4 hari | Offline functionality untuk presensi |
| 60 | App Icon & Splash Screen | ⏳ | 1 hari | PWA branding |

---

## 👤 Employee Self-Service

| No | Fitur | Status | Estimasi | Keterangan |
|----|-------|--------|----------|------------|
| 61 | **Employee Directory** | ⏳ | 4 hari | **🟡 MEDIUM PRIORITY - Contact directory & org chart** |
| 62 | **Performance Reviews** | ⏳ | 8 hari | **🟡 MEDIUM PRIORITY - Evaluation system** |
| 63 | **Training Management** | ⏳ | 6 hari | **🟡 MEDIUM PRIORITY - Training tracking** |
| 64 | **Asset Management** | ⏳ | 5 hari | **🟢 LOW PRIORITY - Company equipment tracking** |
| 65 | **Expense Claims** | ⏳ | 5 hari | **🟡 MEDIUM PRIORITY - Reimbursement system** |
| 66 | Time Tracking | ⏳ | 4 hari | Project time logging |
| 67 | Employee Self-Update | ⏳ | 3 hari | Profile & document updates |

---

## 📊 Advanced Analytics

| No | Fitur | Status | Estimasi | Keterangan |
|----|-------|--------|----------|------------|
| 68 | **HR Dashboard Analytics** | ⏳ | 6 hari | **🟡 MEDIUM PRIORITY - Advanced reporting** |
| 69 | **Attendance Analytics** | ⏳ | 4 hari | **🟡 MEDIUM PRIORITY - Trend analysis** |
| 70 | Export Reports (PDF/Excel) | ⏳ | 3 hari | Report export functionality |
| 71 | Custom Report Builder | ⏳ | 7 hari | Dynamic report creation |

---

## 🔔 Notifikasi & Integrasi

| No | Fitur | Status | Estimasi | Keterangan |
|----|-------|--------|----------|------------|
| 72 | Notifikasi Presensi via Slack | ⏳ | 3 hari | Slack integration |
| 73 | Notifikasi Presensi via Telegram | ⏳ | 3 hari | Telegram bot integration |
| 74 | Webhook Presensi | ⏳ | 4 hari | REST API webhooks |
| 75 | Open API | ⏳ | 5 hari | Public API documentation |

---

## 📈 Summary Progress

**Total Fitur:** 75
**✅ Sudah Selesai:** 14 fitur (19%) - **39 hari kerja**
**🚧 Dalam Pengembangan:** 0 fitur (0%) - **0 hari**
**⏳ Direncanakan:** 61 fitur (81%) - **254 hari kerja**

### ⏱️ Estimasi Waktu Total
- **Fitur Selesai**: 39 hari kerja (~8 minggu)
- **Fitur Tertunda**: 254 hari kerja (~51 minggu)
- **Total Estimasi**: **293 hari kerja (~59 minggu / 14 bulan)**

### 📊 Breakdown Estimasi per Kategori
| Kategori | Selesai | Tertenda | Total Hari |
|----------|---------|----------|-------------|
| **📱 Presensi & Kehadiran** | 8 fitur (22 hari) | 18 fitur (58 hari) | **80 hari** |
| **👥 Manajemen Karyawan** | 2 fitur (7 hari) | 12 fitur (36 hari) | **43 hari** |
| **💰 Payroll & Penggajian** | 6 fitur (20 hari) | 4 fitur (18 hari) | **38 hari** |
| **📊 Laporan & Analitik** | 2 fitur (4 hari) | 3 fitur (14 hari) | **18 hari** |
| **📱 Mobile & PWA** | 0 fitur (0 hari) | 5 fitur (17 hari) | **17 hari** |
| **👤 Employee Self-Service** | 0 fitur (0 hari) | 7 fitur (35 hari) | **35 hari** |
| **📊 Advanced Analytics** | 0 fitur (0 hari) | 4 fitur (20 hari) | **20 hari** |
| **🔔 Notifikasi & Integrasi** | 0 fitur (0 hari) | 4 fitur (15 hari) | **15 hari** |

### 🎯 Fitur Prioritas Tinggi - 10 Fitur Utama

#### 🔥 **HIGH PRIORITY** (Sprint 1-2: 25 hari)
1. **Manajemen Dokumen Karyawan** (6 hari) - Upload, kelola, monitoring kontrak
2. **PWA Implementation** (5 hari) - Service workers, offline, install prompt
3. **Mobile UI Optimization** (4 hari) - Touch-friendly interface
4. **Push Notifications** (3 hari) - Real-time notifications
5. **Employee Directory** (4 hari) - Contact directory & org chart
6. **Payroll Reports** (3 hari) - Laporan payroll per periode/employee

#### 🟡 **MEDIUM PRIORITY** (Sprint 3-4: 27 hari)
7. **HR Dashboard Analytics** (6 hari) - Advanced reporting dashboard
8. **Performance Reviews** (8 hari) - Employee evaluation system
9. **Training Management** (6 hari) - Training tracking & certificates
10. **Expense Claims** (5 hari) - Reimbursement system
11. **Payroll Export (PDF/Excel)** (2 hari) - Export slip gaji dan laporan

#### 🟢 **LOW PRIORITY** (Sprint 5: 8 hari)
12. **Asset Management** (5 hari) - Company equipment tracking
13. **Bank Integration** (5 hari) - Transfer gaji otomatis ke bank

**Total 10 Fitur Utama: 60 hari kerja (~12 minggu)**

### 📝 Catatan Implementasi

**Fitur Existing:**
- **Face Recognition:** Menggunakan face-api.js dengan optimasi mobile
- **GPS Validation:** Radius-based dengan multiple office locations
- **Pengumuman:** Banner carousel dengan rich content editor
- **Leave Management:** Approval workflow dengan email notifications
- **Dashboard:** Real-time stats dengan charts dan analytics
- **Payroll System:** Complete payroll management dengan cut-off period support
- **Payroll Cut-Off:** Periode gaji kustom (26 Sep - 25 Okt 2025)
- **Salary Calculation:** Automatic overtime dan deductions
- **Employee Payslips:** Digital slip gaji dengan detail lengkap

**Fitur Baru - 10 Prioritas:**
- **Dokumen Management:** File upload, versioning, expiry tracking
- **PWA:** Manifest, service workers, offline caching, install prompt
- **Mobile UI:** Touch gestures, responsive design, bottom navigation
- **Push Notifications:** FCM integration, real-time alerts
- **Employee Directory:** Org chart, contact search, team structure
- **Analytics Dashboard:** Charts.js, KPI widgets, export functionality
- **Performance Reviews:** Rating system, goal tracking, feedback loops

---

---

## 💡 Metodologi Estimasi

**Basis Estimasi:**
- Fitur **Simple** (1-2 hari): UI basic, CRUD sederhana, tanpa integrasi kompleks
- Fitur **Medium** (3-4 hari): Logic bisnis menengah, beberapa integrasi, validasi khusus
- Fitur **Complex** (5-6 hari): Integrasi eksternal, algoritma kompleks, high-performance requirements

**Faktor yang Mempengaruhi:**
- ✅ Testing & debugging included
- ✅ Documentation & code review
- ⚠️ Tidak termasuk: research ekstensif, perubahan requirement, bugs major

---

*Last Updated: 21 October 2025*
*Version: 1.3.0 - Added Payroll Category with Cut-Off Period Feature*