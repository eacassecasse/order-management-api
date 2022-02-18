<?php



require_once '../vendor/autoload.php';

use App\api\controller\ProductController;
use App\core\shared\Utilities;
use App\domain\service\ProductService;
use App\domain\service\StorageService;
use App\domain\service\SupplierService;
use Hateoas\HateoasBuilder;
use Hateoas\Configuration\Route;
use Hateoas\UrlGenerator\CallableUrlGenerator;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use App\api\controller\SupplierController;
use App\api\controller\StorageController;
use App\api\controller\UserController;

$url = parse_url(filter_input(\INPUT_SERVER, 'REQUEST_URI'), \PHP_URL_PATH);
$url = explode('/', $url);
if (strlen($url[2]) === 0 || $url[2] !== 'endpoints') {
    header('HTTP/1.1 404 Not Found');
    exit();
}

if (strlen($url[3]) === 0 || $url[3] !== 'products') {
    header("HTTP/1.1 404 Not Found");
    exit();
}


$service = new ProductService();

$urlGenerator = new CallableUrlGenerator(
    function ($route, array $parameters) {
        return $route . '?' . http_build_query($parameters);
    });

$hateoas = HateoasBuilder::create()->setUrlGenerator(null, $urlGenerator)->build();

/*$adapter = new ArrayAdapter(Utilities::toSupplierProductOutputCollectionModel($service->listProducts(1)));
 $pager = Pagerfanta::createForCurrentPageWithMaxPerPage($adapter, 1, 3);
 $factory = new PagerfantaFactory();
 $urlGenerator->generate('supplier_products/', array('id' => 1, 'name' => 'Eddie'));
 $router = new Route('supplier_products', array());
 $paginatedCollection = $factory->createRepresentation($pager, $router);
 $json = $hateoas->serialize($paginatedCollection, 'json');*/

/*$paginatedCollection = new PaginatedRepresentation(
 new CollectionRepresentation(Utilities::toSupplierProductOutputCollectionModel($service->listProducts(1))),
 'supplierProducts',
 array(),
 1,
 3,
 10    );
 $json = $hateoas->serialize($paginatedCollection, 'json');
 echo $json; //var_dump($router);*/

$controller = new UserController();
$controller->delete(2);
