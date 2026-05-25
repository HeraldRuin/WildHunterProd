<?php

namespace Modules\Hotel\Services;

use Carbon\Carbon;
use Modules\Booking\Models\Booking;
use Modules\Hotel\DTO\RoomCalendarData;
use Modules\Hotel\Models\Hotel;
use Modules\Hotel\Models\HotelRoom;
use Modules\Hotel\Models\HotelRoomDate;

class RoomAvailabilityService
{
    /**
     * @var string
     */
    protected string $roomClass;

    /**
     * @var string
     */
    protected string $roomDateClass;

    public function __construct()
    {
        $this->roomClass = HotelRoom::class;
        $this->roomDateClass = HotelRoomDate::class;
    }

    public function getRoomCalendar($room, RoomCalendarData $data): array
    {
        /** ----------------------------------------
         * 1. Загружаем кастомные даты
         * ---------------------------------------- */
        $rows = $this->roomDateClass::query()
            ->where('target_id', $room->id)
            ->whereBetween('start_date', [
                date('Y-m-d 00:00:00', strtotime($data->start)),
                date('Y-m-d 23:59:59', strtotime($data->end))
            ])
            ->get()
            ->keyBy(fn ($row) => date('Y-m-d', strtotime($row->start_date)));

        /** ----------------------------------------
         * 2. Генерируем ВСЕ дни периода
         * ---------------------------------------- */
        $allDates = [];
        $period = periodDate($data->start, $data->end, false);

        foreach ($period as $dt) {
            $dateKey = $dt->format('Y-m-d');

            $allDates[$dateKey] = [
                'id' => uniqid(),
                'start' => $dateKey,
                'allDay' => true,

                'price' => $room->price,
                'number' => $room->number,
                'active' => 1,
                'extendedProps' => [
                    'max_number' => $room->number,
                ],
            ];

            $priceHtml = $data->forSingle ? format_money($room->price) : format_money_main($room->price);
            $allDates[$dateKey]['title'] = $priceHtml . ' x ' . $room->number;
        }

        /** ----------------------------------------
         * 3. Мержим кастомные даты (НЕ затирая extendedProps)
         * ---------------------------------------- */
        foreach ($rows as $dateKey => $row) {
            $price = $row->price ?: $room->price;
            $number = ($row->number !== null) ? (int)$row->number : $room->number;

            $existing = $allDates[$dateKey];

            $isActive = (int) $row->active;
            $priceChanged = false;
            $numberChanged = false;

            if ($isActive) {
                $priceChanged = $row->price !== null && abs((float)$row->price - (float)$room->price) > 0.01;
                $numberChanged = $row->number !== null && (int)$row->number != (int)$room->number;
            }

            if (!$isActive) {
                $title = __('Blocked');
            } elseif ($number == 0) {
                $title = __('Full Books');
            } else {
                $title = format_money_main($price) . ' x ' . $number;
            }

            $allDates[$dateKey] = array_merge(
                $existing,
                [
                    'price' => $price,
                    'number' => $number,
                    'active' => $isActive,
                    'classNames' => $isActive ? ['available-event'] : ['blocked-event'],
                    'title' => $title,
                ],
                [
                    'extendedProps' => array_merge(
                        $existing['extendedProps'],
                        [
                            'max_number' => $room->number,
                            'price_changed' => $priceChanged,
                            'number_changed' => $numberChanged,
                        ]
                    ),
                ]
            );
        }

        /** ----------------------------------------
         * 4. Учитываем бронирования с учётом дня выезда
         * ---------------------------------------- */
        $bookings = $room->getBookingsInRange(
            $data->start,
            $data->end
        );

        foreach ($bookings as $roomBooking) {
            $booking = Booking::find($roomBooking->booking_id);
            if (!$booking) continue;

            $period = periodDate(
                $roomBooking->start_date,
                $roomBooking->end_date,
                false
            );

            $endDate = Carbon::parse($roomBooking->end_date)->format('Y-m-d');

            foreach ($period as $dt) {
                $dateKey = $dt->format('Y-m-d');
                if (!isset($allDates[$dateKey])) continue;

                $day = &$allDates[$dateKey];

                // Добавляем бронь в массив
                $day['bookings'][] = [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'code' => $booking->code,
                    'status' => $booking->status,
                    'statusName' => $booking->statusName,
                ];

                if ($dateKey !== $endDate) {
                    $bookedRooms = (int)($roomBooking->number ?? 0);
                    $day['occupiedRooms'] = ($day['occupiedRooms'] ?? 0) + $bookedRooms;

                    $baseNumber = $day['extendedProps']['max_number'];
                    $freeRooms = max($baseNumber - ($day['occupiedRooms'] ?? 0), 0);

                    if ($freeRooms <= 0) {
                        $day['active'] = 1;
                        $day['number'] = 0;
                        $day['classNames'] = ['full-book-event'];
                        $day['title'] = __('Full Books');
                    } else {
                        $day['active'] = 1;
                        $day['number'] = $freeRooms;
                        $day['classNames'] = ['available-event'];
                        $day['title'] = format_money_main($day['price']) . ' x ' . $day['number'];
                    }
                } else {
                    $day['classNames'] = ['checkout-day-event'];
                    $day['title'] = format_money_main($day['price']) . ' x ' . $day['number'];
                }
            }
        }

        /** ----------------------------------------
         * 5. HTML для броней
         * ---------------------------------------- */
        foreach ($allDates as &$day) {
            if (empty($day['bookings'])) { continue; }
            $bookingHtml = '<div class="calendar-bookings">';
            foreach ($day['bookings'] as $b) {
                $status = htmlspecialchars($b['status'] ?? '');
                $label = htmlspecialchars($b['statusName'] ?? '');

                $bookingHtml .= '<div class="booking-item booking-status-' . $status . '">'
                    . '<span class="booking-id" data-id="' . (int)$b['id'] . '" data-code="' . e($b['code']) . '">'
                    . 'Б' . htmlspecialchars($b['booking_number']) .
                    '</span>'
                    . '<span class="booking-status">' . $label . '</span>';

                // Проверяем, день выезда ли это для этой брони & Добавляем (Выезд) только рядом с этой бронью
                $isCheckout = $this->isCheckoutDay($b['id'], $day['start']);
                if ($isCheckout) {
                    $bookingHtml .= ' <span class="checkout-label">(В)</span>';
                }
                $bookingHtml .= '</div>';
            }

            $bookingHtml .= '</div>';
            $day['bookings_html'] = $bookingHtml;
        }
        unset($day);

        return array_values($allDates);
    }


    public function getSummaryCalendar($hotelId, RoomCalendarData $data): array
    {
        $rooms = $this->roomClass::query()
            ->where('parent_id', $hotelId)
            ->get();

        $allDates = [];
        $period = periodDate($data->start, $data->end, false);

        foreach ($period as $dt) {
            $dateKey = $dt->format('Y-m-d');
            $allDates[$dateKey] = [
                'id' => uniqid(),
                'start' => $dateKey,
                'allDay' => true,
                'price' => 0,
                'number' => 0,
                'active' => 1,
                'extendedProps' => [
                    'max_number' => 0,
                    'price_changed' => false,
                    'number_changed' => false,
                    'is_summary' => true,
                ],
                'title' => '',
                'bookings' => [],
                'bookings_html' => '',
            ];
        }

        foreach ($rooms as $room) {
            $customDates = $this->roomDateClass::query()
                ->where('target_id', $room->id)
                ->whereBetween('start_date', [
                    date('Y-m-d 00:00:00', strtotime($data->start)),
                    date('Y-m-d 23:59:59', strtotime($data->end))
                ])
                ->get()
                ->keyBy(fn($row) => date('Y-m-d', strtotime($row->start_date)));

            foreach ($period as $dt) {
                $dateKey = $dt->format('Y-m-d');
                $day = &$allDates[$dateKey];
                $price = $room->price;
                $number = $room->number;
                $active = 1;

                if (isset($customDates[$dateKey])) {
                    $row = $customDates[$dateKey];
                    $price = $row->price ?: $price;
                    $number = $row->number !== null ? (int)$row->number : $number;
                    $active = (int)$row->active;
                }

                $day['number'] += $number;
                $day['extendedProps']['max_number'] += $room->number;
                $day['price'] = $day['price'] ?: $price;

                $priceHtml = $data->forSingle ? format_money($day['price']) : format_money_main($day['price']);
                $day['title'] = $priceHtml . ' x ' . $day['number'];

                $roomBookings = $room->getBookingsInRange($data->start, $data->end);
                foreach ($roomBookings as $rb) {
                    $booking = Booking::find($rb->booking_id);
                    if (!$booking) continue;

                    $bookingStart = Carbon::parse($rb->start_date)->format('Y-m-d');
                    $bookingEnd = Carbon::parse($rb->end_date)->format('Y-m-d');

                    if ($dateKey >= $bookingStart && $dateKey <= $bookingEnd) {
                        $day['bookings'][$booking->id] = [
                            'id' => $booking->id,
                            'booking_number' => $booking->booking_number,
                            'code' => $booking->code,
                            'status' => $booking->status,
                            'statusName' => $booking->statusName,
                        ];
                    }
                }
                unset($roomBookings);
            }
            unset($day);
        }

        foreach ($allDates as &$day) {
            if (empty($day['bookings'])) continue;

            $day['bookings'] = array_values($day['bookings']);

            $bookingHtml = '<div class="calendar-bookings">';
            foreach ($day['bookings'] as $b) {
                $code = htmlspecialchars($b['code'] ?? '');

                $bookingHtml .= '<div class="booking-item">'
                    . '<span class="booking-id" data-id="' . (int)$b['id'] . '" data-code="' . $code . '">'
                    . 'Б' . htmlspecialchars($b['booking_number']) .
                    '</span>';

                $isCheckout = $this->isCheckoutDay($b['id'], $day['start']);
                if ($isCheckout) {
                    $bookingHtml .= ' <span class="checkout-label">(В)</span>';
                }

                $bookingHtml .= '</div>';
            }
            $bookingHtml .= '</div>';
            $day['bookings_html'] = $bookingHtml;
        }
        unset($day);

        return array_values($allDates);
    }

    /**
     * Проверяет, является ли данный день днем выезда для брони
     *
     * @param int $bookingId
     * @param string $date Y-m-d
     * @return bool
     */
    protected function isCheckoutDay(int $bookingId, string $date): bool
    {
        $booking = Booking::find($bookingId);
        if (!$booking) return false;

        $endDate = Carbon::parse($booking->end_date)->format('Y-m-d');
        return $endDate === $date;
    }

    public function checkAvailability($request)
    {
        $hotel_id = \request('hotel_id');
        $adults = \request('adults');
        if(\request()->input('firstLoad') == "false") {
            $rules = [
                'hotel_id'   => 'required',
                'start_date' => 'required:date_format:Y-m-d',
                'end_date'   => 'required:date_format:Y-m-d',
                'adults'     => 'required',
            ];
            $validator = \Validator::make(request()->all(), $rules);
            if ($validator->fails()) {
//                return $this->sendError($validator->errors()->all());
            }

//            if(strtotime(\request('end_date')) - strtotime(\request('start_date')) < DAY_IN_SECONDS){
//                return $this->sendError(__("Dates are not valid"));
//            }
            if(strtotime(\request('end_date')) - strtotime(\request('start_date')) > 30*DAY_IN_SECONDS){
//                return $this->sendError(__("Maximum day for booking is 30"));
            }
        }
        $hotel = Hotel::find($hotel_id);
        if(empty($hotel_id) or empty($hotel)){
//            return $this->sendError(__("Hotel not found"));
        }

        if(\request()->input('firstLoad') == "false") {
            $numberDays = abs(strtotime(\request('end_date')) - strtotime(\request('start_date'))) / 86400;
            if(!empty($hotel->min_day_stays) and  $numberDays < $hotel->min_day_stays){
//                return $this->sendError(__("You must to book a minimum of :number days",['number'=>$hotel->min_day_stays]));
            }

            if(!empty($hotel->min_day_before_booking)){
                $minday_before = strtotime("today +".$hotel->min_day_before_booking." days");
                if(  strtotime(\request('start_date')) < $minday_before){
//                    return $this->sendError(__("You must book the service for :number days in advance",["number"=>$hotel->min_day_before_booking]));
                }
            }
        }

        $rooms = $hotel->getRoomsAvailability(request()->input());

        $requestedAdults = (int) $adults;
        $totalCapacity = 0;

        foreach ($rooms as $room) {
            $room_adults = (int) ($room['adults'] ?? 0);
            $numRooms    = (int) ($room['number'] ?? 0);

            $roomCapacity = $room_adults * $numRooms;
            $totalCapacity += $roomCapacity;
        }

        if ($totalCapacity < $requestedAdults) {
            return [
                'data' => [
                    'rooms'  => [],
                    'is_empty' => true,
                ],
            ];
        }
        return [
            'data' => [
                'rooms'  => $rooms,
                'is_empty' => false,
            ],
        ];
    }
}
