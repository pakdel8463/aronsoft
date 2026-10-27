<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContactSettingsRequest;
use App\Models\Setting;
use App\Support\ContactInfo;

/** تنظیمات اطلاعات تماس شرکت: /panel/settings/contact (دسترسی settings.contact؛ سوپرکاربر همیشه، با middleware در routes/admin.php) */
class ContactSettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings.contact', [
            'info' => ContactInfo::all(),
            'isCustom' => Setting::where('key', ContactInfo::SETTING_KEY)->exists(),
        ]);
    }

    public function update(ContactSettingsRequest $request)
    {
        $data = $request->validated();

        $clean = [];
        foreach (ContactInfo::FIELDS as $k) {
            $clean[$k] = trim((string) ($data[$k] ?? ''));
        }
        $clean['map'] = [
            'lat' => (float) $data['map']['lat'],
            'lng' => (float) $data['map']['lng'],
            'zoom' => (int) $data['map']['zoom'],
        ];

        ContactInfo::save($clean);

        return redirect()->route('panel.settings.contact')->with('success', 'اطلاعات تماس ذخیره شد.');
    }

    public function reset()
    {
        ContactInfo::reset();

        return redirect()->route('panel.settings.contact')->with('success', 'اطلاعات تماس به مقادیر پیش‌فرض بازگشت.');
    }
}
