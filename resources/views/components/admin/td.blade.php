@props(['muted' => false, 'nowrap' => false])
<td {{ $attributes->class(['text-slate-400' => $muted, 'whitespace-nowrap' => $nowrap]) }}>{{ $slot }}</td>
