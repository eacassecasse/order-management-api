<?php

$url = parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'), PHP_URL_PATH);
$url = explode('/', $url);

if (count($url)) {
    for ($i = 0; $i < count($url); $i++) {
        if (strlen($url[$i]) === 0) {
            $result = false;
        }
        else {
            if ($url[$i] !== 'v1') {
                header('HTTP/1.1 404 Not Found');
                exit();
            }
        }
    }

    if (!$result) {
        header('HTTP/1.1 404 Not Found');
        exit();
    }
}
else {
    header('HTTP/1.1 404 Not Found');
    exit();
}
