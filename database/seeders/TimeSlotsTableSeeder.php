<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TimeSlotsTableSeeder extends Seeder
{
    public function run(): void
    {
        $days = DB::table('days')->get();

        foreach ($days as $day) {
            // Define time slots
            $timeSlots = [
                ['08:00', '08:50', false],
                ['08:55', '09:45', false],
                ['09:50', '10:40', false],
                ['10:45', '11:35', false],
                ['11:40', '12:30', false],
                ['12:30', '13:30', true], // Lunch break
                ['13:30', '14:20', false],
                ['14:25', '15:15', false],
                ['15:20', '16:10', false],
                ['16:15', '17:05', false],
            ];

            foreach ($timeSlots as [$start, $end, $isBreak]) {
                DB::table('time_slots')->insert([
                    'day_id' => $day->id,
                    'start_time' => $start,
                    'end_time' => $end,
                    'is_break' => $isBreak,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
