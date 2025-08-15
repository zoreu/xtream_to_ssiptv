<?php
set_time_limit(0);
ini_set('max_execution_time', 0);

include 'config.php';

function fetchDataFromUrl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3'
    ));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 25);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return null;
    }

    $data = json_decode($response, true);
    curl_close($ch);

    return (json_last_error() === JSON_ERROR_NONE) ? $data : null;
}

if (isset($_GET['username']) && isset($_GET['password'])) {
    $username = $_GET['username'];
    $password = $_GET['password'];

    // Buscar todas as categorias
    $categories_url = "{$host}:{$port}/player_api.php?username={$username}&password={$password}&action=get_live_categories";
    $categories = fetchDataFromUrl($categories_url);

    // Buscar todos os canais de uma vez
    $all_streams_url = "{$host}:{$port}/player_api.php?username={$username}&password={$password}&action=get_live_streams";
    $all_streams = fetchDataFromUrl($all_streams_url);

    if ($categories !== null && $all_streams !== null) {
        // Mapeia category_id → nome
        $cat_map = [];
        foreach ($categories as $c) {
            $cat_map[$c['category_id']] = $c['category_name'];
        }

        $m3u_list = '#EXTM3U size="Medium"' . "\n";

        foreach ($all_streams as $stream) {
            $group_title = $cat_map[$stream['category_id']] ?? 'Sem Categoria';
            $name = $stream['name'];
            $stream_type = $stream['stream_type'];
            $stream_id = $stream['stream_id'];
            $stream_icon = $stream['stream_icon'];
            $epg_channel_id = $stream['epg_channel_id'];

            $m3u_list .= "#EXTINF:-1 type=\"stream\" tvg-id=\"{$epg_channel_id}\" tvg-logo=\"{$stream_icon}\" group-title=\"{$group_title}\",{$name}\n";
            $m3u_list .= "{$host}:{$port}/{$stream_type}/{$username}/{$password}/{$stream_id}.m3u8\n";
        }

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: text/plain; charset=UTF-8');
        header('Content-Length: ' . strlen($m3u_list));
        echo $m3u_list;

    } else {
        // Caso de erro
        $m3u_list = '#EXTM3U size="Medium"' . "\n";
        $name = 'ERRO AO BAIXAR M3U';
        $stream_icon = 'https://png.pngtree.com/png-clipart/20200701/original/pngtree-red-error-icon-png-image_5418881.jpg';
        $m3u_list .= "#EXTINF:-1 type=\"playlist\" tvg-id=\"{$name}\" tvg-logo=\"{$stream_icon}\",{$name}\n";
        $m3u_list .= "http://localhost/erro.m3u\n";

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: text/plain; charset=UTF-8');
        echo $m3u_list;
    }
}
?>
