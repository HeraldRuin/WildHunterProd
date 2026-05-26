@extends('admin.layouts.app')
@section('content')
    <form action="{{route('user.admin.role.save_permissions')}}" method="post">
        @csrf
        <div class="container">
            <div class="d-flex justify-content-between mb20">
                <div class="">
                    <h1 class="title-bar">{{ __('Permission Matrix')}}</h1>
                </div>
            </div>
            @include('admin.message')
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <td><strong>{{ __('Role')}}</strong></td>
                                        @foreach($roles as $role)
                                            <td><strong>{{ucfirst($role->name)}}</strong></td>
                                        @endforeach
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($permissions_group as $group => $items)

                                        <tr>
                                            <td colspan="{{ count($roles) + 1 }}">
                                                <strong>{{ $group }}</strong>
                                            </td>
                                        </tr>

                                        @foreach($items as $item)
                                            <tr>
                                                <td>{{ $item['label'] }}</td>

                                                @foreach($roles as $role)
                                                    <td>
                                                        <input type="checkbox"
                                                               @if(in_array($item['key'], $selectedIds[$role->id]))
                                                                   checked
                                                               @endif
                                                               name="matrix[{{$role->id}}][]"
                                                               value="{{ $item['key'] }}">
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach

                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>&nbsp;</span>
                        <button class="btn btn-primary" type="submit">{{ __('Save Change')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
