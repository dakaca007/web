<?php
// 创建 Swoole WebSocket 服务器（修正地址和端口）

$server = new Swoole\WebSocket\Server("0.0.0.0", 3000);

// 客户端连接事件
$server->on('Open', function (Swoole\WebSocket\Server $server, $request) {
    echo "新客户端连接: {$request->fd}\n";
});

// 接收客户端消息事件
$server->on('Message', function (Swoole\WebSocket\Server $server, $frame) {
    echo "收到消息: {$frame->data}\n";
    // 广播给所有客户端
    foreach ($server->connections as $fd) {
        $server->push($fd, "服务器收到: {$frame->data}");
    }
});

// 客户端断开事件
$server->on('Close', function ($server, $fd) {
    echo "客户端断开: {$fd}\n";
});

echo "WebSocket 服务已启动：ws://0.0.0.0:3000\n";
$server->start();