# urimanage
A library to parse, format and modify URIs following the [PSR-7 URI interface](https://www.php-fig.org/psr/psr-7/])

## Installation
Install via [composer](https://getcomposer.org):
```
composer require urimanage/urimanage
```

## Simple Usage
```php
$uri = new \UriManage\Uri('https://github.com/filecage/urimanage');

echo $uri->getScheme();  // https
echo $uri->getHost();    // github.com
echo $uri->getPath();    // /filecage/urimanage
echo $uri->isAbsolute(); // true
```

## Full API Reference
For a full reference of all available methods supported by PSR-7, please
[see here](https://github.com/php-fig/http-message/blob/efd67d1dc14a7ef4fc4e518e7dee91c271d524e4/docs/PSR7-Interfaces.md#psrhttpmessageuriinterface-methods).
