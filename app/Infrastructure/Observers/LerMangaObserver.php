<?php

namespace Infrastructure\Observers;

use Illuminate\Support\Arr;
use Infrastructure\Models\LerManga;

class LerMangaObserver
{
    public function updating(LerManga $model)
    {
        $original = $model->getOriginal();

        if (Arr::exists($original, 'uid')) $model->uid = Arr::get($original, 'uid', uuid_create());

        $originalChapters = Arr::get($original, 'chapters', []);
        $originalTotalChapters = max($model->total_chapters, Arr::get($original, 'total_chapters', 0));
        $newChapters = array_merge($model?->chapters ?? [], $originalChapters);

        uksort($newChapters, function($a, $b) {
            preg_match('/(\d+)$/', $a, $matchesA);
            preg_match('/(\d+)$/', $b, $matchesB);

            $numA = isset($matchesA[1]) ? (int)$matchesA[1] : 0;
            $numB = isset($matchesB[1]) ? (int)$matchesB[1] : 0;

            return $numA - $numB;
        });

        $model->chapters = $newChapters;
        $model->total_chapters = max($originalTotalChapters, count($newChapters));
    }
}
