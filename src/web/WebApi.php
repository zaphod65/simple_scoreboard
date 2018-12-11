<?php

$base_dir = $_SERVER['DOCUMENT_ROOT'];
require_once("$base_dir/scoreboard/src/Exceptions/Exceptions.php");

/**
 * Provides a simple interface for defining the actions and requirements
 * of a web API endpoint.
 */
class WebApi {
    private $method;
    private $service;
    private $function;
    private $required;

    /**
     * Build a new web API
     *
     * @param method The HTTP method allowed to call this endpoint
     * @param service The service object that will be used to fulfill the action of the API.
     * @param function The function to be called on the service object.
     * @param required The parameters required for the service function.
     */
    public function __construct($method, $service, $function, $required) {
        $this->method = $method;
        $this->service = $service;
        $this->function = $function;
        $this->required = $required;
    }

    /**
     * Process the API request.
     */
    public function process() {
        header("Content-Type: application/json");
        if ($_SERVER['REQUEST_METHOD'] != $this->method) {
            print $this->error(405, 'Method not allowed');
            return false;
        }

        $args = $this->getArgs();
        if (!$args) {
            print $this->error(400, 'Missing arguments.');
            return false;
        }

        try {
            // Allowing only a single service call keeps the web layer very clean
            // and means it is totally free of business logic
            $result = $this->service->{$this->function}($args);
        } catch (\Exceptions\BaseException $e) {
            // Any error that inherits from BaseException will have what we need to
            // make this call work
            print $this->error($e->getHttpCode(), $e->getMessage());
            return false;
        }
        // This response code happens implicitly anyway, but set it explicitly here
        // for clarity
        http_response_code(200);
        print json_encode($result);
    }

    private function error($code, $error) {
        http_response_code($code);
        return json_encode([
            'error' => $error,
        ]);
    }

    private function getArgs() {
        if ($this->method != 'GET') {
            $data = json_decode(file_get_contents('php://input'));
        } else {
            $data = new \stdClass;
            foreach ($_GET as $k => $v) {
                $data->{$k} = $v;
            }
        }

        if (!$data && $this->required) {
            return false;
        }
        foreach ($this->required as $argument) {
            if (!$data->{$argument}) {
                return false;
            }
        }

        return $data;
    }

}
