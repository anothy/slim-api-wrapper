<?php

namespace Anothy\SlimApiWrapper\Tests;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Body;

/**
 * FakeSlimApp
 *
 *
 *
 */
class FakeSlimApp
{
    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param                   $ags
     *
     * @return ResponseInterface
     */
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        $ags
    ) {
        $body = new Body(fopen('php://temp', 'r+'));

        $body->write(json_encode(['foo' => 'bar']));

        return $response->withBody($body);
    }
}
