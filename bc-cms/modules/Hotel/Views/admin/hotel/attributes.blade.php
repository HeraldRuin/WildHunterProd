@if(!empty($attributeBlocks) && count($attributeBlocks))
    @foreach($attributeBlocks as $block)
        @php
            $blockTranslation = $block->translate(app_get_locale());
            $blockTypes = $block->types;
        @endphp
        @if(!empty($blockTypes) && count($blockTypes))
            <div class="panel">
                <div class="panel-title"><strong>{{$blockTranslation->name ?: $block->name}}</strong></div>
                <div class="panel-body">
                    @foreach($blockTypes as $type)
                        @php
                            $typeTranslation = $type->translate(app_get_locale());
                            $typeAttributes = $type->attributes;
                        @endphp
                        @if(!empty($typeAttributes) && count($typeAttributes))
                            <div class="mb20">
                                <div class="mb10"><strong>{{$typeTranslation->name ?: $type->name}}</strong></div>
                                @foreach($typeAttributes as $attribute)
                                    @php $translate = $attribute->translate(app_get_locale()); @endphp
                                    <div class="mb15">
                                        <div><strong>{{$translate->name}}</strong></div>
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
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
@else
    @foreach ($attributes as $attribute)
        @php $translate = $attribute->translate(app_get_locale()); @endphp
        <div class="panel">
            <div class="panel-title"><strong>{{__('Attribute: :name',['name'=>$translate->name])}}</strong></div>
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
@endif
