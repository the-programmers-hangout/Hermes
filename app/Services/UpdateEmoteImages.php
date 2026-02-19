<?php

namespace App\Services;

use Laracord\Services\Service;

class UpdateEmoteImages extends Service
{
    /**
     * The service interval.
     */
    protected int $interval = 600; // 10 minutes

    /**
     * Handle the service.
     */
    public function handle(): void
    {
        $guildId = env('GUILD_ID');
        $guild = $this->discord->guilds->get('id', $guildId);
        if (! $guild) {
            return;
        }

        foreach ($guild->emojis as $emoji) {
            $type = $emoji->animated ? 'ANIMATED' : 'STATIC';
            $emote = \App\Models\Emote::where('emote_id', $emoji->id)->where('type', $type)->whereNull('image')->first();
            if (! $emote) {
                continue;
            }

            $ext = $emoji->animated ? 'gif' : 'png';
            $url = "https://cdn.discordapp.com/emojis/{$emoji->id}.{$ext}";

            try {
                $client = new \GuzzleHttp\Client;
                $response = $client->get($url);
                if ($response->getStatusCode() === 200) {
                    $imageData = $response->getBody()->getContents();
                    $emote->image = $imageData;
                    $emote->save();
                }
            } catch (\Exception $e) {
            }
        }
    }
}
