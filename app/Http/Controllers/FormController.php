<?php

namespace App\Http\Controllers;

use App\Forms\FormSchema;
use App\Forms\FormSubmitter;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** ارسال فرم‌های فرم‌ساز از سایت. بعد از ارسال به همان صفحه (لنگر روی فرم) برمی‌گردد. */
class FormController extends Controller
{
    public function submit(Request $request, Form $form, FormSubmitter $submitter)
    {
        $back = $this->backUrl($request, $form);

        if ($request->filled('_hp')) { // honeypot: ربات‌ها همه‌ی فیلدها را پر می‌کنند
            return redirect()->to($back);
        }
        if ($reason = $form->closedReason()) {
            return redirect()->to($back)->with('form_closed_'.$form->id, $reason);
        }
        if ($form->setting('require_login') && ! $request->user()) {
            return redirect()->guest(route('login'));
        }

        try {
            $entry = $submitter->handle($form, $request);
        } catch (ValidationException $e) {
            return redirect()->to($back)->withErrors($e->errors(), 'form_'.$form->id)
                ->withInput($request->except(array_merge(['_token', '_hp', 'captcha'], array_map(fn ($k) => 'fields.'.$k, $this->fileKeys($form)))));
        }

        if ($form->setting('confirmation_type') === 'redirect' && ($url = $form->setting('redirect_url'))) {
            return redirect()->to($url);
        }

        return redirect()->to($back)->with('form_success_'.$form->id, FormSchema::mergeTags((string) $form->setting('confirmation_message'), $form, $entry, true));
    }

    /** بازگشت به صفحه‌ای که فرم در آن بود (فقط همین دامنه)، روی لنگر فرم. */
    private function backUrl(Request $request, Form $form): string
    {
        $source = (string) $request->input('_source');
        $url = $source && parse_url($source, PHP_URL_HOST) === $request->getHost() ? $source : url()->previous();

        return strtok($url, '#').'#form-'.$form->id;
    }

    private function fileKeys(Form $form): array
    {
        return collect($form->fields ?? [])->where('type', 'file')->pluck('key')->all();
    }
}
