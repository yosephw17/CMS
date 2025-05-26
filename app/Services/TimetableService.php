<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class TimetableService
{
    public function generateSlots(
        string $startTime,
        string $endTime,
        int $slotDuration,
        string $lunchStart,
        string $lunchEnd,
        int $gapDuration = 5
    ): Collection {
        if ($slotDuration <= 0) {
            throw new InvalidArgumentException('Slot duration must be positive.');
        }
        if ($gapDuration < 0) {
            throw new InvalidArgumentException('Gap duration cannot be negative.');
        }

        $current = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        $lunchStart = Carbon::parse($lunchStart);
        $lunchEnd = Carbon::parse($lunchEnd);

        if ($current >= $end) {
            throw new InvalidArgumentException('Start time must be before end time.');
        }
        if ($lunchStart >= $lunchEnd) {
            throw new InvalidArgumentException('Lunch start time must be before lunch end time.');
        }
        if ($lunchStart < $current || $lunchEnd > $end) {
            throw new InvalidArgumentException('Lunch period must be within start and end times.');
        }

        $slots = collect();
        $iterationCount = 0; 
        $maxIterations = 10000; 

        while ($current < $end) {
            if ($iterationCount++ >= $maxIterations) {
                throw new RuntimeException('Maximum iterations exceeded. Possible infinite loop.');
            }

            if ($current >= $lunchStart && $current < $lunchEnd) {
                $slots->push([
                    'start_time' => $lunchStart->format('H:i'),
                    'end_time' => $lunchEnd->format('H:i'),
                    'is_break' => true,
                    'description' => 'Lunch Break',
                    'duration_minutes' => $lunchEnd->diffInMinutes($lunchStart)
                ]);
                $current = $lunchEnd->copy(); // Use copy to avoid mutating
                continue;
            }

            $slotEnd = $current->copy()->addMinutes($slotDuration);

            // Adjust if slot would go into lunch
            if ($slotEnd > $lunchStart && $current < $lunchStart) {
                $slotEnd = $lunchStart->copy();
            }

            // Don't go past end time
            if ($slotEnd > $end) {
                $slotEnd = $end->copy();
            }

            // Determine if gap is needed
            $gapAfter = true;
            if ($slotEnd == $lunchStart || $current == $lunchEnd || $slotEnd == $end) {
                $gapAfter = false;
            }

            $duration = $slotEnd->diffInMinutes($current);
            if ($duration > 0) {
                $slots->push([
                    'start_time' => $current->format('H:i'),
                    'end_time' => $slotEnd->format('H:i'),
                    'is_break' => false,
                    'description' => 'Class Slot',
                    'duration_minutes' => $duration,
                    'has_gap_after' => $gapAfter
                ]);
            }

            $current = $gapAfter ? $slotEnd->copy()->addMinutes($gapDuration) : $slotEnd->copy();
        }

        return $slots;
    }
}