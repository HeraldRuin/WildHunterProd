@extends('admin.layouts.app')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between mb20">
                    <div class="">
                        <h1 class="title-bar">{{$row->id ? __('Edit: ').$row->name : __('Add new attribute')}}</h1>
                    </div>
                </div>
                @include('admin.message')
                @if($row->id)
                    @include('Language::admin.navigation')
                @endif
                <div class="lang-content-box">
                    <form action="{{route('hotel.admin.attribute.store',['id'=>($row->id) ? $row->id : '-1','lang'=>request()->query('lang')])}}" method="post">
                        @csrf
                        <input type="hidden" name="id" value="{{$row->id}}">
                        <div class="panel">
                            <div class="panel-title">{{__('Блок атрибутов')}}</div>
                            <div class="panel-body">
                                <select name="form_block_id" id="attr-form-block" class="form-control">
                                    <option value="" disabled @if(empty($selectedBlockId)) selected @endif hidden>{{__('Выберите главный блок')}}</option>
                                    @foreach(($blocks ?? []) as $block)
                                        @php $blockTranslate = $block->translate(app_get_locale()); @endphp
                                        <option value="{{$block->id}}" @if(!empty($selectedBlockId) && $selectedBlockId == $block->id) selected @endif>
                                            {{$blockTranslate->name ?: $block->name}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="panel">
                            <div class="panel-title">{{__('Тип атрибутов')}}</div>
                            <div class="panel-body">
                                <select name="block_type_id" id="attr-form-block-type" class="form-control">
                                    <option value="" disabled @if(empty($row->block_type_id)) selected @endif hidden>{{__('Выберите тип атрибутов')}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="panel">
                            <div class="panel-title">
                                <strong>{{__("Attribute Content")}}</strong>
                            </div>
                            <div class="panel-body">
                                @include('Hotel::admin/attribute/form')
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span></span>
                            <button class="btn btn-primary" type="submit" id="attr-form-submit" @if(empty($row->block_type_id) || empty($selectedBlockId)) disabled @endif>{{__("Save Change")}}</button>
                        </div>
                    </form>
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
        var $blockSelect = $('#attr-form-block');
        var $typeSelect = $('#attr-form-block-type');
        var $formSubmit = $('#attr-form-submit');
        var typePlaceholder = @json(__('Выберите тип атрибутов'));
        var selectedTypeId = @json($row->block_type_id ?: null);

        function syncFormSubmitState() {
            var canSubmit = Boolean($blockSelect.val()) && Boolean($typeSelect.val());
            $formSubmit.prop('disabled', !canSubmit);
        }

        function fillFormBlockTypes(blockId, selectedTypeId) {
            $typeSelect.empty();
            $typeSelect.append(
                $('<option>', {
                    value: '',
                    text: typePlaceholder,
                    disabled: true,
                    selected: !selectedTypeId,
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
                            text: item.name,
                            selected: selectedTypeId && String(selectedTypeId) === String(item.id)
                        })
                    );
                });
        }

        $blockSelect.on('change', function () {
            fillFormBlockTypes($(this).val(), null);
            syncFormSubmitState();
        });

        $typeSelect.on('change', function () {
            syncFormSubmitState();
        });

        fillFormBlockTypes($blockSelect.val(), selectedTypeId);
        syncFormSubmitState();
    })();
</script>
@endpush
