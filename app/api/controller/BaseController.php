<?php

namespace app\api\controller;

class BaseController
{

    /**
     * __call magic method
     */
    public function __call($name, $arguments)
    {
        $this->sendOutput('', array('HTTP/1.1 404 Not Found'));
    }

    /**
     * Get URI elements.
     * 
     * @return array
     */
    protected function getUriSegments()
    {
        $uri = parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'), PHP_URL_PATH);
        $uri = explode('/', $uri);

        return $uri;
    }

    /**
     * Get Query String parameters
     * 
     * @return array
     */
    protected function getQueryStringParams()
    {
        return parse_str(filter_input(INPUT_SERVER, 'QUERY_STRING'), $query);
    }

    /**
     * Send API Output
     * @param mixed $data
     * @param array $httpHeaders
     */
    protected function sendOutput($data, $httpHeaders = array())
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

        $input = filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        return $input;
    }

    /**
     * Validates Email input
     */
    protected function validEmail($input)
    {
        $input = filter_var($input, FILTER_SANITIZE_EMAIL);
        $input = filter_var($input, FILTER_VALIDATE_EMAIL);

        return $input;
    }

}
