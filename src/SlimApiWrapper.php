<?php

namespace Anothy;

use Slim\Http\Body;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Container;
use Slim\Route;

/**
 * Slim API Wrapper
 *
 * Used as a way of accessing Slim App APIs internally.  There are two types of
 * accessing the APIs, directly where it skips the traversal of middleware(s),
 * or using the full route where it traverses the middleware(s) attached to the
 * Slim app.
 */
class SlimApiWrapper
{
    /**
     * Return as Array
     */
    const RETURN_TYPE_ARRAY = 1;

    /**
     * Return the Response object
     */
    const RETURN_TYPE_RESPONSE = 2;

    /**
     * @var Container
     */
    private $container;

    /**
     * The type of return
     *
     * @var int
     */
    private $returnType;

    /**
     * Constructor
     *
     * @method __construct
     *
     * @param Container $c
     */
    public function __construct(Container $c)
    {
        $this->container = $c;
        $this->returnType = self::RETURN_TYPE_ARRAY;
    }

    /**
     * @param $method
     * @param $routeName
     * @param $options
     *
     * @return array
     */
    private function setup($method, $routeName, $options)
    {
        //
        // Setup default values
        //
        $options = array_merge([
            'namedArgs'   => [],
            'payload'     => [],
            'queryParams' => [],
            'headers'     => [],
        ], $options);

        $options = $this->validateOptions($options);

        /** @var Route $route */
        $route = $this->container->get('router')->getNamedRoute($routeName);

        //
        // Check if Method is allowed in Route
        //
        if (!in_array($method, $route->getMethods())) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The method `%s` is not allowed on named route `%s`.',
                    $method,
                    $routeName
                )
            );
        }

        //
        // Build Request and Response objects
        // Add Route to attribute
        //
        $request = $this->buildRequest($method, $options);
        $request = $request->withAttribute('route', $route);
        $response = new Response();

        return [$request, $response, $options];
    }

    /**
     * Call Internal API
     *
     * @param string $method    HTTP Method.
     * @param string $routeName Given route name.
     * @param array  $options   'namedArgs', 'payload', 'queryParams', and 'headers'
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function call($method, $routeName, $options = [])
    {
        /** @var Request $request */
        /** @var Response $response */
        list($request, $response, $options) = $this->setup(
            $method,
            $routeName,
            $options
        );

        //
        // Skip the Middleware in the path and go straight to the app.
        //
        list($routeCallable, $routeMethod) = explode(
            ':',
            $request->getAttribute('route')->getCallable()
        );
        if ($this->container->has($routeCallable)) {
            $routeObj = $this->container->get($routeCallable);
        } else {
            // Not registered in container, so create a new instance and pass
            // Container to constructor.
            $routeObj = new $routeCallable($this->container);
        }

        // No route method found; so it's in an invoke-able Route Object
        if (empty($routeMethod)) {
            $routeMethod = '__invoke';
        }

        //
        // Run the app!
        //
        $response = call_user_func_array([$routeObj, $routeMethod], [
            $request,
            $response,
            $options['namedArgs']
        ]);

        unset($request);

        return $this->getReturn($response);
    }

    /**
     * callMiddlewareStack
     *
     * @param string $method    HTTP Method.
     * @param string $routeName Given route name.
     * @param array  $options   'namedArgs', 'payload', 'queryParams', and 'headers'
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function callMiddlewareStack($method, $routeName, $options = [])
    {
        /** @var Request $request */
        /** @var Response $response */
        list($request, $response) = $this->setup($method, $routeName, $options);

        $response = $request->getAttribute('route')->run($request, $response);

        return $this->getReturn($response);
    }

    /**
     * Build the Request object
     *
     * @param string $method  Request HTTP method.
     * @param array  $options
     *
     * @return Request
     *
     * @throws \UnexpectedValueException
     */
    private function buildRequest($method, $options)
    {
        //
        // Setup Headers
        //
        $headers = array_merge([
            'CONTENT_TYPE' => 'application/json',
        ], $options['headers']);

        //
        // Setup Body
        //
        $body = new Body(fopen('php://temp', 'r+'));
        if (!empty($options['payload'])) {
            if (is_array($options['payload'])) {
                $body->write(json_encode($options['payload']));
            } else {
                throw new \InvalidArgumentException(
                    'The `payload` value must be an array.'
                );
            }
        }

        //
        // Mock ENV for Request while adding headers
        //
        $env = Environment::mock(array_merge([
            'REQUEST_METHOD' => $method,
            'QUERY_STRING'   => http_build_query($options['queryParams']),
        ], $headers));

        //
        // Make Request
        //
        $request = Request::createFromEnvironment($env);

        //
        // Write body
        //
        $request = $request->withBody($body);

        return $request;
    }

    /**
     * Validate the options
     *
     * @param $options
     *
     * @return array
     */
    private function validateOptions($options)
    {
        //
        // Add Route arguments
        //
        if (!is_array($options['namedArgs'])) {
            trigger_error('The `namedArgs` in the options parameter must be an array.', E_USER_NOTICE);
            $options['namedArgs'] = [];
        }

        //
        // Check header options.
        //
        if (!is_array($options['headers'])) {
            trigger_error('The `headers` in the options parameter must be an array.', E_USER_NOTICE);
            $options['headers'] = [];
        }

        //
        // Setup Query Params
        //
        if (!is_array($options['queryParams'])) {
            trigger_error('The `queryParams` in the options parameter must be an array.', E_USER_NOTICE);
            $options['queryParams'] = [];
        }

        return $options;
    }

    /**
     * @param Response $response
     *
     * @return mixed
     */
    private function getReturn(Response $response)
    {
        if ($this->returnType == self::RETURN_TYPE_ARRAY) {
            $return = $this->jsonDecode((string) $response->getBody());
            $statusCode = $response->getStatusCode();

            unset($response);

            return ['statusCode' => $statusCode] + $return;
        } else {
            return $response;
        }
    }

    /**
     * Decode JSON string
     *
     * @param string $jsonString
     *
     * @return array
     *
     * @throws \UnexpectedValueException
     */
    private function jsonDecode($jsonString)
    {
        $json = json_decode($jsonString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \UnexpectedValueException('A JSON string expected.');
        }

        return $json;
    }

    /**
     * Set the return type. Default is RETURN_TYPE_ARRAY.
     *
     * Anything other than 1 will return the Response object.
     *
     * @param int $type Return type.
     *
     * @return self
     */
    public function setReturnType($type)
    {
        if (!is_numeric($type)) {
            $this->returnType = self::RETURN_TYPE_ARRAY;
        }

        $this->returnType = $type;

        return $this;
    }
}
