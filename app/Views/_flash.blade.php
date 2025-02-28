<!-- Hiển thị thông báo lỗi -->
@if (hasFlash('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        @foreach (getFlash('error') as $error)
            <p><strong>Lỗi:</strong> {{ $error }}</p>
        @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Hiển thị thông báo thành công -->
@if (hasFlash('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        @foreach (getFlash('success') as $success)
            <p><strong>Thành công:</strong> {{ $success }}</p>
        @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
