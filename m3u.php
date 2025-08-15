<?php
set_time_limit(0);
ini_set('max_execution_time', 0);

include 'config.php';


if (isset($_GET['username']) && isset($_GET['password'])) {
    $username = $_GET['username'];
    $password = $_GET['password'];
    $protocol = $_SERVER['REQUEST_SCHEME'];
    $host_user = $_SERVER['HTTP_HOST']; 
    $m3u_list = '#EXTM3U size="Big"' . "\n";
    $m3u_list .= '#EXTINF:0 type="playlist" tvg-logo="https://i.ibb.co/VYPJBvH/background.png",CANAIS' . "\n";
    $m3u_list .= '#EXTBG:https://i.ibb.co/wNsYrsk/image.png' . "\n";
    if (strlen($folder) > 0) {
        $m3u_list .= "{$protocol}://{$host_user}/{$folder}/channels/{$username}/{$password}/" . "\n";
    } else {
        $m3u_list .= "{$protocol}://{$host_user}/channels/{$username}/{$password}/" . "\n";
    }

    $m3u_list .= '#EXTINF:0 type="playlist" tvg-logo="https://i.ibb.co/VYPJBvH/background.png",FILMES' . "\n";
    $m3u_list .= '#EXTBG:https://i.ibb.co/C1MtCkN/image.png' . "\n";
    if (strlen($folder) > 0) {
        $m3u_list .= "{$protocol}://{$host_user}/{$folder}/vod/{$username}/{$password}/" . "\n";
    } else {
        $m3u_list .= "{$protocol}://{$host_user}/vod/{$username}/{$password}/" . "\n";
    }

    $m3u_list .= '#EXTINF:0 type="playlist" tvg-logo="https://i.ibb.co/VYPJBvH/background.png",SÉRIES' . "\n";
    $m3u_list .= '#EXTBG:https://i.ibb.co/41gWh6P/image.png' . "\n";
    if (strlen($folder) > 0) {
        $m3u_list .= "{$protocol}://{$host_user}/{$folder}/series/{$username}/{$password}/" . "\n";
    } else {
        $m3u_list .= "{$protocol}://{$host_user}/series/{$username}/{$password}/" . "\n";
    }
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: text/plain');
    //header('Content-Disposition: attachment; filename="vod.m3u"');
    header('Content-Length: ' . strlen($m3u_list));
    echo $m3u_list;
} else {
    echo 'Parâmetros insuficientes fornecidos.';
}
?>
