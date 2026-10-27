<!-- reports -->
<section id="sec-reports" style="background:#fff;border-top:1px solid #D8E1E5;border-bottom:1px solid #D8E1E5">
  <div id="reports" style="max-width:1200px;margin:0 auto;padding:96px 32px">
    <div style="max-width:620px;margin-bottom:40px">
      @if(trim($s['title'] ?? '') !== '')<h2 style="margin:0 0 18px;font-size:clamp(28px,3vw,38px);line-height:1.5;font-weight:800">{{ $s['title'] }}</h2>@endif
      @if(trim($s['text'] ?? '') !== '')<p style="margin:0;font-size:17px;line-height:2;color:#43535E">{{ $s['text'] }}</p>@endif
    </div>
    <div style="border:1px solid #D8E1E5;border-radius:20px;overflow:hidden;overflow-x:auto">
      <table class="rp" style="min-width:640px">
        @if(!empty($s['columns']))
        <thead><tr>@foreach($s['columns'] as $c)<th scope="col">{{ $c }}</th>@endforeach</tr></thead>
        @endif
        <tbody>
          @foreach($s['rows'] ?? [] as $r)
          <tr><td style="font-weight:700">{{ $r['name'] ?? '' }}</td>@foreach($r['cells'] ?? [] as $c)<td>@if(str_starts_with($c, '!'))<span style="background:#FCEBCF;color:#8A5410;padding:3px 10px;border-radius:7px;font-weight:700;font-size:13px">{{ ltrim(mb_substr($c, 1)) }}</span>@else{{ $c }}@endif</td>@endforeach</tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</section>
