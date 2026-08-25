@extends('admin.layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb20">
            <h1 class="title-bar">{{__("Hotel Attributes")}}</h1>
        </div>
        @include('admin.message')
        <div class="row">
            <div class="col-md-4 mb40">
                <form action="{{route('hotel.admin.attribute.store',['id'=>($row->id) ? $row->id : '-1','lang'=>request()->query('lang')])}}" method="post">
                    @csrf
                    <input type="hidden" name="id" value="{{$row->id}}">
                    <div class="panel">
                        <div class="panel-title">{{__("Add Attributes")}}</div>
                        <div class="panel-body">
                            @include('Hotel::admin/attribute/form',['parents'=>$rows])
                            <div class="">
                                <button class="btn btn-primary" type="submit">{{__("Add new")}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-8">
                <div class="filter-div d-flex justify-content-between ">
                    <div class="col-left">
                        @if(!empty($rows))
                            <form method="post" action="{{route('hotel.admin.attribute.bulkEdit')}}" class="filter-form filter-form-left d-flex justify-content-start">
                                {{csrf_field()}}
                                <select name="action" class="form-control">
                                    <option value="">{{__(" Bulk Action ")}}</option>
                                    <option value="delete">{{__(" Delete ")}}</option>
                                </select>
                                <button data-confirm="{{__("Do you want to delete?")}}" class="btn-info btn btn-icon dungdt-apply-form-btn" type="button">{{__('Apply')}}</button>
                            </form>
                        @endif
                    </div>
                    <div class="col-left">
                        <form method="get" action="{{route('hotel.admin.attribute.index')}} " class="filter-form filter-form-right d-flex justify-content-end" role="search">
                            <input type="text" name="s" value="{{ Request()->s }}" class="form-control" placeholder="{{__("Search by name")}}">
                            <button class="btn-info btn btn-icon btn_search" id="search-submit" type="submit">{{__('Search')}}</button>
                        </form>
                    </div>
                </div>
                <div class="panel">
                    <div class="panel-title d-flex align-items-start" style="gap: 12px;">
                        <div class="form-group mb-0" style="min-width: 260px; flex: 1;">
                            <label>{{__('Блок атрибутов')}}</label>
                            <select name="block_id" id="attr-block-filter" class="form-control">
                                <option value="" disabled selected hidden>{{__('Блок атрибутов')}}</option>
                                @foreach(($blocks ?? []) as $block)
                                    @php $blockTranslate = $block->translate(app_get_locale()); @endphp
                                    <option value="{{$block->id}}">
                                        {{$blockTranslate->name ?: $block->name}}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0" style="min-width: 260px; flex: 1;">
                            <label>{{__('Тип атрибутов')}}</label>
                            <select name="block_type_id" id="attr-block-type-filter" class="form-control">
                                <option value="" disabled selected hidden>{{__('Тип атрибутов')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="panel-body">
                        <form class="bc-form-item">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th width="60px"><input type="checkbox" class="check-all"></th>
                                    <th>{{__("Name")}}</th>
                                    <th>{{__("Position Order")}}</th>
                                    <th class="">{{__("Actions")}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($rows) > 0)
                                    @foreach($rows as $row)
                                        <tr class="attr-row" data-block-type-id="{{$row->block_type_id}}" style="display: none;">
                                            <td><input type="checkbox" class="check-item" name="ids[]" value="{{$row->id}}">
                                            </td>
                                            <td class="title">
                                                <a href="{{route('hotel.admin.attribute.edit',['id'=>$row->id])}}">{{$row->name}}</a>
                                            </td>
                                            <td>
                                                {{$row->position ?? 0}}
                                            </td>
                                            <td>
                                                <a href="{{route('hotel.admin.attribute.edit',['id'=>$row->id])}}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> {{__('Edit')}}
                                                </a>
                                                <a href="{{route('hotel.admin.attribute.term.index',['id'=>$row->id])}}" class="btn btn-sm btn-success"><i class="fa fa"></i> {{__("hotelAdmin.buttons.add_new_service")}}
                                                </a>

                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                <tr class="attr-filter-empty-row">
                                    <td colspan="4" class="attr-filter-empty-text">{{__('Выберите блок и тип атрибутов')}}</td>
                                </tr>
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@php
    $blockTypesForJs = collect($blockTypes ?? [])->map(function ($type) {
        $translation = $type->translate(app_get_locale());
        return [
            'id' => $type->id,
            'block_id' => (string) $type->block_id,
            'name' => $translation->name ?: $type->name,
        ];
    })->values();
@endphp

@push('js')
<script>
    (function () {
        var blockTypes = @json($blockTypesForJs);
        var $blockSelect = $('#attr-block-filter');
        var $typeSelect = $('#attr-block-type-filter');
        var $rows = $('.attr-row');
        var $filterEmptyRow = $('.attr-filter-empty-row');
        var $filterEmptyText = $('.attr-filter-empty-text');
        var typePlaceholder = @json(__('Тип атрибутов'));
        var selectMessage = @json(__('Выберите блок и тип атрибутов'));
        var emptyMessage = @json(__('Нет атрибутов для выбранного типа'));

        function fillBlockTypes(blockId) {
            $typeSelect.empty();
            $typeSelect.append(
                $('<option>', {
                    value: '',
                    text: typePlaceholder,
                    disabled: true,
                    selected: true,
                    hidden: true
                })
            );

            if (!blockId) {
                return;
            }

            blockTypes
                .filter(function (item) {
                    return String(item.block_id) === String(blockId);
                })
                .forEach(function (item) {
                    $typeSelect.append(
                        $('<option>', {
                            value: item.id,
                            text: item.name
                        })
                    );
                });
        }

        function filterAttributesByType(typeId) {
            if (!typeId) {
                $rows.hide();
                $filterEmptyText.text(selectMessage);
                $filterEmptyRow.show();
                return;
            }

            var visibleCount = 0;
            $rows.each(function () {
                var rowTypeId = String($(this).data('block-type-id') || '');
                var isVisible = rowTypeId === String(typeId);
                $(this).toggle(isVisible);
                if (isVisible) {
                    visibleCount++;
                }
            });

            if (visibleCount === 0) {
                $filterEmptyText.text(emptyMessage);
                $filterEmptyRow.show();
            } else {
                $filterEmptyRow.hide();
            }
        }

        $blockSelect.on('change', function () {
            fillBlockTypes($(this).val());
            filterAttributesByType(null);
        });

        $typeSelect.on('change', function () {
            filterAttributesByType($(this).val());
        });

        filterAttributesByType(null);
    })();
</script>
@endpush
