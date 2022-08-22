<?php

use App\Handlers\StartHandler;
use App\Handlers\TikTokHandler;

$bot->hears('\/start.*', StartHandler::class);
$bot->hears('https?://(?:vm|vt)\.tiktok\.com/(?P<id>\w+)', TikTokHandler::class);
$bot->hears('https?://www\.tiktok\.com/(?:embed|@(?P<user_id>[\w\.-]+)/video)/(?P<id>\d+)', TikTokHandler::class);
