<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PayrollPeriod>
 */
class PayrollPeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->sentence(3),
            'cut_off_day' => $this->faker->numberBetween(1, 28),
            'start_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'end_date' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'description' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Create a period with specific cut-off day
     */
    public function withCutOff(int $day = 26): static
    {
        return $this->state(fn (array $attributes) => [
            'cut_off_day' => $day,
        ]);
    }

    /**
     * Create an active period
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create a period for current month with cut-off
     */
    public function currentPeriod(int $cutOffDay = 26): static
    {
        $now = now();
        $startDate = $now->day >= $cutOffDay
            ? $now->copy()->startOfMonth()->day($cutOffDay)
            : $now->copy()->subMonth()->startOfMonth()->day($cutOffDay);

        $endDate = $startDate->copy()->addMonth()->subDay();

        return $this->state(fn (array $attributes) => [
            'cut_off_day' => $cutOffDay,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'name' => $startDate->format('F Y').' - '.$endDate->format('F Y'),
            'description' => 'Current payroll period: '.$startDate->format('d M').' - '.$endDate->format('d M Y'),
            'is_active' => true,
        ]);
    }
}
