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
            $typesWithAttributes = $blockTypes->filter(function ($type) {
                return !empty($type->attributes) && count($type->attributes);
            });
        @endphp
        @if($typesWithAttributes->count())
            <div class="mb20">
                <div style="margin-bottom: 12px;"><strong>{{$blockName}}</strong></div>
                @foreach($typesWithAttributes as $type)
                    @php
                        $typeTranslation = $type->translate(app_get_locale());
                        $typeAttributes = $type->attributes;
                    @endphp
                    <div class="panel">
                        <div class="panel-title">
                            <strong>{{$typeTranslation->name ?: $type->name}}</strong>
                        </div>
                        <div class="panel-body">
                            <div class="terms-scrollable">
                                @foreach($typeAttributes as $attribute)
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
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
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
