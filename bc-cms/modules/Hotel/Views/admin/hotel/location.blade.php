<div class="panel">
    <div class="panel-title"><strong>{{ __('Locations') }}</strong>
        <span class="panel-toggle panel-collapse-toggle">{{ __('Collapse') }}</span>
    </div>
    <div class="panel-body">
        @if (is_default_lang())
            <div class="form-group">
                <label class="control-label">{{ __('Location') }}</label>
                @if (!empty($is_smart_search))
                    <div class="form-group-smart-search">
                        <div class="form-content">
                            <?php
                            $location_name = '';
                            $list_json = [];
                            $traverse = function ($locations, $prefix = '') use (&$traverse, &$list_json, &$location_name, $row) {
                                foreach ($locations as $location) {
                                    $translate = $location->translate();
                                    if ($row->location_id == $location->id) {
                                        $location_name = $translate->name;
                                    }
                                    $list_json[] = [
                                        'id' => $location->id,
                                        'title' => $prefix . ' ' . $translate->name,
                                    ];
                                    $traverse($location->children, $prefix . '-');
                                }
                            };
                            $traverse($hotel_location);
                            ?>
                            <div class="smart-search">
                                <input type="text" class="smart-search-location parent_text form-control"
                                    placeholder="{{ __('-- Please Select --') }}" value="{{ $location_name }}"
                                    data-onLoad="{{ __('Loading...') }}" data-default="{{ json_encode($list_json) }}">
                                <input type="hidden" class="child_id" name="location_id"
                                    value="{{ $row->location_id ?? Request::query('location_id') }}">
                            </div>
                        </div>
                    </div>
                @else
                    <div class="">
                        <select name="location_id" class="form-control">
                            <?php
                            $traverse = function ($locations, $prefix = '') use (&$traverse, $row) {
                                foreach ($locations as $location) {
                                    $selected = '';
                                    if ($row->location_id == $location->id) {
                                        $selected = 'selected';
                                    }
                                    printf("<option value='%s' %s>%s</option>", $location->id, $selected, $prefix . ' ' . $location->name);
                                    $traverse($location->children, $prefix . '-');
                                }
                            };
                            $traverse($hotel_location);
                            ?>
                        </select>
                    </div>
                @endif
            </div>
        @endif
        <div class="form-group">
            <label class="control-label">{{ __('Real address') }}</label>
            <input type="text" name="address" id="customPlaceAddress" class="form-control"
                placeholder="{{ __('Real address') }}" value="{{ $translation->address }}">
        </div>
        <div class="form-group">
            <label class="control-label">{{ __('How to get there') }}</label>
            <input type="text" name="how_to_get" class="form-control" value="{{ $translation->how_to_get }}">
        </div>
        <div class="form-group">
            <div class="nearby-places-block" style="border: 1px solid #ced4da; border-radius: 4px; padding: 10px 14px; background: #fff;">
                <label class="control-label mb-0">{{ __("What's Nearby") }}?</label>
                <div class="nearby-place-fields" style="margin-top: 10px;">
                    <div class="nearby-place-field" data-nearby-row="city" style="margin-bottom: 8px;">
                        <div class="form-row" style="display: flex; align-items: center; gap: 10px;">
                            <span class="nearby-place-icon" title="{{ __('City') }}" style="width: 24px; text-align: center; flex-shrink: 0;">
                                <i class="fa fa-building" style="font-size: 20px;"></i>
                            </span>
                            <input type="text" name="nearby_city" id="nearby_city" class="form-control" value="{{ $row->nearby_city }}" style="flex: 1;">
                            <input type="text" name="nearby_city_distance" id="nearby_city_distance" class="form-control" value="{{ $row->nearby_city_distance }}" placeholder="км" style="max-width: 110px;">
                        </div>
                    </div>
                    <div class="nearby-place-field" data-nearby-row="airport" style="margin-bottom: 8px;">
                        <div class="form-row" style="display: flex; align-items: center; gap: 10px;">
                            <span class="nearby-place-icon" title="{{ __('Air terminal') }}" style="width: 24px; text-align: center; flex-shrink: 0;">
                                <i class="fa fa-plane" style="font-size: 20px;"></i>
                            </span>
                            <input type="text" name="nearby_airport" id="nearby_airport" class="form-control" value="{{ $row->nearby_airport }}" style="flex: 1;">
                            <input type="text" name="nearby_airport_distance" id="nearby_airport_distance" class="form-control" value="{{ $row->nearby_airport_distance }}" placeholder="км" style="max-width: 110px;">
                        </div>
                    </div>
                    <div class="nearby-place-field" data-nearby-row="station">
                        <div class="form-row" style="display: flex; align-items: center; gap: 10px;">
                            <span class="nearby-place-icon" title="{{ __('Train station') }}" style="width: 24px; text-align: center; flex-shrink: 0;">
                                <i class="fa fa-train" style="font-size: 20px;"></i>
                            </span>
                            <input type="text" name="nearby_station" id="nearby_station" class="form-control" value="{{ $row->nearby_station }}" style="flex: 1;">
                            <input type="text" name="nearby_station_distance" id="nearby_station_distance" class="form-control" value="{{ $row->nearby_station_distance }}" placeholder="км" style="max-width: 110px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if (is_default_lang())
            <div class="form-group">
                <label class="control-label">{{ __('The geographic coordinate') }}</label>
                <div class="control-map-group">
                    <div id="map_content"></div>
                    <div class="input-group pt-3">
                        <input type="text" placeholder="{{ __('Search city, address') }}" class="bc_searchbox form-control" autocomplete="off">
                        <button type="button"
                                id="clearSearch"
                                class="btn btn-light d-none">
                            очистить
                        </button>
                    </div>

                    <div class="g-control">
                        <div class="form-group">
                            <label>{{ __('Map Latitude') }}:</label>
                            <input type="text" name="map_lat" class="form-control" value="{{ $row->map_lat }}"
                                onkeydown="return event.key !== 'Enter';">
                        </div>
                        <div class="form-group">
                            <label>{{ __('Map Longitude') }}:</label>
                            <input type="text" name="map_lng" class="form-control" value="{{ $row->map_lng }}"
                                onkeydown="return event.key !== 'Enter';">
                        </div>
                        <div class="form-group">
                            <label>{{ __('Map Zoom') }}:</label>
                            <input type="text" name="map_zoom" class="form-control"
                                value="{{ $row->map_zoom ?? '8' }}" onkeydown="return event.key !== 'Enter';">
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('js')
    <script>
        jQuery(function($) {

            var $input = $('.bc_searchbox');
            var $clearBtn = $('#clearSearch');

            $input.on('input', function () {
                if ($(this).val().length > 0) {
                    $clearBtn.removeClass('d-none');
                } else {
                    $clearBtn.addClass('d-none');
                }
            });

            $clearBtn.on('click', function () {
                $input.val('').trigger('input').focus();
            });
        })
    </script>
@endpush
