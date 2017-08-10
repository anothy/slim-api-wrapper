<?php

namespace Anothy\SlimApiWrapper\Tests;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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
        return $response;
    }
}
