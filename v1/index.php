<?php



require_once '../vendor/autoload.php';

use App\api\controller\ProductController;
use App\api\controller\SupplierController;
use App\api\controller\StorageController;
use App\api\controller\UserController;

$url = parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'), PHP_URL_PATH);

$url = explode('/', $url);

validateURI($url);

function validateURI(?array $url)
{

    if (is_array($url) && count($url)) {
        if (isset($url[1]) && strlen($url[1]) !== 0) {
            if (validateVersion($url[1])) {
                if (isset($url[2]) && strlen($url[2]) !== 0) {
                    if (validatePublicFolder($url[2])) {
                        if (isset($url[3]) && strlen($url[3]) !== 0) {
                            validateEndpoints($url[3], $url);
                        }
                        else {
                            header('HTTP/1.1 404 Not Found');
                        }
                    }
                    else {
                        header('HTTP/1.1 404 Not Found');
                    }
                }
                else {
                    header('HTTP/1.1 404 Not Found');
                }
            }
        }
        else {
            header('HTTP/1.1 404 Not Found');
            exit();
        }
    }
}

function validateVersion(?string $version)
{
    if ($version && $version === 'v1') {
        return true;
    }

    header('HTTP/1.1 404 Not Found');
    exit();
}

function validatePublicFolder(?string $public_folder)
{
    if ($public_folder && $public_folder === 'endpoints') {
        return true;
    }
    else {
        header('HTTP/1.1 404 Not Found');
        exit();
    }
}

function validateEndpoints(?string $endpoint, $path = array())
{

    if ($endpoint) {

        if ($endpoint === 'products') {
            redirectProducts($endpoint, $path);
        }
        else if ($endpoint === 'storages') {
            redirectStorages($endpoint, $path);
        }
        else if ($endpoint === 'suppliers') {
            redirectSuppliers($endpoint, $path);
        }
        else if ($endpoint === 'users') {
            redirectUsers($endpoint, $path);
        }
        else {
            header('HTTP/1.1 404 Not Found');
            exit();
        }
    }
    else {
        header('HTTP/1.1 404 Not Found');
        exit();
    }
}

function redirectProducts(string $endpoint, $path)
{
    $controller = new ProductController();

    if (isset($path[4]) && strlen($path[4]) !== 0) {

        $id = (int)filter_var($path[4], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($id > 0) {
            if (isset($path[5]) && strlen($path[5]) !== 0) {

                $subEndpoint = filter_var($path[5], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                switch ($subEndpoint) {
                    case 'validities':
                        redirectValidities($id, $subEndpoint, $path, $controller);
                        break;
                    case 'suppliers':
                        if (isset($path[6]) && strlen($path[6]) !== 0) {
                            $supplierId = (int)filter_var($path[6], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                            if ($supplierId > 0) {
                                $controller->viewSupplier($id, $supplierId);
                            }
                            else {
                                header('HTTP/1.1 404 Not Found');
                                exit();
                            }
                        }

                        $controller->listSuppliers($id);
                        break;
                    case 'storages':
                        if (isset($path[6]) && strlen($path[6]) !== 0) {
                            $storageId = (int)filter_var($path[6], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                            if ($storageId > 0) {
                                $controller->viewStorage($id, $storageId);
                            }
                            else {
                                header('HTTP/1.1 404 Not Found');
                                exit();
                            }
                        }

                        $controller->listStorages($id);
                        break;
                    case 'update':
                        if (isset($path[6]) && strlen($path[6]) !== 0) {
                            header('HTTP/1.1 404 Not Found');
                            exit();
                        }

                        $controller->update($id);
                        break;
                    case 'delete':
                        if (isset($path[6]) && strlen($path[6]) !== 0) {
                            header('HTTP/1.1 404 Not Found');
                            exit();
                        }

                        $controller->delete($id);
                        break;
                    default:
                        header('HTTP/1.1 404 Not Found');
                        exit();
                }
            }
            else {
                $controller->findOne($id);
            }
        }
        else {
            $subEndpoint = filter_var($path[4], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            if ($subEndpoint === 'create') {
                $controller->create();
            }
            else {
                header('HTTP/1.1 404 Not Found');
                exit();
            }
        }
    }
    else {
        $controller->find();
    }
}

function redirectValidities(int $productId, string $endpoint, $path = array(), ProductController $controller)
{
    if (isset($path[6]) && strlen($path[6]) !== 0) {
        $validityId = (int)filter_var($path[6], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($validityId > 0) {
            if (isset($path[7]) && strlen($path[7]) !== 0) {
                if ((int)filter_var($path[7], FILTER_SANITIZE_FULL_SPECIAL_CHARS) > 0) {
                    header('HTTP/1.1 404 Not Found');
                    exit();
                }
                else {
                    $subEndpoint = filter_var($path[7], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                    switch ($subEndpoint) {
                        case 'edit':
                            $controller->edit($productId, $validityId);
                            break;
                        case 'remove':
                            $controller->remove($productId, $validityId);
                            break;
                        default:
                            header('HTTP/1.1 404 Not Found');
                            exit();
                    }
                }
            }
            else {
                $controller->view($productId, $validityId);
            }
        }
        else {
            $subEndpoint = filter_var($path[6], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            if ($subEndpoint === 'add') {
                $controller->add($productId);
            }
            else {
                header('HTTP/1.1 404 Not Found');
                exit();
            }
        }
    }
    else {
        $controller->listValidities($productId);
    }
}


function redirectStorages(string $endpoint, $path)
{
    $controller = new StorageController();

    if (isset($path[4]) && strlen($path[4]) !== 0) {

        $id = (int)filter_var($path[4], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($id > 0) {
            if (isset($path[5]) && strlen($path[5]) !== 0) {

                $subEndpoint = filter_var($path[5], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                switch ($subEndpoint) {
                    case 'products':
                        redirectStoredProducts($id, $subEndpoint, $path, $controller);
                        break;
                    case 'update':
                        if (isset($path[6]) && strlen($path[6]) !== 0) {
                            header('HTTP/1.1 404 Not Found');
                            exit();
                        }

                        $controller->update($id);
                        break;
                    case 'delete':
                        if (isset($path[6]) && strlen($path[6]) !== 0) {
                            header('HTTP/1.1 404 Not Found');
                            exit();
                        }

                        $controller->delete($id);
                        break;
                    default:
                        header('HTTP/1.1 404 Not Found');
                        exit();
                }
            }
            else {
                $controller->findOne($id);
            }
        }
        else {
            $subEndpoint = filter_var($path[4], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            if ($subEndpoint === 'create') {
                $controller->create();
            }
            else {
                header('HTTP/1.1 404 Not Found');
                exit();
            }
        }
    }
    else {
        $controller->find();
    }
}

function redirectStoredProducts(int $storageId, string $endpoint, $path = array(), StorageController $controller)
{
    if (isset($path[6]) && strlen($path[6]) !== 0) {
        $productId = (int)filter_var($path[6], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($productId > 0) {
            if (isset($path[7]) && strlen($path[7]) !== 0) {
                if ((int)filter_var($path[7], FILTER_SANITIZE_FULL_SPECIAL_CHARS) > 0) {
                    header('HTTP/1.1 404 Not Found');
                    exit();
                }
                else {
                    $subEndpoint = filter_var($path[7], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                    switch ($subEndpoint) {
                        case 'edit':
                            $controller->edit($storageId, $productId);
                            break;
                        case 'remove':
                            $controller->remove($storageId, $productId);
                            break;
                        default:
                            header('HTTP/1.1 404 Not Found');
                            exit();
                    }
                }
            }
            else {
                $controller->view($storageId, $productId);
            }
        }
        else {
            $subEndpoint = filter_var($path[6], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            if ($subEndpoint === 'add') {
                $controller->add($storageId);
            }
            else {
                header('HTTP/1.1 404 Not Found');
                exit();
            }
        }
    }
    else {
        $controller->list($storageId);
    }
}

function redirectSuppliers(string $endpoint, $path)
{
    $controller = new SupplierController();

    if (isset($path[4]) && strlen($path[4]) !== 0) {

        $id = (int)filter_var($path[4], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($id > 0) {
            if (isset($path[5]) && strlen($path[5]) !== 0) {

                $subEndpoint = filter_var($path[5], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                switch ($subEndpoint) {
                    case 'products':
                        redirectSupplierProducts($id, $subEndpoint, $path, $controller);
                        break;
                    case 'update':
                        if (isset($path[6]) && strlen($path[6]) !== 0) {
                            header('HTTP/1.1 404 Not Found');
                            exit();
                        }

                        $controller->update($id);
                        break;
                    case 'delete':
                        if (isset($path[6]) && strlen($path[6]) !== 0) {
                            header('HTTP/1.1 404 Not Found');
                            exit();
                        }

                        $controller->delete($id);
                        break;
                    default:
                        header('HTTP/1.1 404 Not Found');
                        exit();
                }
            }
            else {
                $controller->findOne($id);
            }
        }
        else {
            $subEndpoint = filter_var($path[4], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            if ($subEndpoint === 'create') {
                $controller->create();
            }
            else {
                header('HTTP/1.1 404 Not Found');
                exit();
            }
        }
    }
    else {
        $controller->find();
    }
}

function redirectSupplierProducts(int $supplierId, string $endpoint, $path = array(), SupplierController $controller)
{
    if (isset($path[6]) && strlen($path[6]) !== 0) {
        $productId = (int)filter_var($path[6], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($productId > 0) {
            if (isset($path[7]) && strlen($path[7]) !== 0) {
                if ((int)filter_var($path[7], FILTER_SANITIZE_FULL_SPECIAL_CHARS) > 0) {
                    header('HTTP/1.1 404 Not Found');
                    exit();
                }
                else {
                    $subEndpoint = filter_var($path[7], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                    switch ($subEndpoint) {
                        case 'edit':
                            $controller->edit($supplierId, $productId);
                            break;
                        case 'remove':
                            $controller->remove($supplierId, $productId);
                            break;
                        default:
                            header('HTTP/1.1 404 Not Found');
                            exit();
                    }
                }
            }
            else {
                $controller->view($supplierId, $productId);
            }
        }
        else {
            $subEndpoint = filter_var($path[6], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            if ($subEndpoint === 'add') {
                $controller->add($supplierId);
            }
            else {
                header('HTTP/1.1 404 Not Found');
                exit();
            }
        }
    }
    else {
        $controller->list($supplierId);
    }
}


function redirectUsers(string $endpoint, $path)
{
    $controller = new UserController();

    if (isset($path[4]) && strlen($path[4]) !== 0) {

        $id = (int)filter_var($path[4], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($id > 0) {
            if (isset($path[5]) && strlen($path[5]) !== 0) {

                $subEndpoint = filter_var($path[5], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                switch ($subEndpoint) {
                    case 'update':
                        if (isset($path[6]) && strlen($path[6]) !== 0) {
                            header('HTTP/1.1 404 Not Found');
                            exit();
                        }

                        $controller->update($id);
                        break;
                    case 'delete':
                        if (isset($path[6]) && strlen($path[6]) !== 0) {
                            header('HTTP/1.1 404 Not Found');
                            exit();
                        }

                        $controller->delete($id);
                        break;
                    default:
                        header('HTTP/1.1 404 Not Found');
                        exit();
                }
            }
            else {
                $controller->findOne($id);
            }
        }
        else {
            $subEndpoint = filter_var($path[4], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            if ($subEndpoint === 'create') {
                $controller->create();
            }
            else {
                header('HTTP/1.1 404 Not Found');
                exit();
            }
        }
    }
    else {
        $controller->find();
    }
}