@props(['suave' => false])
@php $f = "'Nunito','Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif"; @endphp
<p class="{{ $suave ? 'dm-suave' : 'dm-texto' }}" style="margin:0 0 16px 0;font-family:{{ $f }};font-size:{{ $suave ? 14 : 16 }}px;line-height:24px;color:{{ $suave ? '#566d76' : '#35505a' }};">{{ $slot }}</p>
