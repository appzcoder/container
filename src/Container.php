<?php

namespace Appzcoder\Container;

use Psr\Container\ContainerInterface;
use ArrayAccess;
use Closure;
use Exception;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

class Container implements ContainerInterface, ArrayAccess
{
    /**
     * Container's definitions.
     *
     * @var array
     */
    protected $definitions = [];

    /**
     * Container's instances.
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Container's parameters.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Set/Register a class into this container.
     *
     * @param  string $name
     * @param  string $name (optional)
     * @param  string $params (optional)
     *
     * @return void
     */
    public function set($name, $class = null, $params = [])
    {
        if (isset($name) && isset($class)) {
            if (!class_exists($class)) {
                throw new Exception("Your given [$class] is not exist.");
            }

            $this->definitions[$name] = $class;

            if (!empty($params)) {
                $this->parameters[$name] = $params;
            }
        } else {
            if (!class_exists($name)) {
                throw new Exception("Your given [$name] is not exist.");
            }

            $this->definitions[$name] = $name;
        }
    }

    /**
     * Get instance by given a class name or alias name.
     *
     * @param  string $name
     *
     * @return object|null
     */
    public function get($name)
    {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        } elseif (isset($this->definitions[$name])) {
            return $this->resolve($name);
        } else {
            return null;
        }
    }

    /**
     * Determine if a given offset exists.
     *
     * @param  string $key
     *
     * @return boolean
     */
    public function has($key)
    {
        return (isset($this->definitions[$key]) || isset($this->instances[$key]));
    }

    /**
     * Resolve or instantiate object of given name.
     *
     * @param  string $name
     *
     * @return object
     */
    protected function resolve($name)
    {
        $class = $this->definitions[$name];

        $parameters = isset($this->parameters[$name]) ? $this->parameters[$name] : [];

        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new Exception("Your given [$class] is not instantiable.");
        }

        $dependencies = $this->getDependencies($class);
        $instances = [];

        foreach ($dependencies as $key => $class) {
            $offset = is_string($key) ? $key : $class;

            if (isset($this->instances[$offset])) {
                $instances[$offset] = $this->instances[$offset];
            } else {
                $this->set($offset, $class);

                $instances[$offset] = $this->get($offset);
            }
        }

        $parameters = array_merge($instances, $parameters);

        $object = $reflection->newInstanceArgs($parameters);

        $this->setInstance($name, $object);

        return $object;
    }

    /**
     * Register an existing instance into this container.
     *
     * @param  string $name
     * @param  object $instance
     *
     * @return void
     *
     * @throws \Exception
     */
    public function setInstance($name, $instance)
    {
        if (is_object($instance) || $instance instanceof Closure) {
            $this->instances[$name] = $instance;
        } else {
            throw new Exception("Your given instance is not an object or closure.");
        }
    }

    /**
     * Call a method of an instance and inject its dependencies.
     *
     * @param  array $callback
     * @param  array $parameters
     *
     * @return void
     */
    public function call($callback, array $parameters = [])
    {
        $dependencies = $this->getMethodDependencies($callback);

        $instances = [];
        foreach ($dependencies as $dependency) {
            $this->set($dependency);

            $instances[$dependency] = $this->resolve($dependency);
        }

        $parameters = array_merge($instances, $parameters);

        return call_user_func_array($callback, $parameters);
    }

    /**
     * Get all constructor's dependencies by given class.
     *
     * @param  string $class
     *
     * @return array
     */
    protected function getDependencies($class)
    {
        $dependencies = [];

        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor) {
            foreach ($constructor->getParameters() as $param) {
                if ($param->getClass()) {
                    $dependencies[] = $param->getClass()->getName();
                }
            }
        }

        return $dependencies;
    }

    /**
     * Get all dependencies of a given method.
     *
     * @param  callable|array $callback
     *
     * @return array
     */
    protected function getMethodDependencies($callback)
    {
        $dependencies = [];

        if (is_array($callback)) {
            $method = new ReflectionMethod($callback[0], $callback[1]);
        } else {
            $method = new ReflectionFunction($callback);
        }

        if ($method !== null) {
            foreach ($method->getParameters() as $param) {
                if ($param->getClass()) {
                    $dependencies[] = $param->getClass()->getName();
                }
            }
        }

        return $dependencies;
    }

    /**
     * Clear the container.
     *
     * @return void
     */
    public function clear()
    {
        $this->definitions = [];
        $this->instances = [];
        $this->parameters = [];
    }

    /**
     * Determine if a given offset exists.
     *
     * @param  string $key
     *
     * @return boolean
     */
    public function offsetExists($key)
    {
        return (isset($this->definitions[$key]) || isset($this->instances[$key]));
    }

    /**
     * Get the value by given a offset.
     *
     * @param  string $key
     *
     * @return object
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set the value by given a offset.
     *
     * @param  string $key
     * @param  string $value
     *
     * @return object
     */
    public function offsetSet($key, $value)
    {
        return $this->setInstance($key, $value);
    }

    /**
     * Unset the value at a given offset.
     *
     * @param  string $key
     *
     * @return object
     */
    public function offsetUnset($key)
    {
        unset($this->definitions[$key]);
        unset($this->instances[$key]);
        unset($this->parameters[$key]);
    }
}
