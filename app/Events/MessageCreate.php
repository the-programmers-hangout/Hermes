<?php

namespace App\Events;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event as Events;
use Laracord\Events\Event;

class MessageCreate extends Event
{
    /**
     * The event handler.
     *
     * @var string
     */
    protected $handler = Events::MESSAGE_CREATE;

    /**
     * Handle the event.
     */
    public function handle(Message $message, Discord $discord)
    {
        if ($message->author->bot) {
            return;
        }

        $customEmotePattern = '/<a?:([a-zA-Z0-9_]+):(\d+)>/';
        $unicodeEmojiPattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u';

        $content = $message->content;
        $author = $message->author;
        $guild = $message->guild_id ? $discord->guilds->get('id', $message->guild_id) : null;

        if ($guild === null) {
            return;
        }

        preg_match_all($customEmotePattern, $content, $customMatches, PREG_SET_ORDER);
        preg_match_all($unicodeEmojiPattern, $content, $unicodeMatches);

        $user = \App\Models\User::firstOrCreate(
            ['discord_id' => $author->id],
            ['username' => $author->username]
        );

        foreach ($customMatches as $match) {
            $emoteName = $match[1];
            $emoteId = $match[2];
            $emote = null;
            $type = 'STATIC';
            if ($guild && $guild->emojis) {
                $emote = $guild->emojis->get('id', $emoteId);
                if ($emote && $emote->animated) {
                    $type = 'ANIMATED';
                }
            }
            if ($emote) {
                $emoteModel = \App\Models\Emote::firstOrCreate(
                    ['emote_id' => $emoteId],
                    [
                        'guild_id' => $guild ? $guild->id : null,
                        'emote_name' => $emoteName,
                        'type' => $type,
                    ]
                );
                $emoteModel->addReaction(
                    user: $user,
                    guildId: $guild ? $guild->id : null,
                    channelId: $message->channel_id,
                    messageId: $message->id,
                    unicodeEmoji: null
                );
            }
        }

        foreach ($unicodeMatches[0] as $emoji) {
            $emoteModel = \App\Models\Emote::firstOrCreate(
                ['emote_id' => $emoji],
                [
                    'guild_id' => $guild ? $guild->id : null,
                    'emote_name' => $emoji,
                    'type' => 'UNICODE',
                ]
            );
            $emoteModel->addReaction(
                user: $user,
                guildId: $guild ? $guild->id : null,
                channelId: $message->channel_id,
                messageId: $message->id,
                unicodeEmoji: $emoji
            );
        }
    }
}
