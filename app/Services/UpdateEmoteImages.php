<?php

namespace App\Services;

use App\Models\Emote;
use GuzzleHttp\Client;
use Laracord\Services\Service;

class UpdateEmoteImages extends Service
{
    /**
     * The service interval.
     */
    protected int $interval = 5; // 10 minutes

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

        $client = new Client;

        foreach ($guild->emojis as $emoji) {
            $emote = Emote::where('emote_id', $emoji->id)
                ->whereIn('type', ['ANIMATED', 'STATIC'])
                ->whereNull('image')
                ->first();

            if (! $emote) {
                continue;
            }

            $ext = $emote->type === 'ANIMATED' ? 'gif' : 'png';
            $url = "https://cdn.discordapp.com/emojis/{$emoji->id}.{$ext}";

            try {
                $response = $client->get($url);
                if ($response->getStatusCode() === 200) {
                    $imageBinary = (string) $response->getBody();

                    if ($imageBinary === '') {
                        continue;
                    }

                    $emote->image = base64_encode($imageBinary);
                    $emote->save();
                }
            } catch (\Exception $e) {
                $this->console()->log("Failed to fetch image for emote ID {$emoji->id}: ".$e->getMessage());
            }
        }
    }
}
