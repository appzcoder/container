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
    protected $fooBar;
    protected $name;

    public function __construct(Bar $bar, $name='Sohel Amin', $param2=null) // Dependency Injected
    {
        $this->bar = $bar;
        $this->name = $name;
    }

    public function setterMethod(FooBar $fooBar) // Dependency Injected on method
    {
        return $this->fooBar = $fooBar;
    }
}

class Bar { } // No dependencies

class FooBar { } // No dependencies


// Instantiate the container
$container = new Appzcoder\Container\Container();

// Registering class with dependencies
$container->set('Foo');

// Registering class with another name
$container->set('foo', 'Bar');

// Binding a closure object with a name
$container->setInstance('FooBar', function () {
    return new FooBar();
});

// Registering class with parameters
$container->set('Foo', 'Foo', ['param 1', 'param 2']);

// Binding an instance with a name
$instance = new FooBar();
$container->setInstance('FooBar', $instance);

// Binding an instance/object with container's array
$container['FooBar'] = new FooBar();

// Calling a setter method with dependencies
$container->set('Foo', 'Foo', ['param 1', 'param 2']);
$instance = $container->get('Foo');
$container->call([$instance, 'setterMethod'], ['param 1', 'param 2']);

// Accessing container or getting instances
$instance1 = $container->get('Foo');
$instance2 = $container['Foo']; // For this should have registered or bounded "Foo"

```

##Author

[Sohel Amin](http://www.sohelamin.com)