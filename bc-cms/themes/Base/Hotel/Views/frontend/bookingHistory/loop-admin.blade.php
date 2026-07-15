<tr>
    <td class="booking-history-type">
        {{ $booking->service ? $booking->booking_number : $booking->booking_number }}
    </td>

    <td class="a-hidden">{{display_date($booking->created_at)}}</td>
    <td>
        <div class="d-flex flex-column gap-1">
           <span
              class="cursor-pointer user-link"
              data-bs-trigger="hover"
              data-bs-html="true"
              data-bs-placement="right"
              data-bs-content="<strong>{{ $booking->creator?->first_name ?? '' }} {{ $booking->creator?->last_name ?? '' }}</strong><br>Email: {{ $booking->creator?->email ?? '' }}<br>Phone: {{ $booking->creator?->phone ?? '' }}"
              @click="{{ $userRole !== 'hunter' && $booking->creator ? "openUserModal({$booking->creator->id}, {$booking->id})" : '' }}">
              {{ $booking->creator ? (!empty($booking->creator->user_name) ? $booking->creator->user_name : $booking->creator->first_name) : 'N/A' }}
           </span>
            @if($booking->status === \Modules\Booking\Models\Booking::PAID)
                <button
                    type="button"
                    class="btn btn-info btn-sm details-btn mt-2"
                    data-bs-toggle="popover"
                    data-bs-trigger="click"
                    data-bs-html="true"
                    data-bs-placement="right"
                    data-bs-content="<strong>{{ $booking->creator?->first_name ?? '' }} {{ $booking->creator?->last_name ?? '' }}</strong><br>Email: {{ $booking->creator?->email ?? '' }}<br>Phone: {{ $booking->creator?->phone ?? '' }}">
                    Контакты
                </button>
            @endif
        </div>
    </td>

    <td class="a-hidden">
        @if($booking->type === 'hotel' && $userRole === \Modules\User\Models\Role::ADMIN)
            <strong>Проживание:</strong>
            <div>
                {{__("CheckIn")}} : {{display_date($booking->start_date)}} <br>
                {{__("Exit")}} : {{display_date($booking->end_date)}} <br>
                {{__("Duration")}} :
                @if($booking->duration_days <= 1)
                    {{__(':count nights',['count'=>$booking->duration_days])}} <br>
                @else
                    {{__(':count nights',['count'=>$booking->duration_days])}} <br>
                @endif

                {{__(':total guest',['count'=>$booking->total_guests])}} <br>
            </div>
        @endif
    </td>
    <td class="{{$booking->status}} a-hidden">
        <div>
            {{$booking->statusName}}
        </div>
    </td>

    <td>
        <div>
            @if(in_array($booking->status, [\Modules\Booking\Models\Booking::FINISHED_PREPAYMENT, \Modules\Booking\Models\Booking::PAID, \Modules\Booking\Models\Booking::COMPLETED]))
                Внесена предоплата: {{ format_money($booking->calculation['prepaid_total']?? 0) }} <br>
                Остаток базе:  {{ format_money($booking->calculation['base_total']?? 0) }} <br>
                Всего: {{ format_money($booking->calculation['total']?? 0) }}
            @endif
        </div>
    </td>

    <td>
        @if($userRole === \Modules\User\Models\Role::ADMIN && $booking->status === \Modules\Booking\Models\Booking::PROCESSING)
            <button
                type="button"
                class="btn btn-success btn-sm mt-2"
                @click="openConfirmBookingModal({{ $booking->id }}, $event)">
                {{ __("Booking apply") }}
            </button>
        @endif

        @if($booking->status === \Modules\Booking\Models\Booking::PAID)
            <button
                type="button"
                class="btn btn-success btn-sm mt-2"
                @click="openFinalizeBookingModal({{ $booking->id }}, $event)">
                {{__("Complete booking")}}
            </button>
        @endif
    </td>
</tr>

{{-- Модальное окно для добавления услуг --}}
@include('Hotel::.frontend.bookingHistory.addServices.add-services-baseadmin')

{{-- Модальное окно для калькуляции --}}
@include('Booking::frontend.modals.calculating.calculating-baseAdmin-modal', ['booking' => $booking])

{{-- Модальное окно поиска заказчика --}}
@include('User::frontend.modals.search-user-modal')
