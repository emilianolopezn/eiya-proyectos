@props(['name', 'size' => 20])
<svg {{ $attributes->merge(['class' => 'icon']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('grid') <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/> @break
        @case('folder') <path d="M3 7V5a2 2 0 0 1 2-2h5l2 3h7a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/><path d="M3 8h18"/> @break
        @case('users') <circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3m1-16a3 3 0 0 1 0 6m5 10v-3a6 6 0 0 0-4-5"/> @break
        @case('activity') <path d="M3 12h4l3-8 4 16 3-8h4"/> @break
        @case('arrow') <path d="M4 12h16m-6-6 6 6-6 6"/> @break
        @case('chevron') <path d="m9 5 7 7-7 7"/> @break
        @case('logout') <path d="M9 4H4v16h5m6-14 6 6-6 6M9 12h12"/> @break
        @case('eye') <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/> @break
        @case('lock') <rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2"/> @break
        @case('check') <path d="m5 12 4 4L19 6"/> @break
        @case('clock') <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/> @break
        @case('calendar') <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 11h18m-13 5h1m5 0h1"/> @break
        @case('search') <circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/> @break
        @case('chart') <path d="M4 20V4m0 16h16M8 15l4-5 4 2 5-7"/> @break
        @case('close') <path d="m6 6 12 12M6 18 18 6"/> @break
        @case('menu') <path d="M4 6h16M4 12h16M4 18h16"/> @break
        @case('info') <circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10h.01"/> @break
    @endswitch
</svg>
