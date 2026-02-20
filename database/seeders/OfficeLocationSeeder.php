<?php

namespace Database\Seeders;

use App\Models\OfficeLocation;
use Illuminate\Database\Seeder;

class OfficeLocationSeeder extends Seeder
{
    public function run(): void
    {
        $offices = [
            [
                'name' => 'Head Office Jakarta',
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat, DKI Jakarta',
                'latitude' => -6.208763,
                'longitude' => 106.845596,
                'radius_meters' => 100,
                'timezone' => 'Asia/Jakarta',
                'is_active' => true,
            ],
            [
                'name' => 'Branch Office Surabaya',
                'address' => 'Jl. Pemuda No. 45, Surabaya, Jawa Timur',
                'latitude' => -7.257472,
                'longitude' => 112.752088,
                'radius_meters' => 150,
                'timezone' => 'Asia/Jakarta',
                'is_active' => true,
            ],
            [
                'name' => 'Branch Office Bandung',
                'address' => 'Jl. Asia Afrika No. 67, Bandung, Jawa Barat',
                'latitude' => -6.921831,
                'longitude' => 107.607544,
                'radius_meters' => 80,
                'timezone' => 'Asia/Jakarta',
                'is_active' => true,
            ],
            [
                'name' => 'Remote Office Bali',
                'address' => 'Jl. Sunset Road No. 89, Denpasar, Bali',
                'latitude' => -8.670458,
                'longitude' => 115.212631,
                'radius_meters' => 200,
                'timezone' => 'Asia/Makassar',
                'is_active' => true,
            ],
            [
                'name' => 'WebSweet Studio Klaten',
                'address' => 'Klaten, Jawa Tengah',
                'latitude' => -7.793983712752481,
                'longitude' => 110.65907539269323,
                'radius_meters' => 100,
                'timezone' => 'Asia/Jakarta',
                'is_active' => true,
            ],
        ];

        foreach ($offices as $office) {
            OfficeLocation::updateOrCreate(
                ['name' => $office['name']],
                $office
            );
        }
    }
}
