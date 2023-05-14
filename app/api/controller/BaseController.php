<?php

namespace App\api\controller;

use App\api\exceptionHandler\ApiExceptionHandler;
use Hateoas\Hateoas;
use Hateoas\HateoasBuilder;
use Hateoas\UrlGenerator\CallableUrlGenerator;

class BaseController
{

    protected Hateoas $hateoas;

    /**
     * __call magic method
     */
    public function __call($name, $arguments)
    {
        $this->sendOutput('', array('HTTP/1.1 404 Not Found'));
    }

    public function __construct(
        protected string $method
    ){
        $urlGenerator = new CallableUrlGenerator(function ($route, $parameters) {
            return $route . '?' . http_build_query($parameters);
        });

        $this->hateoas = HateoasBuilder::create()->setUrlGenerator(null, $urlGenerator)->build();
    }

    /**
     * Get URI elements.
     *
     * @return array
     */
    protected function getUriSegments(): array
    {
        $uri = parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'), PHP_URL_PATH);

        return explode('/', $uri);
    }

    /**
     * Get Query String parameters
     *
     * @return array
     */
    protected function getQueryStringParams(): array
    {
        parse_str(filter_input(INPUT_SERVER, 'QUERY_STRING') ?? '', $query);

        return $query;
    }

    /**
     * Send API Output
     * @param mixed $data
     * @param array $httpHeaders
     */
    protected function sendOutput(mixed $data, array $httpHeaders = array())
    {
        header_remove('Set-Cookie');

        if (is_array($httpHeaders) && count($httpHeaders)) {
            foreach ($httpHeaders as $httpHeader) {
                header($httpHeader);
            }
        }

        echo $data;
        exit();
    }

    /**
     * Sanitize, Validate and Escape an input
     */
    protected function clean($input)
    {
        return filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }

    protected function parseSortParams(array $sortParams): array
    {

        $parsedSortParams = array();

        if (count($sortParams)) {
            foreach ($sortParams as $param) {
                $parsedSortParam = array();

                $explodedParam = explode(",", $param);
                $parameter = $explodedParam[0];

                if (($explodedParam[1] !== 'desc') && ($explodedParam[1] !== 'asc')) {
                    $orderDirection = 'ASC';
                }

                $orderDirection = strtoupper($explodedParam[1]);

                $parsedSortParam[] = $parameter;
                $parsedSortParam[] = $orderDirection;

                $parsedSortParams[] = $parsedSortParam;
            }
        }

        return $parsedSortParams;
    }

    protected function methodNotSupported($requestMethod): void
    {
        $errorMessage = ApiExceptionHandler::handleMethodNotSupported('Method not supported', $requestMethod);
        $errorHeader = 'HTTP/1.1 405 Method Not Allowed';

        $this->sendOutput(
            json_encode(array(
                'error' => $errorMessage
            )), array(
                'Content-Type: application/json', $errorHeader
            )
        );
    }
}
