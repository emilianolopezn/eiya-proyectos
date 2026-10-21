@props(['title' => 'Clientes', 'demo' => false])
<div class="app-shell">
    <button type="button" class="sidebar-backdrop" data-sidebar-close aria-label="Cerrar menú"></button>
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('dashboard') }}" class="sidebar-brand" aria-label="EIYA Estrategia, panorama general"><x-brand :light="true" /></a>
        <div class="workspace-label"><span class="status-dot"></span> PERSONAS Y PROPÓSITO</div>
        <div class="nav-caption">ESPACIO DE TRABAJO</div>
        <nav class="main-nav" aria-label="Navegación principal">
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif><x-icon name="grid" /><span>Panorama general</span></a>
            <a href="{{ route('clients.index') }}" class="nav-item {{ request()->routeIs('clients.*', 'contacts.*') ? 'active' : '' }}" @if(request()->routeIs('clients.*', 'contacts.*')) aria-current="page" @endif><x-icon name="users" /><span>Clientes</span></a>
        </nav>
        <div class="sidebar-bottom">
            <div class="sidebar-manifesto"><img src="{{ asset('images/brand/symbol-0.png') }}" alt="" width="32" height="33"><p>Imagine.<br>Structure.<br><em>Transform.</em></p><span>El valor está en el camino.</span></div>
            <div class="sidebar-user"><span class="avatar user-avatar">AD</span><div><strong>{{ auth()->user()->name }}</strong><small>Equipo EIYA</small></div><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="icon-button logout-button" aria-label="Cerrar sesión" title="Cerrar sesión"><x-icon name="logout" :size="18" /></button></form></div>
        </div>
    </aside>
    <div class="workspace">
        <header class="topbar"><div class="breadcrumb"><button type="button" class="icon-button mobile-menu" data-sidebar-open aria-label="Abrir menú" aria-controls="sidebar" aria-expanded="false"><x-icon name="menu" /></button><span>Espacio de trabajo</span><x-icon name="chevron" :size="12" /><strong>{{ $title }}</strong></div><div class="topbar-right">@if($demo)<span class="demo-pill"><span></span> Vista de demostración</span>@else<span class="human-topbar"><span class="status-dot"></span> El valor está en las personas</span>@endif<span class="topbar-avatar" title="{{ auth()->user()->name }}">{{ mb_substr(auth()->user()->name, 0, 1) }}</span></div></header>
        <main id="main" class="dashboard-content {{ $demo ? '' : 'clients-content' }}">
            @if(session('status'))<div class="save-message" role="status"><x-icon name="check" :size="18" /><p>{{ session('status') }}</p></div>@endif
            {{ $slot }}
            @unless($demo)<footer class="dashboard-footer"><span>© {{ now()->year }} EIYA Estrategia · Clientes</span><span>Cuidar el vínculo también es parte del trabajo.</span></footer>@endunless
        </main>
    </div>
</div>
