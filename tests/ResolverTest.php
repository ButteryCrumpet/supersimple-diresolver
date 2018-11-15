<?php

use PHPUnit\Framework\TestCase;
use SuperSimpleDIResolver\Resolver;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ResolverTest extends TestCase
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;
    /**
     * @var Resolver;
     */
    private $resolver;

    public function setUp()
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->container->method("get")
            ->willReturnCallback(function ($str) {
                if ($str === "ServiceOne") {
                    return new ServiceOne();
                }
                if ($str === "ServiceTwo") {
                    return new ServiceTwo();
                }
                if ($str === "service3") {
                    return new ServiceThree();
                }
                throw new ContainerExceptionMock();
            });
        $this->container->method("has")
            ->willReturnCallback(function ($name) {
                return $name === "service3";
            });
        $this->resolver = new Resolver($this->container);
    }

    public function testCanBeInstantiated()
    {
        $this->assertInstanceOf(
            Resolver::class,
            $this->resolver
        );
    }

    public function testResolves()
    {
        $service = $this->resolver->resolve(Service::class);
        $this->assertInstanceOf(
            Service::class,
            $service
        );
    }

    public function testResolvesWithString()
    {
        $service = $this->resolver->resolve("service3");
        $this->assertInstanceOf(
            ServiceThree::class,
            $service
        );
    }

    public function testResolvesWithArgs()
    {
        $service = $this->resolver->resolve(ServiceWith::class, ["message" => "this is a message"]);
        $this->assertInstanceOf(
            ServiceWith::class,
            $service
        );
    }

    /**
     * @expectedException \Psr\Container\ContainerExceptionInterface
     */
    public function testThrowsOnNoneExistingService()
    {
        $this->resolver->resolve(ServiceError::class);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsOnNoneExistingClass()
    {
        $this->resolver->resolve("NopeClass");
    }
}

// Mock Classes

class Service
{
    public function __construct(ServiceOne $one, ServiceTwo $two) {}
}
class ServiceOne{};
class ServiceTwo{};
class ServiceThree{};

class ServiceError
{
    public function __construct(ServiceOne $one, ServiceThree $three)
    {
    }
}

class ServiceWith
{
    public function __construct(ServiceOne $one, $message)
    {
    }
}

class ContainerExceptionMock extends Error implements NotFoundExceptionInterface
{

}