<div class="panel">
    <div class="panel-title"><strong>{{__("General information")}}</strong>
        @if($row->id)
            <span class="ml-3">{{__("Object ID")}} <strong style="font-size: 18px; font-weight: 700;">{{ $row->id }}</strong></span>
        @endif
        <span class="panel-toggle panel-collapse-toggle">{{ __('Collapse') }}</span>
    </div>
    <div class="panel-body">
        <div class="form-group magic-field" data-id="title" data-type="title">
            <label class="control-label">{{__("Object name")}}</label>
            <input type="text" value="{{$translation->title}}" placeholder="{{__("Name of the hotel")}}" name="title" class="form-control">
        </div>
        <div class="form-group">
            <label class="control-label">{{__("Star rating")}}</label>
            <div class="d-flex align-items-center">
                <div class="hotel-star-rate-input">
                    @for ($i = 5; $i >= 1; $i--)
                        <input type="radio" id="hotel_star_{{ $i }}" name="star_rate" value="{{ $i }}" {{ (int) $row->star_rate === $i ? 'checked' : '' }}>
                        <label for="hotel_star_{{ $i }}"><i class="fa fa-star"></i></label>
                    @endfor
                </div>
                <button type="button" class="btn btn-sm btn-default hotel-star-rate-clear">{{__("Clear")}}</button>
            </div>
        </div>
        <style>
            .hotel-star-rate-input {
                display: flex;
                flex-direction: row-reverse;
                justify-content: flex-end;
            }
            .hotel-star-rate-input input {
                display: none;
            }
            .hotel-star-rate-input label {
                margin: 0 12px 0 0;
                cursor: pointer;
                color: #d0d0d0;
                font-size: 22px;
            }
            .hotel-star-rate-input label:hover,
            .hotel-star-rate-input label:hover ~ label,
            .hotel-star-rate-input input:checked ~ label {
                color: #f5a623;
            }
            .hotel-star-rate-clear {
                margin-left: 8px;
            }
        </style>
        <script>
            document.querySelector('.hotel-star-rate-clear')?.addEventListener('click', function () {
                document.querySelectorAll('.hotel-star-rate-input input').forEach(function (el) {
                    el.checked = false;
                });
            });
        </script>
        <div class="form-group">
            <label class="control-label">{{__("Object type")}}</label>
            <select name="object_type" class="form-control">
                @foreach([
                    'hotel' => 'Отель',
                    'recreation_center' => 'База отдыха',
                    'park_hotel' => 'Парк-отель',
                    'spa_hotel' => 'Спа-отель',
                    'eco_hotel' => 'Эко-отель',
                    'tourist_camp' => 'Турбаза',
                    'sanatorium' => 'Санаторий',
                    'rest_house' => 'Дом отдыха',
                    'glamping' => 'Глэмпинг',
                    'camping' => 'Кемпинг',
                    'guest_country_house' => 'Гостевой дом/Загородный дом',
                ] as $typeValue => $typeLabel)
                    <option value="{{ $typeValue }}" {{ ($row->object_type ?: 'hotel') === $typeValue ? 'selected' : '' }}>{{ $typeLabel }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group hotel-legal-entity-block">
            <label class="control-label">{{__("INN")}}</label>
            <div class="input-group">
                <input type="text" name="legal_inn" class="form-control hotel-legal-inn" value="{{ $row->legal_inn }}" maxlength="12" autocomplete="off">
                <div class="input-group-append">
                    <button type="button" class="btn btn-info hotel-legal-inn-lookup">{{__("Find")}}</button>
                </div>
            </div>
            <div class="hotel-legal-details"@if(empty($row->legal_inn)) style="display: none;"@endif>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <label class="control-label">{{__("Legal entity")}}</label>
                        <input type="text" name="legal_entity" class="form-control" value="{{ $row->legal_entity }}">
                    </div>
                    <div class="col-md-4">
                        <label class="control-label">{{__("OGRN")}}</label>
                        <input type="text" name="legal_ogrn" class="form-control" value="{{ $row->legal_ogrn }}" maxlength="15">
                    </div>
                    <div class="col-md-4">
                        <label class="control-label">{{__("Ownership form")}}</label>
                        <input type="text" name="legal_ownership_form" class="form-control" value="{{ $row->legal_ownership_form }}">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="control-label">{{__("Legal requisites")}}</label>
                    <textarea name="legal_requisites" class="form-control" rows="3">{{ $row->legal_requisites }}</textarea>
                </div>
            </div>
        </div>
        @php
            $dadataPartyUrl = request()->routeIs('hotel.vendor.*')
                ? route('hotel.vendor.dadata.party')
                : route('hotel.admin.dadata.party');
        @endphp
        @push('js')
        @if(!request()->routeIs('hotel.vendor.*'))
        <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/inputmask.min.js"></script>
        <script src="{{ asset('js/phone-mask.js?_ver=' . config('app.asset_version')) }}"></script>
        @endif
        <script>
            jQuery(function ($) {
                var partyUrl = @json($dadataPartyUrl);
                var lookingUp = false;
                var $details = $('.hotel-legal-details');

                function showLegalDetails() {
                    $details.show();
                }

                function hideLegalDetails() {
                    $details.hide();
                    $('[name="legal_entity"]').val('');
                    $('[name="legal_ogrn"]').val('');
                    $('[name="legal_ownership_form"]').val('');
                    $('[name="legal_requisites"]').val('');
                }

                function fillLegalParty(data) {
                    if (!data) {
                        return;
                    }
                    $('[name="legal_entity"]').val(data.legal_entity || '');
                    $('[name="legal_inn"]').val(data.legal_inn || '');
                    $('[name="legal_ogrn"]').val(data.legal_ogrn || '');
                    $('[name="legal_ownership_form"]').val(data.legal_ownership_form || '');
                    $('[name="legal_requisites"]').val(data.legal_requisites || '');
                    showLegalDetails();
                }

                function lookupPartyByInn() {
                    var inn = $.trim($('.hotel-legal-inn').val() || '');
                    if (!inn) {
                        hideLegalDetails();
                        return;
                    }
                    if (lookingUp) {
                        return;
                    }
                    lookingUp = true;
                    $.ajax({
                        url: partyUrl,
                        type: 'POST',
                        dataType: 'json',
                        data: { inn: inn },
                        success: function (res) {
                            if (res && res.status) {
                                fillLegalParty(res.data || {});
                                return;
                            }
                            hideLegalDetails();
                            if (typeof bookingCoreApp !== 'undefined') {
                                bookingCoreApp.showError(res);
                            }
                        },
                        error: function (e) {
                            hideLegalDetails();
                            if (typeof bookingCoreApp !== 'undefined') {
                                bookingCoreApp.showAjaxError(e);
                            }
                        },
                        complete: function () {
                            lookingUp = false;
                        }
                    });
                }

                $(document).on('click', '.hotel-legal-inn-lookup', function (e) {
                    e.preventDefault();
                    lookupPartyByInn();
                });

                $(document).on('blur', '.hotel-legal-inn', function () {
                    var inn = $.trim($(this).val() || '');
                    if (!inn) {
                        hideLegalDetails();
                        return;
                    }
                    lookupPartyByInn();
                });

                $(document).on('keydown', '.hotel-legal-inn', function (e) {
                    if (e.key === 'Enter' || e.keyCode === 13) {
                        e.preventDefault();
                        $(this).blur();
                    }
                });
            });
        </script>
        @endpush
        <div class="form-group">
            <label class="control-label">{{__("Aggregator contact details")}}</label>
            <div class="row">
                <div class="col-md-4">
                    <div class="input-group" title="{{__('Owner phone')}}">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-phone"></i></span>
                        </div>
                        <input type="text" name="aggregator_owner_phone" class="form-control phone-mask" value="{{ $row->aggregator_owner_phone }}" placeholder="{{__('Owner phone')}}" title="{{__('Owner phone')}}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group" title="{{__('Email')}}">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                        </div>
                        <input type="text" name="aggregator_email" class="form-control" value="{{ $row->aggregator_email }}" placeholder="{{__('Email')}}" title="{{__('Email')}}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group" title="{{__('Telegram')}}">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-telegram"></i></span>
                        </div>
                        <input type="text" name="aggregator_telegram" class="form-control" value="{{ $row->aggregator_telegram }}" placeholder="{{__('Telegram')}}" title="{{__('Telegram')}}">
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label">{{__("Guest contacts")}}</label>
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group" title="{{__('Administrator phone')}}">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-phone"></i></span>
                        </div>
                        <input type="text" name="guest_admin_phone" class="form-control phone-mask" value="{{ $row->guest_admin_phone }}" placeholder="{{__('Administrator phone')}}" title="{{__('Administrator phone')}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group" title="{{__('Chat link')}}">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-comments"></i></span>
                        </div>
                        <input type="text" name="guest_chat_link" class="form-control" value="{{ $row->guest_chat_link }}" placeholder="{{__('Chat link')}}" title="{{__('Chat link')}}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-title"><strong>{{__("Hotel Content")}}</strong>
        <span class="panel-toggle panel-collapse-toggle">{{ __('Collapse') }}</span>
    </div>
    <div class="panel-body">
        <div class="form-group magic-field" data-id="content" data-type="content" data-editor="1">
            <label class="control-label" data->{{__("Description")}}</label>
            <div class="">
                <textarea name="content" class="d-none has-ckeditor" id="content" cols="30" rows="10">{{$translation->content}}</textarea>
            </div>
        </div>
{{--        @if(is_default_lang())--}}
{{--            <div class="form-group">--}}
{{--                <label class="control-label">{{__("Youtube Video")}}</label>--}}
{{--                <input type="text" name="video" class="form-control" value="{{$row->video}}" placeholder="{{__("Youtube link video")}}">--}}
{{--            </div>--}}
{{--        @endif--}}
        @if(is_default_lang())
            <div class="form-group">
                <label class="control-label">{{__("Banner Image")}}</label>
                <div class="form-group-image">
                    {!! \Modules\Media\Helpers\FileHelper::fieldUpload('banner_image_id',$row->banner_image_id) !!}
                </div>
            </div>
            <div class="form-group" data-gallery-type="territory">
                <div class="d-flex align-items-center">
                    <label class="control-label mb-0">{{__("Territory gallery")}}</label>
                    <span class="hotel-section-gallery-toggle ml-2" role="button" style="cursor: pointer;"><i class="fa fa-chevron-up"></i></span>
                </div>
                <div class="hotel-section-gallery-body">
                @php
                    $galleryFolders = $row->gallery_folders ?? [];
                    if (is_string($galleryFolders)) {
                        $galleryFolders = json_decode($galleryFolders, true);
                    }
                    $galleryFolders = is_array($galleryFolders) ? array_values($galleryFolders) : [];
                    $galleryFoldersSaveUrl = '';
                    if (!empty($row->id)) {
                        $galleryFoldersSaveUrl = request()->routeIs('hotel.vendor.*')
                            ? route('hotel.vendor.galleryFolders', ['id' => $row->id])
                            : route('hotel.admin.galleryFolders', ['id' => $row->id]);
                    }
                @endphp
                <div class="hotel-gallery-wrap">
                    <input type="hidden" name="gallery_folders" class="hotel-gallery-folders-input" value="{{ htmlspecialchars(json_encode($galleryFolders, JSON_UNESCAPED_UNICODE), ENT_QUOTES) }}">
                    {!! \Modules\Media\Helpers\FileHelper::fieldGalleryUpload('gallery',$row->gallery, ['gallery_type' => 'territory']) !!}
                    @if(false)
                    <div class="hotel-gallery-root">
                        <div class="hotel-gallery-folders">
                            <div class="d-flex hotel-gallery-folder-list">
                                @foreach($galleryFolders as $folder)
                                    @php
                                        $folderImageItems = [];
                                        foreach (array_filter(explode(',', $folder['images'] ?? '')) as $imgId) {
                                            $file = (new \Modules\Media\Models\MediaFile())->findById($imgId);
                                            if (empty($file)) {
                                                continue;
                                            }
                                            $folderImageItems[] = [
                                                'id' => $file->id,
                                                'thumb' => \Modules\Media\Helpers\FileHelper::url($file, 'thumb'),
                                                'edit_path' => $file->getEditPath(),
                                            ];
                                        }
                                    @endphp
                                    <div class="hotel-gallery-folder" data-id="{{ $folder['id'] }}" data-images="{{ json_encode($folderImageItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}">
                                        <div class="hotel-gallery-folder-box">
                                            <span class="delete-folder btn btn-sm btn-danger" title="{{ __('Delete this folder') }}"><i class="fa fa-trash"></i></span>
                                            <i class="fa fa-folder-o hotel-gallery-folder-icon"></i>
                                        </div>
                                        <input type="text" class="hotel-gallery-folder-name form-control" value="{{ $folder['name'] }}" placeholder="{{ __('New folder') }}" autocomplete="off">
                                    </div>
                                @endforeach
                            </div>
                            <div class="text-left mb-2">
                                <button type="button" class="btn btn-info btn-sm hotel-gallery-folder-add"><i class="fa fa-folder"></i> {{ __('Create folder') }}</button>
                            </div>
                        </div>
                    </div>
                    <div class="hotel-gallery-folder-inner" style="display: none;">
                        <div class="hotel-gallery-folder-nav mb-3">
                            <a href="#" class="hotel-gallery-folder-back">{{ __('Gallery') }}</a>
                            / <strong class="hotel-gallery-folder-current-name"></strong>
                        </div>
                        <div class="dungdt-upload-multiple hotel-folder-photos">
                            <div class="attach-demo d-flex"></div>
                            <div class="upload-box">
                                <input type="hidden" class="hotel-folder-photos-input" value="">
                                <div class="text-left">
                                    <span class="btn btn-info btn-sm btn-field-upload"><i class="fa fa-plus-circle"></i> {{ __('Select images') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                </div>
            </div>
            @foreach ([
                ['key' => 'gallery_food', 'type' => 'food', 'label' => __('Food gallery')],
                ['key' => 'gallery_entertainment', 'type' => 'entertainment', 'label' => __('Entertainment gallery')],
                ['key' => 'gallery_amenities', 'type' => 'amenities', 'label' => __('Amenities gallery')],
            ] as $extraGallery)
                <div class="form-group" data-gallery-type="{{ $extraGallery['type'] }}">
                    <div class="d-flex align-items-center">
                        <label class="control-label mb-0">{{ $extraGallery['label'] }}</label>
                        <span class="hotel-section-gallery-toggle ml-2" role="button" style="cursor: pointer;"><i class="fa fa-chevron-up"></i></span>
                    </div>
                    <div class="hotel-section-gallery-body">
                        {!! \Modules\Media\Helpers\FileHelper::fieldGalleryUpload($extraGallery['key'], $row->{$extraGallery['key']}, ['gallery_type' => $extraGallery['type']]) !!}
                    </div>
                </div>
            @endforeach
            <style>
                .hotel-gallery-folder-list {
                    flex-wrap: wrap;
                    margin: 0 -7px;
                }
                .hotel-gallery-folder {
                    flex: 0 0 20%;
                    margin-bottom: 10px;
                    padding: 0 7px;
                }
                .hotel-gallery-folder-box {
                    position: relative;
                    height: 120px;
                    background: #5a5a5a;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 3px;
                    cursor: pointer;
                }
                .hotel-gallery-folder-icon {
                    font-size: 52px;
                    color: #fff;
                    margin: 0;
                }
                .hotel-gallery-folders .delete-folder {
                    position: absolute;
                    top: 8px;
                    left: 8px;
                    z-index: 9;
                    cursor: pointer;
                }
                .hotel-gallery-folder-name {
                    margin-top: 6px;
                    font-size: 13px;
                    text-align: center;
                }
            </style>
            @push('js')
            <script>
                jQuery(function ($) {
                    $(document).on('click', '.hotel-section-gallery-toggle', function () {
                        var $toggle = $(this);
                        var $body = $toggle.closest('.form-group').find('.hotel-section-gallery-body');
                        $body.slideToggle(200, function () {
                            $toggle.find('i').attr('class', $body.is(':visible') ? 'fa fa-chevron-up' : 'fa fa-chevron-down');
                        });
                    });

                    var newLabel = @json(__('New folder'));
                    var deleteLabel = @json(__('Delete this folder'));
                    var currentFolderId = null;
                    var saveUrl = @json($galleryFoldersSaveUrl);

                    function folderHtml() {
                        return '<div class="hotel-gallery-folder" data-id="" data-images="[]">' +
                            '<div class="hotel-gallery-folder-box">' +
                            '<span class="delete-folder btn btn-sm btn-danger" title="' + deleteLabel + '"><i class="fa fa-trash"></i></span>' +
                            '<i class="fa fa-folder-o hotel-gallery-folder-icon"></i>' +
                            '</div>' +
                            '<input type="text" class="hotel-gallery-folder-name form-control" value="" placeholder="' + newLabel + '" autocomplete="off">' +
                            '</div>';
                    }

                    function getStoredFolders($wrap) {
                        try {
                            return JSON.parse($wrap.find('.hotel-gallery-folders-input').val() || '[]');
                        } catch (e) {
                            return [];
                        }
                    }

                    function syncFolders($wrap) {
                        var stored = getStoredFolders($wrap);
                        var byId = {};
                        stored.forEach(function (folder) {
                            byId[String(folder.id)] = folder;
                        });
                        var folders = [];
                        $wrap.find('.hotel-gallery-folder').each(function () {
                            var id = String($(this).data('id'));
                            var prev = byId[id] || {};
                            folders.push({
                                id: id,
                                name: $.trim($(this).find('.hotel-gallery-folder-name').val()),
                                images: prev.images || ''
                            });
                        });
                        $wrap.find('.hotel-gallery-folders-input').val(JSON.stringify(folders));
                    }

                    function imageItemHtml(item) {
                        return '<div class="image-item"><div class="inner">' +
                            '<a class="edit-img btn btn-sm btn-primary edit-multiple" data-id="' + item.id + '" data-file="' + (item.edit_path || '') + '"><i class="fa fa-edit"></i></a>' +
                            '<span class="delete btn btn-sm btn-danger"><i class="fa fa-trash"></i></span>' +
                            '<div class="img-preview"><img class="image-responsive image-preview w-100" src="' + (item.thumb || '') + '"/></div>' +
                            '</div></div>';
                    }

                    function capturePhotos($photos) {
                        var items = [];
                        var ids = [];
                        $photos.find('.attach-demo .image-item').each(function () {
                            var $edit = $(this).find('.edit-img');
                            var id = $edit.data('id');
                            if (!id) {
                                return;
                            }
                            ids.push(id);
                            items.push({
                                id: id,
                                thumb: $(this).find('img').attr('src') || '',
                                edit_path: $edit.data('file') || ''
                            });
                        });
                        return { ids: ids.join(','), items: items };
                    }

                    function persistOpenFolder() {
                        if (!currentFolderId) {
                            return;
                        }
                        var $wrap = $('.hotel-gallery-folders');
                        var captured = capturePhotos($('.hotel-folder-photos'));
                        var folders = getStoredFolders($wrap);
                        folders.forEach(function (folder) {
                            if (String(folder.id) === String(currentFolderId)) {
                                folder.images = captured.ids;
                            }
                        });
                        $wrap.find('.hotel-gallery-folders-input').val(JSON.stringify(folders));
                        $wrap.find('.hotel-gallery-folder').filter(function () {
                            return String($(this).data('id')) === String(currentFolderId);
                        }).attr('data-images', JSON.stringify(captured.items)).data('images', captured.items);
                    }

                    function saveFoldersAjax() {
                        if (!saveUrl) {
                            return;
                        }
                        persistOpenFolder();
                        syncFolders($('.hotel-gallery-folders'));
                        $.ajax({
                            url: saveUrl,
                            type: 'POST',
                            data: {
                                gallery_folders: $('.hotel-gallery-folders-input').val()
                            }
                        });
                    }

                    function openFolder($folder) {
                        persistOpenFolder();
                        currentFolderId = String($folder.data('id'));
                        var name = $.trim($folder.find('.hotel-gallery-folder-name').val()) || newLabel;
                        var items = $folder.attr('data-images') || $folder.data('images') || [];
                        if (typeof items === 'string') {
                            try {
                                items = JSON.parse(items);
                            } catch (e) {
                                items = [];
                            }
                        }
                        if (!Array.isArray(items)) {
                            items = [];
                        }
                        var html = '';
                        var ids = [];
                        items.forEach(function (item) {
                            html += imageItemHtml(item);
                            ids.push(item.id);
                        });
                        var $photos = $('.hotel-folder-photos');
                        $photos.find('.attach-demo').html(html);
                        $photos.find('input').val(ids.join(','));
                        $('.hotel-gallery-folder-current-name').text(name);
                        $('.hotel-gallery-root').hide();
                        $('.hotel-gallery-folder-inner').show();
                    }

                    function closeFolder() {
                        persistOpenFolder();
                        currentFolderId = null;
                        $('.hotel-folder-photos').find('.attach-demo').empty();
                        $('.hotel-folder-photos').find('input').val('');
                        $('.hotel-gallery-folder-inner').hide();
                        $('.hotel-gallery-root').show();
                        saveFoldersAjax();
                    }

                    $(document).on('click', '.hotel-gallery-folder-add', function () {
                        var $wrap = $(this).closest('.hotel-gallery-folders');
                        var $item = $(folderHtml());
                        var id = 'f-' + Date.now();
                        $item.data('id', id);
                        $item.attr('data-id', id);
                        $item.data('images', []);
                        $wrap.find('.hotel-gallery-folder-list').append($item);
                        syncFolders($wrap);
                        $item.find('.hotel-gallery-folder-name').focus();
                    });

                    $(document).on('input', '.hotel-gallery-folders .hotel-gallery-folder-name', function () {
                        syncFolders($(this).closest('.hotel-gallery-folders'));
                    });

                    $(document).on('keydown', '.hotel-gallery-folders .hotel-gallery-folder-name', function (e) {
                        if (e.key === 'Enter' || e.keyCode === 13) {
                            e.preventDefault();
                            $(this).blur();
                        }
                    });

                    $(document).on('blur', '.hotel-gallery-folders .hotel-gallery-folder-name', function () {
                        syncFolders($(this).closest('.hotel-gallery-folders'));
                        saveFoldersAjax();
                    });

                    $(document).on('click', '.hotel-gallery-folder-box', function (e) {
                        if ($(e.target).closest('.delete-folder').length) {
                            return;
                        }
                        openFolder($(this).closest('.hotel-gallery-folder'));
                    });

                    $(document).on('click', '.hotel-gallery-folder-back', function (e) {
                        e.preventDefault();
                        closeFolder();
                    });

                    $(document).on('click', '.hotel-gallery-folders .delete-folder', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        var $folder = $(this).closest('.hotel-gallery-folder');
                        var $wrap = $(this).closest('.hotel-gallery-folders');
                        bookingCoreApp.showConfirm({
                            message: i18n.confirm_delete_folder,
                            callback: function (result) {
                                if (!result) {
                                    return;
                                }
                                $folder.remove();
                                syncFolders($wrap);
                                saveFoldersAjax();
                            }
                        });
                    });

                    $('.hotel-gallery-wrap').closest('form').on('submit', function () {
                        persistOpenFolder();
                    });
                });
            </script>
            @endpush
        @endif
    </div>
</div>

{{--
<div class="panel">
    <div class="panel-title"><strong>{{__("Hotel Policy")}}</strong></div>
    <div class="panel-body">
        @if(is_default_lang())
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{__("Hotel rating standard")}}</label>
                        <input type="number" step="0.1" min="0" max="5" value="{{$row->star_rate}}" placeholder="{{__("Eg: 5")}}" name="star_rate" class="form-control">
                    </div>
                </div>
            </div>
        @endif
        <div class="form-group-item">
            <label class="control-label">{{__('Policy')}}</label>
            <div class="g-items-header">
                <div class="row">
                    <div class="col-md-5">{{__("Title")}}</div>
                    <div class="col-md-5">{{__('Content')}}</div>
                    <div class="col-md-1"></div>
                </div>
            </div>
            <div class="g-items">
                @if(!empty($translation->policy))
                    @foreach($translation->policy as $key=>$item)
                        <div class="item" data-number="{{$key}}">
                            <div class="row">
                                <div class="col-md-5">
                                    <input type="text" name="policy[{{$key}}][title]" class="form-control" value="{{$item['title'] ?? ''}}" placeholder="{{__('Eg: What kind of foowear is most suitable ?')}}">
                                </div>
                                <div class="col-md-6">
                                    <textarea name="policy[{{$key}}][content]" class="form-control" placeholder="...">{{$item['content']}}</textarea>
                                </div>
                                <div class="col-md-1">
                                    <span class="btn btn-danger btn-sm btn-remove-item"><i class="fa fa-trash"></i></span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="text-right">
                <span class="btn btn-info btn-sm btn-add-item"><i class="icon ion-ios-add-circle-outline"></i> {{__('Add item')}}</span>
            </div>
            <div class="g-more hide">
                <div class="item" data-number="__number__">
                    <div class="row">
                        <div class="col-md-5">
                            <input type="text" __name__="policy[__number__][title]" class="form-control" placeholder="{{__('Eg: What kind of foowear is most suitable ?')}}">
                        </div>
                        <div class="col-md-6">
                            <textarea __name__="policy[__number__][content]" class="form-control" placeholder=""></textarea>
                        </div>
                        <div class="col-md-1">
                            <span class="btn btn-danger btn-sm btn-remove-item"><i class="fa fa-trash"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php do_action(\Modules\Hotel\Hook::FORM_AFTER_POLICY,$row) ?>
    </div>
</div>
--}}
