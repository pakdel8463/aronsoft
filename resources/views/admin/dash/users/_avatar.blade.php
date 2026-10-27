@php($size = $size ?? 'size-10 text-sm')
@php($url = $user->profile?->avatar_url)
@if ($url)
    <img src="{{ $url }}" alt="" loading="lazy" class="{{ $size }} shrink-0 rounded-full object-cover ring-2 ring-white shadow-adm">
@else
    @php($tones = ['bg-aron-100 text-aron-700', 'bg-mint-100 text-mint-700', 'bg-amber-100 text-amber-700', 'bg-coral-100 text-coral-700', 'bg-violet-100 text-violet-700', 'bg-sky-100 text-sky-700'])
    <span class="{{ $size }} {{ $tones[$user->id % count($tones)] }} grid shrink-0 place-items-center rounded-full font-extrabold">{{ mb_substr($user->name, 0, 1) }}</span>
@endif
