@props(['light' => false])
<span {{ $attributes->merge(['class' => 'brand']) }}>
    <img src="{{ asset('images/brand/logo-'.($light ? 'light' : 'dark').'.png') }}" alt="EIYA" width="628" height="237">
    <span class="brand-descriptor">ESTRATEGIA</span>
</span>
