<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LokasiKantorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\LokasiKantor::create([
            'nama_lokasi' => 'Oobake Bakery Pusat',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius_meter' => 100,
        ]);
    }
}
