<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
  body{font-family: DejaVu Sans, sans-serif; font-size:11px; color:#1f2937; margin:0; padding:0;}
  .header{border-bottom:3px solid #1e40af; padding:16px 24px 12px; margin-bottom:18px;}
  .header h1{margin:0; font-size:18px; color:#1e40af;}
  .header p{margin:4px 0 0; font-size:10px; color:#6b7280;}
  .card{border:1px solid #e5e7eb; border-radius:6px; padding:14px 16px; margin:0 24px 14px;}
  .card h2{margin:0 0 8px; font-size:12px; color:#374151; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e5e7eb; padding-bottom:6px;}
  .grid2{width:100%; border-collapse:collapse;}
  .grid2 td{padding:4px 8px 4px 0; vertical-align:top; font-size:11px;}
  .grid2 td.label{color:#6b7280; width:42%;}
  .grid2 td.value{font-weight:700; color:#111827;}
  .score-row{display:flex; gap:10px; margin-top:8px;}
  .score-box{flex:1; border:1px solid #e5e7eb; border-radius:6px; padding:10px; text-align:center;}
  .score-box .num{font-size:22px; font-weight:800;}
  .score-box .lbl{font-size:9px; color:#6b7280; text-transform:uppercase;}
  .badge{display:inline-block; padding:3px 10px; border-radius:999px; font-weight:800; font-size:13px;}
  .badge-A{background:#dcfce7; color:#166534;}
  .badge-B{background:#dbeafe; color:#1e40af;}
  .badge-C{background:#fef9c3; color:#854d0e;}
  .badge-D{background:#ffedd5; color:#9a3412;}
  .badge-E{background:#fee2e2; color:#991b1b;}
  .table{width:100%; border-collapse:collapse; margin-top:8px;}
  .table th{background:#f3f4f6; text-align:left; font-size:10px; color:#374151; padding:7px 8px; border:1px solid #e5e7eb;}
  .table td{padding:7px 8px; border:1px solid #e5e7eb; font-size:10px;}
  .table td.center{text-align:center;}
  .table td.right{text-align:right;}
  .note{margin:0 24px; font-size:9px; color:#6b7280; border-top:1px solid #e5e7eb; padding-top:10px;}
  .footer{margin-top:18px; padding:12px 24px 0; border-top:1px solid #e5e7eb; font-size:9px; color:#9ca3af; text-align:center;}
</style>
</head>
<body>
<div class="header">
  <h1>Raport Kinerja Semester — PT Warung Mas Mbull</h1>
  <p>{{ $semesterLabel }} {{ $report->year }} &middot; Digenerate {{ $report->created_at?->format('d M Y H:i') }} &middot; Status: {{ $report->status === 'published' ? 'Terbit' : 'Draft' }}</p>
</div>

<div class="card">
  <h2>Identitas Karyawan</h2>
  <table class="grid2">
    <tr><td class="label">Nama</td><td class="value">{{ $report->user->name }}</td></tr>
    <tr><td class="label">Jabatan / Outlet</td><td class="value">{{ $report->user->position->title ?? '—' }} &middot; {{ $report->user->department->name ?? '—' }}</td></tr>
    <tr><td class="label">Periode</td><td class="value">{{ $semesterLabel }} {{ $report->year }}</td></tr>
    <tr><td class="label">Skor Akhir / Predikat</td><td class="value">{{ number_format($report->final_score,2) }} — {{ $report->grade }} ({{ $gradeLabel }})</td></tr>
  </table>
  <div style="margin-top:10px; text-align:center;">
    <span class="badge badge-{{ $report->grade }}">{{ $report->grade }}</span>
    <span style="margin-left:8px; font-size:10px; color:#6b7280;">{{ $gradeLabel }}</span>
  </div>
</div>

<div class="card">
  <h2>Rincian Penilaian (KPI 50% · LMS 30% · Disiplin 20%)</h2>
  <table class="table">
    <thead>
      <tr><th>Komponen</th><th>Bobot</th><th class="center">Skor</th><th class="right">Skor Terbobot</th><th>Keterangan</th></tr>
    </thead>
    <tbody>
      <tr>
        <td>KPI / Performance Appraisal</td><td class="center">50%</td><td class="center">{{ number_format($report->kpi_score,1) }}</td>
        <td class="right">{{ number_format($report->kpi_score*0.5,2) }}</td><td>Rata-rata appraisal inti periode</td>
      </tr>
      <tr>
        <td>LMS (Kuis 70% + Tugas 30%)</td><td class="center">30%</td><td class="center">{{ number_format($report->lms_score,1) }}</td>
        <td class="right">{{ number_format($report->lms_score*0.3,2) }}</td><td>Skor kuis & tugas periode</td>
      </tr>
      <tr>
        <td>Disiplin (Kehadiran 60% + Pelanggaran 40%)</td><td class="center">20%</td><td class="center">{{ number_format($report->discipline_score,1) }}</td>
        <td class="right">{{ number_format($report->discipline_score*0.2,2) }}</td><td>{{ $report->total_violation_points }} poin &middot; hadir {{ $report->attendance_rate }}%</td>
      </tr>
      <tr style="background:#f9fafb; font-weight:700;">
        <td colspan="3" style="text-align:right;">Total (Skor Akhir)</td><td class="right">{{ number_format($report->final_score,2) }}</td><td>{{ $report->grade }} — {{ $gradeLabel }}</td>
      </tr>
    </tbody>
  </table>
</div>

<div class="card">
  <h2>Rekomendasi Reward &amp; Karir — Tier {{ $rec['tier'] ?? '—' }}</h2>
  @if($rec)
  <p style="margin:0 0 8px; font-size:11px; color:#374151;"><strong>{{ $rec['label'] ?? '' }}</strong> — {{ $rec['priority'] ?? '' }}</p>
  <table class="table">
    <tr><th style="width:38%;">Item</th><th>Status</th></tr>
    <tr><td>Kenaikan Gaji</td><td>{{ !empty($rec['salary_raise']) ? 'Direkomendasikan' : 'Tidak ada' }}</td></tr>
    <tr><td>Bonus</td><td>{{ $rec['bonus'] ?? 'Tidak ada' }}</td></tr>
    <tr><td>Rekomendasi Promosi</td><td>{{ !empty($rec['promotion_recommended']) ? 'Ya' : 'Tidak' }}</td></tr>
    <tr><td>Kelayakan PKWTT</td><td>{{ !empty($rec['pkwtt_eligible']) ? 'Memenuhi syarat' : 'Belum' }}</td></tr>
  </table>
  @if(!empty($rec['pip_required']))<p style="margin:8px 0 0; color:#9a3412; font-weight:700;">⚠ Wajib mengikuti Program PIP (Performance Improvement Plan) — 30 hari.</p>@endif
  @if(!empty($rec['exit_review']))<p style="margin:8px 0 0; color:#991b1b; font-weight:700;">⚠ Perlu evaluasi kelanjutan kerja (exit review).</p>@endif
  @if(!empty($rec['blocked_by_freeze']))<p style="margin:8px 0 0; color:#991b1b; font-weight:700;">⛔ Promosi diblokir: freeze promotion aktif (SP 1) — {{ $rec['warnings'][0] ?? '' }}</p>@endif
  <p style="margin:8px 0 0; font-size:9px; color:#6b7280;">Predikat semester sebelumnya: {{ $rec['previous_grade'] ?? '—' }} @if(!empty($rec['consecutive_a'])) &middot; A beruntun @endif</p>
  @else
  <p style="font-size:11px; color:#6b7280;">Rekomendasi belum tersedia.</p>
  @endif
</div>

@if($report->notes)
<div class="card">
  <h2>Catatan HR</h2>
  <p style="margin:0; white-space:pre-line; font-size:11px;">{{ $report->notes }}</p>
</div>
@endif

<p class="note">Dokumen ini digenerate otomatis oleh SweetHR — Executive HR Dashboard PT Warung Mas Mbull. Bobot penilaian: KPI 50% + LMS 30% + Disiplin 20%. Predikat: A ≥90, B ≥80, C ≥70, D ≥60, E &lt;60.</p>
<div class="footer">
  Dicetak {{ now()->format('d M Y H:i') }} WIB &middot; SweetHR v1 &middot; Halaman 1/1
</div>
</body>
</html>
