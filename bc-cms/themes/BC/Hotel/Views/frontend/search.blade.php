@extends('layouts.app')
@push('css')
    <link href="{{ asset('themes/bc/dist/frontend/module/hotel/css/hotel.css?_ver=' . config('app.asset_version')) }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('libs/ion_rangeslider/css/ion.rangeSlider.min.css') }}" />
@endpush
@section('content')
    @php
        $bg = setting_item('hotel_page_search_banner');
        $homePageId = setting_item('home_page_id');
        if ($homePageId) {
            $homePage = \Modules\Page\Models\Page::with('template')->find($homePageId);
            $homeBlocks = json_decode($homePage?->template?->content ?? '[]', true) ?: [];
            foreach ($homeBlocks as $block) {
                if (($block['type'] ?? null) !== 'form_search_all_service') {
                    continue;
                }
                $bg = $block['model']['list_slider'][0]['bg_image']
                    ?? $block['model']['bg_image']
                    ?? $bg;
                break;
            }
        }
    @endphp
    <div class="bc_search_hotel">
        <div class="bc_banner"
            style="min-height: 280px;@if ($bg) background-image: linear-gradient(0deg,rgba(0, 0, 0, 0.2),rgba(0, 0, 0, 0.2)),url({{ get_file_url($bg, 'full') }});@endif">
        </div>
        <div class="bc_form_search">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        @include('Hotel::frontend.layouts.search.form-search')
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            @include('Hotel::frontend.layouts.search.list-item')
        </div>
    </div>
@endsection

@push('js')
    <script type="text/javascript" src="{{ asset('libs/ion_rangeslider/js/ion.rangeSlider.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/filter.js?_ver=' . config('app.asset_version')) }}"></script>
    <script type="text/javascript" src="{{ asset('module/hotel/js/hotel.js?_ver=' . config('app.asset_version')) }}"></script>
@endpush
