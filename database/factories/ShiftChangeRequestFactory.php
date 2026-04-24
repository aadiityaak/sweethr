<?php

namespace Database\Factories;

use App\Models\ShiftChangeRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShiftChangeRequest>
 */
class ShiftChangeRequestFactory extends Factory
{
    protected $model = ShiftChangeRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $originalDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $requestedDate = $this->faker->dateTimeBetween($originalDate, '+2 months');

        return [
            'user_id' => User::where('is_admin', false)->inRandomOrder()->first()->id ?? 1,
            'original_date' => $originalDate->format('Y-m-d'),
            'requested_date' => $requestedDate->format('Y-m-d'),
            'reason' => $this->faker->randomElement([
                'Ada acara keluarga yang tidak bisa dihindari',
                'Keperluan pribadi mendesak',
                'Ada janji dengan dokter',
                'Menghadiri pernikahan kerabat',
                'Liburan bersama keluarga',
                null,
            ]),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'admin_notes' => function (array $attributes) {
                if ($attributes['status'] === 'pending') {
                    return null;
                }

                return $attributes['status'] === 'approved'
                  ? 'Request disetujui setelah review'
                  : 'Request ditolak karena alasan yang tidak valid';
            },
            'requested_at' => $this->faker->dateTimeBetween('-2 weeks', 'now'),
            'reviewed_at' => function (array $attributes) {
                return $attributes['status'] === 'pending'
                  ? null
                  : $this->faker->dateTimeBetween($attributes['requested_at'], 'now');
            },
            'reviewed_by' => function (array $attributes) {
                return $attributes['status'] === 'pending'
                  ? null
                  : User::where('is_admin', true)->inRandomOrder()->first()->id ?? 1;
            },
        ];
    }
}
