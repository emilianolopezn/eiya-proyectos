@extends('layouts.app')
@section('title', 'Iniciar sesión')
@section('body-class', 'login-page')
@section('content')
<main class="login-shell" id="main">
    <section class="login-panel" aria-labelledby="login-heading">
        <a class="login-brand" href="{{ route('login') }}" aria-label="EIYA Estrategia, inicio"><x-brand /></a>
        <div class="login-content">
            <div class="eyebrow"><span class="tiny-line"></span> ESPACIO DE TRABAJO</div>
            <h1 id="login-heading">Cada transformación<br>comienza aquí.</h1>
            <p class="login-intro">Bienvenido a EIYA Proyectos. Un espacio para<br class="desktop-break"> dar dirección y seguimiento a lo que construimos.</p>
            <form method="POST" action="{{ route('login.store') }}" class="login-form">
                @csrf
                <div class="field">
                    <label for="email">Correo electrónico</label>
                    <input id="email" name="email" type="email" placeholder="nombre@eiya.local" value="{{ old('email') }}" required autofocus autocomplete="username" inputmode="email" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                    @error('email')<p class="field-error" id="email-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="password">Contraseña</label>
                    <div class="password-field">
                        <input id="password" name="password" type="password" placeholder="Escribe tu contraseña" required autocomplete="current-password" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                        <button class="password-toggle" type="button" aria-label="Mostrar contraseña" aria-pressed="false" data-password-toggle><x-icon name="eye" /></button>
                    </div>
                    @error('password')<p class="field-error" id="password-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <label class="remember"><input type="checkbox" name="remember" value="1" @checked(old('remember'))><span>Mantener mi sesión iniciada</span></label>
                <button type="submit" class="button button-dark login-submit"><span>Iniciar sesión</span><x-icon name="arrow" /></button>
                <p class="secure-note"><x-icon name="lock" :size="13" /> Acceso privado para el equipo EIYA</p>
            </form>
        </div>
        <footer class="login-footer"><span>© {{ now()->year }} EIYA Estrategia</span><span>Imagine. Structure. Transform.</span></footer>
    </section>
    <section class="login-visual" aria-label="Paisaje de montañas minerales">
        <img class="login-landscape" src="{{ asset('images/brand/eiya-mountains.png') }}" alt="Cumbres de roca y cordilleras entre la niebla bajo la luz de la mañana" fetchpriority="high">
        <div class="visual-top"><span>ESTRATEGIA · PERSONAS · EVOLUCIÓN</span><span>01 / EIYA</span></div>
        <div class="visual-copy"><span class="eyebrow">UNA MIRADA HACIA LO QUE SIGUE</span><h2>Imaginar el futuro.<br>Construir el camino.</h2><p>Comprender. Estructurar. Implementar. Evolucionar.</p></div>
        <div class="visual-bottom"><span>TRANSFORMAR CON PROPÓSITO.</span><span class="compass-mark">N <span>↗</span></span></div>
    </section>
</main>
@endsection
