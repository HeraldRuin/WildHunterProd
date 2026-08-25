@php
    $hasHierarchy = !empty($attributeBlocks) && count($attributeBlocks) > 0;
    $assignedAttributeIds = collect();
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
                    <span class="panel-toggle panel-collapse-toggle">{{ __('Collapse') }}</span>
                </div>
                <div class="panel-body">
                    @foreach($blockTypes as $type)
                        @php
                            $typeTranslation = $type->translate(app_get_locale());
                            $typeAttributes = $type->attributes ?? collect();
                        @endphp
                        <div class="@if(!$loop->last) mb20 @endif">
                            <div class="mb10">
                                <strong>{{$typeTranslation->name ?: $type->name}}</strong>
                            </div>
                            <div class="terms-scrollable">
                                @forelse($typeAttributes as $attribute)
                                    @php
                                        $assignedAttributeIds->push($attribute->id);
                                        $translate = $attribute->translate(app_get_locale());
                                        $term = $attribute->terms->first();
                                    @endphp
                                    @if($term)
                                        <label class="term-item">
                                            <input @if(!empty($selected_terms) and $selected_terms->contains($term->id)) checked @endif type="checkbox" name="terms[]" value="{{$term->id}}">
                                            <span class="term-name">{{$translate->name}}</span>
                                        </label>
                                    @endif
                                @empty
                                    <div class="text-muted">{{__('Нет атрибутов')}}</div>
                                @endforelse
                            </div>
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
