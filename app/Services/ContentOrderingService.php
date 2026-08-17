<?php

namespace App\Services;

use App\Models\Content;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ContentOrderingService
{
    public function insert(Content $content, int $requestedPosition, int $userId): void
    {
        $items = $this->lockedItems($content->type);
        $position = max(1, min($requestedPosition, $items->count() + 1));
        $items->splice($position - 1, 0, [$content]);

        $this->persist($items, $content, $userId);
    }

    public function move(Content $content, int $requestedPosition, int $userId): void
    {
        $items = $this->lockedItems($content->type)
            ->reject(fn (Content $item): bool => $item->is($content))
            ->values();
        $position = max(1, min($requestedPosition, $items->count() + 1));
        $items->splice($position - 1, 0, [$content]);

        $this->persist($items, $content, $userId);
    }

    public function remove(Content $content, int $userId): void
    {
        $items = $this->lockedItems($content->type)
            ->reject(fn (Content $item): bool => $item->is($content))
            ->values();

        $this->persist($items, $content, $userId);
    }

    /** @return Collection<int, Content> */
    private function lockedItems(string $type): Collection
    {
        return Content::query()
            ->where('type', $type)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    /** @param Collection<int, Content> $items */
    private function persist(Collection $items, Content $subject, int $userId): void
    {
        foreach ($items->values() as $index => $item) {
            $position = $index + 1;
            if ($item->is($subject) || ! $item->exists) {
                $subject->sort_order = $position;

                continue;
            }

            if ($item->sort_order !== $position) {
                DB::table('contents')->where('id', $item->id)->update([
                    'sort_order' => $position,
                    'updated_by' => $userId,
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
