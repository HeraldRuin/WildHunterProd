@php
    $hasHierarchy = !empty($attributeBlocks) && count($attributeBlocks) > 0;
    $assignedAttributeIds = collect();
    $bathSaunaDetails = $bath_sauna_details ?? [];
@endphp

@if($hasHierarchy)
    @foreach($attributeBlocks as $block)
        @php
            $blockTranslation = $block->translate(app_get_locale());
            $blockName = $blockTranslation->name ?: $block->name;
            $blockTypes = $block->types ?? collect();
        @endphp
        @if($blockTypes->count())
            <div class="panel">
                <div class="panel-title">
                    <strong>{{$blockName}}</strong>
                    <span class="panel-toggle panel-collapse-toggle panel-collapse-toggle-icon" title="{{ __('Collapse') }}">
                        <i class="fa fa-chevron-up"></i>
                    </span>
                </div>
                <div class="panel-body">
                    @foreach($blockTypes as $type)
                        @php
                            $typeTranslation = $type->translate(app_get_locale());
                            $typeName = $typeTranslation->name ?: $type->name;
                            $typeAttributes = $type->attributes ?? collect();
                            $isBathSaunaType = $typeName === 'Бани и сауны';
                            $bathSaunaAnyChecked = false;
                            if ($isBathSaunaType) {
                                foreach ($typeAttributes as $attribute) {
                                    $term = $attribute->terms->first();
                                    if ($term && !empty($selected_terms) && $selected_terms->contains($term->id)) {
                                        $bathSaunaAnyChecked = true;
                                        break;
                                    }
                                }
                            }
                        @endphp
                        <div class="@if(!$loop->last) mb20 @endif @if($isBathSaunaType) bath-sauna-group @endif">
                            <div class="mb10">
                                <strong>{{$typeName}}</strong>
                            </div>
                            <div class="terms-scrollable">
                                @forelse($typeAttributes as $attribute)
                                    @php
                                        $assignedAttributeIds->push($attribute->id);
                                        $translate = $attribute->translate(app_get_locale());
                                        $term = $attribute->terms->first();
                                    @endphp
                                    @if($term)
                                        @php
                                            $isChecked = !empty($selected_terms) && $selected_terms->contains($term->id);
                                        @endphp
                                        <label class="term-item">
                                            <input
                                                @if($isBathSaunaType) class="js-bath-sauna-term" @endif
                                                @if($isChecked) checked @endif
                                                type="checkbox"
                                                name="terms[]"
                                                value="{{$term->id}}"
                                            >
                                            <span class="term-name">{{$translate->name}}</span>
                                        </label>
                                        @if($isBathSaunaType)
                                            <input type="hidden" name="bath_sauna_term_ids[]" value="{{$term->id}}">
                                        @endif
                                    @endif
                                @empty
                                    <div class="text-muted">{{__('Нет атрибутов')}}</div>
                                @endforelse
                            </div>
                            @if($isBathSaunaType)
                                <input type="hidden" name="bath_sauna_block_type_id" value="{{$type->id}}">
                                <div class="term-bath-sauna-fields" @if(!$bathSaunaAnyChecked) style="display:none;" @endif>
                                    <div class="form-group mb-2">
                                        <label class="mb-1">{{__('Вместимость (макс. количество человек одновременно)')}}</label>
                                        <input
                                            type="number"
                                            min="1"
                                            class="form-control"
                                            name="bath_sauna_details[capacity]"
                                            value="{{$bathSaunaDetails['capacity'] ?? ''}}"
                                        >
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="mb-1">{{__('Тип оплаты')}}</label>
                                        <div>
                                            <label class="d-block mb-1">
                                                <input
                                                    type="radio"
                                                    name="bath_sauna_details[payment_type]"
                                                    value="included"
                                                    @if(($bathSaunaDetails['payment_type'] ?? '') === 'included') checked @endif
                                                >
                                                {{__('Включено в стоимость')}}
                                            </label>
                                            <label class="d-block">
                                                <input
                                                    type="radio"
                                                    name="bath_sauna_details[payment_type]"
                                                    value="paid_separately"
                                                    @if(($bathSaunaDetails['payment_type'] ?? '') === 'paid_separately') checked @endif
                                                >
                                                {{__('Оплачивается отдельно')}}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="mb-1">{{__('Стоимость (руб./час)')}}</label>
                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="form-control"
                                            name="bath_sauna_details[price_per_hour]"
                                            value="{{$bathSaunaDetails['price_per_hour'] ?? ''}}"
                                        >
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="mb-1">{{__('Минимальный заказ (от скольки часов)')}}</label>
                                        <input
                                            type="number"
                                            min="1"
                                            class="form-control"
                                            name="bath_sauna_details[min_hours]"
                                            value="{{$bathSaunaDetails['min_hours'] ?? ''}}"
                                        >
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
@endif

@php
    $unassignedAttributes = collect($attributes ?? [])->filter(function ($attribute) use ($assignedAttributeIds) {
        return empty($attribute->block_type_id) || !$assignedAttributeIds->contains($attribute->id);
    });
@endphp

@foreach($unassignedAttributes as $attribute)
    @php $translate = $attribute->translate(app_get_locale()); @endphp
    <div class="panel">
        <div class="panel-title"><strong>{{$translate->name}}</strong></div>
        <div class="panel-body">
            <div class="terms-scrollable">
                @foreach($attribute->terms as $term)
                    @php $term_translate = $term->translate(app_get_locale()); @endphp
                    <label class="term-item">
                        <input @if(!empty($selected_terms) and $selected_terms->contains($term->id)) checked @endif type="checkbox" name="terms[]" value="{{$term->id}}">
                        <span class="term-name">{{$term_translate->name}}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>
@endforeach

@push('js')
<script>
    jQuery(function ($) {
        function syncBathSaunaGroup($group) {
            var anyChecked = $group.find('.js-bath-sauna-term:checked').length > 0;
            $group.find('.term-bath-sauna-fields').toggle(anyChecked);
        }

        $(document).on('change', '.js-bath-sauna-term', function () {
            syncBathSaunaGroup($(this).closest('.bath-sauna-group'));
        });

        $('.bath-sauna-group').each(function () {
            syncBathSaunaGroup($(this));
        });
    });
</script>
@endpush

@push('css')
<style>
    .bath-sauna-group .term-bath-sauna-fields {
        margin: 10px 0 0;
        padding: 10px;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 4px;
    }
    .bath-sauna-group .term-bath-sauna-fields .form-group {
        margin-bottom: 8px;
    }
    .bath-sauna-group .term-bath-sauna-fields > .form-group > label.mb-1 {
        display: block;
        font-weight: 500;
        font-size: 12px;
    }
    .bath-sauna-group .term-bath-sauna-fields label.d-block {
        font-weight: 400;
        font-size: 12px;
    }
</style>
@endpush
