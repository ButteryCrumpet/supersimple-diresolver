<?php

namespace SuperSimpleDIResolver;

use Psr\Container\ContainerInterface;

/**
 * Class Resolver
 * @package SuperSimpleDIResolver
 */
class Resolver
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
    public function resolve($name, $args = array())
    {
        if (!class_exists($name)) {
            if ($this->container->has($name)) {
                return $this->container->get($name);
            }
            throw new \InvalidArgumentException(
                "$name is not a defined class or container service name."
            );
        }

        $reflection = new \ReflectionClass($name);
        $params = $reflection
            ->getConstructor()
            ->getParameters();
        $classes = array();
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
            $classes[] = $arg;
        }
        return $reflection->newInstanceArgs($classes);
    }
}