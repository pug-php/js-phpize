# JsPhpize
[![Latest Stable Version](https://poser.pugx.org/pug-php/pug/v/stable.png)](https://packagist.org/packages/pug-php/pug)
[![Total Downloads](https://poser.pugx.org/kylekatarnls/jade-php/downloads.png)](https://packagist.org/packages/pug-php/pug)
[![Build Status](https://travis-ci.org/pug-php/pug.svg?branch=master)](https://travis-ci.org/pug-php/pug)
[![StyleCI](https://styleci.io/repos/59010999/shield?style=flat)](https://styleci.io/repos/59010999)
[![Test Coverage](https://codeclimate.com/github/pug-php/pug/badges/coverage.svg)](https://codecov.io/github/pug-php/pug?branch=master)
[![Code Climate](https://codeclimate.com/github/pug-php/pug/badges/gpa.svg)](https://codeclimate.com/github/pug-php/pug)
[![Reference Status](https://www.versioneye.com/php/kylekatarnls:jade-php/reference_badge.svg?style=flat)](https://www.versioneye.com/php/kylekatarnls:jade-php/references)

Convert js-like syntax to standalone PHP code.

## Install
In the root directory of your project, open a terminal and enter:
```shell
composer require js-phpize/js-phpize
```

Use compile to get PHP equivalent code to JavaScript input:
```php
use JsPhpize\JsPhpize;

$jsPhpize = new JsPhpize();

echo $jsPhpize->compile('foo = { bar: { "baz": "hello" } }');
```

Or use render to execute it directly:
```php
use JsPhpize\JsPhpize;

$jsPhpize = new JsPhpize();

$code = '
    // Create an object
    foo = { bar: { "baz": "hello" } };
    key = 'bar'; // instanciate a string

    return foo[key].baz;
';

$value = $jsPhpize->render($code);

echo $value;
```

This will display ```hello```.

This library intend to is intended to allow js-like in PHP contexts (such as in template engines).
