<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    function setting($key, $default = null) {
        return Setting::where('key', $key)->value('value') ?? $default;
    }
}

if (! function_exists('contact_info')) {
    function contact_info($key = null, $default = null) {
        return $key === null ? \App\Support\ContactInfo::all() : \App\Support\ContactInfo::get($key, $default);
    }
}
