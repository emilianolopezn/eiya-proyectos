@props(['client'])
<span {{ $attributes->class(['client-logo']) }}>
    @if($client->logo_path)<img src="{{ route('clients.logo', $client) }}" alt="Logo de {{ $client->name }}" loading="lazy">@else<span aria-label="Logo pendiente">{{ mb_strtoupper(mb_substr($client->name, 0, 1)) }}</span>@endif
</span>
