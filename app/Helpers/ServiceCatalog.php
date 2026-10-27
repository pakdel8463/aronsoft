<?php

namespace App\Helpers;

use Illuminate\Validation\ValidationException;

/**
 * Server-side price list for the online estimator.
 * Never trust prices coming from the browser: items are matched by id (or exact
 * title for older cached pages) and priced from config/aron_services.php.
 */
class ServiceCatalog
{
    /** @return array<string, array{id:string,title:string,price:int,category:string,category_label:string,required:bool,parent:?string}> */
    public static function flat(): array
    {
        $flat = [];
        foreach ((array) config('aron_services.catalog', []) as $catKey => $cat) {
            foreach ($cat['items'] as $id => $item) {
                $flat[$id] = [
                    'id' => $id,
                    'title' => $item['title'],
                    'price' => (int) $item['price'],
                    'category' => $catKey,
                    'category_label' => $cat['label'],
                    'required' => (bool) ($item['required'] ?? false),
                    'parent' => $item['parent'] ?? null,
                ];
            }
        }

        return $flat;
    }

    /**
     * @param  array<int, array<string, mixed>>  $submitted
     * @return array{items: array<int, array<string, mixed>>, total: int, category: string}
     *
     * @throws ValidationException
     */
    public static function resolve(array $submitted): array
    {
        $flat = self::flat();
        $byTitle = [];
        foreach ($flat as $row) {
            $byTitle[self::norm($row['title'])] = $row['id'];
        }

        $picked = [];
        foreach ($submitted as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = isset($row['id']) && is_string($row['id']) ? $row['id'] : null;
            if (! $id || ! isset($flat[$id])) {
                $title = isset($row['title']) && is_string($row['title']) ? self::norm($row['title']) : '';
                $id = $byTitle[$title] ?? null;
            }
            if (! $id) {
                throw ValidationException::withMessages(['items' => 'یکی از آیتم‌های انتخاب‌شده معتبر نیست.']);
            }
            $picked[$id] = $flat[$id];
        }

        if (! $picked) {
            throw ValidationException::withMessages(['items' => 'حداقل یک خدمت را انتخاب کنید.']);
        }

        $categories = array_unique(array_column($picked, 'category'));
        if (count($categories) !== 1) {
            throw ValidationException::withMessages(['items' => 'در هر پیش‌فاکتور فقط یک نوع خدمت قابل انتخاب است.']);
        }
        $category = $categories[0];

        // sub-features need their parent module
        foreach ($picked as $id => $row) {
            if ($row['parent'] && ! isset($picked[$row['parent']])) {
                unset($picked[$id]);
            }
        }

        // required items are always part of the invoice
        foreach ($flat as $id => $row) {
            if ($row['category'] === $category && $row['required']) {
                $picked[$id] = $row;
            }
        }

        $items = [];
        $total = 0;
        foreach ($picked as $row) {
            $items[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'price' => $row['price'],
                'description' => $row['category_label'],
            ];
            $total += $row['price'];
        }

        return ['items' => $items, 'total' => $total, 'category' => $category];
    }

    private static function norm(string $s): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $s)));
    }
}
