# 📋 Rencana Fitur SweetHR

Daftar lengkap fitur yang akan dikembangkan untuk sistem HR management SweetHR.

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

## 📊 Laporan & Analitik

| No | Fitur | Status | Estimasi | Keterangan |
|----|-------|--------|----------|------------|
| 42 | Statistik Performa Karyawan | ⏳ | 5 hari | Performance analytics |
| 43 | Laporan Catatan Kerja Karyawan | ⏳ | 3 hari | Work log reports |
| 44 | Laporan Izin dan Cuti | ✅ | 2 hari | Leave reports (basic) |
| 45 | Ringkasan Kehadiran | ✅ | 2 hari | Attendance summary dashboard |
| 46 | Rekap Tukar Jadwal Shift | ⏳ | 2 hari | Shift swap reports |

---

## 📱 Mobile & PWA

| No | Fitur | Status | Estimasi | Keterangan |
|----|-------|--------|----------|------------|
| 51 | **PWA Implementation** | ⏳ | 5 hari | **🔥 HIGH PRIORITY - Service workers, offline, install prompt** |
| 52 | **Mobile UI Optimization** | ⏳ | 4 hari | **🔥 HIGH PRIORITY - Touch-friendly interface** |
| 53 | **Push Notifications** | ⏳ | 3 hari | **🔥 HIGH PRIORITY - Real-time notifications** |
| 54 | Offline Mode | ⏳ | 4 hari | Offline functionality untuk presensi |
| 55 | App Icon & Splash Screen | ⏳ | 1 hari | PWA branding |

---

## 👤 Employee Self-Service

| No | Fitur | Status | Estimasi | Keterangan |
|----|-------|--------|----------|------------|
| 56 | **Employee Directory** | ⏳ | 4 hari | **🟡 MEDIUM PRIORITY - Contact directory & org chart** |
| 57 | **Performance Reviews** | ⏳ | 8 hari | **🟡 MEDIUM PRIORITY - Evaluation system** |
| 58 | **Training Management** | ⏳ | 6 hari | **🟡 MEDIUM PRIORITY - Training tracking** |
| 59 | **Asset Management** | ⏳ | 5 hari | **🟢 LOW PRIORITY - Company equipment tracking** |
| 60 | **Expense Claims** | ⏳ | 5 hari | **🟡 MEDIUM PRIORITY - Reimbursement system** |
| 61 | Time Tracking | ⏳ | 4 hari | Project time logging |
| 62 | Employee Self-Update | ⏳ | 3 hari | Profile & document updates |

---

## 📊 Advanced Analytics

| No | Fitur | Status | Estimasi | Keterangan |
|----|-------|--------|----------|------------|
| 63 | **HR Dashboard Analytics** | ⏳ | 6 hari | **🟡 MEDIUM PRIORITY - Advanced reporting** |
| 64 | **Attendance Analytics** | ⏳ | 4 hari | **🟡 MEDIUM PRIORITY - Trend analysis** |
| 65 | Export Reports (PDF/Excel) | ⏳ | 3 hari | Report export functionality |
| 66 | Custom Report Builder | ⏳ | 7 hari | Dynamic report creation |

---

## 🔔 Notifikasi & Integrasi

| No | Fitur | Status | Estimasi | Keterangan |
|----|-------|--------|----------|------------|
| 67 | Notifikasi Presensi via Slack | ⏳ | 3 hari | Slack integration |
| 68 | Notifikasi Presensi via Telegram | ⏳ | 3 hari | Telegram bot integration |
| 69 | Webhook Presensi | ⏳ | 4 hari | REST API webhooks |
| 70 | Open API | ⏳ | 5 hari | Public API documentation |

---

## 📈 Summary Progress

**Total Fitur:** 70
**✅ Sudah Selesai:** 8 fitur (11%) - **24 hari kerja**
**🚧 Dalam Pengembangan:** 0 fitur (0%) - **0 hari**
**⏳ Direncanakan:** 62 fitur (89%) - **235 hari kerja**

### ⏱️ Estimasi Waktu Total
- **Fitur Selesai**: 24 hari kerja (~5 minggu)
- **Fitur Tertunda**: 235 hari kerja (~47 minggu)
- **Total Estimasi**: **259 hari kerja (~52 minggu / 12 bulan)**

### 📊 Breakdown Estimasi per Kategori
| Kategori | Selesai | Tertenda | Total Hari |
|----------|---------|----------|-------------|
| **📱 Presensi & Kehadiran** | 8 fitur (22 hari) | 18 fitur (58 hari) | **80 hari** |
| **👥 Manajemen Karyawan** | 2 fitur (7 hari) | 12 fitur (36 hari) | **43 hari** |
| **📊 Laporan & Analitik** | 2 fitur (4 hari) | 3 fitur (10 hari) | **14 hari** |
| **📱 Mobile & PWA** | 0 fitur (0 hari) | 5 fitur (17 hari) | **17 hari** |
| **👤 Employee Self-Service** | 0 fitur (0 hari) | 7 fitur (35 hari) | **35 hari** |
| **📊 Advanced Analytics** | 0 fitur (0 hari) | 4 fitur (20 hari) | **20 hari** |
| **🔔 Notifikasi & Integrasi** | 0 fitur (0 hari) | 4 fitur (15 hari) | **15 hari** |

### 🎯 Fitur Prioritas Tinggi - 7 Fitur Utama

#### 🔥 **HIGH PRIORITY** (Sprint 1-2: 20 hari)
1. **Manajemen Dokumen Karyawan** (6 hari) - Upload, kelola, monitoring kontrak
2. **PWA Implementation** (5 hari) - Service workers, offline, install prompt
3. **Mobile UI Optimization** (4 hari) - Touch-friendly interface
4. **Push Notifications** (3 hari) - Real-time notifications
5. **Employee Directory** (4 hari) - Contact directory & org chart

#### 🟡 **MEDIUM PRIORITY** (Sprint 3-4: 22 hari)
6. **HR Dashboard Analytics** (6 hari) - Advanced reporting dashboard
7. **Performance Reviews** (8 hari) - Employee evaluation system
8. **Training Management** (6 hari) - Training tracking & certificates
9. **Expense Claims** (5 hari) - Reimbursement system

#### 🟢 **LOW PRIORITY** (Sprint 5: 5 hari)
10. **Asset Management** (5 hari) - Company equipment tracking

**Total 7 Fitur Utama: 47 hari kerja (~9-10 minggu)**

### 📝 Catatan Implementasi

**Fitur Existing:**
- **Face Recognition:** Menggunakan face-api.js dengan optimasi mobile
- **GPS Validation:** Radius-based dengan multiple office locations
- **Pengumuman:** Banner carousel dengan rich content editor
- **Leave Management:** Approval workflow dengan email notifications
- **Dashboard:** Real-time stats dengan charts dan analytics

**Fitur Baru - 7 Prioritas:**
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

*Last Updated: 23 September 2025*
*Version: 1.2.0 - Added 7 Priority Features & New Categories*