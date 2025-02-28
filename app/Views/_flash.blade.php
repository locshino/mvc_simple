<!-- Hiển thị thông báo lỗi -->
@if (hasFlash('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        @foreach ((array) getFlash('error') as $key => $error)
            @if (is_array($error))
                <!-- Mảng đa chiều: Lặp qua các lỗi của field -->
                @foreach ($error as $message)
                    <p><strong>Lỗi{{ is_numeric($key) ? '' : " $key" }}:</strong> {{ $message }}</p>
                @endforeach
            @else
                <!-- Mảng một chiều hoặc chuỗi -->
                <p><strong>Lỗi:</strong> {{ $error }}</p>
            @endif
        @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Hiển thị thông báo thành công -->
@if (hasFlash('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        @foreach ((array) getFlash('success') as $key => $success)
            @if (is_array($success))
                <!-- Mảng đa chiều: Lặp qua các thông báo của field -->
                @foreach ($success as $message)
                    <p><strong>Thành công{{ is_numeric($key) ? '' : " $key" }}:</strong> {{ $message }}</p>
                @endforeach
            @else
                <!-- Mảng một chiều hoặc chuỗi -->
                <p><strong>Thành công:</strong> {{ $success }}</p>
            @endif
        @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
