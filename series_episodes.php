<?php
set_time_limit(0);
ini_set('max_execution_time', 0);

include 'config.php';

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
    $id = $_GET['id'];
    $api_live_channels = $host . ':' . $port . '/player_api.php?username=' . $username . '&password=' . $password . '&action=get_series_info&series_id='.$id;
    $resp_category = fetchDataFromUrl($api_live_channels);

    if ($resp_category !== null) {
        $m3u_list = '#EXTM3U size="Medium"' . "\n";

        // Percorre as temporadas
        foreach ($resp_category['seasons'] as $season) {
            $season_number = $season['season_number'];

            // Percorre os episódios da temporada
            if (isset($resp_category['episodes'][$season_number])) {
                foreach ($resp_category['episodes'][$season_number] as $episode) {
                    $tvg_id = $episode['title'];
                    //$tvg_id = "3Hz - S" . str_pad($episode['season'], 2, '0', STR_PAD_LEFT) . "E" . str_pad($episode['episode_num'], 2, '0', STR_PAD_LEFT) . " - " . $episode['title'];
                    $group_title = $episode['season'] . "ª Temporada";
                    $description = "T" . $episode['season'] . "E" . $episode['episode_num'];
                    //$title = "Episódio " . $episode['episode_num'];
                    $stream_id = $episode['id']; // URL do M3U8 é o ID do episódio
                    $ext = $episode['container_extension'];
                    $title = "Episódio " . $episode['episode_num']; 
                    $stream_type = "series";
                    $m3u_list .= "#EXTINF:-1 type=\"video\" tvg-id=\"$tvg_id\" group-title=\"$group_title\" description=\"$description\" tvg-logo=\"\",$title\n";
                    $m3u_list .= "{$host}:{$port}/{$stream_type}/{$username}/{$password}/{$stream_id}.{$ext}\n"; // Substitua pela URL correta do stream
                }
            }
        }
        header('Access-Control-Allow-Origin: *');
        //header('Content-Type: application/octet-stream');
        //header('Content-Disposition: attachment; filename="vod.m3u"');
        header('Content-Type: text/plain');
        header('Content-Length: ' . strlen($m3u_list));
        echo $m3u_list;
    } else {
        $m3u_list = '#EXTM3U size="Medium"' . "\n";
        $name = 'ERRO AO BAIXAR M3U';
        $stream_icon = 'https://png.pngtree.com/png-clipart/20200701/original/pngtree-red-error-icon-png-image_5418881.jpg';
        // Adiciona a entrada M3U com group-title
        $m3u_list .= "#EXTINF:-1 type=\"playlist\" tvg-id=\"{$name}\" tvg-logo=\"{$stream_icon}\" description=\"\",{$name}\n";
        $m3u_list .= "http://localhost/erro.m3u" . "\n"; 
    }
} else {
    echo 'Parâmetros insuficientes fornecidos.';
}
?>
