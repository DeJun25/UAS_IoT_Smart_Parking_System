<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParkingSlot;
use Carbon\Carbon;

class ParkingSlotSeeder extends Seeder
{
    public function run()
    {
        // Tanggal mulai
        $startDate = Carbon::createFromDate(2023, 6, 1);  // Ganti dengan tanggal yang diinginkan

        // Loop selama 14 hari
        for ($i = 0; $i < 14; $i++) {
            $currentDate = $startDate->copy()->addDays($i);

            // Mengisi data untuk Slot 1, 2, dan 3
            ParkingSlot::create(['slot_number' => 1, 'date' => $currentDate, 'status' => rand(0, 1)]);
            ParkingSlot::create(['slot_number' => 2, 'date' => $currentDate, 'status' => rand(0, 1)]);
            ParkingSlot::create(['slot_number' => 3, 'date' => $currentDate, 'status' => rand(0, 1)]);
        }
    }
}
