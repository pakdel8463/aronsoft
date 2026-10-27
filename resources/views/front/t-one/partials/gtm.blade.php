{{-- Google Tag Manager + Consent Mode v2 — فقط یک‌بار، در <head>، و فقط در محیط production --}}
@php
    $gtmId = config('services.google_tag_manager.id') ?: 'GTM-MMJ4NTXW';
    $gtmOn = (bool) config('services.google_tag_manager.enabled', app()->environment('production'));
@endphp
@if($gtmOn && $gtmId)
<script>
    window.dataLayer = window.dataLayer || [];
    window.gtag = window.gtag || function () { dataLayer.push(arguments); };
    (function () {
        var stored = null;
        try { stored = JSON.parse(localStorage.getItem('consent_status') || 'null'); } catch (e) {}
        gtag('consent', 'default', stored || {
            ad_storage: 'denied', analytics_storage: 'denied', ad_user_data: 'denied',
            ad_personalization: 'denied', personalization_storage: 'denied',
            functionality_storage: 'denied', security_storage: 'granted', wait_for_update: 500
        });
    })();
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});
        var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';
        j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ $gtmId }}');
</script>
@endif
