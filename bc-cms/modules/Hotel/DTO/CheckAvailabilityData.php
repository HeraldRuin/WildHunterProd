<?php

namespace Modules\Hotel\DTO;

use Modules\Hotel\Requests\CheckAvailabilityRequest;

class CheckAvailabilityData
{
    public function __construct(
        public int $hotelId,
        public string $startDate,
        public string $endDate,
        public int $adults,
        public int $children = 0,
        public bool $firstLoad = false,
    ) {}

    public static function fromRequest(CheckAvailabilityRequest $request): self
    {
        return new self(
            hotelId: $request->integer('hotel_id'),
            startDate: $request->string('start_date'),
            endDate: $request->string('end_date'),
            adults: $request->integer('adults'),
            children: $request->integer('children', 0),
            firstLoad: $request->boolean('firstLoad'),
        );
    }

    public function toFilters(): array
    {
        return [
            'hotel_id'   => $this->hotelId,
            'start_date' => $this->startDate,
            'end_date'   => $this->endDate,
            'adults'     => $this->adults,
            'children'   => $this->children,
            'firstLoad'  => $this->firstLoad,
        ];
    }
}
