<?php

require '../vendor/autoload.php';


$uri = parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'), PHP_URL_PATH);
$uri = explode('/', $uri);

// Verifying the endpoints 
if (((isset($uri[3]) && $uri[3] != 'products') &&

(isset($uri[3]) && $uri[3] != 'suppliers') &&

(isset($uri[3]) && $uri[3] != 'storages')) ||

(!isset($uri[3]))) {
    header('HTTP/1.1 404 Not Found');
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

$controller = new $entity();

//Dealing with business logic for endpoints and sub-endpoints
if ($endpoint === 'products') {
    if (isset($uri[4])) {
        $id = $uri[4];

        if (isset($uri[5])) {
            switch ($uri[5]) {
                case 'validities':
                    if (isset($uri[6])) {
                        switch ($method) {
                            case 'GET':
                                $controller->view($uri[4], $uri[6]);
                                break;
                            case 'PUT':
                                $controller->edit($uri[4], $uri[6]);
                                break;
                            case 'DELETE':
                                $controller->remove($uri[4], $uri[6]);
                                break;
                            default:
                                $controller->methodNotSupported(strtoupper($method));
                                break;
                        }
                    }
                    else {
                        switch ($method) {
                            case 'GET':
                                $page = (isset($query['page']) && $query['page'] != '') ? 
                                    filter_var($query['page'], FILTER_SANITIZE_NUMBER_INT, array(FILTER_VALIDATE_INT)) : 1;
                                $limit = (isset($query['limit']) && $query['limit'] != '') ? 
                                    filter_var($query['limit'], FILTER_SANITIZE_NUMBER_INT, array(FILTER_VALIDATE_INT)) : 10;
                                $sorts = isset($query['sort']) ? parseSortParams($query['sort']) : array(["validity_id", "ASC"]);

                                $controller->listValidities($uri[4], $page, $limit, $sorts);
                                break;
                            case 'POST':
                                $controller->add($uri[4]);
                                break;
                            default:
                                $controller->methodNotSupported(strtoupper($method));
                                break;
                        }
                    }

                    break;
                case 'suppliers':
                    if (isset($uri[6])) {
                        switch ($method) {
                            case 'GET':
                                $controller->viewSupplier($uri[4], $uri[6]);
                                break;
                            default:
                                $controller->methodNotSupported(strtoupper($method));
                                break;
                        }
                    }
                    else {
                        switch ($method) {
                            case 'GET':
                                $page = (isset($query['page']) && $query['page'] != '') ? 
                                    filter_var($query['page'], FILTER_SANITIZE_NUMBER_INT, array(FILTER_VALIDATE_INT)) : 1;
                                $limit = (isset($query['limit']) && $query['limit'] != '') ? 
                                    filter_var($query['limit'], FILTER_SANITIZE_NUMBER_INT, array(FILTER_VALIDATE_INT)) : 10;
                                $sorts = isset($query['sort']) ? parseSortParams($query['sort']) : array(["supplier_id", "ASC"]);

                                $controller->listSuppliers($uri[4], $page, $limit, $sorts);
                                break;
                            default:
                                $controller->methodNotSupported(strtoupper($method));
                                break;
                        }
                    }

                    break;
                case 'storages':
                    if (isset($uri[6])) {
                        switch ($method) {
                            case 'GET':
                                $controller->viewStorage($uri[4], $uri[6]);
                                break;
                            default:
                                $controller->methodNotSupported(strtoupper($method));
                                break;
                        }
                    }
                    else {
                        switch ($method) {
                            case 'GET':
                                $page = (isset($query['page']) && $query['page'] != '') ? 
                                    filter_var($query['page'], FILTER_SANITIZE_NUMBER_INT, array(FILTER_VALIDATE_INT)) : 1;
                                $limit = (isset($query['limit']) && $query['limit'] != '') ? 
                                    filter_var($query['limit'], FILTER_SANITIZE_NUMBER_INT, array(FILTER_VALIDATE_INT)) : 10;
                                $sorts = isset($query['sort']) ? parseSortParams($query['sort']) : array(["storage_id", "ASC"]);

                                $controller->listStorages($uri[4], $page, $limit, $sorts);
                                break;
                            default:
                                $controller->methodNotSupported(strtoupper($method));
                                break;
                        }
                    }

                    break;
                default:
                    $controller->notFound();
                    exit();
            }
        }
        else {
            switch (strtoupper($method)) {
                case 'GET':
                    $controller->findOne($uri[4]);
                    break;
                case 'PUT':
                    $controller->update($uri[4]);
                    break;
                case 'DELETE':
                    $controller->delete($uri[4]);
                    break;
                default:
                    $controller->methodNotSupported(strtoupper($method));
                    break;
            }
        }
    }
    else {
        switch (strtoupper($method)) {
            case 'GET':
                $page = (isset($query['page']) && $query['page'] != '') ? 
                    filter_var($query['page'], FILTER_SANITIZE_NUMBER_INT, array(FILTER_VALIDATE_INT)) : 1;
                $limit = (isset($query['limit']) && $query['limit'] != '') ? 
                    filter_var($query['limit'], FILTER_SANITIZE_NUMBER_INT, array(FILTER_VALIDATE_INT)) : 10;
                $sorts = isset($query['sort']) ? parseSortParams($query['sort']) : array(["id", "ASC"]);

                $controller->find($page, $limit, $sorts);
                break;
            case 'POST':
                echo $controller->create();
                break;
            default:
                $controller->methodNotSupported(strtoupper($method));
                break;
        }
    }
}
else if ($endpoint === 'suppliers') {
    if (isset($uri[4])) {
        $id = $uri[4];

        if (isset($uri[5])) {
            switch ($uri[5]) {
                case 'products':
                    if (isset($uri[6])) {
                        switch ($method) {
                            case 'GET':
                                $controller->view($uri[4], $uri[6]);
                                break;
                            case 'PUT':
                                $controller->edit($uri[4], $uri[6]);
                                break;
                            case 'DELETE':
                                $controller->remove($uri[4], $uri[6]);
                                break;
                            default:
                                $controller->methodNotSupported(strtoupper($method));
                                break;
                        }
                    }
                    else {
                        switch ($method) {
                            case 'GET':
                                $page = (isset($query['page']) && $query['page'] != '') ? 
                                    filter_var($query['page'], FILTER_SANITIZE_NUMBER_INT, array(FILTER_VALIDATE_INT)) : 1;
                                $limit = (isset($query['limit']) && $query['limit'] != '') ? 
                                    filter_var($query['limit'], FILTER_SANITIZE_NUMBER_INT, array(FILTER_VALIDATE_INT)) : 10;
                                $sorts = isset($query['sort']) ? parseSortParams($query['sort']) : array(["product_id", "ASC"]);

                                $controller->list($uri[4], $page, $limit, $sorts);
                                break;
                            case 'POST':
                                $controller->add($uri[4]);
                                break;
                            default:
                                $controller->methodNotSupported(strtoupper($method));
                                break;
                        }
                    }

                    break;

                default:
                    $controller->notFound();
                    exit();
            }
        }
        else {
            switch (strtoupper($method)) {
                case 'GET':
                    $controller->findOne($uri[4]);
                    break;
                case 'PUT':
                    $controller->update($uri[4]);
                    break;
                case 'DELETE':
                    $controller->delete($uri[4]);
                    break;
                default:
                    $controller->methodNotSupported(strtoupper($method));
                    break;
            }
        }
    }
    else {
        switch (strtoupper($method)) {
            case 'GET':
                $page = (isset($query['page']) && $query['page'] != '') ? 
                    filter_var($query['page'], FILTER_SANITIZE_NUMBER_INT, array(FILTER_VALIDATE_INT)) : 1;
                $limit = (isset($query['limit']) && $query['limit'] != '') ? 
                    filter_var($query['limit'], FILTER_SANITIZE_NUMBER_INT, array(FILTER_VALIDATE_INT)) : 10;
                $sorts = isset($query['sort']) ? parseSortParams($query['sort']) : array(["id", "ASC"]);

                $controller->find($page, $limit, $sorts);
                break;
            case 'POST':
                $controller->create();
                break;
            default:
                $controller->methodNotSupported(strtoupper($method));
                break;
        }
    }
}
else if ($endpoint === 'storages') {
    if (isset($uri[4])) {
        $id = $uri[4];

        if (isset($uri[5])) {
            switch ($uri[5]) {
                case 'products':
                    if (isset($uri[6])) {
                        switch ($method) {
                            case 'GET':
                                $controller->view($uri[4], $uri[6]);
                                break;
                            case 'PUT':
                                $controller->edit($uri[4], $uri[6]);
                                break;
                            case 'DELETE':
                                $controller->remove($uri[4], $uri[6]);
                                break;
                            default:
                                $controller->methodNotSupported(strtoupper($method));
                                break;
                        }
                    }
                    else {
                        switch ($method) {
                            case 'GET':
                                $page = (isset($query['page']) && $query['page'] != '') ? 
                                    filter_var($query['page'], FILTER_SANITIZE_NUMBER_INT, array(FILTER_VALIDATE_INT)) : 1;
                                $limit = (isset($query['limit']) && $query['limit'] != '') ? 
                                    filter_var($query['limit'], FILTER_SANITIZE_NUMBER_INT, array(FILTER_VALIDATE_INT)) : 10;
                                $sorts = isset($query['sort']) ? parseSortParams($query['sort']) : array(["product_id", "ASC"]);

                                $controller->list($uri[4], $page, $limit, $sorts);
                                break;
                            case 'POST':
                                $controller->add($uri[4]);
                                break;
                            default:
                                $controller->methodNotSupported(strtoupper($method));
                                break;
                        }
                    }

                    break;

                default:
                    $controller->notFound();
                    exit();
            }
        }
        else {
            switch (strtoupper($method)) {
                case 'GET':
                    $controller->findOne($uri[4]);
                    break;
                case 'PUT':
                    $controller->update($uri[4]);
                    break;
                case 'DELETE':
                    $controller->delete($uri[4]);
                    break;
                default:
                    $controller->methodNotSupported(strtoupper($method));
                    break;
            }
        }
    }
    else {
        switch (strtoupper($method)) {
            case 'GET':
                $page = (isset($query['page']) && $query['page'] != '') ? 
                    filter_var($query['page'], FILTER_SANITIZE_NUMBER_INT, array(FILTER_VALIDATE_INT)) : 1;
                $limit = (isset($query['limit']) && $query['limit'] != '') ? 
                    filter_var($query['limit'], FILTER_SANITIZE_NUMBER_INT, array(FILTER_VALIDATE_INT)) : 10;
                $sorts = isset($query['sort']) ? parseSortParams($query['sort']) : array(["id", "ASC"]);

                $controller->find($page, $limit, $sorts);
                break;
            case 'POST':
                $controller->create();
                break;
            default:
                $controller->methodNotSupported(strtoupper($method));
                break;
        }
    }

}

function parseSortParams(array $sortParams): array
{

    $parsedSortParams = array();

    if (count($sortParams)) {
        foreach ($sortParams as $param) {
            $parsedSortParam = array();

            $explodedParam = explode(",", $param);
            $parameter = $explodedParam[0];

            if (($explodedParam[1] !== 'desc') || ($explodedParam[1] !== 'asc')) {
                $orderDirection = 'ASC';
            }

            $orderDirection = strtoupper($explodedParam[1]);

            array_push($parsedSortParam, $parameter);
            array_push($parsedSortParam, $orderDirection);

            array_push($parsedSortParams, $parsedSortParam);
        }
    }

    return $parsedSortParams;
}