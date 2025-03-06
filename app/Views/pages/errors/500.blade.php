@extends('layouts.error')
@section('title', '500 - Lỗi hệ thống')

@section('content')
    <div class="error-wrapper">
        <div class="error-code">500</div>
        <h1 class="error-message">{{ $message ?? 'Có lỗi xảy ra trên hệ thống!' }}</h1>
        <p>Xin lỗi, chúng tôi đang khắc phục vấn đề. Vui lòng thử lại sau.</p>

        @if (isset($error) && env('APP_DEBUG', false))
            <div class="debug-info">
                <strong>Chi tiết lỗi:</strong><br>
                Lỗi: {{ $error }}<br>
                File: {{ $file }}<br>
                Dòng: {{ $line }}
            </div>
        @endif

        <a href="/" class="btn btn-danger mt-3">Trang chủ</a>
    </div>
@endsection

@push('styles')
    <style>
        body {
            background-color: #212529;
            color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-wrapper {
            background: #343a40;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            max-width: 700px;
        }

        .error-code {
            font-size: 100px;
            color: #dc3545;
            font-weight: bold;
        }

        .error-message {
            font-size: 22px;
        }

        .debug-info {
            background: #495057;
            padding: 10px;
            border-radius: 5px;
            text-align: left;
            font-size: 14px;
            margin-top: 20px;
        }
    </style>
@endpush
