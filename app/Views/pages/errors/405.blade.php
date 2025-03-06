@extends('layouts.error')
@section('title', '405 - Phương thức không được phép')

@section('content')
    <div class="error-box">
        <div class="error-code">405</div>
        <h1 class="error-message">{{ $message ?? 'Phương thức không được hỗ trợ!' }}</h1>
        <p class="mt-2">Yêu cầu của bạn không được phép thực hiện trên trang này.</p>
        <a href="javascript:history.back()" class="btn btn-warning mt-3">Quay lại</a>
    </div>
@endsection

@push('styles')
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
        }
        .error-code {
            font-size: 80px;
            color: #ff9800;
            font-weight: bold;
        }
        .error-message {
            font-size: 20px;
            color: #495057;
        }
    </style>
@endpush
