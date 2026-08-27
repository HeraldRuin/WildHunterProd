@php
    $hasHierarchy = !empty($attributeBlocks) && count($attributeBlocks) > 0;
    $assignedAttributeIds = collect();
    $bathSaunaDetails = $bath_sauna_details ?? [];
    $fontVatDetails = $font_vat_details ?? [];
    $mealPlanPrices = collect(($meal_plan_details ?? [])['prices'] ?? []);
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
                            $isFontVatType = $typeName === 'Купели и чаны';
                            $isMealPlanType = $typeName === 'Доступные тарифы питания';
                            $hasTypeExtras = $isBathSaunaType || $isFontVatType;
                            $typeExtrasAnyChecked = false;
                            if ($hasTypeExtras) {
                                foreach ($typeAttributes as $attribute) {
                                    $term = $attribute->terms->first();
                                    if ($term && !empty($selected_terms) && $selected_terms->contains($term->id)) {
                                        $typeExtrasAnyChecked = true;
                                        break;
                                    }
                                }
                            }
                            $typeExtraPrefix = $isBathSaunaType ? 'bath_sauna' : ($isFontVatType ? 'font_vat' : null);
                        @endphp
                        <div class="@if(!$loop->last) mb20 @endif @if($hasTypeExtras) attr-type-extra-group @endif @if($isMealPlanType) meal-plan-group @endif">
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
                                            $attrName = $translate->name ?: $attribute->name;
                                            $isMealPlanPriced = $isMealPlanType && $attrName !== 'Без питания';
                                            $termDetails = $mealPlanPrices->get((string) $term->id, $mealPlanPrices->get((int) $term->id, []));
                                        @endphp
                                        @if($isMealPlanPriced)
                                            <div class="term-with-extra">
                                                <label class="term-item">
                                                    <input
                                                        class="js-meal-plan-term"
                                                        @if($isChecked) checked @endif
                                                        type="checkbox"
                                                        name="terms[]"
                                                        value="{{$term->id}}"
                                                    >
                                                    <span class="term-name">{{$attrName}}</span>
                                                </label>
                                                <input type="hidden" name="meal_plan_term_ids[]" value="{{$term->id}}">
                                                <div class="term-extra-fields" @if(!$isChecked) style="display:none;" @endif>
                                                    <div class="form-group mb-2">
                                                        <label class="mb-1">{{__('Добавить стоимость за одного человека')}}</label>
                                                        <input
                                                            type="number"
                                                            min="0"
                                                            step="0.01"
                                                            class="form-control"
                                                            name="meal_plan_details[{{$term->id}}][price_per_person]"
                                                            value="{{$termDetails['price_per_person'] ?? ''}}"
                                                        >
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <label class="term-item">
                                                <input
                                                    class="@if($isMealPlanType) js-meal-plan-none @endif @if($hasTypeExtras) js-attr-type-extra-term @endif"
                                                    @if($isChecked) checked @endif
                                                    type="checkbox"
                                                    name="terms[]"
                                                    value="{{$term->id}}"
                                                >
                                                <span class="term-name">{{$attrName}}</span>
                                            </label>
                                            @if($hasTypeExtras)
                                                <input type="hidden" name="{{$typeExtraPrefix}}_term_ids[]" value="{{$term->id}}">
                                            @endif
                                        @endif
                                    @endif
                                @empty
                                    <div class="text-muted">{{__('Нет атрибутов')}}</div>
                                @endforelse
                            </div>
                            @if($isMealPlanType)
                                <input type="hidden" name="meal_plan_block_type_id" value="{{$type->id}}">
                            @endif
                            @if($isBathSaunaType)
                                <input type="hidden" name="bath_sauna_block_type_id" value="{{$type->id}}">
                                <div class="attr-type-extra-fields" @if(!$typeExtrasAnyChecked) style="display:none;" @endif>
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
                            @if($isFontVatType)
                                <input type="hidden" name="font_vat_block_type_id" value="{{$type->id}}">
                                <div class="attr-type-extra-fields" @if(!$typeExtrasAnyChecked) style="display:none;" @endif>
                                    <div class="form-group mb-2">
                                        <label class="mb-1">{{__('Расположение')}}</label>
                                        <div>
                                            <label class="d-block mb-1">
                                                <input
                                                    type="radio"
                                                    name="font_vat_details[placement]"
                                                    value="individual"
                                                    @if(($fontVatDetails['placement'] ?? '') === 'individual') checked @endif
                                                >
                                                {{__('Индивидуальный у каждого дома')}}
                                            </label>
                                            <label class="d-block">
                                                <input
                                                    type="radio"
                                                    name="font_vat_details[placement]"
                                                    value="shared"
                                                    @if(($fontVatDetails['placement'] ?? '') === 'shared') checked @endif
                                                >
                                                {{__('Общий на территории')}}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="mb-1">{{__('Цена за одну топку/сеанс')}}</label>
                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="form-control"
                                            name="font_vat_details[price_per_session]"
                                            value="{{$fontVatDetails['price_per_session'] ?? ''}}"
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
        function syncAttrTypeExtraGroup($group) {
            var anyChecked = $group.find('.js-attr-type-extra-term:checked').length > 0;
            $group.find('.attr-type-extra-fields').toggle(anyChecked);
        }

        function syncMealPlanTerm($checkbox) {
            var checked = $checkbox.is(':checked');
            $checkbox.closest('.term-with-extra').find('.term-extra-fields').toggle(checked);
        }

        function clearOtherMealPlans($group) {
            $group.find('.js-meal-plan-term:checked').each(function () {
                $(this).prop('checked', false);
                syncMealPlanTerm($(this));
            });
        }

        $(document).on('change', '.js-attr-type-extra-term', function () {
            syncAttrTypeExtraGroup($(this).closest('.attr-type-extra-group'));
        });

        $(document).on('change', '.js-meal-plan-none', function () {
            var $group = $(this).closest('.meal-plan-group');
            if ($(this).is(':checked')) {
                clearOtherMealPlans($group);
            }
        });

        $(document).on('change', '.js-meal-plan-term', function () {
            var $group = $(this).closest('.meal-plan-group');
            if ($(this).is(':checked')) {
                $group.find('.js-meal-plan-none').prop('checked', false);
            }
            syncMealPlanTerm($(this));
        });

        $('.attr-type-extra-group').each(function () {
            syncAttrTypeExtraGroup($(this));
        });

        $('.js-meal-plan-term').each(function () {
            syncMealPlanTerm($(this));
        });
    });
</script>
@endpush

@push('css')
<style>
    .attr-type-extra-group .attr-type-extra-fields,
    .term-with-extra .term-extra-fields {
        margin: 10px 0 0;
        padding: 10px;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 4px;
    }
    .term-with-extra {
        display: block;
        width: 100%;
        margin-bottom: 8px;
    }
    .attr-type-extra-group .attr-type-extra-fields .form-group,
    .term-with-extra .term-extra-fields .form-group {
        margin-bottom: 8px;
    }
    .attr-type-extra-group .attr-type-extra-fields > .form-group > label.mb-1,
    .term-with-extra .term-extra-fields > .form-group > label.mb-1 {
        display: block;
        font-weight: 500;
        font-size: 12px;
    }
    .attr-type-extra-group .attr-type-extra-fields label.d-block {
        font-weight: 400;
        font-size: 12px;
    }
</style>
@endpush
