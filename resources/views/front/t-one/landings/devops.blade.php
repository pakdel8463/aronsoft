@php
$L = [
 'slug'=>'devops-services','name'=>'خدمات DevOps و زیرساخت ابری','order_type'=>'devops',
 'seo_title'=>'خدمات DevOps، CI/CD، کوبرنتیز و Azure',
 'seo_description'=>'طراحی CI/CD، کانتینرسازی با Docker و Kubernetes، مانیتورینگ، DevSecOps و پشتیبانی سرور؛ خدمات DevOps و زیرساخت ابری برای تیم‌های نرم‌افزاری.',
 'eyebrow'=>'نسل جدید خدمات ابری و DevOps','badge_icon'=>'ph-cloud',
 'h1'=>['خدمات','DevOps','و زیرساخت، برای توسعه‌ای بی‌پایان.'],
 'lead'=>'ما پیچیدگی‌های زیرساخت را حذف می‌کنیم تا تیم شما فقط روی خلق ارزش تمرکز کند. از طراحی معماری تا استقرار خودکار، در کنار شما هستیم.',
 'cta_primary'=>['شروع همکاری',url('/order?type=devops')],'cta_secondary'=>['مشاهده خدمات','#services'],
 'bullets'=>['CI/CD','Docker و Kubernetes','مانیتورینگ'],
 'visual'=>['title'=>'.gitlab-ci.yml','lines'=>[[0,'stages: [build, test, deploy]'],[0,'build:'],[1,'script: docker build -t app .'],[0,'test:'],[1,'script: php artisan test'],[0,'deploy:'],[1,'script: helm upgrade --install app ./chart'],[1,'only: [main]']],'chips'=>[['ph-rocket-launch','استقرار خودکار'],['ph-heartbeat','مانیتورینگ']]],
 'tech'=>[['ph-cube','Docker'],['ph-hexagon','Kubernetes'],['ph-git-branch','GitLab CI'],['ph-github-logo','GitHub Actions'],['ph-cloud','Azure'],['ph-chart-line','Prometheus'],['ph-chart-bar','Grafana'],['ph-stack','Terraform']],
 'features'=>['title'=>'جعبه‌ابزار کامل DevOps','sub'=>'راهکارهای ۳۶۰ درجه برای چرخه حیات نرم‌افزار شما','items'=>[
   ['ph-git-merge','CI/CD Pipeline','طراحی خطوط لوله هوشمند با GitLab CI و GitHub Actions برای استقرار بدون توقف.'],
   ['ph-cube','Container & K8s','داکرایز کردن سرویس‌ها و مدیریت ارکستراسیون کانتینرها با Kubernetes در مقیاس بالا.'],
   ['ph-heartbeat','Observability','مانیتورینگ جامع با Prometheus و Grafana به همراه تحلیل لاگ‌های توزیع‌شده.'],
   ['ph-shield-check','DevSecOps','امنیت به عنوان کد. اسکن آسیب‌پذیری‌ها و ایمن‌سازی زیرساخت لینوکسی.'],
   ['ph-stack','IaC (Terraform)','مدیریت زیرساخت به صورت کد با Terraform و Ansible برای تکرارپذیری محیط‌ها.'],
   ['ph-database','DBOps','بهینه‌سازی، کلاسترینگ و بکاپ‌گیری خودکار دیتابیس‌های SQL و NoSQL.']]],
 'highlight'=>['id'=>'azure','icon'=>'ph-cloud','eyebrow'=>'خدمات Azure','title'=>['قدرت ابری','Microsoft Azure','در دستان شما'],
   'text'=>'ما به عنوان متخصصین پیاده‌سازی سرویس‌های Azure، زیرساخت شما را به ابر مایکروسافت متصل می‌کنیم؛ مقیاس‌پذیری بالا با امنیت سطح سازمانی.',
   'bullets'=>['مدیریت کلاسترهای Azure Kubernetes Service (AKS)','پیاده‌سازی Azure DevOps Pipelines','مهاجرت به سرورهای ابری Azure VM'],
   'tiles'=>[['ph-cloud','Azure Cloud','معماری ابری ترکیبی'],['ph-git-branch','Azure DevOps','مدیریت پروژه و CI/CD']]],
 'packages'=>['title'=>'پلن‌های پشتیبانی و اجرا','sub'=>'قیمت‌ها ماهانه و به میلیون تومان است.','items'=>[
   ['name'=>'استارتاپ','price_note'=>'از','price'=>'۵ میلیون تومان','per'=>'ماهانه','features'=>['تنظیم سرور اولیه','راه‌اندازی CI/CD ساده','پشتیبانی ۸ ساعته'],'cta'=>'انتخاب پلن'],
   ['name'=>'شرکتی','price_note'=>'از','price'=>'۱۵ میلیون تومان','per'=>'ماهانه','featured'=>true,'badge'=>'محبوب‌ترین','features'=>['مدیریت کامل کلاستر K8s','مانیتورینگ پیشرفته','بکاپ‌گیری خودکار','بهینه‌سازی دیتابیس'],'cta'=>'همین حالا شروع کنید'],
   ['name'=>'سازمانی','price_note'=>'اختصاصی','price'=>'استعلام قیمت','features'=>['طراحی معماری High Availability','پیاده‌سازی DevSecOps','تیم اختصاصی DevOps','SLA توافقی'],'cta'=>'تماس بگیرید','href'=>'#lead']]],
 'courses'=>['title'=>'آکادمی تخصصی DevOps','sub'=>'یادگیری بر اساس سناریوهای واقعی بازار کار','items'=>[
   ['ph-cube','دوره جامع مسترینگ داکر','از مفاهیم پایه کانتینر تا نوشتن Dockerfileهای پیشرفته و امنیتی.','۱۲ ساعت آموزش','تکمیل‌شده'],
   ['ph-hexagon','متخصص Kubernetes و Helm','مدیریت کلاستر، دیپلوی با Helm Chart و مفاهیم پیشرفته شبکه در K8s.','۳۰+ ساعت (تخمینی)','در حال ضبط'],
   ['ph-git-branch','ورک‌شاپ CI/CD با GitLab','سناریوهای واقعی پایپ‌لاین‌نویسی برای پروژه‌های Node.js و Python.','زمستان ۱۴۰۳','بزودی']]],
 'faqs'=>[['آیا روی سرور فعلی ما هم کار می‌کنید؟','بله؛ هم روی سرورهای فعلی و هم روی ابر (از جمله Azure) پیاده‌سازی انجام می‌شود.'],['مهاجرت بدون قطعی ممکن است؟','با برنامه‌ریزی مرحله‌ای و محیط Staging، مهاجرت را با حداقل توقف انجام می‌دهیم؛ جزئیات در نیازسنجی مشخص می‌شود.'],['نیازسنجی اولیه رایگان است؟','بله، فرم زیر را پر کنید تا کارشناسان ما برای نیازسنجی با شما تماس بگیرند.']],
 'final'=>['title'=>'بهینه‌سازی زیرساخت را شروع کنید','text'=>'سفارش ثبت کنید یا فرم را پر کنید تا کارشناسان ما برای نیازسنجی رایگان تماس بگیرند.'],
 'form'=>['title'=>'درخواست مشاوره DevOps','type'=>'مشاوره DevOps','message'=>true],
];
@endphp
@include('front.t-one.landings._parts.base')
