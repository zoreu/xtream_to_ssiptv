<?php
set_time_limit(0);
ini_set('max_execution_time', 0);

include 'config.php';

function fetchDataFromUrl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 25);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    return (json_last_error() === JSON_ERROR_NONE) ? $data : null;
}

if (isset($_GET['username']) && isset($_GET['password'])) {
    $username = $_GET['username'];
    $password = $_GET['password'];
    $protocol = $_SERVER['REQUEST_SCHEME'];
    $host_user = $_SERVER['HTTP_HOST'];

    // Pega as categorias de séries
    $api_categories = "{$host}:{$port}/player_api.php?username={$username}&password={$password}&action=get_series_categories";
    $categories = fetchDataFromUrl($api_categories);

    $m3u_list = '#EXTM3U size="Medium"' . "\n";

    // Item "VER TUDO"
    $m3u_list .= "#EXTINF:0 type=\"playlist\" tvg-logo=\"https://i.ibb.co/VYPJBvH/background.png\",VER TUDO\n";
    $m3u_list .= "#EXTBG: #11609e\n";
    $m3u_list .= "{$protocol}://{$host_user}/{$folder}/seriescategory/{$username}/{$password}/-1/\n";

    // Lista categorias de séries
    if ($categories !== null) {
        foreach ($categories as $cat) {
            $cat_id = $cat['category_id'];
            $cat_name = $cat['category_name'];
            $m3u_list .= "#EXTINF:0 type=\"playlist\" tvg-logo=\"https://i.ibb.co/VYPJBvH/background.png\",{$cat_name}\n";
            $m3u_list .= "#EXTBG: #11609e\n";
            $m3u_list .= "{$protocol}://{$host_user}/{$folder}/seriescategory/{$username}/{$password}/{$cat_id}/\n";
        }
    }

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: text/plain');
    header('Content-Length: ' . strlen($m3u_list));
    echo $m3u_list;
} else {
    echo 'Parâmetros insuficientes fornecidos.';
}
?>
