@extends('layouts.error')
@section('title', '404 - Không tìm thấy')

@section('content')
    <div class="error-container">
        <div class="error-code">404</div>
        <h1 class="error-message">{{ $message ?? 'Trang bạn tìm không tồn tại!' }}</h1>
        <p class="mt-3">Có thể bạn đã nhập sai địa chỉ hoặc trang đã bị xóa.</p>
        <a href="/" class="btn btn-primary mt-3">Quay về trang chủ</a>
    </div>
@endsection

@push('styles')
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-container {
            text-align: center;
            padding: 20px;
            max-width: 600px;
        }

        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #dc3545;
        }

        .error-message {
            font-size: 24px;
            color: #6c757d;
        }
    </style>
@endpush
