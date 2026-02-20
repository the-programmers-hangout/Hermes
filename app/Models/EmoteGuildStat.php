<?php

namespace App\Models;

use App\Models\Concerns\BuildsEmoteAggregates;
use App\Models\Concerns\BuildsEmoteUsageLists;
use Illuminate\Database\Eloquent\Model;

class EmoteGuildStat extends Model
{
    use BuildsEmoteAggregates;
    use BuildsEmoteUsageLists;

    protected $fillable = [
        'emote_id',
        'guild_id',
        'usage_count',
    ];

    protected $casts = [
        'emote_id' => 'string',
        'guild_id' => 'string',
    ];

    public function emote()
    {
        return $this->belongsTo(
            Emote::class,
            'emote_id',
            'emote_id'
        );
    }

    public static function dashboardAggregate(): array
    {
        $baseQuery = self::query()
            ->join('emotes', 'emote_guild_stats.emote_id', '=', 'emotes.emote_id');

        $aggregate = self::aggregateSummaryAndUsageByType(
            $baseQuery,
            'emote_guild_stats.usage_count',
            'emote_guild_stats.emote_id'
        );

        $summary = $aggregate['summary'];
        $usageByType = $aggregate['usage_by_type'];

        $unicodeUsage = (int) EmoteLog::query()
            ->join('emotes', 'emote_logs.emote_id', '=', 'emotes.emote_id')
            ->where('emotes.type', 'UNICODE')
            ->count();

        $unicodeUniqueEmotes = (int) EmoteLog::query()
            ->join('emotes', 'emote_logs.emote_id', '=', 'emotes.emote_id')
            ->where('emotes.type', 'UNICODE')
            ->distinct('emote_logs.emote_id')
            ->count('emote_logs.emote_id');

        $topUnicode = EmoteLog::query()
            ->join('emotes', 'emote_logs.emote_id', '=', 'emotes.emote_id')
            ->where('emotes.type', 'UNICODE')
            ->select(
                'emote_logs.emote_id',
                'emotes.emote_name',
                'emotes.type',
                'emotes.image'
            )
            ->selectRaw('COUNT(*) as total_usage')
            ->groupBy(
                'emote_logs.emote_id',
                'emotes.emote_name',
                'emotes.type',
                'emotes.image'
            )
            ->orderByDesc('total_usage')
            ->limit(10)
            ->get();

        $baseTotalUsage = (int) ($summary->total_usage ?? 0);
        $baseUniqueEmotes = (int) ($summary->unique_emotes ?? 0);

        return [
            'total_usage' => $baseTotalUsage + $unicodeUsage,
            'unique_emotes' => $baseUniqueEmotes + $unicodeUniqueEmotes,
            'usage_by_type' => [
                'STATIC' => (int) ($usageByType->get('STATIC') ?? 0),
                'ANIMATED' => (int) ($usageByType->get('ANIMATED') ?? 0),
                'UNICODE' => $unicodeUsage,
            ],
            'top_static' => self::emotesByType(
                $baseQuery,
                'STATIC',
                'emote_guild_stats.usage_count',
                'emote_guild_stats.emote_id',
                'desc'
            ),
            'top_animated' => self::emotesByType(
                $baseQuery,
                'ANIMATED',
                'emote_guild_stats.usage_count',
                'emote_guild_stats.emote_id',
                'desc'
            ),
            'bottom_static' => self::emotesByType(
                $baseQuery,
                'STATIC',
                'emote_guild_stats.usage_count',
                'emote_guild_stats.emote_id',
                'asc'
            ),
            'bottom_animated' => self::emotesByType(
                $baseQuery,
                'ANIMATED',
                'emote_guild_stats.usage_count',
                'emote_guild_stats.emote_id',
                'asc'
            ),
            'top_unicode' => $topUnicode,
        ];
    }
}
