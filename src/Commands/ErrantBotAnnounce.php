<?php

namespace Jiko\Discord\Commands;

use Illuminate\Console\Command;
use Jiko\Activity\Activity;
use Jiko\Discord\Models\Option;

class ErrantBotAnnounce extends Command
{
  protected $signature = 'errantbot:announce {guild}';

  protected $description = 'Check for announcements';

  public function handle()
  {
    $channels = Option::where('option_name', 'stream_announce_channel')->where('related_id', $this->argument('guild'))->get();
    dd($channels);
    foreach ($channels as $channel) {
      $res = json_decode(file_get_contents("https://api.twitch.tv/kraken/streams/$channel?client_id=" . env("TWITCH_CLIENT_ID")));
      if (!$res->stream) continue;

      // Check for recent events
      $lastEvent = Activity::where('category', "TwitchStream")
        ->where('label', $channel)
        ->whereBetween("created_at", [
          (new \Carbon\Carbon("-6 hours"))->format("Y-m-d h:i:s"),
          (new \Carbon\Carbon())->format("Y-m-d h:i:s")
        ])
        ->orderBy('created_at', 'desc')
        ->first();

      if (!$lastEvent) {
        // If no previous event in the past X hours (Treat this as a new stream)
        $event = Activity::create([
          'category' => "TwitchStream",
          'action' => "created",
          'label' => $channel,
          'value' => [
            "channelUrl" => $res->stream->channel->url,
            "currentViewers" => $res->stream->viewers,
            "created_at" => $res->stream->created_at,
            "updated_at" => $res->stream->channel->updated_at,
            "game" => $res->stream->channel->game,
            "streamPreview" => $res->stream->preview->large
          ]
        ]);
        $this->call('twitter:stream-live');
        $this->call('errantbot:send', [
          'message' => "ACTIVITY @category=TwitchStream;@action=" . $lastEvent->action . ";@label=" . $lastEvent->label . ";@data=" . json_encode($res) . ";",
          '--twitter' => 'default'
        ]);
      } else {
        // Created or last stream event/notification older than 3 hours
        if ($lastEvent->action === "created" || ($lastEvent->action === "streaming" && $lastEvent->created_at->timestamp <= strtotime("-3 hours"))) {
          // Create a "streaming event"
          $event = Activity::create([
            'category' => "TwitchStream",
            'action' => "streaming",
            'label' => $channel,
            'value' => [
              "channelUrl" => $res->stream->channel->url,
              "currentViewers" => $res->stream->viewers,
              "created_at" => $res->stream->created_at,
              "updated_at" => $res->stream->channel->updated_at,
              "game" => $res->stream->channel->game,
              "streamPreview" => $res->stream->preview->large
            ]
          ]);
          $this->call('twitter:stream-live');
          $this->call('errantbot:send', ['message' => "ACTIVITY @category=TwitchStream;@action=" . $lastEvent->action . ";@label=" . $lastEvent->label . ";@data=" . json_encode($res) . ";"]);
        }
      }
    }
  }
}