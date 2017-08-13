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

    /**
     * SlimApiWrapperTest constructor.
     *
     * @param null   $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->builder = new Builder($this);
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
}
