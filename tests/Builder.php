<?php

namespace Anothy\SlimApiWrapper\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class Factory
 */
class Builder
{
    /**
     * @var TestCase
     */
    private $testcase;

    /**
     * Builder constructor.
     *
     * @param TestCase $testcase
     */
    public function __construct(TestCase $testcase)
    {
        $this->testcase = $testcase;
    }

    /**
     * Create a mock object of Slim\Container
     *
     * @param array $config The map for 'has' and 'get'.
     *
     * @return \Slim\Container|\PHPUnit_Framework_MockObject_MockObject
     */
    public function stubContainer($config = [])
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

        $stub = $this->testcase->getMockBuilder('Slim\Container')
            ->setMethods(['get','has'])
            ->getMock();

        $stub->expects($this->testcase->exactly($config['expects']['get']))
            ->method('get')
            ->will($this->testcase->returnValueMap($getMap));

        $stub->expects($this->testcase->exactly($config['expects']['has']))
            ->method('has')
            ->will($this->testcase->returnValueMap($hasMap));

        return $stub;
    }

    /**
     * Create a mock object of Slim\Router
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function stubRouter()
    {
        $stub = $this->testcase->getMockBuilder('Router')
            ->setMethods(['getNamedRoute'])
            ->getMock();

        $stub->expects($this->testcase->exactly(1))
            ->method('getNamedRoute')
            ->will(
                $this->testcase->returnValue($this->stubRoute())
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
        $stub = $this->testcase->getMockBuilder('Route')
            ->setMethods(['getMethods','getCallable'])
            ->getMock();

        $stub->expects($this->testcase->exactly(1))
            ->method('getMethods')
            ->will($this->testcase->returnValue(['GET','POST']));

        $stub->expects($this->testcase->exactly(1))
            ->method('getCallable')
            ->will(
                $this->testcase->returnValue($callableRoute)
            );

        return $stub;
    }
}
