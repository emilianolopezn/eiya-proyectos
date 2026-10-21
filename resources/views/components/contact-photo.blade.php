@props(['client', 'contact'])
<span {{ $attributes->class(['contact-photo', 'photo-pending' => !$contact->photo_path]) }}>
    @if($contact->photo_path)<img src="{{ route('contacts.photo', [$client, $contact]) }}" alt="Foto de {{ $contact->name }}" loading="lazy">@else<x-icon name="camera" :size="20" /><span class="sr-only">Foto pendiente de {{ $contact->name }}</span>@endif
</span>
