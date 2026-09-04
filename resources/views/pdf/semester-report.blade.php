<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
  @page { size: A4 portrait; margin: 9mm 10mm 8mm 10mm; }
  * { box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 8.2px; color: #1f2937; margin: 0; padding: 0; line-height: 1.35; }
  .header-table { width: 100%; border-collapse: collapse; }
  .header-table td { vertical-align: middle; }
  .logo-cell { width: 44px; padding-right: 8px; }
  .logo-cell img { width: 40px; height: 40px; object-fit: contain; border-radius: 6px; }
  .logo-placeholder { width: 40px; height: 40px; background: #B91C1C; border-radius: 6px; text-align: center; line-height: 40px; color: #fff; font-weight: 800; font-size: 9px; }
  .brand-name { font-size: 13px; font-weight: 800; color: #B91C1C; margin: 0; line-height: 1.1; }
  .brand-tagline { font-size: 6.2px; color: #6b7280; text-transform: uppercase; letter-spacing: .5px; margin: 1px 0 1px; }
  .brand-meta { font-size: 6px; color: #9ca3af; margin: 0; line-height: 1.25; }
  .header-rule { height: 2.5px; background: #B91C1C; border: none; margin: 6px 0 7px; }
  .title-bar { background: #B91C1C; color: #fff; text-align: center; padding: 6px 10px; border-radius: 5px; margin-bottom: 7px; }
  .title-bar h1 { margin: 0; font-size: 10.5px; letter-spacing: .35px; text-transform: uppercase; }
  .title-bar p { margin: 2px 0 0; font-size: 6.8px; opacity: .92; }
  .title-bar .status-pill { display: inline-block; margin-left: 5px; padding: 1px 6px; border-radius: 999px; font-size: 6px; font-weight: 700; background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.35); }
  .card { border: 1px solid #e5e7eb; border-radius: 5px; padding: 7px 9px; margin-bottom: 6px; background: #fff; }
  .card h2 { margin: 0 0 5px; font-size: 7px; color: #B91C1C; text-transform: uppercase; letter-spacing: .6px; border-bottom: 1px solid #f3f4f6; padding-bottom: 3px; }
  .grid2 { width: 100%; border-collapse: collapse; }
  .grid2 td { padding: 1.5px 4px 1.5px 0; vertical-align: top; font-size: 8.2px; line-height: 1.3; }
  .grid2 td.label { color: #6b7280; width: 32%; font-size: 7px; }
  .grid2 td.value { font-weight: 700; color: #111827; }
  .grid2 td.colon { width: 8px; color: #9ca3af; text-align: center; }
  .ident-table { width: 100%; border-collapse: collapse; }
  .ident-table td { vertical-align: top; }
  .badge-wrap { width: 78px; text-align: center; border-left: 1px dashed #e5e7eb; padding-left: 7px; }
  .badge { display: inline-block; width: 48px; height: 48px; line-height: 48px; border-radius: 50%; font-weight: 800; font-size: 18px; text-align: center; }
  .badge-A { background: #dcfce7; color: #166534; border: 1.5px solid #86efac; }
  .badge-B { background: #dbeafe; color: #1e40af; border: 1.5px solid #93c5fd; }
  .badge-C { background: #fef9c3; color: #854d0e; border: 1.5px solid #fde68a; }
  .badge-D { background: #ffedd5; color: #9a3412; border: 1.5px solid #fed7aa; }
  .badge-E { background: #fee2e2; color: #991b1b; border: 1.5px solid #fecaca; }
  .badge-caption { font-size: 6px; color: #6b7280; margin-top: 2px; text-transform: uppercase; letter-spacing: .35px; }
  .score-final { font-size: 10px; font-weight: 800; color: #111827; margin-top: 1px; }
  .score-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
  .score-table td { width: 33.33%; border: 1px solid #e5e7eb; padding: 4px 4px; text-align: center; }
  .score-table .num { font-size: 11px; font-weight: 800; color: #111827; line-height: 1; }
  .score-table .lbl { font-size: 5.8px; color: #6b7280; text-transform: uppercase; letter-spacing: .3px; margin-top: 1px; }
  .score-table .w { font-size: 6px; color: #9ca3af; margin-top: 1px; }
  .table { width: 100%; border-collapse: collapse; margin-top: 4px; }
  .table th { background: #B91C1C; color: #fff; text-align: left; font-size: 6.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .25px; padding: 4px 5px; border: 1px solid #B91C1C; }
  .table th.center, .table td.center { text-align: center; }
  .table th.right, .table td.right { text-align: right; }
  .table td { padding: 3.5px 5px; border: 1px solid #e5e7eb; font-size: 7.5px; line-height: 1.3; }
  .table tr.total td { background: #fef2f2; font-weight: 800; color: #7f1d1d; border-color: #fecaca; }
  .alert-box { margin-top: 5px; padding: 4px 7px; border-radius: 4px; font-size: 7px; line-height: 1.35; }
  .alert-warn { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
  .alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
  .alert-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
  .kv-table { width: 100%; border-collapse: collapse; }
  .kv-table td { padding: 3px 5px; border: 1px solid #e5e7eb; font-size: 7.5px; }
  .kv-table td:first-child { width: 36%; color: #6b7280; background: #f9fafb; font-weight: 600; }
  .note { margin-top: 6px; font-size: 6.2px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 5px; line-height: 1.4; }
  .footer { margin-top: 6px; padding-top: 5px; border-top: 1px solid #e5e7eb; font-size: 6px; color: #9ca3af; text-align: center; }
  .sig-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  .sig-table td { width: 50%; text-align: center; font-size: 7.5px; vertical-align: bottom; padding: 0 12px; }
  .sig-space { height: 28px; }
  .sig-line { border-top: 1px solid #111827; margin-top: 2px; padding-top: 2px; font-weight: 700; color: #111827; }
  .sig-role { font-size: 6px; color: #6b7280; }
  .legend { margin-top: 4px; font-size: 6px; color: #9ca3af; }
</style>
</head>
<body>

<table class="header-table">
  <tr>
    <td class="logo-cell">
      @if(!empty($logoDataUri))
        <img src="{{ $logoDataUri }}" alt="Logo">
      @else
        <div class="logo-placeholder">WMB</div>
      @endif
    </td>
    <td>
      <div class="brand-name">{{ $companyName ?? 'PT Warung Mas Mbull' }}</div>
      <div class="brand-tagline">{{ $companyTagline ?? 'Food & Beverage — Human Resource Management System' }}</div>
      <div class="brand-meta">
        {{ $companyAddress ?? '' }}
        @if(!empty($companyPhone) || !empty($companyEmail))
          &middot;
          @if(!empty($companyPhone)) {{ $companyPhone }} @endif
          @if(!empty($companyPhone) && !empty($companyEmail)) &middot; @endif
          @if(!empty($companyEmail)) {{ $companyEmail }} @endif
        @endif
      </div>
    </td>
    <td style="text-align:right; width: 88px;">
      <div style="font-size:6px; color:#9ca3af; text-transform:uppercase; letter-spacing:.45px;">Dok. HR</div>
      <div style="font-size:7px; color:#B91C1C; font-weight:800;">RA-{{ $report->year }}-S{{ $report->semester }}-{{ str_pad($report->id, 4, '0', STR_PAD_LEFT) }}</div>
      <div style="font-size:5.5px; color:#6b7280; margin-top:1px;">{{ now()->format('d/m/Y H:i') }} WIB</div>
    </td>
  </tr>
</table>
<hr class="header-rule">

<div class="title-bar">
  <h1>Raport Kinerja Semester</h1>
  <p>{{ $semesterLabel }} {{ $report->year }} &middot; Digenerate {{ $report->created_at?->format('d M Y H:i') }} WIB <span class="status-pill">{{ $report->status === 'published' ? 'TERBIT' : 'DRAFT' }}</span></p>
</div>

<div class="card">
  <h2>Identitas Karyawan</h2>
  <table class="ident-table">
    <tr>
      <td>
        <table class="grid2">
          <tr><td class="label">Nama</td><td class="colon">:</td><td class="value">{{ $report->user->name }}</td></tr>
          <tr><td class="label">Jabatan</td><td class="colon">:</td><td class="value">{{ $report->user->position->title ?? '—' }}</td></tr>
          <tr><td class="label">Outlet / Departemen</td><td class="colon">:</td><td class="value">{{ $report->user->department->name ?? '—' }}</td></tr>
          <tr><td class="label">Periode</td><td class="colon">:</td><td class="value">{{ $semesterLabel }} {{ $report->year }}</td></tr>
          <tr><td class="label">Generator</td><td class="colon">:</td><td class="value">{{ $report->generator->name ?? 'Sistem' }}</td></tr>
          @if($report->published_at)
          <tr><td class="label">Tanggal Terbit</td><td class="colon">:</td><td class="value">{{ $report->published_at->format('d M Y') }}</td></tr>
          @endif
        </table>
      </td>
      <td class="badge-wrap">
        <div class="badge badge-{{ $report->grade }}">{{ $report->grade }}</div>
        <div class="score-final">{{ number_format($report->final_score, 2) }}</div>
        <div class="badge-caption">{{ $gradeLabel }}</div>
        <div style="margin-top:4px; font-size:5.5px; color:#9ca3af; border-top:1px dashed #e5e7eb; padding-top:3px;">
          Skor Akhir<br><span style="font-size:6px; color:#6b7280;">dari 100</span>
        </div>
      </td>
    </tr>
  </table>
  <table class="score-table">
    <tr>
      <td><div class="num">{{ number_format($report->kpi_score, 1) }}</div><div class="lbl">KPI / Appraisal</div><div class="w">Bobot 50% → {{ number_format($report->kpi_score*0.5, 2) }}</div></td>
      <td><div class="num">{{ number_format($report->lms_score, 1) }}</div><div class="lbl">LMS (Kuis 70% + Tugas 30%)</div><div class="w">Bobot 30% → {{ number_format($report->lms_score*0.3, 2) }}</div></td>
      <td><div class="num">{{ number_format($report->discipline_score, 1) }}</div><div class="lbl">Disiplin (Hadir 60% + Poin 40%)</div><div class="w">Bobot 20% → {{ number_format($report->discipline_score*0.2, 2) }}</div></td>
    </tr>
  </table>
</div>

<div class="card">
  <h2>Rincian Penilaian — Bobot KPI 50% · LMS 30% · Disiplin 20%</h2>
  <table class="table">
    <thead>
      <tr><th style="width:34%;">Komponen</th><th class="center" style="width:10%;">Bobot</th><th class="center" style="width:12%;">Skor</th><th class="right" style="width:16%;">Skor Terbobot</th><th style="width:28%;">Keterangan</th></tr>
    </thead>
    <tbody>
      <tr><td>KPI / Performance Appraisal</td><td class="center">50%</td><td class="center">{{ number_format($report->kpi_score,1) }}</td><td class="right">{{ number_format($report->kpi_score*0.5,2) }}</td><td>Rata-rata appraisal inti periode</td></tr>
      <tr><td>LMS (Kuis 70% + Tugas 30%)</td><td class="center">30%</td><td class="center">{{ number_format($report->lms_score,1) }}</td><td class="right">{{ number_format($report->lms_score*0.3,2) }}</td><td>Skor kuis &amp; tugas periode</td></tr>
      <tr><td>Disiplin (Kehadiran + Pelanggaran)</td><td class="center">20%</td><td class="center">{{ number_format($report->discipline_score,1) }}</td><td class="right">{{ number_format($report->discipline_score*0.2,2) }}</td><td>{{ $report->total_violation_points }} poin &middot; hadir {{ $report->attendance_rate }}% &middot; 40% dari skor poin</td></tr>
      <tr class="total"><td colspan="3" style="text-align:right;">Total — Skor Akhir</td><td class="right">{{ number_format($report->final_score,2) }}</td><td>{{ $report->grade }} — {{ $gradeLabel }}</td></tr>
    </tbody>
  </table>
  <div class="legend">Predikat: <strong style="color:#166534;">A ≥90</strong> &middot; <strong style="color:#1e40af;">B ≥80</strong> &middot; <strong style="color:#854d0e;">C ≥70</strong> &middot; <strong style="color:#9a3412;">D ≥60</strong> &middot; <strong style="color:#991b1b;">E &lt;60</strong> &nbsp;|&nbsp; Skor pelanggaran = linear dari 85 poin (&gt;85 = 0).</div>
</div>

<div class="card">
  <h2>Rekomendasi Reward &amp; Karir — Tier {{ $rec['tier'] ?? '—' }}</h2>
  @if($rec)
  <p style="margin:0 0 5px; font-size:7.5px; color:#374151;"><strong>{{ $rec['label'] ?? '' }}</strong> <span style="color:#6b7280;">— prioritas {{ $rec['priority'] ?? '' }}</span></p>
  <table class="kv-table">
    <tr><td>Kenaikan Gaji</td><td>{{ !empty($rec['salary_raise']) ? 'Direkomendasikan (+10%)' : 'Tidak ada' }}</td></tr>
    <tr><td>Bonus</td><td>{{ $rec['bonus'] ?? 'Tidak ada' }}</td></tr>
    <tr><td>Rekomendasi Promosi</td><td>{{ !empty($rec['promotion_recommended']) ? 'Ya' : 'Tidak' }}</td></tr>
    <tr><td>Kelayakan PKWTT</td><td>{{ !empty($rec['pkwtt_eligible']) ? 'Memenuhi syarat (PKWT → PKWTT)' : 'Belum' }}</td></tr>
    @if(isset($rec['previous_grade']))<tr><td>Predikat Sebelumnya</td><td>{{ $rec['previous_grade'] ?? '—' }} @if(!empty($rec['consecutive_a'])) &middot; A beruntun @endif</td></tr>@endif
  </table>
  @if(!empty($rec['pip_required']))<div class="alert-box alert-warn">⚠ Wajib <strong>Program PIP</strong> — 30 hari. Assignment &amp; evaluasi otomatis di LMS.</div>@endif
  @if(!empty($rec['exit_review']))<div class="alert-box alert-danger">⚠ Perlu <strong>evaluasi kelanjutan kerja</strong> (exit review).</div>@endif
  @if(!empty($rec['blocked_by_freeze']))<div class="alert-box alert-danger">⛔ Promosi diblokir: <strong>freeze (SP 1)</strong> — {{ $rec['warnings'][0] ?? 'SP masih berlaku.' }}</div>@endif
  @if(!empty($rec['warnings']) && empty($rec['blocked_by_freeze']))<div class="alert-box alert-info">ℹ {{ $rec['warnings'][0] }}</div>@endif
  @if(!empty($rec['executed_at']))<div class="alert-box alert-info">✓ Dieksekusi {{ \Carbon\Carbon::parse($rec['executed_at'])->format('d M Y H:i') }} WIB @if(!empty($rec['executed_by'])) &middot; #{{ $rec['executed_by'] }} @endif</div>@endif
  @else
  <p style="font-size:7.5px; color:#6b7280;">Rekomendasi belum tersedia.</p>
  @endif
</div>

@if($report->notes)
<div class="card">
  <h2>Catatan HR</h2>
  <p style="margin:0; white-space:pre-line; font-size:7.5px;">{{ $report->notes }}</p>
</div>
@endif

<table class="sig-table">
  <tr>
    <td><div style="color:#6b7280;">Mengetahui,</div><div class="sig-space"></div><div class="sig-line">HRD Manager</div><div class="sig-role">PT Warung Mas Mbull</div></td>
    <td><div style="color:#6b7280;">Menyetujui,</div><div class="sig-space"></div><div class="sig-line">Direktur</div><div class="sig-role">PT Warung Mas Mbull</div></td>
  </tr>
</table>

<p class="note">Dokumen digenerate otomatis oleh <strong>SweetHR</strong> — Executive HR Dashboard PT Warung Mas Mbull. Bobot: KPI 50% + LMS 30% + Disiplin 20%. Predikat: A ≥90, B ≥80, C ≥70, D ≥60, E &lt;60. Sah tanpa tanda tangan basah bila dicetak dari sistem.</p>
<div class="footer">Dicetak {{ now()->format('d M Y H:i') }} WIB &middot; SweetHR v1 &middot; No. RA-{{ $report->year }}-S{{ $report->semester }}-{{ str_pad($report->id, 4, '0', STR_PAD_LEFT) }} &middot; Halaman 1/1</div>
</body>
</html>
