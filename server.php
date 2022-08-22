<?php

use Zetgram\Bot;
use Zetgram\Types\Update;

use Swoole\Http\Server;
use Swoole\Http\Response;
use Swoole\Http\Request;

use Swoole\Event;

use function Swoole\Coroutine\go;


require __DIR__ . '/boot.php';

$http = new Server("app", 9501);

/**
 * @var Bot $bot
 */
$bot = $container->get(Bot::class);

include APP_DIR . 'routes.php';


$http->on("WorkerExit", function ($server, $workerId) {
    Event::Exit();
});


$http->on(
    "request",
    function (Request $request, Response $response) use ($bot) {
        $method = $request->getMethod();
        if ($method == "GET") {
            $response->end("<h1>Hello word</h1>");
            return;
        } else {
            $response->end('');
        }
        go(function() use($request, $bot){
            $data = json_decode($request->rawContent());
            $update = new Update($data);
            $bot->handleUpdate($update);
        });
    }
);


$http->start();
