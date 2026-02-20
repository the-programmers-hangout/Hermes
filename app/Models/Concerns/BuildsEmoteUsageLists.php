<?php

namespace App\Models\Concerns;

use Illuminate\Support\Collection;

trait BuildsEmoteUsageLists
{
    protected static function emotesByType(
        $baseQuery,
        string $type,
        string $usageCountColumn,
        string $emoteIdColumn,
        string $direction = 'desc'
    ): Collection {
        $query = (clone $baseQuery)
            ->where('emotes.type', $type)
            ->select(
                $emoteIdColumn.' as emote_id',
                'emotes.emote_name',
                'emotes.type',
                'emotes.image'
            )
            ->selectRaw("COALESCE(SUM({$usageCountColumn}), 0) as total_usage")
            ->groupBy(
                $emoteIdColumn,
                'emotes.emote_name',
                'emotes.type',
                'emotes.image'
            );

        if ($direction === 'asc') {
            $query->orderBy('total_usage');
        } else {
            $query->orderByDesc('total_usage');
        }

        return $query
            ->limit(10)
            ->get();
    }
}
