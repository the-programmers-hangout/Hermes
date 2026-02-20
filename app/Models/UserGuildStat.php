<?php

namespace App\Models;

use App\Models\Concerns\BuildsEmoteAggregates;
use App\Models\Concerns\BuildsEmoteUsageLists;
use Illuminate\Database\Eloquent\Model;

class UserGuildStat extends Model
{
    use BuildsEmoteAggregates;
    use BuildsEmoteUsageLists;

    protected $fillable = [
        'user_id',
        'guild_id',
        'emote_id',
        'usage_count',
    ];

    protected $casts = [
        'user_id' => 'string',
        'guild_id' => 'string',
        'emote_id' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'discord_id');
    }

    public static function dashboardAggregateForUser(string $userId): array
    {
        $baseQuery = self::query()
            ->join('emotes', 'user_guild_stats.emote_id', '=', 'emotes.emote_id')
            ->where('user_guild_stats.user_id', $userId);

        $aggregate = self::aggregateSummaryAndUsageByType(
            $baseQuery,
            'user_guild_stats.usage_count',
            'user_guild_stats.emote_id',
            'user_guild_stats.user_id'
        );

        $summary = $aggregate['summary'];
        $usageByType = $aggregate['usage_by_type'];

        return [
            'total_usage' => (int) ($summary->total_usage ?? 0),
            'unique_emotes' => (int) ($summary->unique_emotes ?? 0),
            'unique_users' => (int) ($summary->unique_users ?? 0),
            'usage_by_type' => [
                'STATIC' => (int) ($usageByType->get('STATIC') ?? 0),
                'ANIMATED' => (int) ($usageByType->get('ANIMATED') ?? 0),
                'UNICODE' => (int) ($usageByType->get('UNICODE') ?? 0),
            ],
            'top_static' => self::emotesByType(
                $baseQuery,
                'STATIC',
                'user_guild_stats.usage_count',
                'user_guild_stats.emote_id',
                'desc'
            ),
            'top_animated' => self::emotesByType(
                $baseQuery,
                'ANIMATED',
                'user_guild_stats.usage_count',
                'user_guild_stats.emote_id',
                'desc'
            ),
            'bottom_static' => self::emotesByType(
                $baseQuery,
                'STATIC',
                'user_guild_stats.usage_count',
                'user_guild_stats.emote_id',
                'asc'
            ),
            'bottom_animated' => self::emotesByType(
                $baseQuery,
                'ANIMATED',
                'user_guild_stats.usage_count',
                'user_guild_stats.emote_id',
                'asc'
            ),
            'top_unicode' => self::emotesByType(
                $baseQuery,
                'UNICODE',
                'user_guild_stats.usage_count',
                'user_guild_stats.emote_id',
                'desc'
            ),
        ];
    }
}
