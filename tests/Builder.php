<?php

namespace Anothy\SlimApiWrapper\Tests;

use PHPUnit\Framework\TestCase;
use Slim\Router;
use Slim\Route;

/**
 * Builder for Container, Route, Router objects.
 */
class Builder
{
    /**
     * @var TestCase
     */
    private $testcase;

    /**
     * Container Configuration
     *
     * @var array
     */
    private $containerConfig = [];

    /**
     * Router Configuration
     *
     * @var array
     */
    private $routerConfig = [];

    /**
     * Route Configuration
     *
     * @var array
     */
    private $routeConfig = [];

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
     *
     * @return \Slim\Container|\PHPUnit_Framework_MockObject_MockObject
     */
    public function stubContainer()
    {
        $stub = $this->testcase->getMockBuilder('Slim\Container')
            ->setMethods(['get','has'])
            ->getMock();

        $stub->expects($this->testcase->exactly(
            $this->containerConfig['get']['expects']
        ))
            ->method('get')
            ->will($this->testcase->returnValueMap(
                $this->containerConfig['get']['map']
            ));

        $stub->expects($this->testcase->exactly(
            $this->containerConfig['has']['expects']
        ))
            ->method('has')
            ->will($this->testcase->returnValueMap(
                $this->containerConfig['has']['map']
            ));

        return $stub;
    }

    /**
     * Configure the container.
     *
     * @param array $config
     *
     * @return self
     */
    public function configureContainer($config = [])
    {
        $this->containerConfig = array_merge([
            'get' => [
                'expects' => 1,
                'map'     => [
                    'router' => ['router', $this->stubRouter()],
                    'CallableRoute' => [
                        '\MFR\Tests\FakeSlimApp',
                        new FakeSlimApp()
                    ],
                ],
            ],
            'has' => [
                'expects' => 1,
                'map'     => [
                    'CallableRoute' => ['\MFR\Tests\FakeSlimApp', true],
                ],
            ],
        ], $config);

        return $this;
    }

    /**
     * Create a mock object of Slim\Router
     *
     * @return Router|\PHPUnit_Framework_MockObject_MockObject
     */
    public function stubRouter()
    {
        $stub = $this->testcase->getMockBuilder('Router')
            ->setMethods(['getNamedRoute'])
            ->getMock();

        $stub->expects($this->testcase->exactly(
            $this->routerConfig['getNamedRoute']['expects']
        ))
            ->method('getNamedRoute')
            ->will(
                $this->testcase->returnValueMap(
                    $this->routerConfig['getNamedRoute']['map']
                )
            );

        return $stub;
    }

    /**
     * Configure the Router
     *
     * @param array $config
     *
     * @return self
     */
    public function configureRouter($config = [])
    {
        $this->routerConfig = array_merge([
            'getNamedRoute' => [
                'expects' => 1,
                'map' => [],
            ],
        ], $config);

        return $this;
    }

    /**
     * Create a mock object of Slim\Route
     *
     * @return Route|\PHPUnit_Framework_MockObject_MockObject
     */
    public function stubRoute()
    {
        $stub = $this->testcase->getMockBuilder('Route')
            ->setMethods(['getMethods','getCallable'])
            ->getMock();

        $stub->expects($this->testcase->exactly(
            $this->routeConfig['getMethods']['expects']
        ))
            ->method('getMethods')
            ->will($this->testcase->returnValue(
                $this->routeConfig['getMethods']['return']
            ));

        $stub->expects($this->testcase->exactly(
            $this->routeConfig['getCallable']['expects']
        ))
            ->method('getCallable')
            ->will(
                $this->testcase->returnValue(
                    $this->routeConfig['getCallable']['return']
                )
            );

        return $stub;
    }

    /**
     * Configure the Route object.
     *
     * @param array $config
     *
     * @return self
     */
    public function configureRoute($config = [])
    {
        $this->routeConfig = array_merge([
            'getMethods'  => [
                'expects' => 1,
                'return' => ['GET','POST'],
            ],
            'getCallable' => [
                'expects' => 1,
                'return' => '\MFR\Tests\FakeSlimApp',
            ],
        ], $config);

        return $this;
    }
}
