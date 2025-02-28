<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Truy cập bị từ chối</title>
    <style>
        body {
            background-color: #e9ecef;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .error {
            text-align: center;
            color: #212529;
        }
        .code {
            font-size: 150px;
            color: #6c757d;
        }
        .msg {
            font-size: 28px;
        }
    </style>
</head>
<body>
    <div class="error">
        <div class="code">403</div>
        <h1 class="msg">{{ $message ?? 'Bạn không có quyền truy cập!' }}</h1>
        <a href="/" style="color: #007bff; text-decoration: none;">Quay về</a>
    </div>

    @include('_flash')
</body>
</html>