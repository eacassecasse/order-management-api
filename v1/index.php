<?php

require '../vendor/autoload.php';


$uri = parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'), PHP_URL_PATH);
$uri = explode('/', $uri);

// Verifying the endpoints
if ((isset($uri[3]) && ($uri[3] != 'products' && $uri[3] != 'suppliers' && $uri[3] != 'storages')) ||
    (!isset($uri[3]))) {
    header('HTTP/1.1 404 Not Found');
    exit();
}

if (!authenticate()) {
    header('HTTP/1.1 401 Unauthorized');
    exit();
}

// Getting request method 
$method = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// Get Query String
parse_str($_SERVER['QUERY_STRING'] ?? '', $query);

// Getting Main Endpoint
$endpoint = $uri[3];

// Building an instance of object by getting endpoint
$instance = substr($endpoint, 0, (strlen($endpoint) - 1));

// Creating entity from an instance of endpoint
$entity = "\App\api\controller\\" . ucfirst($instance) . 'Controller';

$controller = new $entity($method);
$controller->processRequest();

function authenticate(): bool
{
    return true;
}
