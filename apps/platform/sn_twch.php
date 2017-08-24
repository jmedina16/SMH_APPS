<?php

$url = 'https://api.twitch.tv/kraken/user';
$data = array();
$test = curlPost($url, $data);

print_r($test);


function curlPost($url, $data) {
    $final_url = $url . '?' . http_build_query($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $final_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/vnd.twitchtv.v5+json',
        'Client-ID: hachm5pc7975xa5t07y4pdmgmvhqsy',
        'Authorization: OAuth q9mxctsxxynpujocqqbdsu3e7de59e'
    ));
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}
?>
    
