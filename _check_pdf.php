<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$r = App\Models\SemesterReport::with(['user.position','user.department','generator'])->first();
if (!$r) { echo "no report\n"; exit(1); }
$rec = $r->recommendation ?? [];
$sl = $r->semester==='1' ? 'Semester I (Jan-Jun)' : 'Semester II (Jul-Des)';
$gl = App\Models\SemesterReport::gradeLabel($r->grade);
$logo = public_path('icons/logo.jpg');
$ld = is_file($logo) ? 'data:image/jpeg;base64,'.base64_encode(file_get_contents($logo)) : null;
$data = [
  'report'=>$r,'rec'=>$rec,'semesterLabel'=>$sl,'gradeLabel'=>$gl,
  'logoDataUri'=>$ld,
  'companyName'=>App\Models\CompanySetting::get('company_name','PT Warung Mas Mbull'),
  'companyTagline'=>App\Models\CompanySetting::get('company_tagline',''),
  'companyAddress'=>App\Models\CompanySetting::get('company_address',''),
  'companyPhone'=>App\Models\CompanySetting::get('company_phone',''),
  'companyEmail'=>App\Models\CompanySetting::get('company_email',''),
];
$pdf = Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.semester-report', $data)->setPaper('a4','portrait');
$out = $pdf->output();
file_put_contents(storage_path('app/test-raport.pdf'), $out);
$pages = preg_match_all('/\/Type\s*\/Page[^s]/', $out, $m);
echo "pdf ".strlen($out)." bytes pages ".intval($pages)."\n";
echo "saved to ".storage_path('app/test-raport.pdf')."\n";
