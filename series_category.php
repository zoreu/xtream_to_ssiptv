<?php

include 'config.php';
set_time_limit(0);
ini_set('max_execution_time', 0);

function fetchDataFromUrl($url) {
    // Inicializa uma sessão cURL
    $ch = curl_init();

    // Define as opções cURL
    curl_setopt($ch, CURLOPT_URL, $url); // Define a URL para a solicitação
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retorna a transferência como uma string
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3'
    )); // Define o User-Agent para imitar o Chrome
    // Desabilitar verificação SSL (não recomendado em produção)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 25); // Tempo limite de conexão
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Tempo limite total    
    // Executa a solicitação cURL
    $response = curl_exec($ch);

    // Verifica se houve erro na solicitação cURL
    if (curl_errno($ch)) {
        echo 'Erro no cURL: ' . curl_error($ch);
        $data = null;
    } else {
        // Decodifica a resposta JSON
        $data = json_decode($response, true);

        // Verifica se a resposta JSON foi decodificada com sucesso
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo 'Erro ao decodificar JSON: ' . json_last_error_msg();
            $data = null;
        }
    }

    // Fecha a sessão cURL
    curl_close($ch);

    // Retorna os dados decodificados
    return $data;
}

if (isset($_GET['username']) && isset($_GET['password']) && isset($_GET['id'])) {
    $username = $_GET['username'];
    $password = $_GET['password'];
    $protocol = $_SERVER['REQUEST_SCHEME'];
    $host_user = $_SERVER['HTTP_HOST']; 
    $id = $_GET['id'];

    if ($id == '-1') {
        $api_url = $host . ':' . $port . '/player_api.php?username=' . $username . '&password=' . $password . '&action=get_series';
    } else {
        $api_url = $host . ':' . $port . '/player_api.php?username=' . $username . '&password=' . $password . '&action=get_series&category_id=' . $id;
    }

    $resp_category = fetchDataFromUrl($api_url);

    if ($resp_category !== null) {
        $m3u_list = '#EXTM3U size="Medium"' . "\n";

        foreach ($resp_category as $stream) {
            $name = $stream['name'];
            $series_id = $stream['series_id'];
            $stream_icon = $stream['cover'];

            $m3u_list .= "#EXTINF:-1 type=\"playlist\" tvg-id=\"{$name}\" tvg-logo=\"{$stream_icon}\" description=\"\",{$name}\n";
            $m3u_list .= "{$protocol}://{$host_user}/{$folder}/episodes/{$username}/{$password}/{$series_id}/\n"; 
        }

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: text/plain');
        header('Content-Length: ' . strlen($m3u_list));
        echo $m3u_list;
    } else {
        // Caso de erro
        $m3u_list = '#EXTM3U size="Medium"' . "\n";
        $name = 'ERRO AO BAIXAR M3U';
        $stream_icon = 'https://png.pngtree.com/png-clipart/20200701/original/pngtree-red-error-icon-png-image_5418881.jpg';
        $m3u_list .= "#EXTINF:-1 type=\"playlist\" tvg-id=\"{$name}\" tvg-logo=\"{$stream_icon}\" description=\"\",{$name}\n";
        $m3u_list .= "http://localhost/erro.m3u\n";
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: text/plain');
        echo $m3u_list;
    }
} else {
    echo 'Parâmetros insuficientes fornecidos.';
}
?>
