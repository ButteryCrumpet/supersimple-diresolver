<?php

namespace SuperSimpleDIResolver;

/**
 * Interface ResolverInterface
 * @package SuperSimpleDIResolver
 */
interface ResolverInterface
{
    /**
     * @param string $name
     * @return mixed
     */
    public function resolve($name);

    /**
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function resolveWith($name, array $args);
}