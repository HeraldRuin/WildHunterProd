@php $showGallerySocial = true; @endphp
@foreach ($row->getTypedGalleries() as $gallery)
    @if (!empty($gallery['items']))
        <div class="g-gallery hotel-typed-gallery" data-gallery-type="{{ $gallery['type'] }}">
            <h3>{{ $gallery['label'] }}</h3>
            <div class="fotorama" data-width="100%" data-thumbwidth="135" data-thumbheight="135" data-thumbmargin="15" data-nav="thumbs" data-allowfullscreen="true">
                @foreach ($gallery['items'] as $item)
                    <a href="{{ $item['large'] }}" data-thumb="{{ $item['thumb'] }}" data-alt="{{ $gallery['label'] }}"></a>
                @endforeach
            </div>
            @if ($showGallerySocial)
                @php $showGallerySocial = false; @endphp
                <div class="social">
                    <div class="social-share">
                        <span class="social-icon">
                            <i class="icofont-share"></i>
                        </span>
                        <ul class="share-wrapper">
                            <li>
                                <a class="facebook" href="https://www.facebook.com/sharer/sharer.php?u={{ $row->getDetailUrl() }}&amp;title={{ $translation->title }}" target="_blank" rel="noopener" original-title="{{ __('Facebook') }}">
                                    <i class="fa fa-facebook fa-lg"></i>
                                </a>
                            </li>
                            <li>
                                <a class="twitter" href="https://twitter.com/share?url={{ $row->getDetailUrl() }}&amp;title={{ $translation->title }}" target="_blank" rel="noopener" original-title="{{ __('Twitter') }}">
                                    <i class="fa fa-twitter fa-lg"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @if (method_exists($row, 'isWishList'))
                        <div class="service-wishlist {{ $row->isWishList() }}" data-id="{{ $row->id }}" data-type="{{ $row->type }}">
                            <i class="fa fa-heart-o"></i>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif
@endforeach
