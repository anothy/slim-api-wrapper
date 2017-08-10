<?php

namespace Anothy\SlimApiWrapper\Tests;

use Anothy\SlimApiWrapper;
use PHPUnit\Framework\TestCase;

/**
 * Test SlimApiWrapperTest
 */
class SlimApiWrapperTest extends TestCase
{
    public function testSomething()
    {
        $container = $this->stubContainer();
        $saw = new SlimApiWrapper($container);

        $result = $saw->call('GET', 'test-route');

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // Setup the test doubles

    /**
     * Create a mock object of Slim\Container
     *
     * @param array $map Return map for get() method.
     *
     * @return \Slim\Container|\PHPUnit_Framework_MockObject_MockObject
     */
    private function stubContainer($map = [])
    {
        $map = array_merge([
            ['router',        $this->stubRouter()],
            ['CallableRoute', new FakeSlimApp()],
        ], $map);

        $stub = $this->getMockBuilder('Slim\Container')
                     ->setMethods(['get','has'])
                     ->getMock();

        $stub->expects($this->exactly(2))
             ->method('get')
             ->will($this->returnValueMap($map));

        $stub->expects($this->exactly(1))
             ->method('has')
             ->will($this->returnValue(true));

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
    private function stubRoute($callableRoute = 'CallableRoute')
    {
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
