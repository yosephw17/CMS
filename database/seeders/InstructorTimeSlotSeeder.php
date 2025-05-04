<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstructorTimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        $totalSlots = 60; // time_slot_id from 1 to 60
        $instructors = range(1, 10);

        foreach ($instructors as $instructorId) {
            // Number of unavailable slot pairs to assign (e.g. 3 pairs per instructor)
            $unavailablePairs = rand(3, 5);
            $usedSlots = [];

            for ($i = 0; $i < $unavailablePairs; $i++) {
                do {
                    $startSlot = rand(1, $totalSlots - 1); // ensure we have a next one
                } while (in_array($startSlot, $usedSlots) || in_array($startSlot + 1, $usedSlots));

                $usedSlots[] = $startSlot;
                $usedSlots[] = $startSlot + 1;

                DB::table('instructor_time_slot')->insert([
                    [
                        'instructor_id' => $instructorId,
                        'time_slot_id' => $startSlot,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'instructor_id' => $instructorId,
                        'time_slot_id' => $startSlot + 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]);
            }
        }
    }
}
