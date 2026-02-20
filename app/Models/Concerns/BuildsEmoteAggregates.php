<?php

namespace App\Models\Concerns;

trait BuildsEmoteAggregates
{
    protected static function aggregateSummaryAndUsageByType(
        $baseQuery,
        string $usageCountColumn,
        string $emoteIdColumn,
        ?string $userIdColumn = null
    ): array {
        $summaryQuery = (clone $baseQuery)
            ->selectRaw("COALESCE(SUM({$usageCountColumn}), 0) as total_usage")
            ->selectRaw("COUNT(DISTINCT {$emoteIdColumn}) as unique_emotes");

        if ($userIdColumn !== null) {
            $summaryQuery->selectRaw("COUNT(DISTINCT {$userIdColumn}) as unique_users");
        }

        $summary = $summaryQuery->first();

        $usageByType = (clone $baseQuery)
            ->select('emotes.type')
            ->selectRaw("COALESCE(SUM({$usageCountColumn}), 0) as total_usage")
            ->groupBy('emotes.type')
            ->pluck('total_usage', 'type');

        return [
            'summary' => $summary,
            'usage_by_type' => $usageByType,
        ];
    }
}
