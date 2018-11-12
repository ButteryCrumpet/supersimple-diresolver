<?php

namespace SuperSimpleDIResolver;

use Psr\Container\ContainerInterface;

/**
 * Class Resolver
 * @package SuperSimpleDIResolver
 */
class Resolver implements ResolverInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Resolver constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Takes a class name and returns an instance
     * of the class injected its dependencies resolved
     * from the container.
     *
     * @param string $name
     * @return mixed|object
     * @throws \ReflectionException
     */
    public function resolve($name)
    {
        if (!class_exists($name)) {
            throw new \InvalidArgumentException("$name is not a defined class.");
        }

        $reflection = new \ReflectionClass($name);
        $params = $reflection->getConstructor()
            ->getParameters();
        $classes = array();
        foreach ($params as $param) {
            $class = $param->getClass();
            if (is_null($class)) {
                continue;
            }
            $classes[] = $this->container->get($class->name);
        }
        return $reflection->newInstanceArgs($classes);
    }

    /**
     * Takes a class name and extra named args
     * and returns an instance of the class injected
     * its dependencies resolved from the container
     * as well as the extra arguments passed in.
     *
     * e.g __constructor(Service $service, $argument)
     *  $args will equal [ 'argument' => $toPassIn ]
     *
     * @param string $name
     * @param array $args
     * @return mixed|object
     * @throws \ReflectionException
     */
    public function resolveWith($name, array $args)
    {
        $reflection = new \ReflectionClass($name);
        $params = $reflection->getConstructor()
            ->getParameters();
        $resolvedArgs = array();
        foreach ($params as $param) {
            $class = $param->getClass();
            $arg = null;
            if (is_null($class)) {
                if (isset($args[$param->name])) {
                    $arg = $args[$param->name];
                }
            } else {
                $arg = $this->container->get($class->name);
            }
            $resolvedArgs[] = $arg;
        }
        return $reflection->newInstanceArgs($resolvedArgs);
    }
}