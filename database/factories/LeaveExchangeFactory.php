<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LeaveRequest>
 */
class LeaveExchangeFactory extends Factory
{
  protected $model = LeaveRequest::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $leaveTypes = LeaveType::whereIn('code', ['ANNUAL', 'PERSONAL'])->pluck('id')->toArray();
    $employees = User::where('is_admin', false)->where('employment_status', 'active')->pluck('id')->toArray();
    $admin = User::where('is_admin', true)->first();

    $startDate = Carbon::now()->addDays(rand(5, 60));
    $endDate = $startDate->copy()->addDays(rand(1, 7));
    $totalDays = $startDate->diffInDays($endDate) + 1;

    $statusOptions = ['pending', 'approved', 'rejected'];
    $status = fake()->randomElement($statusOptions);

    $exchangeReasons = [
      'Tukar libur untuk menghadiri acara keluarga di luar kota',
      'Tukar libur karena ada keperluan mendadak yang tidak bisa dihindari',
      'Tukar libur untuk mengurus dokumen penting di instansi pemerintah',
      'Tukar libur untuk menghadiri acara pernikahan saudara',
      'Tukar libur untuk liburan keluarga yang sudah direncanakan',
      'Tukar libur untuk mengantar orang tua ke rumah sakit',
      'Tukar libur untuk menghadiri wisuda anak di universitas',
      'Tukar libur untuk rapat keluarga besar di kampung halaman',
      'Tukar libur untuk seminar wajib di kantor pusat',
      'Tukar libur untuk parent meeting di sekolah anak',
    ];

    $data = [
      'user_id' => fake()->randomElement($employees),
      'leave_type_id' => fake()->randomElement($leaveTypes),
      'start_date' => $startDate,
      'end_date' => $endDate,
      'total_days' => $totalDays,
      'reason' => fake()->randomElement($exchangeReasons) . '. ' . fake()->sentence(10),
      'status' => $status,
      'created_at' => Carbon::now()->subDays(rand(1, 10)),
      'updated_at' => Carbon::now()->subDays(rand(0, 5)),
    ];

    if ($status === 'approved' && $admin) {
      $data['approved_by'] = $admin->id;
      $data['approved_at'] = Carbon::now()->subDays(rand(1, 5));
      $data['admin_notes'] = fake()->randomElement([
        'Approved. Semoga keperluan Anda berjalan lancar.',
        'Approved. Silakan melaksanakan tukar libur dengan baik.',
        'Approved. Jangan lupa menyelesaikan pekerjaan sebelumnya.',
        'Approved. Semoga acara Anda sukses.',
      ]);
    } elseif ($status === 'rejected' && $admin) {
      $data['approved_by'] = $admin->id;
      $data['approved_at'] = Carbon::now()->subDays(rand(1, 5));
      $data['rejection_reason'] = fake()->randomElement([
        'Maaf, tukar libur tidak dapat disetujui karena kebutuhan operasional.',
        'Mohon maaf, periode ini sedang banyak deadline project.',
        'Tukar libur untuk alasan ini tidak dapat disetujui.',
        'Silakan ajukan tukar libur di waktu yang lebih sesuai.',
      ]);
    }

    return $data;
  }

  /**
   * Indicate that the leave exchange is pending.
   */
  public function pending(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => 'pending',
      'approved_by' => null,
      'approved_at' => null,
      'rejection_reason' => null,
    ]);
  }

  /**
   * Indicate that the leave exchange is approved.
   */
  public function approved(): static
  {
    $admin = User::where('is_admin', true)->first();
    return $this->state(fn(array $attributes) => [
      'status' => 'approved',
      'approved_by' => $admin?->id,
      'approved_at' => Carbon::now()->subDays(rand(1, 5)),
      'admin_notes' => fake()->randomElement([
        'Approved. Semoga keperluan Anda berjalan lancar.',
        'Approved. Silakan melaksanakan tukar libur dengan baik.',
      ]),
      'rejection_reason' => null,
    ]);
  }

  /**
   * Indicate that the leave exchange is rejected.
   */
  public function rejected(): static
  {
    $admin = User::where('is_admin', true)->first();
    return $this->state(fn(array $attributes) => [
      'status' => 'rejected',
      'approved_by' => $admin?->id,
      'approved_at' => Carbon::now()->subDays(rand(1, 5)),
      'rejection_reason' => fake()->randomElement([
        'Maaf, tukar libur tidak dapat disetujui karena kebutuhan operasional.',
        'Mohon maaf, periode ini sedang banyak deadline project.',
      ]),
    ]);
  }

  /**
   * Indicate that the leave exchange is for upcoming dates.
   */
  public function upcoming(): static
  {
    $startDate = Carbon::now()->addDays(rand(10, 90));
    $endDate = $startDate->copy()->addDays(rand(1, 7));

    return $this->state(fn(array $attributes) => [
      'start_date' => $startDate,
      'end_date' => $endDate,
      'total_days' => $startDate->diffInDays($endDate) + 1,
    ]);
  }

  /**
   * Indicate that the leave exchange is for past dates.
   */
  public function past(): static
  {
    $startDate = Carbon::now()->subDays(rand(30, 90));
    $endDate = $startDate->copy()->addDays(rand(1, 7));

    return $this->state(fn(array $attributes) => [
      'start_date' => $startDate,
      'end_date' => $endDate,
      'total_days' => $startDate->diffInDays($endDate) + 1,
      'created_at' => $startDate->copy()->subDays(rand(10, 20)),
      'updated_at' => $startDate->copy()->subDays(rand(5, 15)),
    ]);
  }

  /**
   * Indicate that the leave exchange is for annual leave.
   */
  public function annualLeave(): static
  {
    $annualLeave = LeaveType::where('code', 'ANNUAL')->first();
    return $this->state(fn(array $attributes) => [
      'leave_type_id' => $annualLeave?->id,
    ]);
  }

  /**
   * Indicate that the leave exchange is for personal leave.
   */
  public function personalLeave(): static
  {
    $personalLeave = LeaveType::where('code', 'PERSONAL')->first();
    return $this->state(fn(array $attributes) => [
      'leave_type_id' => $personalLeave?->id,
    ]);
  }
}
