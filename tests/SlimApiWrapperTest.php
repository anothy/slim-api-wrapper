<?php

namespace Anothy\SlimApiWrapper\Tests;

use Anothy\SlimApiWrapper;
use PHPUnit\Framework\TestCase;

/**
 * Test SlimApiWrapperTest
 */
class SlimApiWrapperTest extends TestCase
{
    /**
     * @var Builder
     */
    private $builder;

    protected function setUp()
    {
        $this->builder = new Builder($this);
    }

    protected function tearDown()
    {
        $this->builder = null;
    }

    /**
     * Test 'call' method.
     */
    public function testCallMethod()
    {
        // Test where Container has the Callable route.
        $container = $this->builder->stubContainer([
            'expects' => [
                'get' => 2,
                'has' => 1,
            ],
        ]);
        $saw = new SlimApiWrapper($container);

        $result = $saw->call('GET', 'test-route');

        $this->assertEquals(200, $result['statusCode']);
        $this->assertEquals('bar', $result['foo']);

        // Test where Container does NOT have Callable route.
        $container = $this->builder->stubContainer([
            'map' => [
                'has' => [
                    'CallableRoute' => ['CallableRoute', false],
                ],
                'get' => []
            ],
            'expects' => [
                'get' => 1,
                'has' => 1,
            ],
        ]);
        $saw = new SlimApiWrapper($container);

        $result = $saw->call('GET', 'test-route');

        $this->assertEquals(200, $result['statusCode']);
        $this->assertEquals('bar', $result['foo']);
    }

    /**
     * Test the `callMiddlewareStack` method.
     */
    public function testCallMiddlewareStack()
    {
        // Test where Container does NOT have Callable route.
        $container = $this->builder->stubContainer([
            'map' => [
                'has' => [],
                'get' => []
            ],
            'expects' => [
                'get' => 1,
                'has' => 1,
            ],
        ]);
        $saw = new SlimApiWrapper($container);

        $result = $saw->callMiddlewareStack('GET', 'test-route');

        $this->assertEquals(200, $result['statusCode']);
        $this->assertEquals('bar', $result['foo']);
    }
}
