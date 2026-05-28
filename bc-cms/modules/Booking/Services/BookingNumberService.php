<?php

namespace Modules\Booking\Services;

use Illuminate\Support\Facades\DB;
use Modules\Booking\Models\BookingCounter;

class BookingNumberService
{
    public function generate(int $hotelId ): int
    {
        return DB::transaction(function () use ($hotelId) {

            $counter = BookingCounter::where('hotel_id', $hotelId)
                ->lockForUpdate()
                ->first();

            if (!$counter) {

                $counter = BookingCounter::create([
                    'hotel_id' => $hotelId,
                    'last_number' => 0,
                ]);
            }

            $counter->increment('last_number');

            return $counter->fresh()->last_number;
        });
    }
}
