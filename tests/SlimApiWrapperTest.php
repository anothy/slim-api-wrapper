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
     * Test 'call' method.
     */
    public function testCallMethod()
    {
        // Test where Container has the Callable route.
        $container = $this->stubContainer([
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
        $container = $this->stubContainer([
            'map' => [
                'has' => [
                    'CallableRoute' => ['CallableRoute', false],
                ],
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

    // -------------------------------------------------------------------------
    // Setup the test doubles

    /**
     * Create a mock object of Slim\Container
     *
     * @param array $config The map for 'has' and 'get'.
     *
     * @return \Slim\Container|\PHPUnit_Framework_MockObject_MockObject
     */
    private function stubContainer($config = [])
    {
        $config = array_merge([
            'map' => [
                'get' => [],
                'has' => [],
            ],
            'expects' => [
                'get' => 0,
                'has' => 0,
            ]
        ], $config);

        $getMap = array_merge([
            'router'        => ['router',        $this->stubRouter()],
            'CallableRoute' => [
                '\Anothy\SlimApiWrapper\Tests\FakeSlimApp',
                new FakeSlimApp()
            ],
        ], (array) $config['map']['get']);

        $hasMap = array_merge([
            'CallableRoute' => ['\Anothy\SlimApiWrapper\Tests\FakeSlimApp', true],
        ], (array) $config['map']['has']);

        $stub = $this->getMockBuilder('Slim\Container')
                     ->setMethods(['get','has'])
                     ->getMock();

        $stub->expects($this->exactly($config['expects']['get']))
             ->method('get')
             ->will($this->returnValueMap($getMap));

        $stub->expects($this->exactly($config['expects']['has']))
             ->method('has')
             ->will($this->returnValueMap($hasMap));

        return $stub;
    }

    /**
     * Create a mock object of Slim\Router
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function stubRouter()
    {
        $stub = $this->getMockBuilder('Router')
                     ->setMethods(['getNamedRoute'])
                     ->getMock();

        $stub->expects($this->exactly(1))
             ->method('getNamedRoute')
             ->will(
                 $this->returnValue($this->stubRoute())
             );

        return $stub;
    }

    /**
     * Create a mock object of Slim\Route
     *
     * @param string $callableRoute Return by Router:getCallable()
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function stubRoute(
        $callableRoute = '\Anothy\SlimApiWrapper\Tests\FakeSlimApp'
    ) {
        $stub = $this->getMockBuilder('Route')
                     ->setMethods(['getMethods','getCallable'])
                     ->getMock();

        $stub->expects($this->exactly(1))
             ->method('getMethods')
             ->will($this->returnValue(['GET','POST']));

        $stub->expects($this->exactly(1))
             ->method('getCallable')
             ->will(
                 $this->returnValue($callableRoute)
             );

        return $stub;
    }
}
