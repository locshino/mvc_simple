<!-- Sidebar -->
<nav class="col-md-3 col-lg-2 d-md-block sidebar p-3">
    <h4 class="text-center py-2">{{ $title ?? "Sidebar" }}</h4>
    <ul class="nav flex-column">
        @php
            $request_uri = request()->uri();
        @endphp
        @if (isset($links))
            @foreach ($links as $label => $link)
                <li class="nav-item">
                    <a class="nav-link {{ $link == $request_uri ? 'active-link' : '' }}"
                        href="{{ $link }}">{{ $label }}</a>
                </li>
            @endforeach
        @else
            <li class="nav-item">
                <a class="nav-link active-link" href="{{ env('APP_URL', '/') }}">Trang chủ</a>
            </li>
        @endif
    </ul>
</nav>
