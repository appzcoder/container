<?php

namespace Appzcoder\Container;

use ArrayAccess;
use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

class Container implements ArrayAccess
{

    /**
     * Instance of this class.
     *
     * @var static
     */
    protected static $instance;

    /**
     * Container's instances.
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Let the container access globally.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Register class to create instances based on types.
     *
     * @param  string $name
     * @param  \Closure|string|object $class
     * @param  array  $parameters
     * @return object
     */
    public function make($name, $class = null, array $parameters = [])
    {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        } elseif (isset($class) && class_exists($class)) {
            return $this->instances[$name] = $this->build($class, $parameters);
        } elseif (isset($class) && (is_object($class) || $class instanceof Closure)) {
            return $this->instances[$name] = $class;
        } else {
            return $this->instances[$name] = $this->build($name, $parameters);
        }
    }

    /**
     * Register an existing instance into this container.
     *
     * @param  string $name
     * @param  object $instance
     * @return object
     *
     * @throws \Exception
     */
    public function instance($name, $instance)
    {
        if (is_object($instance)) {
            return $this->instances[$name] = $instance;
        }

        throw new \Exception("Your given instance is not an object.");
    }

    /**
     * Call a method of an instance and inject its dependencies.
     *
     * @param  array $callback
     * @param  array $parameters
     * @return void
     */
    public function call($callback, array $parameters = [])
    {
        $dependencies = $this->getMethodDependencies($callback);
        $instances = $this->makeBulk($dependencies);
        $parameters = array_merge($instances, $parameters);

        return call_user_func_array($callback, $parameters);
    }

    /**
     * Instantiate object of given class.
     *
     * @param  string $class
     * @param  array  $parameters
     * @return object
     *
     * @throws \Exception
     */
    protected function build($class, array $parameters = [])
    {
        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new \Exception("Your given [$class] is not instantiable.");
        }

        $dependencies = $this->getDependencies($class);
        $instances = $this->makeBulk($dependencies);
        $parameters = array_merge($instances, $parameters);

        $object = $reflection->newInstanceArgs($parameters);

        return $object;
    }

    /**
     * Get all constructor's dependencies by given class.
     *
     * @param  string $class
     * @return array
     */
    protected function getDependencies($class)
    {
        $dependencies = [];

        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor !== null) {
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
     * Make bulk instances of given array of classes.
     *
     * @param  array $classes
     * @return array
     */
    public function makeBulk(array $classes)
    {
        $instances = [];
        foreach ($classes as $key => $class) {
            $offset = is_string($key) ? $key : $class;
            $instances[$offset] = $this->make($offset, $class);
        }

        return $instances;
    }

    /**
     * Clear all registered instances or classes.
     *
     * @return void
     */
    public function clear()
    {
        $this->instances = [];
    }

    /**
     * Determine if a given offset exists.
     *
     * @param  string $key
     * @return boolean
     */
    public function offsetExists($key)
    {
        return isset($this->instances[$key]);
    }

    /**
     * Get the value at a given offset.
     *
     * @param  string $key
     * @return object
     */
    public function offsetGet($key)
    {
        return isset($this->instances[$key]) ? $this->instances[$key] : null;
    }

    /**
     * Set the value at a given offset.
     *
     * @param  string $key
     * @param  string $value
     * @return object
     */
    public function offsetSet($key, $value)
    {
        return $this->make($key, $value);
    }

    /**
     * Unset the value at a given offset.
     *
     * @param  string $key
     * @return object
     */
    public function offsetUnset($key)
    {
        unset($this->instances[$key]);
    }
}
