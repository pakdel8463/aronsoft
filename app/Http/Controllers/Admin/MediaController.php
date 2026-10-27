<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaRelation;
use App\Services\Media\MediaException;
use App\Services\Media\MediaStorage;
use App\Services\Media\MediaUploader;
use App\Services\Media\MediaUsage;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index()
    {
        return view('admin.dash.media.index', [
            'counts' => $this->counts(),
            'disk' => MediaStorage::diskName(),
            'maxKb' => config('media.max_kb'),
            'maxUpload' => ini_get('upload_max_filesize'),
        ]);
    }

    /** JSON برای کتابخانه و انتخابگر: q, type, sort, page, per_page */
    public function list(Request $request)
    {
        $sort = $request->query('sort', 'new');
        $q = Media::query()->with('uploader:id,name')
            ->ofType($request->query('type'))
            ->when(trim((string) $request->query('q', '')) !== '', function ($w) use ($request) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], trim($request->query('q'))).'%';
                $w->where(fn ($x) => $x->where('original_name', 'like', $like)->orWhere('title', 'like', $like)->orWhere('alt', 'like', $like)->orWhere('file_name', 'like', $like));
            });

        match ($sort) {
            'old' => $q->orderBy('id'),
            'name' => $q->orderBy('original_name'),
            'size' => $q->orderByDesc('size'),
            default => $q->orderByDesc('id'),
        };

        $page = $q->paginate(min(80, max(8, (int) $request->query('per_page', 36))));

        return response()->json([
            'items' => $page->getCollection()->map(fn (Media $m) => $m->toPicker())->values(),
            'meta' => ['page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
            'counts' => $this->counts(),
        ]);
    }

    public function store(Request $request, MediaUploader $uploader)
    {
        $files = $request->file('files') ?? ($request->file('file') ? [$request->file('file')] : []);
        $files = is_array($files) ? $files : [$files];
        if (! $files) {
            return response()->json(['message' => 'فایلی ارسال نشد (احتمالاً حجم از upload_max_filesize/post_max_size سرور بیشتر است).', 'items' => [], 'errors' => []], 422);
        }

        $items = [];
        $errors = [];
        foreach ($files as $file) {
            try {
                $items[] = $uploader->store($file, $request->user(), $request->only(['alt', 'title', 'caption']))->toPicker();
            } catch (MediaException $e) {
                $errors[] = ['name' => $file->getClientOriginalName(), 'message' => $e->getMessage()];
            }
        }

        return response()->json(['items' => $items, 'errors' => $errors, 'message' => $items ? null : ($errors[0]['message'] ?? 'آپلود ناموفق بود.')], $items ? 200 : 422);
    }

    public function show(Media $media)
    {
        return response()->json($media->load('uploader:id,name')->toPicker() + ['usage' => MediaUsage::for($media)]);
    }

    public function update(Request $request, Media $media)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'alt' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:2000',
        ]);
        $media->update($data);

        return response()->json(['ok' => true, 'item' => $media->fresh()->toPicker()]);
    }

    public function replace(Request $request, Media $media, MediaUploader $uploader)
    {
        $request->validate(['file' => 'required|file']);
        try {
            $media = $uploader->replace($media, $request->file('file'), $request->user());
        } catch (MediaException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['ok' => true, 'item' => $media->toPicker(), 'usage' => MediaUsage::for($media)]);
    }

    /** اگر رسانه استفاده شده باشد، بدون force=1 با ۴۰۹ و فهرست استفاده‌ها رد می‌شود */
    public function destroy(Request $request, Media $media)
    {
        $usage = MediaUsage::for($media);
        if ($usage && ! $request->boolean('force')) {
            return response()->json(['ok' => false, 'used' => true, 'usage' => $usage, 'message' => 'این فایل در '.count($usage).' جا استفاده شده است.'], 409);
        }
        $this->purge($media);

        return response()->json(['ok' => true, 'counts' => $this->counts()]);
    }

    public function bulkDestroy(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer']);
        $deleted = [];
        $skipped = [];
        foreach (Media::whereIn('id', $data['ids'])->get() as $m) {
            if (! $request->boolean('force') && MediaUsage::for($m)) {
                $skipped[] = $m->id;

                continue;
            }
            $this->purge($m);
            $deleted[] = $m->id;
        }

        return response()->json(['ok' => true, 'deleted' => $deleted, 'skipped' => $skipped, 'counts' => $this->counts()]);
    }

    private function purge(Media $media): void
    {
        MediaStorage::deleteFiles($media);
        MediaRelation::where('media_id', $media->id)->delete();
        $media->delete();
    }

    private function counts(): array
    {
        $t = Media::query()->selectRaw('type, count(*) as c')->groupBy('type')->pluck('c', 'type');

        return [
            'all' => (int) $t->sum(),
            'image' => (int) ($t['image'] ?? 0),
            'video' => (int) ($t['video'] ?? 0),
            'audio' => (int) ($t['audio'] ?? 0),
            'document' => (int) ($t['file'] ?? 0),
        ];
    }
}
