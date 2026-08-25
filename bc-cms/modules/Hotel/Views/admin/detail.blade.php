@extends('admin.layouts.app')

@section('content')
    <form action="{{ route('hotel.admin.store', ['id' => $row->id ? $row->id : '-1', 'lang' => request()->query('lang')]) }}"
        method="post">
        @csrf
        <div class="container-fluid">
            <div class="d-flex justify-content-between mb20">
                <div class="">
                    <h1 class="title-bar">{{ $row->id ? __('Edit: ') . $row->title : __('Add new hotel') }}</h1>
                    @if ($row->slug)
                        <p class="item-url-demo">{{ __('Permalink') }}: {{ url(config('hotel.hotel_route_prefix')) }}/@if($row->getUrlLocationSlug()){{ $row->getUrlLocationSlug() }}/@endif<a
                                href="#" class="open-edit-input" data-name="slug">{{ $row->slug }}</a>
                        </p>
                    @endif
                </div>
                <div class="">
                    @if ($row->id)
                        <a class="btn btn-warning btn-xs"
                            href="{{ route('hotel.admin.room.index', ['hotel_id' => $row->id]) }}" target="_blank"><i
                                class="fa fa-hand-o-right"></i> {{ __('Manage Rooms') }}</a>
                    @endif
                    @if ($row->slug)
                        <a class="btn btn-primary btn-xs" href="{{ $row->getDetailUrl(request()->query('lang')) }}"
                            target="_blank">{{ __('View Hotel') }}</a>
                    @endif
                </div>
            </div>
            @include('admin.message')
            @if ($row->id)
                @include('Language::admin.navigation')
            @endif
            <div class="lang-content-box">
                <div class="row">
                    <div class="col-md-9">
                        @include('Hotel::admin.hotel.content')
                        {{-- @include('Hotel::admin.hotel.users-assign-base') --}}
                        @include('Hotel::admin.hotel.location')
                        @include('Hotel::admin.hotel.pricing')
                        @include('Hotel::admin.hotel.surrounding')
                        @include('Core::admin/seo-meta/seo-meta')
                    </div>
                    <div class="col-md-3">
                        <div class="panel">
                            <div class="panel-title"><strong>{{ __('Publish') }}</strong></div>
                            <div class="panel-body">
                                @if (is_default_lang())
                                    <div>
                                        <label><input @if ($row->status == 'publish') checked @endif type="radio"
                                                name="status" value="publish"> {{ __('Publish') }}
                                        </label>
                                    </div>
                                    <div>
                                        <label><input @if ($row->status == 'draft') checked @endif type="radio"
                                                name="status" value="draft"> {{ __('Draft') }}
                                        </label>
                                    </div>
                                @endif
                                <div class="text-right">
                                    <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i>
                                        {{ __('Save Changes') }}</button>
                                </div>
                            </div>
                        </div>
                        @if (is_default_lang())
                            <div class="panel">
                                <div class="panel-title"><strong>{{ __('Author Setting') }}</strong></div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <?php $user = !empty($row->author_id) ? App\User::find($row->author_id) : false;
                                        \App\Helpers\AdminForm::select2(
                                            'author_id',
                                            [
                                                'configs' => [
                                                    'ajax' => [
                                                        'url' => route('user.admin.getForSelect2'),
                                                        'dataType' => 'json',
                                                    ],
                                                    'allowClear' => true,
                                                    'placeholder' => __('-- Select User --'),
                                                ],
                                            ],
                                            !empty($user->id) ? [$user->id, $user->getDisplayName() . ' (#' . $user->id . ')'] : false,
                                        );
                                        ?>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (is_default_lang())
                            <div class="panel">
                                <div class="panel-title"><strong>{{ __('Availability') }}</strong></div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label>{{ __('Hotel Featured') }}</label>
                                        <br>
                                        <label>
                                            <input type="checkbox" name="is_featured"
                                                @if ($row->is_featured) checked @endif value="1">
                                            {{ __('Enable featured') }}
                                        </label>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('Hotel Related IDs') }}</label>
                                        <input type="text" value="{{ $row->related_ids }}"
                                            placeholder="{{ __('Eg: 100,200') }}" name="related_ids" class="form-control">
                                        <p>
                                            <i>{{ __('Separated by comma') }}</i>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @include('Hotel::admin.hotel.attributes')

                            <div class="panel">
                                <div class="panel-title"><strong>{{ __('Image on the card') }}</strong></div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        {!! \Modules\Media\Helpers\FileHelper::fieldUpload('image_id', $row->image_id) !!}
                                    </div>
                                </div>
                            </div>
                            {{--                            @include('Hotel::admin.hotel.ical') --}}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('js')
    {!! App\Helpers\MapEngine::scripts() !!}
    <script>
        jQuery(function($) {
            var panelCollapseLabel = @json(__('Collapse'));
            var panelExpandLabel = @json(__('Expand'));

            $(document).on('click', '.panel-collapse-toggle', function () {
                var $toggle = $(this);
                var $body = $toggle.closest('.panel').children('.panel-body');
                var isIconToggle = $toggle.hasClass('panel-collapse-toggle-icon');
                $body.slideToggle(200, function () {
                    var isVisible = $body.is(':visible');
                    if (isIconToggle) {
                        $toggle.find('i')
                            .toggleClass('fa-chevron-up', isVisible)
                            .toggleClass('fa-chevron-down', !isVisible);
                        $toggle.attr('title', isVisible ? panelCollapseLabel : panelExpandLabel);
                    } else {
                        $toggle.text(isVisible ? panelCollapseLabel : panelExpandLabel);
                    }
                });
            });

            var lastNearbySearchKey = '';

            function isGenericPoiName(name) {
                if (!name) return true;
                return /^(аэропорт|аэровокзал|airport|вокзал|железнодорожный вокзал|ж\/?д\.?\s*вокзал|railway station|train station|автовокзал|bus station)$/i.test(
                    String(name).trim()
                );
            }

            function extractGeoName(geoObject) {
                if (!geoObject) return '';
                var name = '';
                var description = '';
                var text = '';
                var address = '';
                try {
                    if (typeof geoObject.getName === 'function') {
                        name = geoObject.getName() || '';
                    }
                    if (typeof geoObject.getAddressLine === 'function') {
                        address = geoObject.getAddressLine() || '';
                    }
                    if (geoObject.properties) {
                        name = name || geoObject.properties.get('name') || '';
                        description = geoObject.properties.get('description') || '';
                        text = geoObject.properties.get('text') || '';
                        // Company / org metadata from Yandex Search
                        var company = geoObject.properties.get('CompanyMetaData')
                            || geoObject.properties.get('companyMetaData');
                        if (company && company.name && !isGenericPoiName(company.name)) {
                            return company.name;
                        }
                    }
                } catch (e) {}

                // Prefer specific title over generic "Железнодорожный вокзал" / "Аэропорт"
                if (name && !isGenericPoiName(name)) {
                    return name;
                }
                if (description && !isGenericPoiName(description)) {
                    return isGenericPoiName(name) && name
                        ? (description + ', ' + name)
                        : description;
                }
                if (text && !isGenericPoiName(text)) {
                    return text;
                }
                if (address) {
                    return address;
                }
                return name || description || text || '';
            }

            function extractCityName(geoObject) {
                if (!geoObject) return '';
                try {
                    if (typeof geoObject.getLocalities === 'function') {
                        var localities = geoObject.getLocalities();
                        if (localities && localities.length) {
                            return localities[0];
                        }
                    }
                    if (typeof geoObject.getAdministrativeAreas === 'function') {
                        var areas = geoObject.getAdministrativeAreas();
                        if (areas && areas.length) {
                            return areas[0];
                        }
                    }
                } catch (e) {}
                return extractGeoName(geoObject);
            }

            function nearbyBounds(coords, delta) {
                delta = delta || 0.8;
                return [
                    [Number(coords[0]) - delta, Number(coords[1]) - delta],
                    [Number(coords[0]) + delta, Number(coords[1]) + delta]
                ];
            }

            function haversineKm(a, b) {
                var R = 6371;
                var lat1 = Number(a[0]) * Math.PI / 180;
                var lat2 = Number(b[0]) * Math.PI / 180;
                var dLat = (Number(b[0]) - Number(a[0])) * Math.PI / 180;
                var dLng = (Number(b[1]) - Number(a[1])) * Math.PI / 180;
                var h = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                    Math.cos(lat1) * Math.cos(lat2) *
                    Math.sin(dLng / 2) * Math.sin(dLng / 2);
                return R * 2 * Math.atan2(Math.sqrt(h), Math.sqrt(1 - h));
            }

            function formatDistanceKm(km) {
                if (km === null || typeof km === 'undefined' || isNaN(km)) return '';
                if (km < 1) {
                    return (Math.round(km * 1000)) + ' м';
                }
                return (Math.round(km * 10) / 10) + ' км';
            }

            function objectCoords(obj) {
                if (!obj || !obj.geometry || typeof obj.geometry.getCoordinates !== 'function') {
                    return null;
                }
                return obj.geometry.getCoordinates();
            }

            function pickNearestPlace(geoCollection, coords) {
                if (!geoCollection) return null;
                var length = typeof geoCollection.getLength === 'function' ? geoCollection.getLength() : 0;
                if (!length) return null;

                var candidates = [];
                for (var i = 0; i < length; i++) {
                    var obj = geoCollection.get(i);
                    if (!obj) continue;
                    var name = extractGeoName(obj);
                    if (!name) continue;
                    var objCoords = objectCoords(obj);
                    var dist = objCoords ? haversineKm(coords, objCoords) : Infinity;
                    candidates.push({
                        name: name,
                        coords: objCoords,
                        distanceKm: objCoords ? dist : null,
                        generic: isGenericPoiName(name)
                    });
                }
                if (!candidates.length) return null;

                candidates.sort(function (a, b) {
                    var da = a.distanceKm === null ? Infinity : a.distanceKm;
                    var db = b.distanceKm === null ? Infinity : b.distanceKm;
                    if (da !== db) return da - db;
                    // Same/unknown distance: prefer specific name over "Железнодорожный вокзал"
                    return Number(a.generic) - Number(b.generic);
                });

                var nearest = candidates[0];
                // If nearest title is generic, take a named one within +25% distance
                if (nearest.generic) {
                    for (var j = 1; j < candidates.length; j++) {
                        var c = candidates[j];
                        if (c.generic) continue;
                        if (nearest.distanceKm === null || c.distanceKm === null) {
                            return c;
                        }
                        if (c.distanceKm <= nearest.distanceKm * 1.25) {
                            return c;
                        }
                    }
                }
                return nearest;
            }

            function setNearbyValue(type, name, distanceKm) {
                var $name = $('#nearby_' + type);
                var $distance = $('#nearby_' + type + '_distance');
                if ($name.length) {
                    $name.val(name || '');
                }
                if ($distance.length) {
                    $distance.val(formatDistanceKm(distanceKm));
                }
            }

            function setNearbyFromObject(type, obj, fromCoords) {
                if (!obj) {
                    setNearbyValue(type, '', null);
                    return;
                }
                var name = type === 'city' ? extractCityName(obj) : extractGeoName(obj);
                if (!name) {
                    name = extractGeoName(obj);
                }
                var toCoords = objectCoords(obj);
                var distanceKm = toCoords ? haversineKm(fromCoords, toCoords) : null;
                setNearbyValue(type, name, distanceKm);
            }

            function kmToDelta(km) {
                // ~111 km per degree of latitude
                return Number(km) / 111;
            }

            function searchInBounds(query, bounds) {
                var options = {
                    boundedBy: bounds,
                    strictBounds: true,
                    results: 25
                };

                if (typeof ymaps.search === 'function') {
                    return ymaps.search(query, options).then(function (res) {
                        return res;
                    }, function () {
                        return ymaps.geocode(query, options);
                    });
                }

                return ymaps.geocode(query, options);
            }

            function searchTextNear(coords, queries, type, radiusesKm) {
                if (typeof queries === 'string') {
                    queries = [queries];
                }
                radiusesKm = radiusesKm || [40, 80, 120];
                var index = 0;

                function tryNext() {
                    if (index >= radiusesKm.length) {
                        setNearbyValue(type, '', null);
                        return;
                    }

                    var maxKm = radiusesKm[index];
                    var bounds = nearbyBounds(coords, kmToDelta(maxKm));
                    index += 1;

                    var pending = queries.map(function (query) {
                        return searchInBounds(query, bounds).then(function (res) {
                            return res ? (res.geoObjects || res) : null;
                        }, function () {
                            return null;
                        });
                    });

                    Promise.all(pending).then(function (collections) {
                        var best = null;
                        collections.forEach(function (collection) {
                            var place = pickNearestPlace(collection, coords);
                            if (!place || place.distanceKm === null) return;
                            if (place.distanceKm > maxKm) return;
                            if (!best || place.distanceKm < best.distanceKm) {
                                best = place;
                            }
                        });

                        if (best) {
                            setNearbyValue(type, best.name, best.distanceKm);
                            return;
                        }

                        tryNext();
                    });
                }

                tryNext();
            }

            function searchNearbyPlaces(coords) {
                if (!window.ymaps || !coords || coords.length < 2) return;
                var lat = Number(coords[0]);
                var lng = Number(coords[1]);
                if (!lat || !lng) return;

                var key = lat.toFixed(5) + ',' + lng.toFixed(5);
                if (key === lastNearbySearchKey) return;
                lastNearbySearchKey = key;
                coords = [lat, lng];

                ymaps.ready(function () {
                    ymaps.geocode(coords, { kind: 'locality', results: 1 }).then(function (res) {
                        var obj = res.geoObjects.get(0);
                        if (obj) {
                            setNearbyFromObject('city', obj, coords);
                            return;
                        }
                        return ymaps.geocode(coords, { results: 1 });
                    }).then(function (res) {
                        if (!res) return;
                        setNearbyFromObject('city', res.geoObjects.get(0), coords);
                    }, function () {
                        setNearbyValue('city', '', null);
                    });

                    // Airports are farther; search both "аэропорт" and "аэровокзал"
                    searchTextNear(coords, ['аэропорт', 'аэровокзал'], 'airport', [70, 120, 180]);
                    searchTextNear(coords, ['железнодорожный вокзал', 'вокзал'], 'station', [40, 70, 100]);
                });
            }

            function coordsFromInputs() {
                var lat = Number($('input[name=map_lat]').val());
                var lng = Number($('input[name=map_lng]').val());
                if (!lat || !lng) return null;
                return [lat, lng];
            }

            function hasSavedNearbyValues() {
                return ['city', 'airport', 'station'].some(function (type) {
                    return String($('#nearby_' + type).val() || '').trim() ||
                        String($('#nearby_' + type + '_distance').val() || '').trim();
                });
            }

            $(document).on('change', 'input[name=map_lat], input[name=map_lng]', function () {
                var coords = coordsFromInputs();
                if (coords) {
                    searchNearbyPlaces(coords);
                }
            });

            new BCMapEngine('map_content', {
                center: [{{ $row->map_lat ?? setting_item('map_lat_default', 51.505) }},
                    {{ $row->map_lng ?? setting_item('map_lng_default', -0.09) }}
                ],
                zoom: {{ $row->map_zoom ?? '8' }},
                allowSetMarker: true,
                onMarkerSet: function (coords) {
                    searchNearbyPlaces(coords);
                },

                ready: function(engineMap) {

                    @if ($row->map_lat && $row->map_lng)
                    engineMap.addMarker([
                        {{ $row->map_lat }},
                        {{ $row->map_lng }}
                    ], { icon_options: {}, silent: true });
                    if (!hasSavedNearbyValues()) {
                        searchNearbyPlaces([{{ $row->map_lat }}, {{ $row->map_lng }}]);
                    }
                    @endif

                    engineMap.searchBox($('.bc_searchbox'), function(dataLatLng) {

                        engineMap.clearMarkers();
                        engineMap.addMarker(dataLatLng, { icon_options: {} });

                        $("input[name=map_lat]").val(dataLatLng[0]).trigger('change');
                        $("input[name=map_lng]").val(dataLatLng[1]).trigger('change');
                        searchNearbyPlaces([dataLatLng[0], dataLatLng[1]]);
                    });

                    $('#clearSearch').on('click', function () {
                        lastNearbySearchKey = '';
                        engineMap.resetToInitial();
                    });

                    // Fallback if marker already on map and nearby fields are empty
                    setTimeout(function () {
                        var coords = coordsFromInputs();
                        if (coords && !hasSavedNearbyValues()) {
                            lastNearbySearchKey = '';
                            searchNearbyPlaces(coords);
                        }
                    }, 800);
                }
            });
        })
    </script>
@endpush
