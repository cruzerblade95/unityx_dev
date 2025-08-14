@php
    $sidebarMenu = json_decode(file_get_contents(resource_path('views/user/menu/sidebarMenu.json')));
@endphp
<aside class="sidebar">
    <ul class="sidebar-menu">
        @foreach($sidebarMenu as $item)
            <li>
                <a href="{{ url($item->url) }}">
                    <i class="{{ $item->icon }}"></i> {{ $item->title }}
                </a>
            </li>
        @endforeach
    </ul>
</aside>
