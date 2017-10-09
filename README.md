RAD Micro Framework
==========================

## WARNING WIP Framework

## What is RAD?
RAD for Rest API Dedicated Framework.

## Installation

[PHP](https://php.net) 7.0+ and [Composer](https://getcomposer.org) are required.

To get the latest version of RAD Framework, simply add the following line to the require block of your `composer.json` file:

```
"rad/rad-framework": "dev-master"
```

## Usage

```php
class App extends Api {
    public function addControllers(){
        return array(
            MyController::class
        );
    }
}

class MyController extends Controller{
    /**
     * @api 1
     * @get /
     * @produce html
     */
    public function helloWorld(Request $request,Response $response,Route $route){
        $api->getResponse()->setData("<b>Hello World</b>");
    }
}

```

## PSR Support

* [psr-3](http://www.php-fig.org/psr/psr-3/)
* [psr-4](http://www.php-fig.org/psr/psr-4/)
* [psr-7](http://www.php-fig.org/psr/psr-7/) WIP
* [psr-16](http://www.php-fig.org/psr/psr-16/) WIP



