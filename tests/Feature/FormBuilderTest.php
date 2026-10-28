<?php

namespace Tests\Feature;

use App\Captcha\Captcha;
use App\Forms\FormSchema;
use App\Forms\FormTemplates;
use App\Models\Form;
use App\Models\FormEntry;
use App\Models\User;
use App\Pages\PageBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FormBuilderTest extends TestCase
{
    use RefreshDatabase;

    private function makeForm(string $template = 'contact', array $settings = []): Form
    {
        $tpl = FormTemplates::all()[$template];

        return Form::create([
            'title' => 'فرم '.$template,
            'slug' => $template.'-'.uniqid(),
            'fields' => FormSchema::normalizeFields($tpl['fields']),
            'settings' => FormSchema::normalizeSettings(array_merge($tpl['settings'], $settings)),
        ]);
    }

    private function submit(Form $form, array $fields, array $extra = [])
    {
        return $this->from('/contact-page')->post(route('forms.submit', $form), array_merge([
            '_form_id' => $form->id,
            '_source' => url('/contact-page'),
            'fields' => $fields,
            'captcha' => Captcha::solvedPayload(),
        ], $extra));
    }

    public function test_admin_screens_render(): void
    {
        $admin = $this->admin();
        $form = $this->makeForm('project');
        $this->submit($form, ['project_type' => 'سایت شرکتی', 'name' => 'سارا', 'mobile' => '09121234567']);
        $entry = FormEntry::firstOrFail();

        $this->actingAs($admin)->get(route('panel.forms.index'))->assertOk()->assertSee($form->title)->assertSee($form->shortcode());
        $this->actingAs($admin)->get(route('panel.forms.edit', $form))->assertOk()->assertSee('data-fb-form', false);
        $this->actingAs($admin)->get(route('panel.forms.preview', $form))->assertOk()->assertSee('data-fm-form', false);
        $this->actingAs($admin)->get(route('panel.form-entries.index'))->assertOk()->assertSee('سارا');
        $this->actingAs($admin)->get(route('panel.form-entries.index', ['form' => $form->id]))->assertOk();
        $this->actingAs($admin)->get(route('panel.form-entries.show', $entry))->assertOk()->assertSee('09121234567');
        $this->assertSame('in_progress', $entry->fresh()->status);
    }

    public function test_create_from_template_and_save_fields_and_settings(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('panel.forms.store'), ['title' => 'استخدام', 'template' => 'job'])->assertRedirect();
        $form = Form::where('title', 'استخدام')->firstOrFail();
        $this->assertSame('ارسال درخواست همکاری', $form->setting('submit_label'));

        $fields = $form->fields;
        $fields[] = ['id' => 'zz', 'type' => 'select', 'key' => 'City Name!', 'label' => '<b>شهر</b>', 'options' => [['label' => 'تهران'], ['label' => 'شیراز', 'value' => 'shz'], ['label' => '']]];
        $fields[] = ['type' => 'unknown_type', 'key' => 'x'];
        $settings = $form->allSettings();
        $settings['submit_label'] = 'بفرست';
        $settings['limit_entries'] = '۵';

        $this->actingAs($admin)->put(route('panel.forms.update', $form), [
            'title' => 'استخدام ۱۴۰۵', 'slug' => '', 'is_active' => '1',
            'fields_json' => json_encode($fields), 'settings' => $settings,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $form->refresh();
        $this->assertSame('بفرست', $form->setting('submit_label'));
        $this->assertSame(5, $form->setting('limit_entries'));
        $last = collect($form->fields)->last();
        $this->assertSame('city_name', $last['key']);
        $this->assertSame('شهر', $last['label']);
        $this->assertSame([['label' => 'تهران', 'value' => 'تهران'], ['label' => 'شیراز', 'value' => 'shz']], $last['options']);
        $this->assertCount(count($fields) - 1, $form->fields); // unknown type dropped

        $this->actingAs($admin)->post(route('panel.forms.duplicate', $form))->assertRedirect();
        $this->assertSame(2, Form::count());
    }

    public function test_public_submission_validates_and_stores_entry(): void
    {
        $form = $this->makeForm('contact');

        $this->submit($form, ['name' => '', 'mobile' => '123', 'message' => ''])
            ->assertRedirect('/contact-page#form-'.$form->id)
            ->assertSessionHasErrorsIn('form_'.$form->id, ['fields.name', 'fields.mobile', 'fields.message']);
        $this->assertSame(0, FormEntry::count());

        $this->submit($form, ['name' => 'علی', 'mobile' => '۰۹۱۲۱۲۳۴۵۶۷', 'email' => 'ali@example.com', 'subject' => 'مشاوره', 'message' => 'سلام'])
            ->assertRedirect('/contact-page#form-'.$form->id)
            ->assertSessionHas('form_success_'.$form->id);
        $entry = FormEntry::firstOrFail();
        $this->assertSame('09121234567', $entry->data['mobile']); // Persian digits normalised
        $this->assertSame('new', $entry->status);
        $this->assertSame('علی', $entry->title());
    }

    public function test_captcha_is_required_and_single_use(): void
    {
        $form = $this->makeForm('contact');
        $ok = ['name' => 'علی', 'mobile' => '09121234567', 'message' => 'سلام'];

        $this->submit($form, $ok, ['captcha' => ''])->assertSessionHasErrorsIn('form_'.$form->id, ['captcha']);
        $this->submit($form, $ok, ['captcha' => 'garbage'])->assertSessionHasErrorsIn('form_'.$form->id, ['captcha']);

        $token = Captcha::solvedPayload();
        $this->submit($form, $ok, ['captcha' => $token])->assertSessionHas('form_success_'.$form->id);
        $this->submit($form, $ok, ['captcha' => $token])->assertSessionHasErrorsIn('form_'.$form->id, ['captcha']);
        $this->assertSame(1, FormEntry::count());

        // the captcha can be switched off per form
        $open = $this->makeForm('contact', ['captcha' => false]);
        $this->submit($open, $ok, ['captcha' => ''])->assertSessionHas('form_success_'.$open->id);
    }

    public function test_conditional_logic_hides_and_skips_fields(): void
    {
        $form = $this->makeForm('project');
        $base = ['name' => 'سارا', 'mobile' => '09121234567'];

        // shop_features is only visible for «فروشگاه اینترنتی»: its value is dropped otherwise
        $this->submit($form, $base + ['project_type' => 'سایت شرکتی', 'shop_features' => ['درگاه پرداخت']]);
        $this->assertArrayNotHasKey('shop_features', FormEntry::latest('id')->first()->data);

        $this->submit($form, $base + ['project_type' => 'فروشگاه اینترنتی', 'shop_features' => ['درگاه پرداخت', 'هک']])
            ->assertSessionHasErrorsIn('form_'.$form->id, ['fields.shop_features.1']);

        $this->submit($form, $base + ['project_type' => 'فروشگاه اینترنتی', 'shop_features' => ['درگاه پرداخت'], 'deadline' => ['y' => 1405, 'm' => 9, 'd' => 1]]);
        $data = FormEntry::latest('id')->first()->data;
        $this->assertSame(['درگاه پرداخت'], $data['shop_features']);
        $this->assertSame('1405/09/01', $data['deadline']);
    }

    public function test_file_upload_is_private_and_downloadable_from_the_panel(): void
    {
        Storage::fake('local');
        $form = $this->makeForm('job');
        $fields = ['name' => 'رضا', 'mobile' => '09121234567', 'email' => 'reza@example.com', 'role' => 'DevOps', 'privacy' => '1'];

        $this->submit($form, $fields + ['resume' => UploadedFile::fake()->create('cv.exe', 10)])
            ->assertSessionHasErrorsIn('form_'.$form->id, ['fields.resume']);

        $this->submit($form, $fields + ['resume' => UploadedFile::fake()->create('cv.pdf', 50, 'application/pdf')])->assertSessionHas('form_success_'.$form->id);
        $entry = FormEntry::firstOrFail();
        $this->assertSame('cv.pdf', $entry->data['resume']['name']);
        Storage::disk('local')->assertExists($entry->data['resume']['path']);

        $this->actingAs($this->admin())->get(route('panel.form-entries.file', [$entry, 'resume']))->assertOk()->assertDownload('cv.pdf');
        $this->actingAs(User::factory()->create(['is_superuser' => 0, 'is_staff' => 0]))->get(route('panel.form-entries.file', [$entry, 'resume']))->assertRedirect();

        $this->actingAs($this->admin())->delete(route('panel.form-entries.destroy', $entry))->assertRedirect();
        Storage::disk('local')->assertMissing($entry->data['resume']['path']);
    }

    public function test_closed_inactive_limited_and_honeypot(): void
    {
        $ok = ['name' => 'علی', 'mobile' => '09121234567', 'message' => 'سلام'];

        $inactive = $this->makeForm('contact');
        $inactive->update(['is_active' => false]);
        $this->submit($inactive, $ok)->assertSessionHas('form_closed_'.$inactive->id);

        $limited = $this->makeForm('contact', ['limit_entries' => 1]);
        $this->submit($limited, $ok)->assertSessionHas('form_success_'.$limited->id);
        $this->submit($limited, $ok)->assertSessionHas('form_closed_'.$limited->id);

        $hp = $this->makeForm('contact');
        $this->submit($hp, $ok, ['_hp' => 'bot'])->assertRedirect();
        $this->assertSame(0, $hp->entries()->count());
    }

    public function test_entries_bulk_update_and_export(): void
    {
        $admin = $this->admin();
        $form = $this->makeForm('contact');
        foreach (['علی', 'مریم'] as $n) {
            $this->submit($form, ['name' => $n, 'mobile' => '09121234567', 'message' => 'سلام']);
        }
        $ids = FormEntry::pluck('id')->all();

        $this->actingAs($admin)->patch(route('panel.form-entries.update', $ids[0]), ['status' => 'done', 'admin_note' => 'تماس گرفته شد', 'is_starred' => '1'])->assertRedirect();
        $this->assertTrue(FormEntry::find($ids[0])->is_starred);

        $this->actingAs($admin)->post(route('panel.form-entries.bulk'), ['ids' => $ids, 'action' => 'archived'])->assertRedirect();
        $this->assertSame(2, FormEntry::where('status', 'archived')->count());

        $csv = $this->actingAs($admin)->get(route('panel.form-entries.export', ['form' => $form->id, 'status' => 'archived']))->assertOk()->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('مریم', $csv);
        $this->assertStringContainsString('تماس گرفته شد', $csv);

        $this->actingAs($admin)->post(route('panel.form-entries.bulk'), ['ids' => $ids, 'action' => 'delete'])->assertRedirect();
        $this->assertSame(0, FormEntry::count());
    }

    public function test_form_renders_in_page_builder_block_and_post_shortcode(): void
    {
        $form = $this->makeForm('contact');
        $page = PageBuilder::createPage('تماس', 'contact-us');
        $page->update(['is_active' => true]);
        PageBuilder::addBlock($page, 'form', ['form' => $form->id, 'title' => 'با ما در ارتباط باشید']);

        $this->get('/contact-us')->assertOk()->assertSee('با ما در ارتباط باشید')->assertSee('id="form-'.$form->id.'"', false)->assertSee('data-pow-captcha', false);

        $html = \App\Forms\Shortcodes::render('<p>متن</p><p>[form id="'.$form->id.'"]</p>');
        $this->assertStringContainsString('data-fm-form', $html);
        $this->assertSame('', \App\Forms\Shortcodes::render('[form id="999"]'));
    }

    public function test_permissions_are_enforced(): void
    {
        $form = $this->makeForm();
        $staff = User::factory()->create(['is_superuser' => 0, 'is_staff' => 1]);

        $this->actingAs($staff)->get(route('panel.forms.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('panel.form-entries.index'))->assertForbidden();
    }

    public function test_captcha_challenge_endpoint(): void
    {
        $this->getJson(route('captcha.challenge'))->assertOk()->assertJsonStructure(['algorithm', 'challenge', 'maxnumber', 'salt', 'signature']);

        $tampered = json_decode(base64_decode(Captcha::solvedPayload()), true);
        $tampered['signature'] = str_repeat('0', 64);
        $this->assertFalse(Captcha::verify(base64_encode(json_encode($tampered))));
    }
}
