@extends('layouts.user')
@section('content')
    <h2 class="title-bar no-border-bottom">
        {{ __("Admin Base") }}
    </h2>
    @include('admin.message')

    <div class="row">
        @foreach($base_admins as $base_admin)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="admin-card p-3 text-center shadow-sm rounded">
                    <div class="admin-avatar mb-3">
                        <img src="{{ $base_admin['avatar_url'] ?? 'https://via.placeholder.com/80' }}"
                             class="rounded-circle" width="80" height="80">
                    </div>
                    <h5 class="admin-name mb-1">{{ $base_admin['name'] }}</h5>
                    <p class="admin-email mb-1 text-muted">{{ $base_admin['email'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <style>
        .admin-card {
            background-color: #fff;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .admin-avatar img {
            object-fit: cover;
        }
        .admin-name {
            font-weight: 600;
        }
        .admin-email {
            font-size: 0.9rem;
        }
    </style>
@endsection
