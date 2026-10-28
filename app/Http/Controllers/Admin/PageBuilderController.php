<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Page;
use App\Pages\BlockSchema;
use App\Pages\PageBuilder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** صفحه‌ساز: بلوک‌های یک صفحه (افزودن، ویرایش، جابه‌جایی، پنهان/نمایش، کپی، حذف، بازگشت به پیش‌فرض). */
class PageBuilderController extends Controller
{
    public function show(Page $page)
    {
        return view('admin.dash.pages.builder', [
            'page' => $page,
            'blocks' => $page->blocks()->get(),
            'library' => PageBuilder::library(),
            'categories' => PageBuilder::CATEGORIES,
        ]);
    }

    public function store(Request $request, Page $page)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys((array) config('blocks', [])))],
            'after' => ['nullable', 'integer'],
        ]);

        $sort = null;
        if (! empty($data['after']) && ($after = $page->blocks()->find($data['after']))) {
            $page->blocks()->where('sort', '>', $after->sort)->increment('sort');
            $sort = $after->sort + 1;
        } elseif ($request->input('after') === '0') { // «اول صفحه»
            $page->blocks()->increment('sort');
            $sort = 0;
        }
        $block = PageBuilder::addBlock($page, $data['type'], [], $sort);

        return redirect()->route('panel.pages.blocks.edit', [$page, $block])->with('success', 'بلوک «'.$block->label().'» اضافه شد؛ محتوایش را ویرایش کنید.');
    }

    public function edit(Page $page, Block $block)
    {
        $this->ensureBelongs($page, $block);
        abort_unless($block->isKnown(), 404);
        $siblings = $page->blocks()->get(['id', 'type', 'anchor', 'is_active', 'sort']);

        return view('admin.dash.pages.block', [
            'page' => $page,
            'block' => $block,
            'definition' => $block->definition(),
            'data' => $block->content(),
            'siblings' => $siblings,
        ]);
    }

    public function update(Request $request, Page $page, Block $block)
    {
        $this->ensureBelongs($page, $block);
        $fields = $block->definition()['fields'] ?? [];
        $request->validate(
            ['anchor' => ['nullable', 'string', 'max:60', 'regex:/^[A-Za-z0-9\-_]*$/']] + BlockSchema::rules($fields),
            ['anchor.regex' => 'شناسه‌ی لنگر فقط حروف انگلیسی، عدد و خط تیره می‌تواند داشته باشد.'],
            ['anchor' => 'شناسه‌ی لنگر'] + BlockSchema::attributes($fields),
        );

        $block->update([
            'data' => BlockSchema::sanitize($fields, (array) $request->input('data', [])),
            'anchor' => PageBuilder::uniqueAnchor($page, (string) ($request->input('anchor') ?: $block->type), $block->id),
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->input('after_save') === 'builder') {
            return redirect()->route('panel.pages.builder', $page)->with('success', 'بلوک «'.$block->label().'» ذخیره شد.');
        }

        return back()->with('success', 'بلوک ذخیره شد.');
    }

    public function reset(Page $page, Block $block)
    {
        $this->ensureBelongs($page, $block);
        $def = $block->definition();
        $block->update(['data' => BlockSchema::sanitize($def['fields'] ?? [], (array) ($def['defaults'] ?? []))]);

        return back()->with('success', 'محتوای پیش‌فرض بلوک بازگردانده شد.');
    }

    public function toggle(Page $page, Block $block)
    {
        $this->ensureBelongs($page, $block);
        $block->update(['is_active' => ! $block->is_active]);

        return back()->with('success', $block->is_active ? 'بلوک نمایش داده می‌شود.' : 'بلوک پنهان شد.');
    }

    public function duplicate(Page $page, Block $block)
    {
        $this->ensureBelongs($page, $block);
        $page->blocks()->where('sort', '>', $block->sort)->increment('sort');
        $copy = $page->blocks()->create([
            'type' => $block->type,
            'anchor' => PageBuilder::uniqueAnchor($page, (string) ($block->anchor ?: $block->type)),
            'data' => $block->data,
            'is_active' => $block->is_active,
            'sort' => $block->sort + 1,
        ]);

        return redirect()->route('panel.pages.blocks.edit', [$page, $copy])->with('success', 'از بلوک یک کپی ساخته شد.');
    }

    public function destroy(Page $page, Block $block)
    {
        $this->ensureBelongs($page, $block);
        $label = $block->label();
        $block->delete();

        return redirect()->route('panel.pages.builder', $page)->with('success', "بلوک «{$label}» حذف شد.");
    }

    public function sort(Request $request, Page $page)
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];
        foreach (array_values($ids) as $i => $id) {
            $page->blocks()->whereKey($id)->update(['sort' => $i + 1]);
        }
        $page->touch();

        return response()->json(['message' => 'ترتیب بلوک‌ها ذخیره شد.']);
    }

    private function ensureBelongs(Page $page, Block $block): void
    {
        abort_unless($block->page_id === $page->id, 404);
    }
}
