# Dependency Injection Container
Dependency Injection Container


## Installation

1. Run 
    ```
    composer require appzcoder/container:"dev-master"
    ```
    
2. Add bellow lines to your script
	```php
	require 'vendor/autoload.php';
	```

## Usage

```php
class Foo // Has dependencies
{
    protected $bar;
    protected $anotherClass;
    protected $name;

    public function __construct(Bar $bar, $name)
    {
        $this->bar = $bar;
        $this->name = $name;
    }

    public function setterMethod(AnotherClass $anotherClass)
    {
        return $this->anotherClass = $anotherClass;
    }
}

class FooBar { } // No dependencies

class AnotherClass { } // No dependencies


// Instantiate the container
$container = Container::getInstance();

// Registering class with dependencies
$container->make('Foo');

// Registering class with another name
$container->make('foo', 'Foo');

// Binding a closure object with a name
$container->make('FooBar', function () {
    return new FooBar();
});

// Registering class with parameters
$container->make('Foo', 'Foo', ['param 1', 'param 2']);

// Binding an instance with a name
$instance = new FooBar();
$container->instance('FooBar', $instance);

// Binding an instance/orbject with container's array
$container['FooBar'] = new FooBar();

// Calling a setter method with dependencies
$instance = $container->make('Foo', 'Foo', ['param 1', 'param 2']);
$container->call([$instance, 'setterMethod'], ['param 1', 'param 2']);

// Accessing container or getting instances
$instance1 = $container->make('Foo');
$instance2 = $container['Foo']; // For this should have registered or bounded "Foo"

```

##Author

[Sohel Amin](http://www.sohelamin.com)