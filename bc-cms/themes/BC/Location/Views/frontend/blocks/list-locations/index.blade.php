<div class="container">
    <div class="bc-list-locations @if(!empty($layout)) {{ $layout }} @endif">
        <div class="title">
            {{$title}}
        </div>
        @if(!empty($desc))
            <div class="sub-title">
                {{$desc}}
            </div>
        @endif
        @if(!empty($rows))
            <div class="list-item">
                @if(!empty($layout) and in_array($layout, ['style_2', 'style_3', 'style_4']))
                    <div class="owl-carousel">
                        @foreach($rows as $key=>$row)
                            @include('Location::frontend.blocks.list-locations.loop')
                        @endforeach
                    </div>
                @else
                    <div class="row">
                        @foreach($rows as $key=>$row)
                            <?php
                            $size_col = 4;
                            if($key == 0){
                                $size_col = 8;
                            }
                            ?>
                            <div class="col-lg-{{$size_col}} col-md-6">
                                @include('Location::frontend.blocks.list-locations.loop')
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
