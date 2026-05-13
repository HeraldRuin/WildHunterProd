@php
    /** @var \Modules\Booking\Models\Booking $booking */
    /** @var \App\User $hunter */
@endphp

<p>Здравствуйте, {{ $hunter->first_name ?? $hunter->name ?? $hunter->email }}!</p>

<p>
    Таймер сбора охотников по бронированию №{{ $booking->booking_number }} завершён.
</p>

@if(!empty($booking->service))
    <p>
        Объект бронирования: <strong>{{ $booking->service->title ?? '' }}</strong>
    </p>
@endif

<p>
    Пожалуйста, зайдите в личный кабинет и примите решение по этому сбору.
</p>

<p>С уважением,<br>Сервис бронирования</p>

