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
class A
{
    protected $b;
    protected $c;
    protected $name;

    public function __construct(B $b, $name)
    {
        $this->b = $b;
        $this->name = $name;
    }

    public function setterMethod(C $c)
    {
        return $this->c = $c;
    }
}

class B { }

class C { }

class D { }

class Foo { }

class Bar { }

class FooBar { }

// Instantiate the container
$container = Container::getInstance();

// Registering class A with it's dependency with parameters
$classA = $container->make('A', 'A', ['sohel']);

// Calling a Setter Method of a instance with parameters
var_dump($container->call([$classA, 'setterMethod'], ['sohel amin']));

// Registering a class by given the class name and index
$container->make('ClassIndex', 'Foo');

// Registering a instance with closure
$container->make('Bar', function () {
	return new Bar();
});

// Registering a instance with a name
$fooBar = new FooBar();
$container->instance('FooBar', $fooBar);

// Registering a class via container's array
$container['D'] = new D;

// Getting a instance from container's array
$classD = $container['D'];

```

##Author

[Sohel Amin](http://www.sohelamin.com)
