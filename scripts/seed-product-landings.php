<?php
declare(strict_types=1);
$root=dirname(__DIR__);require $root.'/vendor/autoload.php';$app=require $root.'/bootstrap/app.php';$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$products=[
 ['attendance','Aron Attendance','سامانه حضور و غیاب آرون','مدیریت حضور و غیاب سازمانی، شیفت‌ها و درخواست‌های کارکنان.','https://humawatch.com','#4357a6',['مدیریت شیفت‌های کاری','درخواست مرخصی و مأموریت','گزارش‌های مدیریتی','ثبت تردد کارکنان']],
 ['moneyiar','Moneyiar','مانیـار | مدیریت مالی شخصی','ابزار ثبت درآمد، هزینه، بودجه‌بندی و بدهی‌ها.','https://moneyiar.com','#3857c5',['ثبت درآمد و هزینه','بودجه‌بندی','مدیریت طلب و بدهی','گزارش‌های مالی']],
 ['calorieto','Calorieto','کالریتو | تغذیه و فعالیت','مدیریت تغذیه، پیاده‌روی، قدم‌شمار و چالش‌های گروهی.','https://calorieto.com','#7c50c7',['مدیریت تغذیه','ثبت فعالیت و قدم‌شمار','چالش‌های کاربران','شبکه اجتماعی و کوین']],
];
foreach($products as [$slug,$name,$title,$desc,$url,$accent,$features]){
 $landing=\App\Models\LandingPage::query()->firstOrNew(['slug'=>$slug]);
 if($landing->exists){echo "Existing landing $slug untouched\n";continue;}
 $landing->fill(['title'=>$name,'template'=>'cms','is_active'=>false,'seo_title'=>$title,'seo_description'=>$desc,'appearance'=>['accent'=>$accent,'background'=>'#f7f8fc','foreground'=>'#16213d'],
 'draft_sections'=>[['type'=>'hero','title'=>$title,'subtitle'=>'محصولی از آرون سافت','body'=>$desc,'button_text'=>'مشاهده وب‌سایت','button_url'=>$url,'enabled'=>true],['type'=>'features','title'=>'امکانات کلیدی','items'=>$features,'enabled'=>true],['type'=>'gallery','title'=>'تصاویر واقعی نرم‌افزار و اپلیکیشن','items'=>[],'enabled'=>true],['type'=>'portfolio','title'=>'درباره پروژه','enabled'=>true],['type'=>'cta','title'=>'برای شروع آماده‌اید؟','body'=>'برای آشنایی بیشتر و شروع استفاده وارد وب‌سایت محصول شوید.','button_text'=>'ورود به سایت محصول','button_url'=>$url,'enabled'=>true]]]);$landing->save();echo "Draft created: $slug\n";
}
echo "Drafts only. Add screenshots, link portfolios and publish in /panel/landing.\n";
