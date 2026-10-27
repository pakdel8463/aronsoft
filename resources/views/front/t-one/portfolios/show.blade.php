@php
    use App\Support\CaseStudy;
    use App\View\Seo;

    $img = Seo::img($portfolio->featured_image);
    $techs = Seo::techList($portfolio->technologies);
    $crumbs = [['خانه', url('/')], ['نمونه‌کارها', route('portfolio.index')], [$portfolio->title, null]];
    $creative = [
        '@type' => 'CreativeWork',
        'name' => $portfolio->title,
        'description' => Seo::excerpt($portfolio->seo_description ?: $portfolio->description, 200),
        'url' => url()->current(),
        'inLanguage' => 'fa-IR',
        'creator' => ['@id' => url('/').'#organization'],
    ];
    if ($img) { $creative['image'] = $img; }

    $fa = fn ($v) => strtr((string) $v, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);

    $duration = null;
    if ($portfolio->start_date && $portfolio->end_date && $portfolio->end_date->gte($portfolio->start_date)) {
        $days = $portfolio->start_date->diffInDays($portfolio->end_date);
        $months = intdiv($days, 30);
        $duration = $months >= 1 ? $fa($months).' ماه' : $fa(max($days, 1)).' روز';
    }
    $doneAt = null;
    if ($portfolio->end_date && class_exists(\Morilog\Jalali\Jalalian::class)) {
        try { $doneAt = $fa(\Morilog\Jalali\Jalalian::fromCarbon($portfolio->end_date)->format('F Y')); } catch (\Throwable $e) {}
    }
    $progress = $portfolio->progress;
    $isDone = empty($progress) || (int) $progress >= 100;
    $host = $portfolio->preview_link ? preg_replace('#^www\.#', '', (string) parse_url($portfolio->preview_link, PHP_URL_HOST)) : null;

    $chapters = CaseStudy::chapters($portfolio->description);
    $stage = CaseStudy::accent($portfolio->featured_image, '#2f4ff5', 255);
    [$sr, $sg, $sb] = sscanf($stage, '#%02x%02x%02x');
    $stageInk = (0.2126 * $sr + 0.7152 * $sg + 0.0722 * $sb) > 140 ? '#0c1433' : '#ffffff';

    $gallery = $portfolio->images->map(fn ($im) => Seo::img($im->path ?? $im->image ?? null))->filter()->values();
    $inChapters = min(3, intdiv($gallery->count(), 2));
    $chImg = [];
    $gi = 0;
    foreach ($chapters as $i => $ch) {
        if ($gi < $inChapters && ($ch['prose'] !== '' || $ch['features'])) { $chImg[$i] = $gallery[$gi++]; }
    }
    $restGallery = $gallery->slice($gi)->values();

    $team = method_exists($portfolio, 'teamMembers') ? $portfolio->teamMembers : collect();
    $orderUrl = url('/order');
    $checkSvg = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 10.5l4 4 8-9"/></svg>';
@endphp
@component('front.t-one.layouts.content')
    @slot('head')
        <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lalezar&display=swap">
        {!! Seo::jsonLd([Seo::breadcrumbs(collect($crumbs)->map(fn ($c) => [$c[0], $c[1] ?: url()->current()])->all()), $creative]) !!}
    @endslot

    @slot('topcss')
    <style>
        .pf{--pf-stage:{{ $stage }};--pf-sink:{{ $stageInk }};--pf-fg:#0c1433;--pf-mut:#59648a;--pf-line:#dde2ef;--pf-soft:#eff2fb;--pf-blue:#2f4ff5;--pf-disp:'Lalezar',Vazirmatn,Tahoma,sans-serif;color:var(--pf-fg);line-height:1.9}
        .pf *{box-sizing:border-box}
        .pf h1,.pf h2,.pf h3{font-family:var(--pf-disp);font-weight:400;text-wrap:balance;margin:0;color:var(--pf-fg)}
        .pf a{color:inherit}
        .pf-w{max-width:1180px;margin-inline:auto;padding-inline:1rem}
        .pf-hero{padding-top:2.2rem}
        .pf-crumb{font-size:.82rem;color:var(--pf-mut);display:flex;gap:.5rem;flex-wrap:wrap}
        .pf-crumb a{text-decoration:none}.pf-crumb b{color:var(--pf-fg)}
        .pf-kick{display:inline-block;margin-top:1.6rem;font-weight:800;font-size:.9rem;padding:.2rem .9rem;border-radius:99px;background:var(--pf-stage);color:var(--pf-sink)}
        .pf h1{font-size:clamp(3rem,9.2vw,8rem);line-height:1.12;margin-top:1rem;letter-spacing:-.01em}
        .pf-lead{max-width:38rem;font-size:1.3rem;line-height:2.1;color:var(--pf-mut);margin:1.4rem 0 0;font-weight:500}
        .pf-row{display:flex;flex-wrap:wrap;gap:.8rem;margin-top:2rem}
        .pf-btn{display:inline-flex;align-items:center;gap:.6rem;min-height:54px;padding:0 1.7rem;border-radius:99px;font-weight:800;font-size:.98rem;text-decoration:none;border:2px solid var(--pf-fg);transition:transform .2s;cursor:pointer;background:none;color:var(--pf-fg)}
        .pf-btn:hover{transform:translateY(-2px)}
        .pf-btn.fill{background:var(--pf-fg);color:#fff}
        .pf-btn:focus-visible,.pf a:focus-visible{outline:3px solid var(--pf-blue);outline-offset:3px}
        .pf-meta{display:grid;grid-template-columns:repeat(4,1fr);margin:3rem 0 0;border-top:2px solid var(--pf-fg)}
        .pf-meta div{padding:1rem 0;min-width:0}
        .pf-meta div+div{padding-inline-start:1.4rem;border-inline-start:1px solid var(--pf-line)}
        .pf-meta dt{font-size:.78rem;color:var(--pf-mut);font-weight:700}
        .pf-meta dd{margin:0;font-weight:800;overflow-wrap:anywhere}
        @media(max-width:760px){.pf-meta{grid-template-columns:1fr 1fr}.pf-meta div:nth-child(3){padding-inline-start:0;border-inline-start:0}.pf-meta div:nth-child(n+3){border-top:1px solid var(--pf-line)}}
        .pf-stage{margin-top:3rem;background:var(--pf-stage);border-radius:2rem 2rem 0 0;padding:3.5rem 1.2rem 0;position:relative;background-image:radial-gradient(rgba(12,20,51,.13) 1.4px,transparent 1.4px);background-size:26px 26px}
        .pf-stage:before{content:"";position:absolute;inset:0;border-radius:inherit;background:radial-gradient(60% 80% at 50% 0%,rgba(255,255,255,.4),transparent 70%);pointer-events:none}
        .pf-frame{position:relative;z-index:2;max-width:980px;margin:0 auto -6rem;border-radius:1.1rem;background:#fff;box-shadow:0 60px 80px -40px rgba(12,20,51,.55),0 0 0 1px rgba(12,20,51,.12);overflow:hidden}
        .pf-chrome{height:36px;display:flex;align-items:center;gap:7px;padding:0 15px;background:#eef0f7;border-bottom:1px solid #dfe3ee;direction:ltr}
        .pf-chrome i{width:11px;height:11px;border-radius:50%;background:#ff5f57}.pf-chrome i:nth-child(2){background:#febc2e}.pf-chrome i:nth-child(3){background:#28c840}
        .pf-chrome span{margin-inline:auto;font-size:.75rem;line-height:1;color:#6b7699;background:#fff;border-radius:99px;padding:.35rem 1.4rem;max-width:60%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .pf-frame img{display:block;width:100%;height:auto;aspect-ratio:16/10;object-fit:cover;background:#f3f5fb}
        @media(prefers-reduced-motion:no-preference){.pf-frame{animation:pfRise 1.1s cubic-bezier(.2,.8,.2,1) both .15s}@keyframes pfRise{from{opacity:0;transform:translateY(70px) scale(.97)}to{opacity:1;transform:none}}}
        .pf-after{height:8.5rem}
        .pf-story{display:grid;grid-template-columns:13rem minmax(0,1fr);gap:4rem;align-items:start;padding-bottom:2rem}
        .pf-rail{position:sticky;top:6rem;display:grid;gap:.2rem;font-weight:700;font-size:.95rem}
        .pf-rail p{margin:0 0 .8rem;font-size:.78rem;color:var(--pf-mut)}
        .pf-rail a{display:flex;align-items:center;gap:.8rem;text-decoration:none;color:var(--pf-mut);padding:.45rem 0;transition:color .2s}
        .pf-rail a:before{content:"";flex:none;width:.7rem;height:.7rem;border-radius:50%;border:2px solid var(--pf-line);transition:.25s}
        .pf-rail a.on{color:var(--pf-fg)}.pf-rail a.on:before{background:var(--pf-stage);border-color:var(--pf-fg);transform:scale(1.25)}
        @media(max-width:900px){.pf-story{grid-template-columns:1fr;gap:0}.pf-rail{display:none}}
        .pf-chap{padding-block:3.2rem;border-top:1px solid var(--pf-line);scroll-margin-top:5rem}
        .pf-chap:first-child{border-top:0;padding-top:0}
        .pf-chap h2{font-size:clamp(2.2rem,5vw,3.4rem);line-height:1.4}
        .pf-big{font-size:clamp(1.25rem,2.3vw,1.7rem);line-height:2.1;font-weight:500;margin:1.2rem 0 0;max-width:46rem}
        .pf-rich{max-width:44rem;color:var(--pf-mut);font-size:1.06rem;line-height:2.15}
        .pf-rich p{margin:1.2rem 0 0}
        .pf-rich p:first-child{color:var(--pf-fg);font-weight:500;font-size:clamp(1.25rem,2.3vw,1.7rem);line-height:2.1;margin-top:1.2rem}
        .pf-rich strong{color:var(--pf-fg)}.pf-rich a{color:var(--pf-blue);font-weight:700}
        .pf-rich h3{font-size:1.6rem;margin:2rem 0 .5rem}
        .pf-rich img{max-width:100%;height:auto;border-radius:1rem;margin-top:1.4rem}
        .pf-quote{margin:2.5rem 0 0;max-width:46rem}
        .pf-quote q{display:block;quotes:none;font:400 clamp(1.8rem,3.8vw,2.8rem)/1.8 var(--pf-disp);color:var(--pf-fg)}
        .pf-quote q span{background:linear-gradient(transparent 58%,var(--pf-stage) 58%);padding-inline:.15em;box-decoration-break:clone;-webkit-box-decoration-break:clone}
        .pf-quote cite{display:block;margin-top:1rem;font-style:normal;color:var(--pf-mut);font-weight:700;font-size:.9rem}
        .pf-feat{list-style:none;margin:2rem 0 0;padding:0;display:grid;grid-template-columns:1fr 1fr;column-gap:2.5rem;max-width:48rem}
        .pf-feat li{display:flex;gap:.9rem;align-items:flex-start;padding:1.1rem 0;border-top:1px solid var(--pf-line);font-weight:700}
        .pf-feat svg{flex:none;margin-top:.45rem;width:1.15rem;height:1.15rem}
        @media(max-width:640px){.pf-feat{grid-template-columns:1fr}}
        .pf-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(10rem,1fr));margin-top:2.4rem;border-block:2px solid var(--pf-fg)}
        .pf-stats div{padding:1.6rem 0}
        .pf-stats div+div{padding-inline-start:1.6rem;border-inline-start:1px solid var(--pf-line)}
        .pf-stats b{display:block;font:400 clamp(2.6rem,6vw,4.8rem)/1.2 var(--pf-disp);color:var(--pf-fg)}
        .pf-stats span{font-weight:700;color:var(--pf-mut);font-size:.92rem}
        @media(max-width:640px){.pf-stats div+div{padding-inline-start:0;border-inline-start:0;border-top:1px solid var(--pf-line)}}
        .pf-fig{margin:2.4rem 0 0;max-width:48rem}
        .pf-fig button{display:block;width:100%;padding:0;border:0;cursor:zoom-in;border-radius:1rem;overflow:hidden;background:#fff;box-shadow:0 40px 60px -36px rgba(12,20,51,.45),0 0 0 1px var(--pf-line);transition:transform .4s}
        .pf-fig button:hover{transform:translateY(-4px)}
        .pf-fig img{display:block;width:100%;height:auto}
        .pf-th{display:flex;justify-content:space-between;align-items:baseline;gap:1rem;margin-top:3.5rem;font-weight:800}
        .pf-th small{color:var(--pf-mut);font-weight:500}
        .pf-tech{overflow:hidden;border-block:1px solid var(--pf-line);padding-block:1.4rem;margin-top:1rem;direction:ltr}
        .pf-run{display:flex;gap:2.5rem;width:max-content;font:400 clamp(2.6rem,6vw,4.6rem)/1.1 var(--pf-disp);animation:pfRun 28s linear infinite}
        .pf-run span{display:flex;align-items:center;gap:2.5rem;white-space:nowrap;color:var(--pf-fg)}
        .pf-run span:after{content:"";width:.9rem;height:.9rem;background:var(--pf-stage);border-radius:50%}
        .pf-run span:nth-child(even){-webkit-text-stroke:2px var(--pf-fg);color:transparent}
        @keyframes pfRun{to{transform:translateX(-50%)}}
        @media(prefers-reduced-motion:reduce){.pf-run{animation:none}}
        .pf-team{display:flex;flex-wrap:wrap;gap:1.4rem;margin-top:1.4rem}
        .pf-member{display:flex;align-items:center;gap:.8rem}
        .pf-avatar{width:3rem;height:3rem;border-radius:50%;object-fit:cover;background:var(--pf-soft);display:grid;place-items:center;font-weight:800;flex:none}
        .pf-member b{display:block;line-height:1.5}.pf-member small{color:var(--pf-mut)}
        .pf-tags{display:flex;flex-wrap:wrap;gap:.6rem;margin-top:1.4rem}
        .pf-tags span{padding:.3rem 1rem;border-radius:99px;background:var(--pf-soft);font-weight:700;font-size:.88rem}
        .pf-gal{display:grid;gap:1rem;grid-template-columns:repeat(2,1fr);margin-top:3.5rem}
        @media(min-width:900px){.pf-gal{grid-template-columns:repeat(4,1fr);grid-auto-rows:13rem;grid-auto-flow:dense}.pf-gal .pf-g:first-child{grid-column:span 2;grid-row:span 2}}
        .pf-g{padding:0;border:1px solid var(--pf-line);border-radius:1.1rem;overflow:hidden;background:#f3f5fb;cursor:zoom-in;aspect-ratio:3/2}
        @media(min-width:900px){.pf-g{aspect-ratio:auto}}
        .pf-g img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .5s}.pf-g:hover img{transform:scale(1.05)}
        .pf-lb{border:0;padding:0;background:transparent;max-width:min(94vw,1200px);max-height:92vh;overflow:visible}
        .pf-lb::backdrop{background:rgba(10,16,38,.88)}
        .pf-lb img{display:block;max-width:100%;max-height:88vh;border-radius:1rem}
        .pf-lb button{position:absolute;top:-3rem;inset-inline-end:0;width:2.6rem;height:2.6rem;border-radius:50%;border:0;background:#fff;font-size:1.3rem;cursor:pointer}
        .pf-next{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,26rem);gap:2.5rem;align-items:center;margin-top:5rem;padding-block:2.6rem;border-block:2px solid var(--pf-fg);text-decoration:none}
        .pf-next small{color:var(--pf-mut);font-weight:700}
        .pf-next h3{font-size:clamp(2.2rem,5.4vw,4.4rem);line-height:1.3;margin-top:.4rem;transition:transform .35s}
        .pf-next:hover h3{transform:translateX(-.8rem)}
        .pf-next .th{aspect-ratio:8/5;border-radius:1rem;background:#eff2fb center/cover no-repeat;box-shadow:0 0 0 1px var(--pf-line);transform:rotate(-2deg);transition:transform .35s}
        .pf-next:hover .th{transform:rotate(0) scale(1.03)}
        @media(max-width:800px){.pf-next{grid-template-columns:1fr}}
        .pf-prev{display:inline-block;margin-top:1.2rem;font-weight:700;color:var(--pf-mut);text-decoration:none}
        .pf-rel{padding-top:3rem}
        .pf-cta{margin:4.5rem 0 4rem;background:var(--pf-blue);color:#fff;border-radius:2rem;padding:clamp(1.6rem,5vw,4rem);display:grid;grid-template-columns:1.1fr .9fr;gap:2.5rem;align-items:center}
        .pf-cta h2{color:#fff;font-size:clamp(2.1rem,4.8vw,3.5rem);line-height:1.45}
        .pf-cta p.s{margin:1rem 0 0;max-width:30rem;line-height:2.05;opacity:.92}
        .pf-cta ul{list-style:none;margin:1.4rem 0 0;padding:0;display:grid;gap:.5rem;font-weight:700}
        .pf-cta li:before{content:"✓";margin-inline-end:.7rem;font-weight:800}
        .pf-card{background:#fff;color:#0c1433;border-radius:1.4rem;padding:1.6rem}
        .pf-card h3{font-size:1.15rem;font-weight:800;font-family:inherit}
        .pf-card p{color:#59648a;font-size:.85rem;margin:.2rem 0 1rem;line-height:1.9}
        .pf-card .alt{display:block;text-align:center;margin-top:.9rem;font-size:.85rem;font-weight:700;color:var(--pf-blue);text-decoration:none}
        @media(max-width:860px){.pf-cta{grid-template-columns:1fr}}
        .pf-bar{display:none}
        @media(max-width:899px){.pf-bar{display:flex;position:fixed;inset-inline:0;bottom:0;z-index:60;gap:.8rem;align-items:center;padding:.7rem 1rem calc(.7rem + env(safe-area-inset-bottom));background:rgba(255,255,255,.97);border-top:1px solid var(--pf-line);font-weight:800;font-size:.85rem;transform:translateY(110%);transition:transform .35s}.pf-bar.is-on{transform:none}.pf-bar span{flex:1}}
    </style>
    @endslot

    <div class="pf">
        <div class="pf-w">
            <header class="pf-hero">
                <nav class="pf-crumb" aria-label="مسیر صفحه">
                    <a href="{{ url('/') }}">خانه</a><span aria-hidden="true">›</span>
                    <a href="{{ route('portfolio.index') }}">نمونه‌کارها</a><span aria-hidden="true">›</span>
                    <b aria-current="page" dir="auto">{{ \Illuminate\Support\Str::limit($portfolio->title, 60) }}</b>
                </nav>
                <span class="pf-kick">مطالعه‌ی موردی{{ $portfolio->categories->count() ? ' · '.$portfolio->categories->pluck('name')->join('، ') : '' }}</span>
                <h1 dir="auto">{{ $portfolio->title }}</h1>
                @if($portfolio->short_description)<p class="pf-lead" dir="auto">{{ $portfolio->short_description }}</p>@endif

                <div class="pf-row">
                    <a class="pf-btn fill" href="#pf-start">پروژه‌ای مثل این می‌خواهم</a>
                    @if($portfolio->preview_link)<a class="pf-btn" href="{{ $portfolio->preview_link }}" target="_blank" rel="noopener nofollow">مشاهده‌ی سایت زنده ↗</a>@endif
                </div>

                <dl class="pf-meta">
                    <div><dt>کارفرما</dt><dd dir="auto">{{ $portfolio->client_name ?: 'محرمانه' }}</dd></div>
                    <div><dt>{{ $duration ? 'مدت اجرا' : 'ساخته‌شده با' }}</dt><dd dir="ltr" style="text-align:end">{{ $duration ?: ($techs ? implode(' · ', array_slice($techs, 0, 3)) : '—') }}</dd></div>
                    <div><dt>وضعیت</dt><dd>@if($isDone)تحویل‌شده{{ $doneAt ? ' · '.$doneAt : '' }}@else در حال توسعه · {{ $fa((int) $progress) }}٪ @endif</dd></div>
                    <div><dt>آدرس</dt><dd dir="ltr" style="text-align:end">@if($host)<a href="{{ $portfolio->preview_link }}" target="_blank" rel="noopener nofollow">{{ $host }}</a>@else پروژه‌ی خصوصی @endif</dd></div>
                </dl>

                <div class="pf-stage" id="pf-top">
                    <div class="pf-frame">
                        <div class="pf-chrome" aria-hidden="true"><i></i><i></i><i></i>@if($host)<span>{{ $host }}</span>@endif</div>
                        @if($img)<img src="{{ $img }}" alt="تصویر پروژه {{ $portfolio->title }}" width="1600" height="1000" fetchpriority="high">@else<div style="aspect-ratio:16/10;background:#eff2fb"></div>@endif
                    </div>
                </div>
            </header>
            <div class="pf-after" aria-hidden="true"></div>

            @if($chapters)
            <main class="pf-story">
                <aside class="pf-rail" aria-label="فصل‌های داستان">
                    <p>داستان این پروژه</p>
                    @foreach($chapters as $i => $ch)<a href="#pf-c{{ $i }}" class="{{ $i === 0 ? 'on' : '' }}" dir="auto">{{ $ch['title'] }}</a>@endforeach
                </aside>
                <div>
                    @foreach($chapters as $i => $ch)
                        <section class="pf-chap" id="pf-c{{ $i }}">
                            <h2 dir="auto">{{ $ch['title'] }}</h2>
                            @if($ch['prose'] !== '')<div class="pf-rich">{!! $ch['prose'] !!}</div>@endif

                            @foreach($ch['quotes'] as $q)<div class="pf-quote"><q dir="auto"><span>{{ $q }}</span></q></div>@endforeach

                            @if($ch['features'])
                                <ul class="pf-feat">@foreach($ch['features'] as $f){!! '<li>'.$checkSvg.'<div dir="auto">'.$f.'</div></li>' !!}@endforeach</ul>
                            @endif

                            @if($ch['stats'])
                                <div class="pf-stats">@foreach($ch['stats'] as $st)<div><b dir="auto">{{ $st['value'] }}</b><span dir="auto">{{ $st['label'] }}</span></div>@endforeach</div>
                            @endif

                            @if(isset($chImg[$i]))
                                <figure class="pf-fig"><button type="button" data-pf-img="{{ $chImg[$i] }}" aria-label="بزرگ‌نمایی تصویر"><img src="{{ $chImg[$i] }}" alt="نمایی از پروژه {{ $portfolio->title }}" width="1200" height="800" loading="lazy"></button></figure>
                            @endif
                        </section>
                    @endforeach
                </div>
            </main>
            @endif

            @if($techs)
                <div class="pf-th"><span>ساخته‌شده با</span><small>تکنولوژی‌های این پروژه</small></div>
                <div class="pf-tech" aria-hidden="true"><div class="pf-run">
                    @for($k = 0; $k < max(2, (int) ceil(16 / max(count($techs), 1))); $k++)@foreach($techs as $t)<span>{{ $t }}</span>@endforeach @endfor
                </div></div>
                <span class="sr-only">{{ implode('، ', $techs) }}</span>
            @endif

            @if($portfolio->tags->count())<div class="pf-tags">@foreach($portfolio->tags as $tg)<span># {{ $tg->name }}</span>@endforeach</div>@endif

            @if($team->count())
                <div class="pf-th"><span>تیم پروژه</span></div>
                <div class="pf-team">
                    @foreach($team as $m)
                        <div class="pf-member">
                            @if(!empty($m->photo))<img class="pf-avatar" src="{{ Seo::img($m->photo) }}" alt="{{ $m->name }}" width="48" height="48" loading="lazy">@else<span class="pf-avatar">{{ \Illuminate\Support\Str::substr($m->name, 0, 1) }}</span>@endif
                            <div><b dir="auto">{{ $m->name }}</b>@if($m->role)<small dir="auto">{{ $m->role }}</small>@endif</div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($restGallery->count())
                <div class="pf-th"><span>نگاهی نزدیک‌تر</span></div>
                <div class="pf-gal" style="margin-top:1.2rem">
                    @foreach($restGallery as $src)<button type="button" class="pf-g" data-pf-img="{{ $src }}" aria-label="بزرگ‌نمایی تصویر {{ $loop->iteration }}"><img src="{{ $src }}" alt="تصویر {{ $loop->iteration }} از پروژه {{ $portfolio->title }}" width="1200" height="800" loading="lazy"></button>@endforeach
                </div>
            @endif
            @if($gallery->count())<dialog class="pf-lb" id="pf-lb" aria-label="نمایش تصویر"><button type="button" aria-label="بستن">✕</button><img alt=""></dialog>@endif

            @if($next)
                <a class="pf-next" href="{{ route('portfolio.show', $next->slug) }}">
                    <div><small>پروژه‌ی بعدی</small><h3 dir="auto">{{ $next->title }} ←</h3></div>
                    <div class="th" role="img" aria-label="کاور {{ $next->title }}" @if($ni = Seo::img($next->featured_image)) style="background-image:url('{{ $ni }}')" @endif></div>
                </a>
            @endif
            @if($previous)<a class="pf-prev" href="{{ route('portfolio.show', $previous->slug) }}">→ پروژه‌ی قبلی: <span dir="auto">{{ $previous->title }}</span></a>@endif

            @if($relatedPortfolios->count())
                <section class="pf-rel" aria-labelledby="pf-rel">
                    <div class="pf-th" style="margin-top:0"><span id="pf-rel">پروژه‌های مشابه</span></div>
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3" style="margin-top:1.2rem">@foreach($relatedPortfolios as $p)@include('front.t-one.partials.portfolio-card', ['p' => $p])@endforeach</div>
                </section>
            @endif

            <section class="pf-cta" id="pf-start" aria-labelledby="pf-cta-t">
                <div>
                    <h2 id="pf-cta-t">ماجرای بعدی را برای کسب‌وکار شما می‌نویسیم</h2>
                    <p class="s">همان‌طور که این پروژه را از ایده تا تحویل ساختیم، برای شما هم می‌توانیم. شماره‌تان را بگذارید تا مشاور ما تماس بگیرد.</p>
                    <ul><li>پیشنهاد فنی و قیمت اولیه، بدون هزینه و تعهد</li><li>گفت‌وگو با کسی که واقعاً پروژه را اجرا می‌کند</li><li>زمان‌بندی روشن پیش از شروع کار</li></ul>
                </div>
                <div class="pf-card">
                    <h3>شروع گفت‌وگو</h3>
                    <p>نام و شماره‌ی موبایل کافی است؛ بقیه‌اش را در تماس می‌پرسیم.</p>
                    @include('front.t-one.partials.lead-form', ['page' => 'portfolio/'.$portfolio->slug, 'type' => 'درخواست از صفحه‌ی نمونه‌کار: '.$portfolio->title, 'cta' => 'می‌خواهم پروژه‌ام را شروع کنم', 'withName' => true])
                    <a href="{{ $orderUrl }}" class="alt">ترجیح می‌دهم فرم کامل سفارش را پر کنم ←</a>
                </div>
            </section>
        </div>

        <div class="pf-bar" id="pf-bar" aria-hidden="true">
            <span>پروژه‌ای مثل این می‌خواهید؟</span>
            <a href="#pf-start" class="btn btn-primary" tabindex="-1">شروع گفت‌وگو</a>
        </div>
    </div>

    @slot('script')
    <script>
    (function () {
        var bar = document.getElementById('pf-bar'), stage = document.getElementById('pf-top');
        if (bar && stage && 'IntersectionObserver' in window) {
            new IntersectionObserver(function (e) {
                var on = !e[0].isIntersecting && e[0].boundingClientRect.top < 0;
                bar.classList.toggle('is-on', on);
                bar.setAttribute('aria-hidden', on ? 'false' : 'true');
                var a = bar.querySelector('a'); if (a) a.tabIndex = on ? 0 : -1;
            }, {threshold: 0}).observe(stage);
        }
        var links = [].slice.call(document.querySelectorAll('.pf-rail a'));
        if (links.length && 'IntersectionObserver' in window) {
            var io = new IntersectionObserver(function (es) {
                es.forEach(function (e) { if (e.isIntersecting) links.forEach(function (a) { a.classList.toggle('on', a.getAttribute('href') === '#' + e.target.id); }); });
            }, {rootMargin: '-35% 0px -55% 0px'});
            links.forEach(function (a) { var s = document.querySelector(a.getAttribute('href')); if (s) io.observe(s); });
        }
        var lb = document.getElementById('pf-lb');
        if (lb && lb.showModal) {
            var im = lb.querySelector('img');
            document.querySelectorAll('[data-pf-img]').forEach(function (b) {
                b.addEventListener('click', function () { im.src = b.getAttribute('data-pf-img'); lb.showModal(); });
            });
            lb.querySelector('button').addEventListener('click', function () { lb.close(); });
            lb.addEventListener('click', function (e) { if (e.target === lb) lb.close(); });
        }
    })();
    </script>
    @endslot
@endcomponent
