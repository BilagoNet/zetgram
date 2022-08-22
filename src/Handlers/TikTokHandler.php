<?php

namespace App\Handlers;

use Zetgram\ApiAbstract;
use Zetgram\Handlers\MessageHandler;
use Zetgram\ReplyKeyboard;
use Zetgram\Types\Message;

use App\Tiktok;


class TikTokHandler extends MessageHandler
{
    /**
     * @var ApiAbstract
     */
    private ApiAbstract $api;

    public function __construct(ApiAbstract $api)
    {
        $this->api = $api;
    }

    public function handleMessage(Message $message)
    {
        go(function () use ($message) {
            $msg = $this->api->sendMessage($message->chat->id, trans('wait'), null, $message->messageId);
            $tiktok = new Tiktok($message->text);
            $video = json_decode($tiktok->getInfo());
            if(!$video->ok){
                $this->api->editMessageText("Topilmadi!", $msg->chat->id, $msg->message_id);
                return;
            }
            $desc = $video->description;
            $video_url = $video->video_url;
            $duration = $video->duration;
            $this->api->sendVideo(
                $message->chat->id,
                $video_url,
                $duration,
                null, null, null,
                $desc
            );
        });
    }
}
