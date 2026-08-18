<div class="row">
    <div class="col-lg-3 col-md-12">
        @include('Hotel::frontend.layouts.search.filter-search')
    </div>
    <div class="col-lg-9 col-md-12">
        <div class="bc-list-item">
            <div class="topbar-search">
                <h2 class="text result-count">
                    {{ plural_hotels_found($rows->total()) }}
                </h2>
                <div class="control bc-form-order">
                    @include('Layout::global.search.orderby',['routeName'=>'hotel.search'])
                </div>
            </div>
            <div class="ajax-search-result">
                @include('Hotel::frontend.ajax.search-result')
            </div>
        </div>
    </div>
</div>
